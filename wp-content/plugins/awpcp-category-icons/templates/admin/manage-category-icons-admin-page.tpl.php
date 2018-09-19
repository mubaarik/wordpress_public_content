<?php if ( ! is_null( $category ) ): ?>
<p><?php
    $message = __( 'You are managing the icon associated with the category <category-name>. Select an icon from below to set or change the associated icon.', 'another-wordpress-classifieds-plugin' );
    echo str_replace( '<category-name>', "<b>{$category->name}</b>", $message );
?><p>
<?php endif; ?>

<form id="awpcp-manage-category-icons-form" class="awpcp-manage-category-icons-form" method="post">
    <div class="awpcp-category-icons-manager">
        <input type="hidden" name="selected-icon" data-bind="value: selectedIcon" />

        <div class="awpcp-selected-category-icon" data-bind="visible: selectedIcon">
            <?php $alt_text = str_replace( '<category-name>', $category->name, __( 'Icon for <category-name>', 'another-wordpress-classifieds-plugin' ) ); ?>
            <span class="awpcp-selected-category-icon-title"><?php echo esc_html( __( 'Current Icon:', 'another-wordpress-classifieds-plugin' ) ); ?></span>
            <div class="awpcp-selected-category-icon-image-container"><img alt="<?php echo esc_attr( $alt_text ); ?>" data-bind="attr: { src: selectedIconUrl }" /></div>
        </div>

        <div class="awpcp-manage-category-icons-tabs awpcp-tabs">
            <ul class="awpcp-tabs-nav clearfix">
                <li class="awpcp-tabs-tab"><a href="#awpcp-standard-icons"><?php echo __( 'Standard Icons', 'another-wordpress-classifieds-plugin' ); ?></a></li>
                <li class="awpcp-tabs-tab"><a href="#awpcp-custom-icons"><?php echo __( 'Custom Icons', 'another-wordpress-classifieds-plugin' ); ?></a></li>
            </ul>

            <div id="awpcp-standard-icons" class="awpcp-standard-icons awpcp-tabs-panel">
                <p>
                    Exactly 1000 icons from <a href="http://www.famfamfam.com/lab/icons/silk/">famfamfam</a> Silk set are included in the standard package.
                    The icons were packed in one sprite for easy embedding by <a href="https://github.com/p-h-p/famfamfam">Dejan Marjanovic</a>.
                </p>

                <ul class="clearfix">
                <?php foreach ( $standard_icons as $icon ): ?>
                    <li>
                        <input id="awpcp-category-icon-<?php echo $icon; ?>" type="radio" name="selected-icon" value="standard:<?php echo $icon; ?>.png" data-bind="checked: selectedIcon">
                        <label for="awpcp-category-icon-<?php echo $icon; ?>" class="awpcp-category-icon awpcp-category-icon-<?php echo $icon; ?>" ></label>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>

            <div id="awpcp-custom-icons" class="awpcp-custom-icons awpcp-tabs-panel">
                <p>
                    The standard icons are all 32x32 pixel images. You can upload custom icons with any dimensions.
                    The plugin will not resize the file after they are uploaded; the images will be shown in the
                    frontend in its original form. We recommend you to upload custom icons with the same dimensions
                    as the standard icons, but you are free to upload larger images if you require so.
                </p>

                <?php $media_uploader = awpcp_category_icon_uploader_component(); ?>
                <?php echo $media_uploader->render( array() ); ?>

                <ul class="awpcp-custom-icons-list clearfix" data-bind="foreach: customIcons">
                    <li class="awpcp-custom-icons-list-item">
                        <div class="awpcp-custom-icons-list-item-container">
                            <div class="awpcp-custom-icons-list-item-toolbar">
                                <input type="radio" name="selected-icon" data-bind="attr: { id: id, value: 'custom:' + id }, checked: $root.selectedIcon">
                                <a href="javascript:;" title="<?php echo __( 'Delete Custom Icon', 'awpcp-category-icons' ); ?>" data-bind="click: $root.onDeleteIconButtonClicked"><span class="awpcp-category-icon awpcp-category-icon-image_delete"></span></a>
                                <a href="javascript:;"><span class="awpcp-spinner awpcp-spinner-visible" data-bind="visible: isBeingModified"></span></a>
                            </div>
                            <label class="awpcp-custom-icons-list-item-preview" data-bind="attr: { 'for': id }"><img data-bind="attr: { src: url }"></label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?php $messages = awpcp_messages_component(); ?>
    <?php echo $messages->render( array( 'custom-icons-manager' ) ); ?>

    <div>
        <input class="button button-primary" type="submit" value="<?php echo __( 'Set Category Icon', 'another-wordpress-classifieds-plugin' ); ?>" name="set-category-icon">
        <input class="button button-secondary" type="submit" value="<?php echo __( 'Clear Category Icon', 'another-wordpress-classifieds-plugin' ); ?>" name="clear-category-icon">
    </div>
</form>
