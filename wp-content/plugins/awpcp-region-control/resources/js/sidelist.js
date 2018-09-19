/*global AWPCP*/

AWPCP.define('regions/sidelist', ['jquery', 'awpcp/jquery-collapsible'],
function($/*, collapsible*/) {
    return function (container) {
        function loadHTMLContent() {
            return $.ajax(container.data('url'), {
                dataType: 'json'
            });
        }

        return {
            render: function() {
                var self = this, request = loadHTMLContent();

                request.done(function(response) {
                    container.html( response.html ).find('li').collapsible();
                    self.expandActiveRegion( container, container.data( 'active-region' ) );
                });

                // request.fail(function(response) {});

                // request.always(function(response) {});
            },

            expandActiveRegion: function( container, regionId ) {
                var activeRegionHandler, parentRegionsHandlers, handlers;

                activeRegionHandler = container.find( '#region-' + regionId + ' .js-handler' );
                parentRegionsHandlers = $( activeRegionHandler.parents( '.js-handler' ).get().reverse() );
                handlers = parentRegionsHandlers.add( activeRegionHandler );

                handlers.click();
            }
        };
    };
});
