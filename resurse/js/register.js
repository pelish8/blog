$(document).ready(function ($) {
    var errorMessage = new App.message.Error();

    $('.js-button-click').click(function () {
        var button = $(this).attr('disabled', 'disabled'),
            name = $('.js-name').val(),
            email = $('.js-email').val(),
            password = $('.js-password').val(),
            confirmPassword = $('.js-confirm-password').val(),
            hasError = false;
            console.log(1);
        errorMessage.hide();
        if (name.length === 0) {
            errorMessage.addMessage('Name must have at least 3 characters.');
            hasError = true;
        }

        if (!App.validate.email(email)) {
            errorMessage.addMessage('Invalid email address.');
            hasError = true;
        }
        if (password.length === 0 || password !== confirmPassword) {
            errorMessage.addMessage('Passwords do not match.');
            hasError = true;
        } else if (password.length < 6) {
            errorMessage.addMessage('Password should have at least 6 characters.');
            hasError = true;
        }
        if (hasError) {
            errorMessage.show();
            button.removeAttr('disabled');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: App.apiPath + '/register',
            data: {
                name: name,
                email: email,
                password: password,
                confirmPassword: confirmPassword
            },
            success: function (data, status, request) {
                if (data.status === 'ok') {
                    document.location = '/login';
                } else if (data.reason === 'user-exists') {
                    errorMessage.show('Sorry, email address ' + $('.js-email').val() + ' is already in use.');
                } else {
                    errorMessage.show('Error Sending Data.');
                }
                button.removeAttr('disabled');
            },
            error: function () {
                button.removeAttr('disabled');
                errorMessage.show('Error Sending Data.');
            }
        });
    });
});