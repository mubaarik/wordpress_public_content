<h2><?php _e( 'Upload Files', 'awpcp-attachments' ); ?></h2>

<?php
    foreach ( $messages as $message ) {
        echo awpcp_print_message( $message );
    }

    foreach( $errors as $error ) {
        echo awpcp_print_message( $error, array( 'error' ) );
    }
?>

<ul class="upload-conditions clearfix">
    <li><?php _e( 'Image slots available', 'awpcp-attachments' ); ?>: <strong><?php echo $images_left; ?></strong></li>
    <li><?php _e( 'Max image size', 'awpcp-attachments' ); ?>: <strong><?php echo $max_image_size / 1000; ?> KB</strong></li>
    <li><?php _e( 'Max attachment size', 'awpcp-attachments' ); ?>: <strong><?php echo $max_attachment_size / 1000; ?> KB</strong></li>
</ul>

<?php $is_primary_set = false; ?>

<?php
    $url = add_query_arg( $hidden, $page->url() );
    $link = '<a href="%1$s" title="%2$s"><span>%2$s</span></a>';
?>

<?php $fm = awpcp_file_manager_component(); ?>
<?php $fm->configure( array( 'images_allowed' => $images_allowed ) ); ?>
<?php echo $fm->render( $listing, array_merge( $files, $images ) ); ?>

<h3><?php _e( 'Add Files', 'awpcp-attachments' ) ?></h3>

<p><?php _ex( 'Use the check icons in front of each upload field to mark an uploaded image as the primary image for the Ad.', 'upload files step', 'awpcp-attachments' ); ?></p>

<form class="awpcp-upload-images-form" method="post" enctype="multipart/form-data">

    <?php include( AWPCP_DIR . '/frontend/templates/page-place-ad-upload-fields.tpl.php' ); ?>

    <p class="form-submit">
        <input class="button" type="submit" value="<?php echo esc_attr( $next ); ?>" id="awpcp-submit-no-images" name="submit-no-images">
        <input class="button" type="submit" value="<?php _e( 'Upload Files', 'awpcp-attachments' ); ?>" id="awpcp-submit-with-images" name="submit">

        <input type="hidden" name="step" value="upload-images">
        <?php foreach ( $hidden as $name => $value ): ?>
        <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
        <?php endforeach; ?>
    </p>
</form>
