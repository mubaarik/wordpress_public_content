if (typeof jQuery !== 'undefined') {

    var AWPCP = jQuery.AWPCP = jQuery.extend({}, jQuery.AWPCP, AWPCP);

    (function($, undefined) {

        $.AWPCP.ExtraField = function(container) {
            var self = this, c;

            self.container = $(container);

            self.categories = self.container.attr('data-category').split(',');
            self.categories = $.map(self.categories, function(category) {
                c = parseInt(category, 10);
                return isNaN(c) ? null : c;
            });

            $.subscribe('/category/updated', function(event, dropdown, category) {
                if ($.contains(dropdown.closest('form').get(0), self.container.get(0))) {
                    if (self.container.hasClass('awpcp-extra-field-always-visible')) {
                        self.enable();
                    } else if (0 === self.categories.length) {
                        self.enable();
                    } else if (category !== null && $.inArray(category, self.categories) !== -1) {
                        self.enable();
                    } else {
                        self.disable();
                    }
                }
            });
        };

        $.extend($.AWPCP.ExtraField.prototype, {
            enable: function() {
                var elements = this.container.find(':input');
                if (elements.prop) {
                    elements.prop('disabled', false);
                } else {
                    elements.removeAttr('disabled');
                }
                this.container.show().removeClass('awpcp-extra-field-hidden');
            },

            disable: function() {
                var elements = this.container.find(':input');
                if (elements.prop) {
                    elements.prop('disabled', true);
                } else {
                    elements.attr('disabled', 'disabled');
                }
                this.container.hide().addClass('awpcp-extra-field-hidden');
            }
        });

        // handler to show Extra Fields when a particular category is selected
        // it works both in Place Ad page and Search Ads page
        $(function() {
            $('.awpcp-extra-field').each(function() {
                $.noop(new $.AWPCP.ExtraField($(this)));
            });
        });

    })(jQuery);
}
