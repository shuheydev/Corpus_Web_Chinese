<?php
	
	class SqliteManipulator{
		
		private $dbPath;
		private $db;
		
		public $totalResult;
		public $totalResultCount;
		public $lastSearchRecordNum;
		public $lastSearchElapsedTime;
		public $jSectionCount;//J分類（CEMP）ごとの検索結果件数
		
		public function __construct($_dbPath){
			
			if($_dbPath!=null)
			{
				$this->dbPath=$_dbPath;
				
				try{
					$this->db=new SQLite3($this->dbPath);
				}
				catch(Exception $e){
					echo "DBを開けませんでした。";
				}
			}

			
			$this->InitProperties();
		}
		
		private function InitProperties()
		{
			$this->totalResult=array();
			$this->totalResultCount=0;
			$this->lastSearchRecordNum=0;
			$this->lastSearchElapsedTime=0.0;
			$this->jSectionCount=array('C'=>0,'E'=>0,'M'=>0,'P'=>0);//J分類（CEMP）ごとの検索結果件数
		}
		
		//DBを検索する関数
		//単独のテーブルから検索する。
		private function SearchFullText($_inputWord_ch,$_inputWord_jp,$_inputWord_ch_except,$_inputWord_jp_except,$_table,$_limit){

			
			//処理時間計測開始
			//$time_start=microtime(true);
			
			//対象のテーブルをすべて連結したビューの名前
			$joinedTable="JoinedTable";
			
			//もしViewがあれば削除する。
			$this->db->query("DROP VIEW IF EXISTS ".$joinedTable);
			
			//選択されたテーブルをまとめてビューを作成。
			//$this->db->query("CREATE VIEW ".$joinedTable." AS SELECT * FROM ".$_tables);

			//クエリに使用する検索用の文字列を生成
			$searchString_ch=$this->GenerateSearchString($_inputWord_ch);
			$searchString_jp=$this->GenerateSearchString($_inputWord_jp);
			
			//クエリに使用する除外用の文字列を生成
			//$exceptString_ch=$this->GenerateExceptString($_inputWord_ch_except);
			//$exceptString_jp=$this->GenerateExceptString($_inputWord_jp_except);
			

			$qry="";
			//クエリの生成
			if($searchString_ch!='' and $searchString_jp=='')
			{
				$qry="SELECT * FROM ".$_table." a WHERE a.col7 MATCH '".$searchString_ch."' LIMIT ".$_limit;
			}
			else if($searchString_ch=='' and $searchString_jp!='')
			{
				$qry="SELECT * FROM ".$_table." a WHERE a.col8 MATCH '".$searchString_jp."' LIMIT ".$_limit;
			}
			else if($searchString_ch!='' and $searchString_jp!='')
			{
				$qry="SELECT * FROM ".$_table." a WHERE rowid IN (SELECT rowid FROM ".$_table." b WHERE b.col7 MATCH '".$searchString_ch."' AND rowid IN (SELECT rowid FROM ".$_table." c WHERE c.col8 MATCH '".$searchString_jp."')) LIMIT ".$_limit;
			}	
			
			//echo "<br/>";
			

			try
			{
				//DBに検索クエリを送る
				$resultFromDB=$this->db->query($qry);
				
				//ハイライト用の正規表現を生成する
				$highlightRegex_ch="";
				$highlightRegex_jp="";
				
				if($_inputWord_ch!=''){
					$wordList_ch=array();
					$wordList_ch=preg_split("/( |　)/",$_inputWord_ch);
					
					$highlightRegex_ch="(".implode("|",$wordList_ch).")";
				}
				if($_inputWord_jp!=''){
					$wordList_jp=array();
					$wordList_jp=preg_split("/( |　)/",$_inputWord_jp);
					
					$highlightRegex_jp="(".implode("|",$wordList_jp).")";
				}
				
				//除外用の正規表現を生成する
				$exceptRegex_ch="";
				$exceptRegex_jp="";
				
				if($_inputWord_ch_except!=''){
					$wordList_ch_except=array();
					$wordList_ch_except=preg_split("/( |　)/",$_inputWord_ch_except);
					
					$exceptRegex_ch="(".implode("|",$wordList_ch_except).")";
				}
				if($_inputWord_jp_except!=''){
					$wordList_jp_except=array();
					$wordList_jp_except=preg_split("/( |　)/",$_inputWord_jp_except);
					
					$exceptRegex_jp="(".implode("|",$wordList_jp_except).")";
				}
				
				
				$filteredResult=array();
				//除外条件で絞り込む
				while($rowResult=$resultFromDB->fetchArray()){
						$row_docNum=$rowResult[0];//文献番号
						$row_ch=$rowResult[1];//中国語
						$row_jp=$rowResult[2];//日本語
						$row_ipcSection_Initial=$rowResult[3];//IPC分類の先頭1文字
						$row_ipcSection=$rowResult[4];//IPC分類
						$row_jSection_Initial=$rowResult[5];//J分類の先頭1文字
						$row_jSection=$rowResult[6];//J分類
						
						
						//どうやらすべてのngramが含まれているレコードが引っかかるようだ。
						//なので、検索文字列がすべて含まれていないものはここで除外する。
						//C#のほうの挙動はどうだったかな。
						
						if($_inputWord_ch!="")
						{
							//すべての検索語を含んでいるかを表すフラグ。
							//含んでいる場合はTrue;
							$containAllWords=true;
							foreach($wordList_ch as $ch)
							{
								if(preg_match("/".$ch."/",$row_ch,$matchResult_ch)==0)
								{
									$containAllWords=false;
									break;
								}
							}
							
							if($containAllWords==false)
							{
								continue;//次のデータへ
							}
						}
						if($_inputWord_jp!="")
						{
							//すべての検索語を含んでいるかを表すフラグ。
							//含んでいる場合はTrue;
							$containAllWords=true;
							foreach($wordList_jp as $jp)
							{
								if(preg_match("/".$jp."/",$row_jp,$matchResult_jp)==0){
									$containAllWords=false;
									break;
								}
							}
							
							if($containAllWords==false)
							{
								continue;//次のデータへ
							}
						}
						
						
						//除外単語が含まれていたら飛ばす
						if($_inputWord_ch_except!=""){
							if(preg_match($exceptRegex_ch,$row_ch,$matchResult_ch)==1){
								continue;
							}	
						}
						if($_inputWord_jp_except!=""){
							if(preg_match($exceptRegex_jp,$row_jp,$matchResult_jp)==1){
								continue;
							}	
						}
						
						$row=array('DocumentNumber'=>$row_docNum,'Chinese'=>$row_ch,'Japanese'=>$row_jp,'IPCInitial'=>$row_ipcSection_Initial,'IPC'=>$row_ipcSection,'JBunruiInitial'=>$row_jSection_Initial,'JBunrui'=>$row_jSection,'DictionaryName'=>$_table);
						
						$filteredResult[]=$row;
				}
				
				
			}
			catch(Exception $e)
			{

			}
			
			
			return $filteredResult;
		}
		
		
		public function SearchFullTextFromTables($_inputWord_ch,$_inputWord_jp,$_inputWord_ch_except,$_inputWord_jp_except,$_selectedDataTables,$_limit)
		{
			//必要な情報が入力されているかをチェックする。
			if(count($_selectedDataTables)==0)
			{
				return;
			}
			if($_inputWord_ch=="" && $_inputWord_jp=="")
			{
				return;
			}

			
			//処理時間計測開始
			$time_start=microtime(true);
			
			//メンバ変数を初期化。
			$this->InitProperties();
			
			//全てのテーブルを順に検索する
			foreach($_selectedDataTables as $table){
				//DBを検索
				$result=$this->SearchFullText($_inputWord_ch,$_inputWord_jp,$_inputWord_ch_except,$_inputWord_jp_except,$table,"2000");
				
				//J分類3桁を取得
				$jSection=mb_substr($table,3,3);
				//テーブルごとの検索結果件数を記録
				$this->jSectionCount[$jSection]=count($result);
				
				//J分類の頭文字を取得
				$jSection_Initial=mb_substr($table,3,1);
				//J分類ごとの検索結果件数を累積
				$this->jSectionCount[$jSection_Initial]+=count($result);
				
				//処理時間を累積
				//$this->totalElapsedTime+=$this->lastSearchElapsedTime;
				
				//検索結果をマージ
				$this->totalResult=array_merge($this->totalResult,$result);
			}
			//全検索結果の件数
			$this->totalResultCount=count($this->totalResult);
			
			
			//検索に要した時間を格納
			$this->lastSearchElapsedTime=round(microtime(true)-$time_start,3);//小数点以下3桁で四捨五入
			
			return $this->totalResult;
		}
		
		
		
		
		private function GenerateSearchString($_inputWord){
		
			if($_inputWord=="")
			{
				return "";
			}
			
			//入力された検索語をスペースで区切る
			$wordList=array();
			$wordList=preg_split("/( |　)/",$_inputWord);		
			
			$searchString="";
			if($wordList!=Null){
				//区切られた各単語を2gramに分割してそれを配列に格納する。
				$ngramStringList=array();
				foreach($wordList as $word){
					//検索語の2gramを生成。
					$ngramArray=StringManipulator::GenerateNgramArray($word,2);
					$ngramString=implode(" ",$ngramArray);//配列内の文字列をスペース区切りで連結
					$ngramStringList[]=$ngramString;
				}
				//この配列の中の文字列をスペースで連結する。
				$searchString=implode(" ",$ngramStringList);			
			}		

			return $searchString;

		}
		
		
		private function GenerateExceptString($_inputWord_except){
		
			if($_inputWord_except=="")
			{
				return "";
			}
			//入力された検索語をスペースで区切る
			$wordList=array();
			$wordList=preg_split("/( |　)/",$_inputWord_except);
			
			$exceptString="";
			if($wordList!=Null){
				//区切られた各単語を2gramに分割してそれを配列に格納する。
				$ngramStringList=array();
				foreach($wordList as $word){
					//除外語の2gramを生成。
					$ngramArray=StringManipulator::GenerateNgramArray($word,2);
					$ngramString="-".implode(" -",$ngramArray);//配列内の文字列に"-"をつけてスペース区切りで連結
					$ngramStringList[]=$ngramString;
				}
				
				//この配列の中の文字列をスペースで連結する。
				$exceptString=implode(" ",$ngramStringList);
				

			}		


			return $exceptString;

		}
		
		
		//全文検索機能のために生成された補助テーブルではなく、
		//検索対象のデータが入っているテーブルのみを取得
		public function GetDataTables(){
			//DBの全てのテーブル名を取得
			$allTables=$this->db->query("SELECT name FROM sqlite_master WHERE type='table'");
			

			$dataTables=array();
			while($rowTable=$allTables->fetchArray()){
				$table=$rowTable[0];
				
				if(preg_match('/(_content|_segments|_segdir)/',$table,$matches)==1)
				{
					continue;
				}
				
				$dataTables[]=$table;
			}
			return $dataTables;
		}	
	
	
	
		public function FilterSearchResultByIPC($_selectedIPC)
		{
			if(count($_selectedIPC)==0)
			{
				return;
			}

			$filteredResult=array();
			//指定のIPC分類のデータのみを取得
			foreach($_selectedIPC as $ipc)
			{
				$filter_func=function($value) use ($ipc){return ($value["IPCInitial"]==$ipc);};
				$filteredResult=array_merge($filteredResult,array_filter($this->totalResult,$filter_func));
				//$filteredResult=array_merge($filteredResult,array_search($ipc,array_column($this->totalResult,"IPCInitial")));
			}
			$this->totalResult=$filteredResult;			
			$this->totalResultCount=count($this->totalResult);
			
			//絞り込まれたデータに対し、J分類ごとの件数をカウントする			
			foreach(array_keys($this->jSectionCount) as $jSection)
			{
				if(mb_strlen($jSection)==1)
				{
					$filter_func_count=function($value) use ($jSection){return ($value["JBunruiInitial"]==$jSection);};
				}
				else
				{
					$filter_func_count=function($value) use ($jSection){return ($value["JBunrui"]==$jSection);};
				}
				
				$this->jSectionCount[$jSection]=count(array_filter($filteredResult,$filter_func_count));

			}
		}
	
	
	
		public function ComposeHTMLTableFromSearchResult($_startIdx,$_num,$_inputWord_ch,$_inputWord_jp)
		{
			//開始インデックスは0以上の整数
			if($_startIdx<0)
			{
				return "";
			}
			
			//取得件数は1以上の整数。
			if($_num<1)
			{
				return "";
			}
			
			
			//ハイライト用の正規表現を生成する
			$highlightRegex_ch="";
			$highlightRegex_jp="";
			
			if($_inputWord_ch!=''){
				$wordList_ch=array();
				$wordList_ch=preg_split("/( |　)/",$_inputWord_ch);
				
				$highlightRegex_ch="(".implode("|",$wordList_ch).")";
			}
			if($_inputWord_jp!=''){
				$wordList_jp=array();
				$wordList_jp=preg_split("/( |　)/",$_inputWord_jp);
				
				$highlightRegex_jp="(".implode("|",$wordList_jp).")";
			}
			
			
			$html="";
			
			
			//結果を表形式で表示
			$html.= "<table class='searchResult'>";
			$html.= "<thead>";
			$html.= "<tr>";
			$html.= "<th>文献番号</th>";
			$html.= "<th>中国語</th>";
			$html.= "<th>日本語</th>";
			$html.= "<th>J分類</th>";
			$html.= "<th>IPC</th>";
			$html.= "</tr>";
			$html.= "</thead>";
			$html.= "<col align='center'>";
			$html.= "<col width='50%' valign='top'>";
			$html.= "<col width='50%' valign='top'>";
			$html.= "<col align='center'>";
			$html.= "<col align='center'>";
			$html.= "<tbody>";
			
			


			//結果を開始インデックスから指定件数表示
			$loopNum=$_num;
			if($_startIdx+$loopNum>$this->totalResultCount)
			{
				$loopNum=$this->totalResultCount-$_startIdx;
			}
			
			
			for($resultIdx=$_startIdx;$resultIdx< $_startIdx+$loopNum;$resultIdx++){
				
				if($resultIdx>=$this->totalResultCount){
					break;
				}
				
				$rowResult=$this->totalResult[$resultIdx];
				$result_docNum=$rowResult['DocumentNumber'];//文献番号
				$result_ch=$rowResult['Chinese'];//中国語
				$result_jp=$rowResult['Japanese'];//日本語
				$result_jSection=$rowResult['JBunrui'];//J分類
				$result_ipcSection=$rowResult['IPC'];//IPC
				

				
				$html.= "<tr>";
				
				$html.= "<td class='other'>";
				$html.= $result_docNum;
				$html.= "</td>";
				
				$html.= "<td class='chinese'>";
				if($_inputWord_ch!='')
				{
					$html.= preg_replace("/".$highlightRegex_ch."/","<span class='searchwords_ch'>"."\${1}"."</span>",$result_ch);
				}
				else
				{
					$html.= $result_ch;
				}						
				$html.= "</td>";

				$html.= "<td class='japanese'>";
				if($_inputWord_jp!='')
				{
					$html.= preg_replace("/".$highlightRegex_jp."/","<span class='searchwords_jp'>"."\${1}"."</span>",$result_jp);
				}
				else
				{
					$html.= $result_jp;
				}
				$html.= "</td>";
				
				$html.= "<td class='other'>";
				$html.= $result_jSection;
				$html.= "</td>";
				
				$html.= "<td class='other'>";
				$html.= $result_ipcSection;
				$html.= "</td>";
				
				$html.= "</tr>";
			}

			
			
			$html.= "</tbody>";
			$html.= "</table>";
			
			return $html;
		}
		
		
		public function ComposeSearchResultInfo()
		{
			$resultInfo="";
			//検索結果の件数と処理時間の表示。
			$resultInfo.= $this->totalResultCount."件";
			$resultInfo.= "(";
			$resultInfo.= "C:".$this->jSectionCount['C']."件、";
			$resultInfo.= "E:".$this->jSectionCount['E']."件、";
			$resultInfo.= "M:".$this->jSectionCount['M']."件、";
			$resultInfo.= "P:".$this->jSectionCount['P']."件";
			$resultInfo.= ")。";
			$resultInfo.= $this->lastSearchElapsedTime."秒。";
			
			return $resultInfo;
		}
	}
	

?>