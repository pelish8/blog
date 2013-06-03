(function (global) {
    var app = {
        message: {},
        validate: {},
        articles: {}
    };

    app.message.Error = function Error() {
        this.messages = [];

        this.element = $('<ul class="error-bar"></ul>');
        this.element.hide();
        
        $('#content').prepend(this.element);
        
        $(document).on('click', '.error-bar .cancel', $.proxy(function() { 
            this.hide();
        }, this));

        this.show = function (message) {
            var messageElement = '<div class="cancel">x</div>',
                length;

            if (arguments.length > 0) {
                this.addMessage(arguments[0]);
            };

            length = this.messages.length - 1;
            for (; length >= 0; length--) {
                messageElement += '<li>' + this.messages[length] + '</li>';
            };
            this.element.html(messageElement);

            this.element.fadeIn();
        };

        this.hide = function () {
            this.messages = [];
            this.element.hide();
        };

        this.addMessage = function (message) {
            this.messages.push(message);
        };
    };

    app.validate.email = function (email) {
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    };

    app.articles.ShortPost = function (post) {
        var dateArray = post.create_date.split(" "),
            date = dateArray[0],
            time = dateArray[1],
            urlPath = '/' + date + '/' + time.replace(/:/g, '-') + '/' + $('<div></div>').text(post.url_path).html();
            shorts = $('<div class="post"><h3><a href="' + urlPath +
                        '">' +
                        $('<div></div>').text(post.title).html() +
                        '</a></h3><div class="author">' + $('<div></div>').text(post.author).html() +
                        '</div><p class="content">' +
                        $('<div></div>').text(post.content).html().substring(0, 256) + (post.content.length > 256 ? '...' : '') +
                        '</p></div>');
            return shorts;
    };

    app.articles.Pagination = function (activePage, totalRowCount, pageSize, target) {
        var pagesNumber = Math.ceil(totalRowCount / pageSize);
        var html = '<ul class="pure-paginator js-pagination">';
        html += '<li><a class="pure-button prev';
        if (activePage == 1) {
            html += ' pure-button-disabled';
        }
        html += '" href="#">&#171;</a></li>';

        for (var i = 1; i <= pagesNumber; ++i) {
            html += '<li><a class="pure-button';
            if (i == activePage) {
                html += ' pure-button-active';
            }
            html += '" href="#">' + i + '</a></li>';
        }

        html += '<li><a class="pure-button next';

        if (activePage == pagesNumber) {
            html += ' pure-button-disabled';
        }
        html += '">&#187;</a></li>';
        html += '</ul>';
        $(target).empty();
        $(target).html(html);
    };
    
    app.articles.Comment = function (comment) {
        var comment = $('<div class="comment" ><div class="author">' + 
                        $('<div></div>').text(comment.author).html() + 
                        '</div><div class="create-date">' + 
                        $('<div></div>').text(comment.createDate).html() + 
                        '</div>' +
                        '<pre>' + 
                        $('<div></div>').text(comment.comment).html() + 
                        '</pre></div>');
        
        return comment;
    };

    global.App = app;
}(this));