/* global AWPCP */
AWPCP.define( 'campaign-manager/jquery-campaign-advertisement-position-content-form', [ 'jquery' ],
function( $ ) {
    var CampaignAdvertisementPositionContentForm = function( container ) {
        this.container = $( container );
        this.setup();
    };

    $.extend( CampaignAdvertisementPositionContentForm.prototype, {
        setup: function() {
            var self = this;

            self.form = self.container.find( 'form' );
            self.contentTypeOptions = self.container.find( '.awpcp-advertisement-position-content-type-field' );
            self.uploadImageField = self.container.find( '.awpcp-advertisement-position-upload-field' );
            self.customContentField = self.container.find( '.awpcp-advertisement-position-content-field' );
            self.loadingIcon = self.container.find( '.spinner' );

            self.setupPlugins();
            self.showActiveField();
        },

        setupPlugins: function() {
            var self = this;

            self.contentTypeOptions.buttonset();
            self.contentTypeOptions.find( ':radio' ).change( function() {
                self.showActiveField();
            } );

            self.form.ajaxForm( {
                beforeSubmit: function() {
                    self.loadingIcon.addClass( 'is-visible-inline-block' );
                },

                success: function( response ) {
                    self.loadingIcon.removeClass( 'is-visible-inline-block' );

                    if ( response.status === 'ok' ) {
                        var form = $( response.html ).find( 'form' );
                        self.container.find( '.inside' ).html( form );
                        self.setup();
                    } else {
                        var errors = $( '<div class="awpcp-error awpcp-inline-form-error">' );

                        $.each(response.errors, function(k,v) {
                            errors.append(v + '</br>');
                        });

                        self.form.append(errors);
                    }
                }
            } );
        },

        showActiveField: function() {
            var self = this;

            if ( self.contentTypeOptions.find( ':checked' ).val() === 'image' ) {
                self.uploadImageField.show();
                self.customContentField.hide();
            } else {
                self.customContentField.show();
                self.uploadImageField.hide();
            }
        }
    } );

    $.fn.CampaignAdvertisementPositionContentForm = function() {
        return $( this ).each( function() {
            $.noop( new CampaignAdvertisementPositionContentForm( $( this ) ) );
        } );
    };
} );
