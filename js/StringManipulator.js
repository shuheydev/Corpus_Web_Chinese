function ComposeHTMLTableFromSearchResult(_searchResult,_startIdx,_num,_inputWord_ch,_inputWord_jp)
{
	//開始インデックスは0以上の整数
	if(_startIdx<0)
	{
		return "";
	}
	
	//取得件数は1以上の整数。
	if(_num<1)
	{
		return "";
	}
	
	
	//ハイライト用の正規表現を生成する
	var highlightRegex_ch="";
	var highlightRegex_jp="";
	
	if(_inputWord_ch!=''){
		var wordList_ch=new Array();
		wordList_ch=_inputWord_ch.split(/( |　)/);
		
		highlightRegex_ch="("+wordList_ch.join("|")+")";// "(".implode("|",wordList_ch).")";
	}
	if(_inputWord_jp!=''){
		var wordList_jp=new Array();
		wordList_jp=_inputWord_jp.split(/( |　)/);
		
		highlightRegex_jp="("+wordList_jp.join("|")+")";//,wordList_jp).")";
	}
	
	
	
	
	
	var html="";
	
	
	//結果を表形式で表示
	html+= "<table >";
	html+= "<thead>";
	html+= "<tr>";
	html+= "<th>文献番号</th>";
	html+= "<th>中国語</th>";
	html+= "<th>日本語</th>";
	html+= "<th>J分類</th>";
	html+= "<th>IPC</th>";
	html+= "</tr>";
	html+= "</thead>";
	html+= "<col align='center'>";
	html+= "<col width='50%' valign='top'>";
	html+= "<col width='50%' valign='top'>";
	html+= "<col align='center'>";
	html+= "<col align='center'>";
	html+= "<tbody>";
	
	

	var searchResultCount=_searchResult.length;
	//結果を開始インデックスから指定件数表示
	var loopNum=_num;
	if(_startIdx+loopNum>searchResultCount)
	{
		loopNum=searchResultCount-_startIdx;
	}
	
	
	for(var resultIdx=_startIdx;resultIdx< _startIdx+loopNum;resultIdx++){
		
		var rowResult=_searchResult[resultIdx];
		var result_docNum=rowResult['文献番号'];//文献番号
		var result_ch=rowResult['中国語'];//中国語
		var result_jp=rowResult['日本語'];//日本語
		var result_ipcSection=rowResult['J分類'];//J分類
		var result_jSection=rowResult['IPC'];//IPC
		

		
		html+= "<tr>";
		
		html+= "<td class='other'>";
		html+= result_docNum;
		html+= "</td>";
		
		html+= "<td class='chinese'>";
		if(_inputWord_ch!='')
		{
			html+= result_ch.replace(new RegExp(highlightRegex_ch,'g'),"<span class='searchwords_ch'>"+"$1"+"</span>");
		}
		else
		{
			html+= result_ch;
		}						
		html+= "</td>";

		html+= "<td class='japanese'>";
		if(_inputWord_jp!='')
		{
			html+= result_jp.replace(new RegExp(highlightRegex_jp,'g'),"<span class='searchwords_jp'>"+"$1"+"</span>");
		}
		else
		{
			html+= result_jp;
		}
		html+= "</td>";
		
		html+= "<td class='other'>";
		html+= result_jSection;
		html+= "</td>";
		
		html+= "<td class='other'>";
		html+= result_ipcSection;
		html+= "</td>";
		
		html+= "</tr>";
	}

	
	
	html+= "</tbody>";
	html+= "</table>";
	
	return html;
}



function ShowPartialResult(start,num)
{
	//var partialResultHtml=<?php echo json_encode($sqliteManipulator->ComposeHTMLTableFromSearchResult(0,100,$inputWord_ch,$inputWord_jp),JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
	var partialResultHtml=ComposeHTMLTableFromSearchResult(totalResult,start,num,inputWord_ch,inputWord_jp);
	
	document.getElementById("searchResult").innerHTML=partialResultHtml;
}