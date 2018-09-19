/*global list_args*/
(function($, undefined) {
    $(function() {
        $('.wp-list-table.comments').admin({
            actions: {
                remove: 'awpcp-delete-comment',
                edit: 'awpcp-edit-comment'
            },
            ajaxurl: $.AWPCP.get('ajaxurl'),
            data: list_args, // screen settings
            base: '#comment-',
            ignore: ['flag', 'unflag', 'spam', 'unspam', 'view-ad']
        });
    });
})(jQuery);
