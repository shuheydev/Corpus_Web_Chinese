<?php

error_reporting(E_ALL);//E_STRICTレベル以外のエラーを報告する
ini_set('display_errors','On');//画面にエラーを表示させる

//ライブラリの読み込み
//相対パスによる指定はコストが高いらしい
//一度だけ読み込む。
require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/PDCCorpusUtility.php");
    
//post通信の場合のみ
if(!empty($_POST)){



        //変数にユーザー情報を代入
        $userID=$_POST['userID'];
        $userPass=$_POST['pass'];

        //ユーザーアカウントDBへの接続準備
        $dbHost='localhost';
        $dbUser='cjhonyaku_db';
        $dbPassword='cjhonyaku';
        $dsn='mysql:dbname=cjhonyaku_db;host='.$dbHost.';charset=utf8';

        $options=array(
            //SQL実行失敗時に例外をスロー
            PDO::ATTR_ERRMODE =>PDO::ERRMODE_EXCEPTION,
            //デフォルトフェッチモードを連想配列形式に設定
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            //バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
            //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true,
        );


        //PDOオブジェクト生成(DBへ接続)
        $dbh=new PDO($dsn,$dbUser,$dbPassword,$options);

        //SQL文(クエリー)作成
        $stmt=$dbh->prepare('SELECT * FROM userData WHERE name=:userID AND password=:userPass');

        //プレースホルダに値をセットし、SQL文を実行
        $stmt->execute(array(':userID'=>$userID,':userPass'=>$userPass));

        $result=0;

        $result=$stmt->fetch(PDO::FETCH_ASSOC);


                //echo json_encode($_POST);

        //結果が0でない場合
        if(!empty($result)){

            //セッションファイル保存先を設定
            PDCCorpusUtility::SetSessionSavePath();
            //SESSIONを使うのにsession_start()を呼び出す
            session_name('corpus');
            session_start();
            
            //SESSION['login']に値を代入
            $_SESSION['login']=true;

            //検索ページへ移動
            //headerメソッドは、このメソッドを実行する前にechoなど画面出力処理を行っているとエラーになる。
            // header("Location:../pdccorpus.php");
            // exit;

            echo json_encode(array('result'=>true));
        }
        else
        {
            //$loginErrorMessage="ユーザ名またはパスワードが間違っています。";
            echo json_encode(array('result'=>false));
        }

}
?>