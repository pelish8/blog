$(document).ready(function ($) {
    var errorMessage = new App.message.Error();
    $('.js-save-button-click').click(function () {
        var button = $(this).attr('disabled', 'disabled'),
            title = $('.js-title').val(),
            article = $('.js-article').val(),
            tags = $('.js-tags').val(),
            hasError = false;
        errorMessage.hide();
        if (title.length === 0 && title !== ' ' && title.length < 250) {
            errorMessage.addMessage('Title field cannot be empty or space filled.');
            hasError = true;
        }
        
        if (article.length === 0 && title !== ' ') {
            errorMessage.addMessage('Article field cannot be empty or space filled.');
            hasError = true;
        }

        if (hasError) {
            errorMessage.show();
            button.removeAttr('disabled');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: App.apiPath + '/create-article',
            data: {
                title: title,
                article: article,
                tags: tags
            },
            success: function (data, status, request) {
                if (data.status === 'ok') {
                    window.location = '/';
                } else if (data.reason === 'access-denied') {
                    window.location = '/login';
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

    $('.js-cancel-button-click').click(function () {
        window.location = '/';
    });
});
