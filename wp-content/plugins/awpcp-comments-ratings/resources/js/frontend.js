/*global AWPCP*/

AWPCP.run('comments-ratings/define-jquery-raty', ['jquery'],
function($) {
    if ($.isFunction($.fn.raty)) {
        AWPCP.define('jquery-raty', ['jquery'], function() {});
    }
});

AWPCP.run('comments-ratings/init-frontend', ['jquery', 'comments/comment', 'awpcp/jquery-ratings'],
function($, Comment/*,ratings*/) {
    $(function() {
        $('.awpcp-ad-rating').ratings();

        // enable ajax edit/delete actions in individual comments
        $('.awpcp-comment').each(function() {
            $.noop(new Comment($(this)));
        });
    });
});
