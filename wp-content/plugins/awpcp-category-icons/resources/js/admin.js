/* global AWPCP */
AWPCP.run( 'awpcp/manage-category-icons', [
    'jquery',
    'knockout',
    'awpcp/custom-category-icons-uploader',
    'awpcp/category-icons-manager',
    'awpcp/media-uploader-view',
    'awpcp/settings',
    'awpcp/jquery-messages'
],
function( $, ko, CustomCategoryIconsUploader, CategoryIconsManager, MediaUploaderView, settings ) {
    $( function() {
        var form = $( '#awpcp-manage-category-icons-form' );

        if ( form.length ) {
            var options = settings.get( 'manage-category-icons-options' ),
                data = settings.get( 'category-icons-data' );

            var model = new CustomCategoryIconsUploader( { settings: options } );

            $.noop(new MediaUploaderView({
                el: $( '.awpcp-media-uploader' ),
                model: model
            }));

            CategoryIconsManager.init( form.find( '.awpcp-category-icons-manager' ).get(0), data, options );

            form.find( '.awpcp-manage-category-icons-tabs' ).tabs();
            form.find( '.awpcp-messages' ).appendTo( form.find( '.awpcp-custom-icons.awpcp-tabs-panel' ) ).AWPCPMessages();
        }
    } );
} );
