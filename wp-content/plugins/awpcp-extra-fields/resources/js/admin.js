if (typeof jQuery !== 'undefined') {

    var AWPCP = jQuery.AWPCP = jQuery.extend({}, jQuery.AWPCP, AWPCP);

    (function($, undefined) {
        $(function() {
            $('.awpcp-extra-fields-form .category-checklist').each(function() {
                $.noop(new $.AWPCP.CategoriesChecklist(this));
            });
        });

    })(jQuery);
}
