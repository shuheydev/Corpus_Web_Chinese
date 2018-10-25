
$(function () {
    //  < !--Jセクションのサブ項目のチェックボックスのON、OFFに合わせて大項目のオンオフを切り替える-- >
    //操作されたチェックボックスを取得
    $('input[type="checkbox"]').change(function (e) {

        var checked = $(this).prop("checked"),//チェック状態を取得
            container = $(this).parent(),//親を取得。チェックボックスの親はli
            siblings = container.siblings();//兄弟を取得

        //大項目のli要素の下のすべてのチェックボックスに対して大項目のチェック状態を適用する。
        container.find('input[type="checkbox"]').prop({
            indeterminate: false,
            checked: checked
        });

        //兄弟要素すべてのチェックを行う
        function checkSiblings(el) {
            //渡されるサブ項目のli要素の親(サブ項目のul)の親(大項目のli要素)
            var parent = el.parent().parent(),
                all = true;

            //自分の兄弟チェックボックスが全てチェックされているかを確認する
            el.siblings().each(function () {
                return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
            });


            if (all && checked) {
                //すべてチェックされていて、サブ項目自身もチェックされている場合は
                //一つ上のチェックボックスをチェック状態にする。
                parent.children('input[type="checkbox"]').prop({
                    indeterminate: false,
                    checked: checked
                });

                checkSiblings(parent);

            } else if (all && !checked) {
                //すべてチェックされていて、サブ項目自身はチェックされていない場合
                //一つ上のチェックボックスをサブ項目と同じチェック状態にする
                //ただし、大項目の子孫に一つでもチェックされていないチェックボックスが存在する場合は、中間状態にする。
                parent.children('input[type="checkbox"]').prop("checked", checked);
                parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
                checkSiblings(parent);

            } else {
                //上記以外、上の階層のすべてのli要素の直下のチェックボックスを中間状態にする。
                el.parents("li").children('input[type="checkbox"]').prop({
                    indeterminate: true,
                    checked: false
                });

            }

        }

        checkSiblings(container);

    });


    //JSection選択リストのアコーディオンアニメーション
    //ラベルがクリックされたときに閉じたり開いたりする
    $("label#accordionLabel").click(function () {
        var targetElement = $(this).next("#accordionTarget");

        var displayStatus = targetElement.is(":visible");//slideToggleよりも前じゃないとだめ。

        var sectionName = targetElement.parent().prop("id");

        var displayStatusElement = $("input#displayStatus_" + sectionName);

        if (displayStatus == true) {
            displayStatusElement.val(false);
        }
        else {
            displayStatusElement.val(true);
        }

        targetElement.slideToggle();

        //console.log("yeah");
    });


    $('button#button_Clear').click(function () {

        $('input[name="word_ch"]').val('');
        $('input[name="word_jp"]').val('');
        $('input[name="word_ch_except"]').val('');
        $('input[name="word_jp_except"]').val('');

    });


    //これがinput要素だったらon('submit',...)
    $('button.js_submit').on('click', function (e) {

        e.preventDefault();

        //テキストボックスの値を配列に格納する
        //Array型[]だと送信できない(空)になるので、オブジェクト型の配列{}を使う
        var searchWords = {};
        $('.js_searchWordInputField').each(function () {
            searchWords[$(this).attr('name')] = $(this).val();
        });
        //console.log(searchWords);

        var selectedDictionaries = [];
        $('.js_dictionaries').each(function () {
            if ($(this).prop('checked'))
                selectedDictionaries.push($(this).val());
        });

        //console.log(searchWords);
        //console.log(selectedDictionaries);

        $.ajax({
            type: 'post',
            url: './ajax/pdc_corpus_ajax.php',
            data: {
                action: e.target.value,
                searchWords: searchWords,
                selectedDictionaries: selectedDictionaries,
            },
            dataType: 'json',
        }).done(function (data, status) {
            console.log(data);
            //console.log(status);
            //console.log(searchWords);

            $('#div_searchResult').html(data['resultHTML']);

            if (data['action'] == 'search') {
                $('#div_searchResultInfo').text(data['searchResultInfoText']);

                //検索単語が未入力、または検索対象の辞書が未選択の場合はメッセージを出す
                var alertMessages = [];
                if (selectedDictionaries.length == 0) {
                    alertMessages.push('検索対象の辞書を選択してください');
                    $('#div_jsectionSelectArea').addClass('borderRed');
                }
                else {
                    $('#div_jsectionSelectArea').removeClass('borderRed');
                }
                if (searchWords['word_ch'] == "" && searchWords['word_jp'] == "") {
                    alertMessages.push('検索したい単語を入力してください。');
                    if (searchWords['word_ch'] == "")
                        $('#div_ChineseInputField input').addClass('borderRed');

                    if (searchWords['word_jp'] == "")
                        $('#div_JapaneseInputField input').addClass('borderRed');

                }
                else {
                    $('#div_ChineseInputField input').removeClass('borderRed');
                    $('#div_JapaneseInputField input').removeClass('borderRed');
                }
                //console.log(alertMessages);
                if (alertMessages.length > 0) {
                    $('#div_searchAlertMessage').html("<span class='alertMessage'>" + alertMessages.join('<br/>') + "</span>");
                }
                else {
                    $('#div_searchAlertMessage').html('');
                }
            }

            var pagingInfo = data['currentPageNum'] + ' / ' + data['totalPageCount'] + ' ページ。( ' + data['currentPageRange'] + ' 件)';
            $('#div_paging_top span.js_pagingInfo').text(pagingInfo);
            $('#div_paging_bottom span.js_pagingInfo').text(pagingInfo);

            if (data['totalPageCount'] > 1) {
                $('#div_paging_top').show();
                $('#div_paging_bottom').show();
                //console.log('shown');
            }
            else {
                $('#div_paging_top').hide();
                $('#div_paging_bottom').hide();
                //console.log('hide');
            }


            $('.js_seachResultCount').each(function () {
                var key = $(this).attr('id');
                $(this).text(data['countEachDictionary'][key] === undefined ? "0件" : data['countEachDictionary'][key] + "件");
                //console.log(data['countEachDictionary'][key]);
            });
        });
    });

});
