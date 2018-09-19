/* global AWPCP, plupload, Backbone */

AWPCP.define( 'awpcp/custom-category-icons-uploader', ['jquery', 'awpcp/settings'],
function ( $, settings ) {
    var CustomCategoryIconsUploader = Backbone.Model.extend({
        prepareUploader: function( container, dropzone, browseButton ) {
            var self = this;

            self.uploader = new plupload.Uploader({
                browse_button: browseButton.get(0),
                url: settings.get( 'ajaxurl' ),
                container: container.get(0),
                drop_element: dropzone.get(0),
                filters: {
                    mime_types: [
                        { title: 'Custom Icons', extensions: 'jpg,gif,png' }
                    ],
                    prevent_duplicates: true
                },
                multipart_params: {
                    action: 'awpcp-upload-custom-category-icon'
                },
                chunk_size: '10000000',
                runtimes : 'html5,flash,silverlight,html4',
                multiple_queues: true,
                flash_swf_url : self.get('settings').flash_swf_url,
                silverlight_xap_url : self.get('settings').silverlight_xap_url
            });

            self.uploader.init();

            self.uploader.bind( 'FilesAdded', self.onFilesAdded, self );
            self.uploader.bind( 'FileUploaded', self.onFileUplaoded, self );
        },

        onFilesAdded: function( uploader/*, files*/ ) {
            uploader.start();
        },

        onFileUplaoded: function( uploader, file, data ) {
            var response = $.parseJSON( data.response );

            if ( response.status === 'ok' && response.file ) {
                $.publish( '/custom-icon/uploaded', [ file, response.file ] );
            } else if ( response.status !== 'ok' ) {
                file.status = plupload.FAILED;
                uploader.trigger( 'UploadProgress', file );
            }
        }
    });

    return CustomCategoryIconsUploader;
} );
