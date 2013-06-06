$(document).ready(function () {
    var errorMessage = new App.message.Error();

    var addComments = function () {
        $.ajax({
            type: 'GET',
            url: App.apiPath +'/comments',
            cache: false,
            data: {
                articleId: App.articleId
            },
            success: function (data, status, request) {
                if (data.status === 'ok') {
                    var target = $('.js-comments'),
                        comments = $('<div></div>');

                    $.each(data.result, function(key, value) {
                        comments.append(App.articles.comments.comment(value));
                    });

                    target.empty();
                    target.append(comments.html());
                } else {
                    errorMessage.show('Error Sending Data.');
                }
            },
            error: function () {
                errorMessage.show('Error Sending Data.');
            }
        });
    };

    addComments();

    var saveComment = function (name, comment, parentId) {
        $.ajax({
            type: 'POST',
            url: App.apiPath +'/create-comment',
            data: {
                name: name,
                comment: comment,
                articleId: App.articleId,
                parentId: parentId
            },
            success: function (data, status, request) {
                if (data.status === 'ok') {
                   addComments();
                   $('.js-comment').val('');
                } else {
                    errorMessage.show('Error Sending Data.');
                }
            },
            error: function () {
                errorMessage.show('Error Sending Data.');
            }
        });
    }

    var getAddCometForm = function (parentId) {
        var element = '<div class="pure-form pure-form-stacked comment-form">' +
                '<label for="user-name">Name</label>' +
                '<input type="text" placeholder="Name" class="js-name" value="' + App.name +'">'+
                '<input type="hidden" class="js-parent-id" value="' + parentId + '">'+
                '<label for="user-comment">Comment</label>' +
                '<textarea class="js-comment"></textarea>' +
                '<button class="pure-button pure-button-primary js-add-button-click">Add</button>' +
                '<button class="pure-button pure-button-primary js-close-button-click close">Close</button></div>';

            return $(element);
    }
        $(document).on('click', '.js-add-button-click', function () {
            var button = $(this).attr('disabled', 'disabled'),
                parent = $(this).parent(),
                name = parent.find('.js-name').val(),
                comment = parent.find('.js-comment').val(),
                parentId = parent.find('.js-parent-id').val(),
                hasError = false;
                console.log(errorMessage);
            errorMessage.hide();
            if (name.length === 0 && name !== ' ') {
                errorMessage.addMessage('Name field cannot be empty or space filled.');
                hasError = true;
            }

            if (comment.length === 0 && comment !== ' ') {
                errorMessage.addMessage('Comment field cannot be empty or space filled.');
                hasError = true;
            }

            if (hasError) {
                errorMessage.show();
                button.removeAttr('disabled');
                return false;
            }

            saveComment(name, comment, parentId);
            $(this).parent().remove();
            return false;
        });

        $(document).on('click', '.js-reply', function () {
            var commentId = $(this).attr('comment-id'),
                parent = $(this).parent(),
                isActive = parent.find('.comment-form').attr('active');
            if (!isActive) {
                var form = getAddCometForm(commentId);
                form.attr('active', 'active');
                $(this).parent().append(form);
            }
            return false;
        });

        $(document).on('click', '.js-add-comment', function () {
            var parent = $(this).parent(),
                isActive = parent.find('.comment-form').attr('active');
            if (!isActive) {
                var form = getAddCometForm('');
                form.attr('active', 'active');
                $(this).parent().append(form);
            }
            return false;
        });

        $(document).on('click', '.js-close-button-click', function () {
            var parent = $(this).parent();
            parent.remove();
            $(this).parent().parent().removeAttr('active');
        });
});