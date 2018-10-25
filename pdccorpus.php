<?php
	//エラーを表示する
	error_reporting(E_ALL);
	ini_set('display_errors','On');


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
	if(empty($_SESSION['login'])) header('location:corpus_login.php');
	

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

	function CreateChineseInputField()
	{
		echo "<div id='div_ChineseInputField' class='div_inputField floatLeft'>";
		echo "中国語<br/>";
		//テキストボックスの生成、入力内容の復元
		if($_SESSION['word_ch']!=""){
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_ch' value='".$_SESSION['word_ch']."' >";
		}
		else{
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_ch' value=''>";
		}
		echo "</div>";
	}

	function CreateChineseExceptInputField()	
	{
		echo "<div id='div_ChineseExceptInputField' class='div_inputField floatLeft'>";
		echo "中国語除外<br/>";
		//テキストボックスの生成、入力内容の復元
		if($_SESSION['word_ch_except']!=""){
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_ch_except' value='".$_SESSION['word_ch_except']."' >";
		}
		else{
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_ch_except' value=''>";
		}
		echo "</div>";
	}
	
	function CreateJapaneseInputField()
	{
		echo "<div id='div_JapaneseInputField' class='div_inputField floatLeft'>";
		echo "日本語<br/>";
		//テキストボックスの生成、入力内容の復元
		if($_SESSION['word_jp']!=""){
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_jp' value='".$_SESSION['word_jp']."' >";
		}
		else{
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_jp' value=''>";
		}
		echo "</div>";
	}
	
	function CreateJapaneseExceptInputField()
	{
		echo "<div id='div_JapaneseExceptInputField' class='div_inputField floatLeft js_wink'>";
		echo "日本語除外<br/>";
		//テキストボックスの生成、入力内容の復元
		if($_SESSION['word_jp_except']!=""){
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_jp_except' value='".$_SESSION['word_jp_except']."'>";
		}
		else{
			echo "<input class='input_searchWordInputField js_searchWordInputField' type='text' name='word_jp_except' value=''>";
		}
		echo "</div>";
	}
	
	

	
	function CreateJSectionCheckBoxField($_dataTables)
	{
		global $jCaption;
		global $sqliteManipulator;
		

		echo "<div id='JSectionCheckBoxField'>";
		
		//テーブルのリストからJ分類の１文字目ECMPのリストを取得する。
		$japioSectionNameArray=array();		
		foreach($_dataTables as $table){
			$japioSectionName=mb_substr($table,3,1);//例：「TM_C01」→「C」
			$japioSectionNameArray[$japioSectionName]=$japioSectionName;
		}

		//まずは大項目を作る
		echo "<ul class='topLevel'>";
		foreach($japioSectionNameArray as $japioSectionName)
		{
			echo "<li id='".$japioSectionName."'>";//C,E,M,Pの階層
			//C,E,M,Pそれぞれのアコーディオンが開いているかどうかの状態を保持するためのもの
			//隠しデータ。
			//表示はされないが、現在の開閉状態を保持しておき、フォーム送信時に送られる。
			if(isset($_SESSION['displayStatus'][$japioSectionName]))
			{
				echo "<input type='hidden' id='displayStatus_".$japioSectionName."' name='displayStatus[".$japioSectionName."]' value='".$_SESSION['displayStatus'][$japioSectionName]."'>";
			}
			else
			{
				echo "<input type='hidden' id='displayStatus_".$japioSectionName."' name='displayStatus[".$japioSectionName."]' value='false'>";
			}
			
			//C,E,M,Pそれぞれのチェックボックスの状態を保持するためのもの。
			//隠しデータ。
			//true,indeterminate,falseの３状態
			//表示はされないが、現在のチェック状態を保持しておき、フォーム送信時に送られる。
			if(isset($_SESSION['sectionCheckStatus'][$japioSectionName]))
			{
				//nameにキーを指定して配列に入れることができる
				echo "<input type='hidden' id='sectionCheckStatus_".$japioSectionName."' name='sectionCheckStatus[".$japioSectionName."]' value='".$_SESSION['sectionCheckStatus'][$japioSectionName]."'>";
			}
			else
			{
				echo "<input type='hidden' id='sectionCheckStatus_".$japioSectionName."' name='sectionCheckStatus[".$japioSectionName."]' value='false'>";
			}
			
			
			//JSectionの大項目(E,C,M,P)のチェックボックスを作る
			//前回のチェック状態と同じチェック状態を指定する
			if(isset($_SESSION['sectionCheckedStatus'][$japioSectionName]))
			{
				if($_SESSION['sectionCheckedStatus'][$japioSectionName]=="true" or $_SESSION['sectionCheckedStatus'][$japioSectionName]=="indeterminate")
				{
					echo "<input type='checkbox' id='sectionCheckBox' checked>";
				}
				else
				{
					echo "<input type='checkbox' id='sectionCheckBox'>";
				}
			}
			else
			{
				echo "<input type='checkbox' id='sectionCheckBox'>";
			}
			
			//各J分類の表示用の検索結果件数を作成。
			$checkBoxLabel_resultCount=0;
			if(isset($sqliteManipulator->jSectionCount[$japioSectionName]))
			{
				$checkBoxLabel_resultCount=$sqliteManipulator->jSectionCount[$japioSectionName];
			}
			else
			{
				$checkBoxLabel_resultCount=0;
			}
				
			//各チェックボックスのラベル
			echo "<label class='japioSectionLabel' id='accordionLabel'>".$japioSectionName."(".$jCaption[$japioSectionName].")"."<span id='".$japioSectionName."' class='resultCountString js_seachResultCount'></span>"."</label>";
			
			if(isset($_SESSION['displayStatus'][$japioSectionName]) and $_SESSION['displayStatus'][$japioSectionName]=="true")
			{
				echo "<ul id='accordionTarget'>";
			}
			else
			{
				echo "<ul id='accordionTarget' style='display:none'>";
			}
			
			//大項目の中のサブ項目に対する処理
			foreach($_dataTables as $table){
				$japioSectionNameFull=mb_substr($table,3);//例：「TM_C01」→「C01」
				
				//現在処理している大項目と同じ分類のサブ項目だけを対象にする
				if(mb_substr($table,3,1)!=$japioSectionName)	
				{
					continue;
				}
				

				$checkedAtLastTime=false;//前回チェックされていたかどうかを保持する
				if(isset($_SESSION['selectedCheckBox'])==true){
					//前回のチェック状況のリスト内に含まれる場合はTrue
					if(in_array($table,$_SESSION['selectedCheckBox'])==true){
						$checkedAtLastTime=true;
					}
				}
				
				echo "<li>";
				
				//各J分類サブ項目の表示用の検索結果件数を作成。
				if(isset($sqliteManipulator->jSectionCount[$japioSectionNameFull]))
				{
					$checkBoxLabel_resultCount=$sqliteManipulator->jSectionCount[$japioSectionNameFull];
				}
				else
				{
					$checkBoxLabel_resultCount=0;
				}
				
				//前回チェックされていたら、今回もチェックされた状態にしておく
				if($checkedAtLastTime==true){
					echo "<input class='js_dictionaries' type='checkbox' name='selectedCheckBox[]' value='".$table."' id='".$table."' checked />";
				}
				else{
					echo "<input class='js_dictionaries' type='checkbox' name='selectedCheckBox[]' value='".$table."' id='".$table."'>";
				}
				//ラベルをつける
				echo "<label for=".$table.">".$japioSectionNameFull."(".$jCaption[$japioSectionNameFull].")"."<span id='".$japioSectionNameFull."' class='resultCountString js_seachResultCount'></span>"."</label>";
				echo "</li>";
			}
			echo "</ul>";
			echo "</li>";
		}

		echo "</ul>";
		
		echo "</div>";
	}
	
	
	
	
	
	//1ページの表示件数
	$displayNumPerPage=50;
	
	
	//J分類キャプション
	$jCaption=array('C'=>'化学','C01'=>'農業','C02'=>'家庭用具','C03'=>'生活','C04'=>'医療','C05'=>'分離','C06'=>'無機','C07'=>'有機','C08'=>'高分子','C09'=>'冶金','C10'=>'繊維','C11'=>'遺伝子','C12'=>'医療','C13'=>'コンビナトリアル',
	'E'=>'電気','E01'=>'電気','E02'=>'半導体','E03'=>'電子','E04'=>'電力',
	'M'=>'機械','M01'=>'ゲーム','M02'=>'工具','M03'=>'材料加工','M04'=>'運輸','M05'=>'包装','M06'=>'ごみ','M07'=>'建築','M08'=>'建具','M09'=>'機械','M10'=>'力学','M11'=>'蒸気','M12'=>'加熱','M13'=>'ナノ技術',
	'P'=>'物理','P01'=>'計測','P02'=>'光学','P03'=>'制御','P04'=>'情報','P05'=>'デジタル');
	


	
	//POSTからSESSIONへデータを移す。必要。
	PostToSession();

	
	
	//DB(SQLite)に接続
	$sqliteManipulator=new SqliteManipulator($_SERVER['DOCUMENT_ROOT']."/corpus/db/OfficialDB.db");
	//テーブル名のリストを取得。
	$dataTables=$sqliteManipulator->GetDataTables();
	
	
	//検索文字列を取得
	$inputWord_ch=StringManipulator::TrimForSearch($_SESSION['word_ch']);//@$_POST['word_ch'];
	$inputWord_jp=StringManipulator::TrimForSearch($_SESSION['word_jp']);//@$_POST['word_jp'];
	//除外文字列を取得
	$inputWord_ch_except=StringManipulator::TrimForSearch($_SESSION['word_ch_except']);//@$_POST['word_ch_except'];
	$inputWord_jp_except=StringManipulator::TrimForSearch($_SESSION['word_jp_except']);//@$_POST['word_jp_except'];

			
	//選択されたセクションのデータテーブルのリストを取得する。
	if(isset($_SESSION['selectedCheckBox']))
	{
		$selectedDataTables=$_SESSION['selectedCheckBox'];
	}
	else
	{
		$selectedDataTables=array();
	}
		
		
	
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

		

	function CreatePrevNextButton()
	{
		echo "<button class='js_submit button_pdccorpus' type='submit' name='action' value='prev' class='floatLeft'>前</button>";
		echo "<button class='js_submit button_pdccorpus' type='submit' name='action' value='next' class='floatLeft'>次</button>";
	}


?>







<html lang="ja">

	<head>
		<link rel="shortcut icon" href="../img/icon/PDC_Logo_White_128_128.ico">
		<title>PDCコーパスWeb　中日</title>
		
		<!--スタイルシートの指定。-->
		<link rel='stylesheet' type='text/css' href='./css/pdc_corpus.css'>
			

	</head>
	
	<body>
		<div id="container">
			<header>
				<img id="img_pdc_logo" src="./img/PDC_Logo_Original.png">
				<h1 id='title'>PDCコーパスWeb　中日</h1>

				<nav id="top-nav">
					<ul>
						<li>
							<div class='logout'>
								<form method="post" action="" id='form_Logout'>			
									<button type='submit' name='action' value='logout' id='button_Logout' class='button_Logout'>ログアウト</button>
								</form>
							</div>
						</li>
					</ul>	
				</nav>

			</header>

			<main>
				<!--検索条件入力フォーム-->
				<div id='div_searchCondition'>
					<form method="post" action="" id='form_searchCondition' class="site-width">
						
						<div id="div_inputTextArea">
							<div id="div_inputTextAreaWrapper">
								<?php CreateChineseInputField();?> 
								<?php CreateChineseExceptInputField();?> 
								<?php CreateJapaneseInputField();?>
								<?php CreateJapaneseExceptInputField();?>
								<div id="div_inputTextAreaButtonField">
								<button type='button' id='button_Clear' class='button_pdccorpus'>クリア</button>
								<button class='js_submit button_pdccorpus' type="submit" name="action" value="search" id='button_submit' class='adjustMarginForSubmitbutton'>検索</button>
							</div>
							</div>

							<!--検索結果情報の表示領域-->
							<div id='div_searchResultInfo' class='floatLeft'>

							</div>
							<!-- 検索条件が入力されていない場合のアラート表示領域 -->
							<div id='div_searchAlertMessage' class='floatRight'>

							</div>
												
						</div>

						<!-- 辞書選択用のチェックボックス用のリスト -->
						<div id="div_jsectionSelectArea">
							<?php CreateJSectionCheckBoxField($dataTables); ?>
						</div>
					</form>
				</div>

				<!-- 検索結果の上に表示させるページ切り替え用ボタン、情報 -->
				<div id='div_paging_top' >
					<form method="post" action="" id="form_prevNextButtonArea_Top">				

						<?php
							CreatePrevNextButton();
						?>
						<span class='js_pagingInfo'></span>
					</form>		

				</div>

				<!--検索結果の表示領域-->
				<div id='div_searchResult' class="site-width">
					
				</div>

				<!-- 検索結果の下に表示させるページ切り替え用ボタン、情報 -->
				<div id="div_paging_bottom">
					<form method="post" action="" id="form_prevNextButtonArea_Bottom">
						<span class='js_pagingInfo'></span>
						<?php
							CreatePrevNextButton();
						?>
					</form>
				</div>

			</main>

			<!-- フッター -->
			<footer>
				Copyright
				<a href="http://www.pdc.co.jp/">特許デイタセンター</a>. All Rights Reserved.
			</footer>
			
		</div>


		<!--javascriptの読み込み-->
		<script type='text/javascript' src='./js/jquery-3.3.1.min.js'></script>
		<script type='text/javascript' src='./js/StringManipulator.js'></script>
		<script type="text/javascript" src="./js/pdc_corpus.js"></script>

		<script>

		</script>
	</body>
</html>