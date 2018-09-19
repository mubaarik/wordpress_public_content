/* global AWPCP */
AWPCP.run( 'campaign-manager/campaign-form', [ 'jquery', 'awpcp/settings', 'awpcp/category-dropdown' ],
function( $, settings ) {
    $( function() {
        var startDateDatepicker, endDateDatepicker;

        startDateDatepicker = $( '#awpcp-campaign-start-date' ).datepicker( {
            dateFormat: settings.get( 'date-format' ),
            onSelect: function( selected ) {
               endDateDatepicker.datepicker( 'option', 'minDate', selected );
            }
        });

        endDateDatepicker = $( '#awpcp-campaign-end-date' ).datepicker( {
            dateFormat: settings.get( 'date-format' )
        });

        $( '.awpcp-campaign-status' ).buttonset();
    } );

    $( function() {
        var campaignSectionsContainer = $( '.awpcp-campaign-sections' );

        campaignSectionsContainer.admin( {
            actions: {
                add: 'awpcp-add-campaign-section',
                edit: 'awpcp-edit-campaign-section',
                remove: 'awpcp-delete-campaign-section'
            },
            data: {
                campaign: parseInt( campaignSectionsContainer.attr( 'campaign-id' ), 10 )
            },
            ajaxurl: settings.get( 'ajaxurl' ),
            onFormReady: function( action, form ) {
                var pagesCountDescription = form.find( '.awpcp-campaign-section-pages-count-description' ),
                    positionsList = form.find( '.awpcp-campaign-section-positions' ),
                    spinner = form.find( '.awpcp-campaign-section-form-spinner' );

                form.find( '.awpcp-category-dropdown' ).categorydropdown();

                var updateResultsPagesCount = function( response, categoryName ) {
                    pagesCountDescription.find( '.awpcp-results-pages-count-placeholder' ).text( response.count );
                    pagesCountDescription.find( '.awpcp-campaign-section-category-placeholder' ).text( categoryName );
                    pagesCountDescription.show();
                };

                var updatePositionsList = function( response ) {
                    var existingCheckboxes = positionsList.find( 'li :checkbox' );

                    $.each( response.positions, function( slug, name ) {
                        var checkbox = $( '<input type="checkbox" name="positions[]">' ).val( slug );

                        if ( existingCheckboxes.is( '[value="' + slug + '"]:checked' ) ) {
                            if ( $.fn.prop ) {
                                checkbox.prop( 'checked', true );
                            } else {
                                checkbox.attr( 'checked', 'checked' );
                            }
                        }

                        positionsList.append( $( '<li>' )
                            .append( $( '<label>' ).text( name )
                                .prepend( checkbox ) ) );
                    } );

                    existingCheckboxes.closest( 'li' ).remove();
                };

                var loadResultPagesCount = function( event, dropdown, category ) {
                    if ( category === null ) {
                        return;
                    }

                    // clean up observer
                    if ( ! $.contains( document, form[0] ) ) {
                        $.unsubscribe( '/category/updated', loadResultPagesCount );
                        return;
                    }

                    var categoryName = dropdown.find( '[value="' + category + '"]' ).text();

                    spinner.addClass( 'is-visible-inline-block' );

                    $.getJSON( settings.get( 'ajaxurl' ), {
                        action: 'awpcp-get-campaign-section-configuration-options',
                        category: category
                    }, function( response ) {
                        if ( response.status === 'ok' ) {
                            updateResultsPagesCount( response, categoryName );
                            updatePositionsList( response );
                        }
                        spinner.removeClass( 'is-visible-inline-block' );
                    });
                };


                $.subscribe( '/category/updated', loadResultPagesCount );
            },

            onSuccess: function( action, row ) {
                $.publish( '/campaign-section/updated', [ row.attr( 'data-id' ) ] );
            }
        } );
    } );
} );

AWPCP.run( 'campaign-manager/campaign-form', [ 'jquery', 'awpcp/settings', 'campaign-manager/jquery-campaign-advertisement-position-content-form' ],
function( $, settings ) {
    $( function() {
        var container = $( '.awpcp-campaign-content' );

        container.find( '.awpcp-advertisement-position-content-form' ).CampaignAdvertisementPositionContentForm();

        function showAdvertisementPositionsContentForms( forms ) {
            var siblingForm = null,
                activePositionsSlugs = [];

            $.each( forms, function( index, position ) {
                var newForm = $( position.form ),
                    existingForm = container.find( '#awpcp-advertisement-position-content-form-' + position.slug );

                if ( existingForm.length === 0 ) {
                    if ( siblingForm === null ) {
                        container.find( '.awpcp-row' ).prepend( newForm );
                    } else {
                        siblingForm.closest( '.awpcp-one-half' ).after( newForm );
                    }

                    siblingForm = newForm.CampaignAdvertisementPositionContentForm();
                } else {
                    siblingForm = existingForm;
                }

                activePositionsSlugs.push( position.slug );
            } );

            container.find( '.awpcp-advertisement-position-content-form' ).each( function() {
                var form = $( this );

                if ( $.inArray( form.attr( 'position-slug' ), activePositionsSlugs ) === -1 ) {
                    form.closest( '.awpcp-one-half' ).remove();
                }
            } );
        }

        function loadAdvertisementPositionsContentForms( /*event, section*/ ) {
            var loadingIcon = container.find( '> h3 > .spinner' );

            loadingIcon.addClass( 'is-visible-inline-block' );

            $.getJSON( settings.get( 'ajaxurl' ), {
                action: 'awpcp-load-advertisement-positions-content-forms',
                campaign: container.attr( 'campaign-id' )
            }, function( response ) {
                loadingIcon.removeClass( 'is-visible-inline-block' );
                if ( response.status === 'ok' ) {
                    showAdvertisementPositionsContentForms( response.forms );
                }
            } );
        }

        $.subscribe( '/campaign-section/updated', loadAdvertisementPositionsContentForms );
    } );
} );
