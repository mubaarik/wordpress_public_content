<tr style="" class="inline-edit-row quick-edit-row alternate inline-editor" id="edit-1">
    <td class="colspanchange" colspan="6">
        <?php $label = _e( 'Edit', 'awpcp-comments-ratings' ); ?>
        <form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
        <fieldset class="inline-edit-col-left"><div class="inline-edit-col">
                <h4><?php echo $label; ?></h4>

                <label>
                    <span class="title"><?php _e('Title', 'awpcp-comments-ratings' ) ?></span>
                    <span class="input-text-wrap"><input type="text" value="<?php echo esc_attr($entry->title) ?>" name="title"></span>
                </label>

                <label>
                    <span class="title"><?php _e('Comment', 'awpcp-comments-ratings' ) ?></span>
                    <textarea name="comment" rows="1" cols="22" autocomplete="off"><?php echo esc_textarea($entry->comment)?></textarea>
                </label>
        </fieldset>

        <p class="submit inline-edit-save">
            <?php $cancel = __( 'Cancel', 'awpcp-comments-ratings' ); ?>
            <a class="button-secondary cancel alignleft" title="<?php echo esc_attr( $cancel ); ?>" href="#inline-edit" accesskey="c"><?php echo esc_html( $cancel ); ?></a>
            <a class="button-primary save alignright" title="<?php echo esc_attr( $label ); ?>" href="#inline-edit" accesskey="s"><?php echo esc_html( $label ); ?></a>
            <img alt="" src="http://local.wordpress.org/wp-admin/images/wpspin_light.gif" style="display: none;" class="waiting">
            <input type="hidden" value="<?php echo esc_attr( $entry->id ); ?>" name="id">
            <input type="hidden" value="<?php echo esc_attr( $_POST['action'] ); ?>" name="action">
            <br class="clear">
        </p>
        </form>
    </td>
</tr>
