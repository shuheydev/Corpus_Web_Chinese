<?php
	//json出力を指定
	header("Content-Type: application/json; charset=utf-8");

	//ライブラリの読み込み
	//相対パスによる指定はコストが高いらしい
	require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/SqliteManipulatorClass.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/corpus/php/StringManipulatorClass.php");

	//DBに接続
	$sqliteManipulator=new SqliteManipulator($_SERVER['DOCUMENT_ROOT']."/corpus/db/OfficialDB.db");
	
	$tableNames=array();
	foreach($sqliteManipulator->GetDataTables() as $tableName)
	{
		//$tableNames[]=array('tableName'=>$tableName);
		$tableNames[]=$tableName;
	}
	
	//JSON形式で出力。
	echo json_encode($tableNames,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>