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
        
        this.show = function (message) {
            var messageElement = '',
                length;
            
            if (arguments.length > 0) {
                this.addMessage(arguments[0]);
            }
            
            length = this.messages.length - 1;
            for (; length >= 0; length--) {
                messageElement += '<li>' + this.messages[length] + '</li>';
            }
            
            this.element.html(messageElement);
            
            this.element.fadeIn();
        }
        
        this.hide = function () {
            this.messages = [];
            this.element.hide();
        }
        
        this.addMessage = function (message) {
            this.messages.push(message);
        }
    }
    
    app.validate.email = function (email) {
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }
    
    app.articles.ShortPost = function (post, target) {
        var shorts = $('<div class="post"><h3>' +
                        $('<div></div>').text(post.title).html() +
                        '</h3><div class="author">' + $('<div></div>').text(post.author).html() +
                        '</div><p class="content">' +
                        $('<div></div>').text(post.content).html().substring(0, 256) + (post.content.length > 256 ? '...' : '') + 
                        '</p></div>');
        target.append(shorts);
    }
    
    global.App = app;
}(this));