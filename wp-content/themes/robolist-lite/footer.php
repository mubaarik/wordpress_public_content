<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package robolist-lite
 */

?>
<footer>
    <?php if ( (is_active_sidebar('robolist_lite_footer_1') || is_active_sidebar('robolist_lite_footer_2') || is_active_sidebar('robolist_lite_footer_3')|| is_active_sidebar('robolist_lite_footer_4'))) { ?>

    <div class="prefooter">
        <div class="container">
            <div class="row">

                <?php if (is_active_sidebar('robolist_lite_footer_1')) :?>
                    <div class="col-md-3">
                        <?php dynamic_sidebar('robolist_lite_footer_1') ?>
                    </div>
                <?php endif; ?>

                <?php if (is_active_sidebar('robolist_lite_footer_2')) :?>
                    <div class="col-md-3 col-sm-6">
                        <?php dynamic_sidebar('robolist_lite_footer_2') ?>
                    </div>
                <?php endif; ?>

                <?php if (is_active_sidebar('robolist_lite_footer_3')) :?>
                    <div class="col-md-3 col-sm-6">
                        <?php dynamic_sidebar('robolist_lite_footer_3') ?>
                    </div>
                <?php endif; ?>

                <?php if (is_active_sidebar('robolist_lite_footer_4')) :?>
                    <div class="col-md-3 col-sm-6">
                        <?php dynamic_sidebar('robolist_lite_footer_4') ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <?php } ?>

    <div class="botfooter">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="copyright">
                        <p>
                            <a href="<?php echo esc_url(__('https://wordpress.org/', 'robolist-lite')); ?>"><?php
                                /* translators: %s: CMS name, i.e. WordPress. */
                                printf(esc_html__('Proudly powered by %s', 'robolist-lite'), 'WordPress');
                                ?></a> <span class="sep"> | </span>
                            <?php
                            /* translators: 1: Theme name, 2: Theme author. */
                            printf(esc_html__('Theme: %1$s by %2$s.', 'robolist-lite'), '<a target="_blank" href="https://codethemes.co/product/robolist-lite" rel="nofollow" title="Robolist Lite">Robolist Lite</a>', 'Code Themes'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
