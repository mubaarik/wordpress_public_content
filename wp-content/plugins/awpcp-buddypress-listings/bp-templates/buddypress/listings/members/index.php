<?php if ( version_compare( awpcp_buddypress_wrapper()->version(), '2.1', '<' ) ): ?>
<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
    <ul><?php bp_get_options_nav(); ?></ul>
</div>
<?php endif; ?>

<?php bp_get_template_part( 'listings/listings-loop' ); ?>
