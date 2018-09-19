<?php if ( count( $files ) > 0 ): ?>
<div class="awpcp-attachments">
    <div class="awpcp-attachments-title"><?php _e( 'Attachments', 'awpcp-attachments' ); ?></div>
    <ul class="awpcp-attachments-list">
        <?php foreach ( $files as $file ): ?>
        <li>
            <img src="<?php echo esc_attr( $file->get_icon_url() ); ?>" />&nbsp;
            <a href="<?php echo esc_attr( $file->get_original_file_url() ); ?>" title="<?php echo esc_attr( $file->name ); ?>" target="_blank"><?php echo esc_html( $file->name ); ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
