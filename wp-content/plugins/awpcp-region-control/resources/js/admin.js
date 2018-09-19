/*global AWPCP:true, ajaxurl:true */

if (jQuery !== undefined) {

    var AWPCP = jQuery.AWPCP = jQuery.extend({}, jQuery.AWPCP, AWPCP);

    /**
     * RegionParent field
     */
    (function($, undefined) {

        $.AWPCP.RegionParentField = function(element) {
            var self = this;
            self.element = $(element);
            self.form = self.element.closest('form');
            self.type = self.form.find('[name="regions_region_type"]');

            self.form.find('[name="regions_region_parent_name"]').autocomplete({
                source: function(request, response) {
                    $.getJSON(ajaxurl, {
                        action: 'awpcp-region-control-autocomplete',
                        term: request.term,
                        type: self.type.val()
                    }, function(data) {
                        if (data.items) {
                            response(data.items);
                        }
                    });
                },

                select: function(event, ui) {
                    if (ui.item) {
                        self.element.val(ui.item.id);
                    }
                }
            });
        };

    })(jQuery);

    /**
     * Handle Regions Admin
     */
    (function($, undefined) {
        /* Handle delete regions in Region admin page */
        $('#myregions table.listcatsh').delegate('td > a.delete', 'click', function(event) {
            event.preventDefault();

            var link = $(event.target),
                row = link.closest('tr'),
                columns = row.find('td').length;
            $.post(ajaxurl, {
                id: row.data('region-id'),
                action: 'awpcp-delete-region',
                columns: columns
            }, function(response) {
                var inline = $(response.html).insertAfter(row);

                inline.find('a.cancel').click(function() {
                    row.show();
                    inline.remove();
                });

                var form = inline.find('form');

                inline.delegate('a.delete', 'click', function() {
                    var waiting = inline.find('img.waiting').show();
                    form.ajaxSubmit({
                        data: { 'remove': true },
                        dataType: 'json',
                        success: function(response) {
                            // mission acomplished!
                            if (response.status === 'success') {
                                row.remove();
                                inline.remove();
                            } else {
                                waiting.hide();
                                form.find('div.error').remove();
                                form.append('<div class="error"><p>' + response.message + '</p></div>');
                            }
                        }
                    });
                });
            });
        });

        /* Handle regions forms */
        $(function() {
            var selector = '[name="regions_region_parent"]', parent;

            $.AWPCP.validate();

            $.validator.addMethod('region-parent', function(value, element) {
                var parent_region = parseInt($(element).closest('form').find(selector).val(), 10);
                var parent_type = $(element).closest('form').find('[name="regions_region_type"]').val();

                if (parent_type == 1) {
                    return true;
                }

                return value !== "" && (this.optional(element) || parent_region > 0);
            }/*, validation message provided as a default validation message in awpcp.php */);

            parent = $(selector).each(function() {
                $.noop(new $.AWPCP.RegionParentField(this));
            });

            parent.closest('form').each(function() {
                $(this).validate({
                    rules: {
                        'regions_region_parent_name': {
                            required: false,
                            'region-parent': true
                        }
                    },
                    messages: {
                        'regions_region_parent_name': $.AWPCP.l10n('region-control-admin', 'region_parent')
                    },
                    errorPlacement: function(error, element) {
                        error.addClass('awpcp-error');
                        element.closest('div, p').append(error);
                    }
                });
            });
        });
    }(jQuery));
}
