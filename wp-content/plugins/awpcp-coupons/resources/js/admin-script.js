/*global AWPCP, ajaxurl */
AWPCP.run('awpcp-coupons/admin-script', [ 'jquery', 'awpcp/datepicker-field' ],
function ($, DatepickerField) {
        /* Handlers for Manage Coupons page */
        $(function() {
            var plans = $('#awpcp-admin-coupons');
            if (plans.length) {
                plans.admin({
                    actions: {
                        add: 'awpcp-add-coupon',
                        remove: 'awpcp-delete-coupon',
                        edit: 'awpcp-edit-coupon'
                    },
                    ajaxurl: ajaxurl,
                    base: '#coupon-',
                    onFormReady: function(action, inline) {
                        var form = inline.find('form'),
                            expire = form.find('input[name=expire_date]');

                        $.noop( new DatepickerField( expire ) );
                    }
                });
            }
        });
} );
