/*global AWPCP*/
AWPCP.define('regions/jquery-sidelist', ['jquery', 'regions/sidelist'],
function($, Sidelist) {
    $.fn.sidelist = function() {
        $(this).each(function() {
            var container = $(this);

            if (!container.data('Sidelist')) {
                container.data('Sidelist', new Sidelist(container));
                container.data('Sidelist').render();
            }
        });
    };
});
