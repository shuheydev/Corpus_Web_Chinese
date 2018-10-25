<?php
	//ライブラリの読み込み
	//相対パスによる指定はコストが高いらしい
	//session_startよりも前に置く。
	require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/SqliteManipulatorClass.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/StringManipulatorClass.php");
	
	//xml出力を指定。
	//header("Content-Type: text/xml");
	
	//json出力を指定
	header("Content-Type: application/json; charset=utf-8");
	
	//セッションファイル保存先 
	//session_save_path('/virtual/cjhonyaku/session');
	//session_save_path('C:\xampp\htdocs\corpus\session');
	
	//セッション開始。
	session_start();
	

	
	
	//DBに接続
	$sqliteManipulator=new SqliteManipulator($_SERVER['DOCUMENT_ROOT']."/corpus/db/OfficialDB.db");


	
	$inputWord_ch="";
	$inputWord_jp="";
	$inputWord_ch_except="";
	$inputWord_jp_except="";
	
		
	//検索文字列を取得
	if(isset($_GET['word_ch']))
	{
		$inputWord_ch=StringManipulator::TrimForSearch($_GET['word_ch']);
		//$_SESSION['word_ch']=$inputWord_ch;
	}
	if(isset($_GET['word_jp']))
	{
		$inputWord_jp=StringManipulator::TrimForSearch($_GET['word_jp']);
		//$_SESSION['word_jp']=$inputWord_jp;
	}
	
	//除外文字列を取得
	if(isset($_GET['word_ch_except']))
	{
		$inputWord_ch_except=StringManipulator::TrimForSearch($_GET['word_ch_except']);
		//$_SESSION['word_ch_except']=$inputWord_ch_except;
	}
	if(isset($_GET['word_jp_except']))
	{
		$inputWord_jp_except=StringManipulator::TrimForSearch($_GET['word_jp_except']);
		//$_SESSION['word_jp_except']=$inputWord_jp_except;
	}
	
	$selectedDataTables=array();
	//対象のテーブルを取得
	if(isset($_GET['targetTables']))
	{
		$selectedDataTables=$_GET['targetTables'];
		//$_SESSION['targetTables']=$selectedDataTables;
	}
		
	$selectedIPC=array();
	//絞り込み対象のIPC分野を取得
	if(isset($_GET['targetIPC']))
	{
		$selectedIPC=$_GET['targetIPC'];
	}
	
	$pageNumber="";
	if(isset($_GET['pageNumber']))
	{
		$pageNumber=$_GET['pageNumber'];
	}
	
	$displayNumPerPage="";
	if(isset($_GET['displayNumPerPage']))
	{
		$displayNumPerPage=$_GET['displayNumPerPage'];
	}
	
	
	//セッション変数がない場合は空の変数を作成しておく。
	if(isset($_SESSION['word_ch'])==false)
	{
		$_SESSION['word_ch']="";
	}
	if(isset($_SESSION['word_jp'])==false)
	{
		$_SESSION['word_jp']="";
	}
	if(isset($_SESSION['word_ch_except'])==false)
	{
		$_SESSION['word_ch_except']="";
	}
	if(isset($_SESSION['word_jp_except'])==false)
	{
		$_SESSION['word_jp_except']="";
	}	
	if(isset($_SESSION['selectedDataTables'])==false)
	{
		$_SESSION['selectedDataTables']=array();
	}		
	if(isset($_SESSION['selectedIPC'])==false)
	{
		$_SESSION['selectedIPC']=array();
	}
	if(isset($_SESSION['pageNumber'])==false)
	{
		$_SESSION['pageNumber']="";
	}
	if(isset($_SESSION['displayNumPerPage'])==false)
	{
		$_SESSION['displayNumPerPage']="";
	}
	
		
	//検索条件が前回と比べて変更があるかをチェック。
	//なければ検索は行わずにセッション変数に格納されている検索結果を使用する。
	$isSameSearchCondition=true;
	if($inputWord_ch!=$_SESSION['word_ch'])
	{
		$isSameSearchCondition=false;
	}
	if($inputWord_jp!=$_SESSION['word_jp'])
	{
		$isSameSearchCondition=false;
	}
	if($inputWord_ch_except!=$_SESSION['word_ch_except'])
	{
		$isSameSearchCondition=false;
	}
	if($inputWord_jp_except!=$_SESSION['word_jp_except'])
	{
		$isSameSearchCondition=false;
	}
	if($selectedDataTables!=$_SESSION['selectedDataTables'])
	{
		$isSameSearchCondition=false;
	}
	/*
	if($selectedIPC!=$_SESSION['selectedIPC'])
	{
		$isSameSearchCondition=false;
	}
	*/
	if($isSameSearchCondition==true)
	{
		//echo "second<br/>";
		if(isset($_SESSION['sqliteManipulator']))
		{
			$sqliteManipulator=$_SESSION['sqliteManipulator'];
		}		
	
	}
	else
	{
		//echo "first<br/>";
		//新規検索なので、検索条件をセッション変数に格納する
		$_SESSION['word_ch']=$inputWord_ch;
		$_SESSION['word_jp']=$inputWord_jp;
		$_SESSION['word_ch_except']=$inputWord_ch_except;
		$_SESSION['word_jp_except']=$inputWord_jp_except;
		$_SESSION['selectedDataTables']=$selectedDataTables;
		
		
		//DBを検索して結果を取得
		//検索結果はインスタンスのプロパティtotalResultに入る。	
		$sqliteManipulator->SearchFullTextFromTables($inputWord_ch,$inputWord_jp,$inputWord_ch_except,$inputWord_jp_except,$selectedDataTables,"2000");
		
		//検索結果をセッション変数に格納。
		$_SESSION['sqliteManipulator']=$sqliteManipulator;	
	}
	

	//ここでクローンしておかないと絞り込み時に$sqliteManipulator経由で$_SESSION['sqliteManipulator']も変わってしまう。
	$returnData=clone $sqliteManipulator;
	

	//IPCによる絞り込み処理
	$returnData->FilterSearchResultByIPC($selectedIPC);

	//$sqliteManipulator->FilterSearchResultByIPC($selectedIPC);
	//var_dump($returnData);
	/*
	$returnData=new SqliteManipulator(null);
	$returnData->totalResult=array_slice($data->totalResult,0,1);
	$returnData->totalResultCount=$data->totalResultCount;
	$returnData->lastSearchElapsedTime=$data->lastSearchElapsedTime;
	$returnData->jSectionCount=$data->jSectionCount;
	*/
	
	//JSON形式で出力。
	////ただし、結果は先頭の100個のみ
	//$returnData->totalResult=array_slice($returnData->totalResult,0,1);
	//$sqliteManipulator->totalResult=array_slice($sqliteManipulator->totalResult,0,100);
	if($isSameSearchCondition==true)
	{
		$startIndex=$pageNumber*$displayNumPerPage;
		//$returnData->totalResult=array_slice($returnData->totalResult,$startIndex,$displayNumPerPage);
	}
	else
	{
		//$returnData->totalResult=array_slice($returnData->totalResult,0,$displayNumPerPage);
	}
	
	echo json_encode($returnData,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);


?>