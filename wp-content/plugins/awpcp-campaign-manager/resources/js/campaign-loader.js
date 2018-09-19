/* global AWPCP */
AWPCP.define( 'campaign-manager/campaign-loader', [ 'jquery', 'awpcp/settings' ],
function( $, settings ) {
    var CampaignLoader = function( elements ) {
        var self = this;

        self.placeholders = {};
        self.campaigns = [];

        self.findCampaignPlaceholders( elements );
    };

    $.extend( CampaignLoader.prototype, {
        findCampaignPlaceholders: function( elements ) {
            var self = this;

            elements.each( function( index, element ) {
                var placeholder = $( element ),
                    campaign = {
                        position: placeholder.attr( 'data-position' ),
                        category: placeholder.attr( 'data-category' ),
                        page: placeholder.attr( 'data-page' )
                    };

                if ( typeof self.placeholders[ campaign.position ] !== 'undefined' ) {
                    self.placeholders[ campaign.position ] = self.placeholders[ campaign.position ].add( placeholder );
                } else {
                    self.placeholders[ campaign.position ] = placeholder;
                }

                self.campaigns.push( campaign );
            } );
        },

        loadCampaigns: function() {
            var self = this;

            if ( self.campaigns.length === 0 ) {
                return;
            }

            $.getJSON( settings.get( 'ajaxurl' ), {
                action: 'awpcp-load-campaigns',
                campaigns: self.campaigns
            }, function( response ) {
                if ( response.status === 'ok' ) {
                    self.insertAdvertisements( response.advertisements );
                }
            });
        },

        insertAdvertisements: function( advertisements ) {
            var self = this;

            $.each( advertisements, function( position, content ) {
                if ( content.length ) {
                    self.placeholders[ position ].html( content );
                } else {
                    self.placeholders[ position ].addClass( 'is-empty' );
                }
            } );
        }
    } );

    return CampaignLoader;
} );
