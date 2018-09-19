/*global AWPCP*/
AWPCP.define('awpcp/jquery-ratings', ['jquery', 'awpcp/settings', 'awpcp/localization', 'jquery-raty'],
function($, settings, localization/*, raty*/) {
    var Ratings = function(container) {
        var stars = container.find('.awpcp-ad-rating-stars'),
            message = container.find('.awpcp-ad-rating-message'),
            ratingsCount = container.find('.awpcp-ad-rating-count'),
            base = stars.attr('data-images-base'),
            previousScore = stars.attr('data-rating');

        ratingsCount.text(stars.attr('data-count'));

        stars.raty({
            cancel: true,
            path: stars.attr('data-images-path'),
            starHalf: base + '-half.png',
            starOff: base + '-off.png',
            starOn: base + '-on.png',
            readOnly: parseInt(stars.attr('data-read-only'), 10) > 0,
            score: previousScore,

            click: function(score) {
                $.post(settings.get('ajaxurl'), {
                    action: score === null ? 'awpcp-ratings-delete' : 'awpcp-ratings-rate',
                    ad: stars.attr('data-ad-id'),
                    rating: score
                }, function(data) {
                    if (data.status === 'ok') {
                        stars.raty('score', data.rating);
                        ratingsCount.text(data.count);
                        message.text(localization.get('raty', 'thank-you-message'));
                        previousScore = score;
                    } else {
                        stars.raty('score', previousScore);
                        message.text(localization.get('raty', 'error-message'));
                    }
                }, 'json');
            },

            mouseover: function(score, event) {
                if (typeof event !== 'undefined') {
                    message.empty();
                }
            }
        });
    };

    $.fn.ratings = function() {
        return $(this).each(function() {
            $.noop( new Ratings( $( this ) ) );
        });
    };
});
