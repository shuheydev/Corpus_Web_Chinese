<?php
	class StringManipulator{
		
		//Ngramの配列を生成する関数
		public static function GenerateNgramArray($_word,$_gram){
			$ngram=array();
			
			$wordLength=mb_strlen($_word);
			//1文字の場合はここでリターン
			if($wordLength==1)
			{
				$ngram[]=$_word;
				return $ngram;
			}
			

			
			for($position=0;$position<=$wordLength-$_gram;$position++){
				$ngram[]=mb_substr($_word,$position,$_gram);
			}
			
			return $ngram;
		}
		
		//連続するスペースを１つにする
		private static function TrimCosencutiveSpace($_string){
			$target=$_string;
			
			//入力文字列内の連続するスペースを１つにする。
			$target=preg_replace("/( |　)+/"," ",$_string);
			
			return $target;
		}
		
		//入力文字列の前後のスペースを削除する。
		private static function MyTrim($_string){
			$target=$_string;
			
			//入力文字列の前後のスペースを削除する。
			$target=preg_replace("/^( |　)+|( |　)+\$/","",$_string);
			
			return $target;
		}
		
		//コーパス検索用に入力文字列をトリミングする。
		//MyTrimとTrimConsencutiveSpaceを行う。
		public static function TrimForSearch($_targetString)
		{
			//前後の空白文字を取り除く
			$_targetString=StringManipulator::MyTrim($_targetString);
			
			//連続する空白文字を1つにまとめる
			$_targetString=StringManipulator::TrimCosencutiveSpace($_targetString);
			
			return $_targetString;
		}
	}

?>