/*global AWPCP*/

AWPCP.define('comments/comment', ['jquery', 'awpcp/settings'],
function($, settings) {
    var Comment = function(element) {
        this.element = $(element);
        this.id = parseInt(this.element.attr('data-comment-id'), 10);

        this.init();
    };

    $.extend(Comment.prototype, {
        init: function() {
            var self = this;

            this.element.delegate('.awpcp-comment-actions a', 'click', function(event) {
                event.preventDefault();

                var link = $(this),
                    action = link.closest('li').attr('class');

                $.getJSON(settings.get('ajaxurl'), {
                    id: self.id,
                    action: 'awpcp-comments-' + action + '-comment'
                }, function(response) {
                    if (response.status && response.status === 'ok') {
                        if (action === 'edit') {
                            self.show_edit_form(response);
                        } else if (action === 'delete') {
                            self.show_delete_form(response);
                        } else if (action === 'flag') {
                            self.flag_comment(response);
                        }
                    } else {
                        self.show_errors(response);
                    }
                });
            });
        },

        /**
         * Shows the updated version of comment.
         */
        show_comment: function(response) {
            var self = this,
                comment = this.element.find('.awpcp-comment-container');

            self.form.remove();
            comment.replaceWith($(response.html).find('.awpcp-comment-container'));

            self.remove_errors();
        },

        remove_comment: function(/*response*/) {
            this.element.remove();
        },

        flag_comment: function(/*response*/) {
            this.element.find('li.flag').fadeOut();
        },

        /**
         * Shows the edit comment form.
         */
        show_edit_form: function(response) {
            var self = this,
                comment = this.element.find('.awpcp-comment-container');

            // include comment form
            self.form = $(response.html);
            self.form.appendTo(self.element).find('textarea').focus();

            self.form.find('.cancel').click(function() {
                self.form.remove();
                self.remove_errors();
                comment.show();
            });

            self.form.ajaxForm({
                url: settings.get('ajaxurl'),
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'awpcp-comments-save-comment'
                },
                success: function(response) {
                    if (response.status && response.status === 'error') {
                        self.show_errors(response);
                    } else {
                        self.show_comment(response);
                    }
                }
            });

            // hide current comment body
            comment.hide();

            self.remove_errors();
        },

        show_delete_form: function(response) {
            var self = this;

            // include comment form
            self.form = $(response.html);
            self.form.appendTo(self.element);

            self.form.find('.cancel').click(function() {
                self.form.remove();
                self.remove_errors();
            });

            self.form.ajaxForm({
                url: settings.get('ajaxurl'),
                type: 'post',
                dataType: 'json',
                data: {
                    id: self.id,
                    confirmed: true,
                    action: 'awpcp-comments-delete-comment'
                },
                success: function(response) {
                    if (response.status && response.status === 'error') {
                        self.show_errors(response);
                    } else {
                        self.remove_comment(response);
                    }
                }
            });

            self.remove_errors();
        },

        /**
         * Shows errors.
         */
        show_errors: function(response) {
            var self = this;

            // form errors
            if (self.form && self.form.parent().length) {
                $.each(response.errors, function(k, error) {
                    self.element.append('<div class="awpcp-error">' + error + '</div>');
                });
            // other errors
            } else {
                $.each(response.errors, function(k, error) {
                    self.element.prepend('<div class="awpcp-error">' + error + '</div>');
                });
            }
        },

        remove_errors: function() {
            this.element.find('.awpcp-error').remove();
        }
    });

    return Comment;
});
