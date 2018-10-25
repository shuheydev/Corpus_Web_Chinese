$(function () {
    inputCheck();

    $(".js_userID").keyup(function () {
        inputCheck();
    });

    $(".js_password").keyup(function () {
        inputCheck();
    });

    function inputCheck() {
        //両方に入力があるかをチェック
        if ($(".js_userID").val() != "" && $(".js_password").val() != "") {
            $("button[name='login']").prop('disabled', false);
            $("button[name='login']").removeClass('buttonDisabled');
        }
        else {
            $("button[name='login']").prop('disabled', true);
            $("button[name='login']").addClass('buttonDisabled');
        }
    }

    $('.js_button_submit').on('click', function (e) {
        e.preventDefault();

        $.ajax({
            type: 'post',
            url: './ajax/corpus_login_ajax.php',
            data: {
                userID: $('.js_userID').val(),
                pass: $('.js_password').val(),
            },
            dataType: 'json',
        }).done(function (data, status) {
            // console.log(data);
            if (data['result']) {
                // console.log('hello');
                //$('#loginErrorMessage').html('&nbsp;');
                window.location.href = "/corpus/pdccorpus.php";
            }
            else {
                $('#loginErrorMessage').html('ユーザーIDまたはパスワードが間違っています');
                $('#div_loginPanel').effect
            }
        });
    });
});