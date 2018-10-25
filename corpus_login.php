<?php

//error_reporting(E_ALL);//E_STRICTレベル以外のエラーを報告する
//ini_set('display_errors','On');//画面にエラーを表示させる
//
////ライブラリの読み込み
////相対パスによる指定はコストが高いらしい
////一度だけ読み込む。
//require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/PDCCorpusUtility.php");
//
//    
////post通信されていなかった場合
//if(!empty($_POST)){
//
//
//    //項目に入力があるかをチェック
//    define('MSG01','入力必須です');
//    define('MSG02','IDまたはパスワードが違います。');
//    define('MSG03','ユーザー名には半角英数字及び「_」のみが使用できます');
//    define('MSG04','パスワードには半角英数字のみが使用できます');
//
//    //エラーメッセージを入れる配列
//    //連想配列として使う
//    $err_msg=array();
//
//    //ユーザーIDが未入力
//    if(empty($_POST['userID'])){
//        $err_msg['userID']=MSG01;
//    }
//    //パスワードが未入力
//    if(empty($_POST['pass'])){
//        $err_msg['pass']=MSG01;
//    }
//
//    //入力自体はされていた場合
//    //入力内容についてバリデーションを行う
//    if(empty($err_msg)){
//
//        //入力内容に変なプログラム、サーバーに対して攻撃をおこなうものなど
//        //を無効化するための処理を行う必要がある。
//        $userID=htmlspecialchars($_POST['userID'],ENT_QUOTES,'utf-8');
//        $pass=htmlspecialchars($_POST['pass'],ENT_QUOTES,'utf-8');
//
//        //ユーザー名のチェック
//        if(!preg_match('/^[a-zA-Z0-9_]+$/',$userID)){
//            $err_msg['userID']=MSG03;
//        }
//
//        //パスワードのチェック
//        if(!preg_match('/^[a-zA-Z0-9]+$/',$pass)){
//            $err_msg['pass']=MSG04;
//        }
//
//
//    }
//
//
//    //ここまででバリデーションチェックにひっかかっていない場合、
//    //データベースと照合
//    if(empty($err_msg))
//    {
//        //変数にユーザー情報を代入
//        $userID=$_POST['userID'];
//        $userPass=$_POST['pass'];
//
//        //ユーザーアカウントDBへの接続準備
//        //
//        $dbHost='localhost';
//        $dbUser='cjhonyaku_db';
//        $dbPassword='cjhonyaku';
//        $dsn='mysql:dbname=cjhonyaku_db;host='.$dbHost.';charset=utf8';
//
//        $options=array(
//            //SQL実行失敗時に例外をスロー
//            PDO::ATTR_ERRMODE =>PDO::ERRMODE_EXCEPTION,
//            //デフォルトフェッチモードを連想配列形式に設定
//            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
//            //バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
//            //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
//            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true,
//        );
//
//
//        //PDOオブジェクト生成(DBへ接続)
//        $dbh=new PDO($dsn,$dbUser,$dbPassword,$options);
//
//        //SQL文(クエリー)作成
//        $stmt=$dbh->prepare('SELECT * FROM userData WHERE name=:userID AND password=:userPass');
//
//        //プレースホルダに値をセットし、SQL文を実行
//        $stmt->execute(array(':userID'=>$userID,':userPass'=>$userPass));
//
//        $result=0;
//
//        $result=$stmt->fetch(PDO::FETCH_ASSOC);
//
//
//        //結果が0でない場合
//        if(!empty($result)){
//
//            //セッションファイル保存先を設定
//            PDCCorpusUtility::SetSessionSavePath();
//            //SESSIONを使うのにsession_start()を呼び出す
//            session_name('corpus');
//            session_start();
//            
//            //SESSION['login']に値を代入
//            $_SESSION['login']=true;
//
//            //検索ページへ移動
//            //headerメソッドは、このメソッドを実行する前にechoなど画面出力処理を行っているとエラーになる。
//            header("location:pdccorpus.php");
//        }
//        else
//        {
//            $loginErrorMessage="ユーザ名またはパスワードが間違っています。";
//        }
//    }
//}
?>




<!DOCTYPE html>

<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>PDCコーパスWeb　中日　ログイン</title>
    <link rel="stylesheet" type="text/css" href="./css/pdc_corpus.css">
    <!-- ログイン画面はスタイル指定が多くないのでHTML内に直接記述した -->
    <style>
 
        .site-width{
            width:35%;
            margin: 0 auto;
        }

        main{
            padding-bottom: 50px;
        }

        #form{
            overflow: hidden;
        }
        input[type="text"]{
            color: #545454;
            height: 60px;
            width: 100%;
            padding: 5px 10px;
            font-size: 16px;
            display: block;
            margin-bottom: 20px;
            box-sizing: border-box;
            background: white;
        }
        input[type="password"]{
            color: #545454;
            height: 60px;
            width: 100%;
            padding: 5px 10px;
            font-size: 16px;
            display: block;
            margin-bottom: 20px;
            box-sizing: border-box;
            
        }
        button[type="submit"]{
            border:none;
            padding:15px 30px;

            background: #0068b6;
            color:white;
            float:right;
            margin-bottom:20px;

        }
        button[type="submit"]:hover{
            background: #111;
            cursor: pointer;
        }

        .err_msg{
            color: #ff4b4b;
        }

        h2.textCenter{
            text-align:center;
            background:#0068b6;
            color:white;
            border-radius: 0 0 10px 10px / 0 0 10px 10px;
            -webkit-border-radius: 0 0 10px 10px / 0 0 10px 10px;
            -moz-border-radius: 0 0 10px 10px / 0 0 10px 10px;
        }
        
        span#loginErrorMessage{
            color: #FF1493;
            font-weight: bold;
        }
        #div_loginPanel{
            margin:80px 20px;
            padding:0 20px;
            box-shadow:5px 5px 20px gray;
            border-radius: 3px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
        }
        .textCenter{
            margin-bottom:20px;
            padding:20px 0;
        }
    </style>
</head>

<body>
    <div id="container">
        <header>
            <img id="img_pdc_logo" src="./img/PDC_Logo_Original.png">
            <h1 id='title'>PDCコーパスWeb　中日</h1>
        </header>
        <main class='site-width'>
            
            <div id='div_loginPanel'>
                <h2 class='textCenter'>ログイン</h2>
                <span id="loginErrorMessage"></span>
                <form id="form" method="post">
                    <span id='err_msg_userID' class="err_msg">&nbsp;</span>
                    
                    <input class='js_userID' type="text" name="userID" placeholder="ユーザーID" value="<?php if(!empty($_POST['userID'])) echo htmlspecialchars( $_POST['userID'],ENT_QUOTES,'utf-8');?>">
                    
                    <span id='err_msg_password' class="err_msg">&nbsp;</span>
                    
                    <input class='js_password' type="password" name="pass" placeholder="パスワード" value="<?php if(!empty($_POST['pass'])) echo htmlspecialchars( $_POST['pass'],ENT_QUOTES,'utf-8');?>">
                    
                    <button class='js_button_submit' type="submit" name='login' value="login_chinese">ログイン</button>
                </form>                
                
<!--
                <span id="loginErrorMessage"><?php if(!empty($loginErrorMessage)) echo $loginErrorMessage ?></span>
                <form id="form" method="post">
                    <span class="err_msg"><?php if(!empty($err_msg['userID'])) echo $err_msg['userID']; ?></span>
                    <input type="text" name="userID" placeholder="ユーザーID" value="<?php if(!empty($_POST['userID'])) echo htmlspecialchars( $_POST['userID'],ENT_QUOTES,'utf-8');?>">
                    <span class="err_msg"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass'] ?></span>
                    <input type="password" name="pass" placeholder="パスワード" value="<?php if(!empty($_POST['pass'])) echo htmlspecialchars( $_POST['pass'],ENT_QUOTES,'utf-8');?>">
                    <button type="submit" name='login' value="login_chinese">ログイン</button>
                </form>
-->
            </div>

        </main>

        <footer>
            Copyright <a href="http://www.pdc.co.jp/">特許デイタセンター</a>. All Rights Reserved.
        </footer>
    </div>
    
    <script type='text/javascript' src='./js/jquery-3.3.1.min.js'></script>
    <script type='text/javascript' src='./js/corpus_login.js'></script>
</body>

</html>