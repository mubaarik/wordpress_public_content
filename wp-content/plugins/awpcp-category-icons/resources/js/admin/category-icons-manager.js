/* global AWPCP */
AWPCP.define( 'awpcp/category-icons-manager', [ 'jquery', 'knockout', 'awpcp/settings' ],
function( $, ko, settings ) {
    var CategoryIconsManager = function( element, data, options ) {
        var self = this;

        self.selectedIcon = ko.observable( data.selectedIcon );
        self.customIcons = ko.observableArray( self.processCustomIcons( data.customIcons ) );

        self.selectedIconUrl = ko.computed( function() {
            var icon = self.selectedIcon(), baseurl, filename;

            if ( icon.indexOf( 'custom:' ) === 0 ) {
                baseurl = options.custom_icon_base_url;
                filename = icon.replace( 'custom:', '' );
            } else {
                baseurl = options.standard_icon_base_url;
                filename = icon.replace( 'standard:', '' );
            }

            return filename.length ? baseurl + filename : '';
        } );

        $.subscribe( '/custom-icon/uploaded', onCustomIconUploaded );

        self.onDeleteIconButtonClicked = function( icon ) {
            icon.isBeingModified( true );

            $.post( settings.get( 'ajaxurl' ), {
                action: 'awpcp-delete-custom-category-icon',
                filename: icon.name
            }, function( response ) {
                if ( response.status === 'ok' ) {
                    self.customIcons.remove( icon );
                    $.publish( '/custom-icon/deleted', [ icon ] );
                } else if ( response.status === 'error' ) {
                    $.publish( '/messages/custom-icons-manager', { type: 'error', 'content': response.errors.join( ' ' ) } );
                }

                icon.isBeingModified( false );
            } );
        };

        function onCustomIconUploaded( event, file, customIcon ) {
            self.customIcons.push( self.processCustomIcon( customIcon ) );
        }
    };

    $.extend( CategoryIconsManager.prototype, {
        processCustomIcons: function( customIcons ) {
            return $.map( customIcons, this.processCustomIcon );
        },

        processCustomIcon: function( icon ) {
            return {
                id: icon.id,
                name: icon.name,
                url: icon.url,
                isBeingModified: ko.observable( false )
            };
        }
    } );

    return {
        init: function( element, data, options ) {
            var model = new CategoryIconsManager( element, data, options );

            ko.applyBindings( model, element );

            return model;
        }
    };
} );
