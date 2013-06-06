(function (global) {
    var pageNumber = 1,
        pageSize = 10,
        pagination = false;

        global.App.getArticles = function (pageNumber, pageSize) {
            $.ajax({
                type: 'GET',
                url: App.apiPath + '/articles',
                cache: false,
                data: {
                    pageNumber: App.pageNumber,
                    pageSize: App.pageSize
                },
                success: function (data, status, request) {
                    $(document).ready(function () {
                        if (data.status === 'ok') {
                            var target = $('.posts'),
                                articles = $('<div></div>');
                                
                                if (data.result.totalRowCount > 0) { 
                                    $.each(data.result, function(key, value) {
                                        if (key === 'totalRowCount') {
                                            return;
                                        }
                                        articles.append(App.articles.ShortPost(value));
                                    });
                                    target.empty();
                                    target.append(articles.html());
                                    App.articles.Pagination(pageNumber, data.result.totalRowCount, pageSize, '.js-pagination-box');
                            } else {
                                $('.js-pagination-box').html('No Data To Display.');
                            }
                        }
                    });
                },
                error: function () {
                }
            });
        }

    $(document).ready(function () {
        $('.js-pagination-box').click(function (event) {
            var target = $(event.target),
                targetHtml = target.html();

            if (target.hasClass('pure-button-disabled') || target.hasClass('pure-button-active')) {
                return;
            }

            if (pageNumber > 1) {
                var f = $('.js-pagination-box prev');
                if (f.hasClass('pure-button-disabled'))
                    f.removClass('pure-button-disabled');
            }

            if (targetHtml == '«') {
                App.pageNumber--;
            } else if (targetHtml == '»') {
                App.pageNumber++;
            } else {
                App.pageNumber = targetHtml;
            }
            App.getArticles(App.pageNumber, App.pageSize);
        });
    });

} (window));