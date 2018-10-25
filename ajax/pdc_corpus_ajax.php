<?php
	//エラーを表示する
	error_reporting(E_ALL);
    ini_set('display_errors','On');
    
//POST送信のときだけ	
if(!empty($_POST))
{	
    // if(!empty($_POST)){
    //     echo print_r($_POST,true);
    // }

    //ライブラリの読み込み
	//相対パスによる指定はコストが高いらしい
	//一度だけ読み込む。
	require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/SqliteManipulatorClass.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/StringManipulatorClass.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/PDCCorpusUtility.php");


    //セッションファイル保存先を設定
	PDCCorpusUtility::SetSessionSavePath();
	//セッションを使えるようにする。
	session_name('corpus');
    session_start();


    //ログインしていない状態でこのページを訪問した場合はログイン画面へ飛ばす。
	//if(empty($_SESSION['login'])) header('location:corpus_login.php');
    
	//POSTメソッドで送られてきた情報をSESSION変数に格納する
	function PostToSession()
	{
		//HTMLが更新される前に、現在のテキストボックス、チェックボックスの状態を保存する。
		//テキストボックスの場合は、入力がないと_POSTにその連想配列が作成されない。
		if(isset($_POST['word_ch'])){
			$_SESSION['word_ch']=@$_POST['word_ch'];
		}
		else{
			if(isset($_SESSION['word_ch']))
			{
			}
			else
			{
				$_SESSION['word_ch']="";
			}
		}
		if(isset($_POST['word_ch_except'])){
			$_SESSION['word_ch_except']=@$_POST['word_ch_except'];
		}
		else{
			if(isset($_SESSION['word_ch_except']))
			{
			}
			else
			{
				$_SESSION['word_ch_except']="";
			}
		}
			
		if(isset($_POST['word_jp'])){
			$_SESSION['word_jp']=@$_POST['word_jp'];
		}
		else{
			if(isset($_SESSION['word_jp']))
			{
			}
			else
			{
				$_SESSION['word_jp']="";
			}
		}
		if(isset($_POST['word_jp_except'])){
			$_SESSION['word_jp_except']=@$_POST['word_jp_except'];
		}
		else{
			if(isset($_SESSION['word_jp_except']))
			{
			}
			else
			{
				$_SESSION['word_jp_except']="";
			}
		}
		
		
		if(isset($_POST['selectedCheckBox']))
		{
			//選択されたセクションをセッションに保存
			$_SESSION['selectedCheckBox']=@$_POST['selectedCheckBox'];
		}
		else
		{
			if(isset($_POST['action']))
			{
				if($_POST['action']=="search")
				{
					$_SESSION['selectedCheckBox']=array();
				}
			}
		}


		if(isset($_POST['displayStatus']))
		{
			$_SESSION['displayStatus']=$_POST['displayStatus'];
		}
		else
		{
			if(isset($_POST['action']))
			{
				if($_POST['action']=="search")
				{
					$_SESSION['displayStatus']=array();
				}
			}
		}
		
		if(isset($_SESSION['displayStatus']))
		{
			foreach($_SESSION['displayStatus'] as $displayStatus)
			{
				if($displayStatus=="1")
				{
					$displayStatus=false;
				}
				else
				{
					$displayStatus=true;
				}
			}
		}		

		if(isset($_POST['sectionCheckedStatus']))
		{
			$_SESSION['sectionCheckedStatus']=$_POST['sectionCheckedStatus'];
		}
		else
		{
			$_SESSION['sectionCheckedStatus']=array();
		}
		foreach($_SESSION['sectionCheckedStatus'] as $displayStatus)
		{
			if($displayStatus=="1")
			{
				$displayStatus=false;
			}
			else
			{
				$displayStatus=true;
			}
		}

	}

	

	//1ページの表示件数
	$displayNumPerPage=50;



    //POSTからSESSIONへデータを移す。必要。
    //PostToSession();
    

    //DB(SQLite)に接続
	$sqliteManipulator=new SqliteManipulator($_SERVER['DOCUMENT_ROOT']."/corpus/db/OfficialDB.db");
	//テーブル名のリストを取得。
    $dataTables=$sqliteManipulator->GetDataTables();
    

    //選択されたセクションのデータテーブルのリストを取得する。
	if(isset($_POST['selectedDictionaries']))
	{
		$selectedDataTables=$_POST['selectedDictionaries'];
	}
	else
	{
		$selectedDataTables=array();
    }

    //検索語を取得する。
    $inputWord_ch=$_POST['searchWords']['word_ch'];
    $inputWord_jp=$_POST['searchWords']['word_jp'];
    $inputWord_ch_except=$_POST['searchWords']['word_ch_except'];
    $inputWord_jp_except=$_POST['searchWords']['word_jp_except'];

    
    //echo print_r($selectedDataTables,true);


    if(isset($_POST['action']))
	{
		if($_POST['action']=="search")
		{
			
			//DBを検索して結果を取得
			//検索結果はインスタンスのプロパティtotalResultに入る。
			$sqliteManipulator->SearchFullTextFromTables($inputWord_ch,$inputWord_jp,$inputWord_ch_except,$inputWord_jp_except,$selectedDataTables,"1000000000");
			
			$_SESSION['sqliteManipulator']=$sqliteManipulator;
			
			$_SESSION['pageNumber']=0;

		}
		elseif($_POST['action']=="next")
		{
			$sqliteManipulator=$_SESSION['sqliteManipulator'];
			$_SESSION['pageNumber']+=1;
			
			if($_SESSION['pageNumber']*$displayNumPerPage+1>$sqliteManipulator->totalResultCount)
			{
				$_SESSION['pageNumber']-=1;
			}
		}
		elseif($_POST['action']=="prev")
		{
			$sqliteManipulator=$_SESSION['sqliteManipulator'];
			$_SESSION['pageNumber']-=1;
			if($_SESSION['pageNumber']<0)
			{
				$_SESSION['pageNumber']=0;
			}
		}
		elseif($_POST['action']=="logout")
		{
			$_SESSION=array();
			session_destroy();
			
			header("Location:corpus_login.php");
			exit;
		}
    }


    //ページ数を計算
    $startIdx=$_SESSION['pageNumber']*$displayNumPerPage;
    $loopNum=$displayNumPerPage;
    if($startIdx+$loopNum>$sqliteManipulator->totalResultCount)
    {
        $loopNum=$sqliteManipulator->totalResultCount-$startIdx;
    }

    //データを返す
    //action
    //検索結果(HTMLテーブル)
    //総件数
    //各辞書ごとの件数
    $result=array(
        'action'=>$_POST['action'],
        'resultHTML'=>$sqliteManipulator->ComposeHTMLTableFromSearchResult($startIdx,$loopNum,$inputWord_ch,$inputWord_jp),
        'totalCount'=>$sqliteManipulator->totalResultCount,
        'countEachDictionary'=>$sqliteManipulator->jSectionCount,
        'searchResultInfoText'=>$sqliteManipulator->ComposeSearchResultInfo(),
        'totalPageCount'=>floor(($sqliteManipulator->totalResultCount / $displayNumPerPage)+1),
        'currentPageNum'=>$_SESSION['pageNumber']+1,
        'currentPageRange'=>($startIdx+1)."～".($startIdx+$loopNum),
    );

    echo json_encode($result);
    //1ページ目はあらかじめセットしておく
    //echo $sqliteManipulator->ComposeHTMLTableFromSearchResult($startIdx,$loopNum,$inputWord_ch,$inputWord_jp);

    //echo print_r($selectedDataTables,true);
}
?>