<tr style="" class="inline-edit-row quick-edit-row alternate inline-editor awpcp-row">
    <td class="colspanchange" colspan="<?php echo $columns; ?>">
        <form class="awpcp-campaign-section-form clearfix" action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
            <?php foreach( $hidden_fields as $field_name => $field_value ): ?>
            <input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>">
            <?php endforeach; ?>
            <fieldset class="inline-edit-col-left awpcp-one-third">
                <div class="inline-edit-col">
                    <h4><?php echo esc_html( __( 'Section Category', 'awpcp-campaign-manager' ) ); ?></h4>
                    <?php
                        $dropdown = new AWPCP_CategoriesDropdown();
                        echo $dropdown->render( array(
                            'name' => 'category',
                            'label' => false,
                            'selected' => $campaign_section_data['category'],
                            'required' => true,
                            'placeholders' => array(
                                'default-option-first-level' => __( 'Select a Category', 'awpcp-campaign-manager' ),
                                'default-option-second-level' => __( 'Select a Sub-category', 'awpcp-campaign-manager' ),
                            ),
                        ) );
                    ?>
                </div>
            </fieldset>
            <fieldset class="inline-edit-col-center awpcp-one-third">
                <div class="inline-edit-col">
                    <h4><?php echo esc_html( __( 'Section Pages', 'awpcp-campaign-manager' ) ); ?></h4>
                    <input type="text" placeholder="1,3-4,6" name="pages" value="<?php echo esc_attr( $campaign_section_data['pages'] ); ?>"><span class="awpcp-campaign-section-form-spinner spinner"></span>
                    <?php $text = esc_html( __( 'There are %s pages of results for listings in category %s.' ) ); ?>
                    <p class="awpcp-campaign-section-pages-count-description help-text hidden"><?php echo sprintf( $text, '<strong class="awpcp-results-pages-count-placeholder"></strong>', '<strong class="awpcp-campaign-section-category-placeholder"></strong>' ) ?></p>
                    <p class="help-text"><?php echo esc_html( __( 'Enter a comma separated list of page numbers or page ranges. For example, if you want to select pages 1, 2, 6, 7, 8, 9, 10 and 13, you can enter: 1,2,6-10,13.' ) ); ?></p>
                </div>
            </fieldset>
            <fieldset class="inline-edit-col-right awpcp-one-third">
                <div class="inline-edit-col">
                    <h4><?php echo esc_html( __( 'Section Positions', 'awpcp-campaign-manager' ) ); ?><span class="awpcp-campaign-section-form-spinner spinner"></span></h4>
                    <ul class="awpcp-campaign-section-positions">
                    <?php foreach ( $advertisement_positions as $position_slug => $position_name ): ?>
                        <?php if ( in_array( $position_slug, $campaign_section_data['positions'] ) ): ?>
                        <li><label><input type="checkbox" name="positions[]" value="<?php echo esc_attr( $position_slug ); ?>" checked="checked"> <?php echo esc_html( $position_name ); ?></label></li>
                        <?php else: ?>
                        <li><label><input type="checkbox" name="positions[]" value="<?php echo esc_attr( $position_slug ); ?>"> <?php echo esc_html( $position_name ); ?></label></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </fieldset>
            <p class="submit">
                <?php $secondary_label = __( 'Cancel', 'awpcp-campaign-manager' ); ?>
                <a class="button-secondary cancel" title="<?php echo esc_attr( $secondary_label ); ?>" accesskey="c"><?php echo esc_html( $secondary_label ); ?></a>
                <?php $primary_label = __( 'Save Campaign Section', 'awpcp-campaign-manager' ); ?>
                <a class="button-primary save" title="<?php echo esc_attr( $primary_label ); ?>" accesskey="s"><?php echo esc_html( $primary_label ); ?></a>
                <span class="spinner awpcp-inline-form-spinner">
            </p>
        </form>
    </td>
</tr>
