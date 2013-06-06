$(document).ready(function () {
    var errorMessage = new App.message.Error();
    $('.js-log-in-button').click(function () {
        var button = $(this).attr('disabled', 'disabled'),
            email = $('.js-email').val(),
            password = $('.js-password').val(),
            hasError = false;

        errorMessage.hide();
        if (!App.validate.email(email)) {
            errorMessage.addMessage('Please provide a valid email address.');
            hasError = true;
        }

        if (hasError) {
            errorMessage.show();
            button.removeAttr('disabled');
            return;
        }

        $.ajax({
            type: 'POST',
            url: App.apiPath + '/login',
            data: {
                email: email,
                password: password
            },
            success: function (data, status, request) {
                if (data.status === 'ok') {
                    document.location = '/login';
                } else if (data.reason === 'invalid-credentials') {
                    errorMessage.show('Your email or password were incorrect.');
                } else {
                    errorMessage.show('Error Sending Data.');
                }
                button.removeAttr('disabled');
            },
            error: function () {
                button.removeAttr('disabled');
                // errorMessage.show('Error Sending Data.');
            }
        });

    });
});
