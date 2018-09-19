<?php $page_id = 'awpcp-admin-comments' ?>
<?php $page_title = awpcp_admin_page_title( __( 'Comments', 'awpcp-comments-ratings' ) ); ?>
<?php $show_sidebar = false ?>

<?php include(AWPCP_DIR . '/admin/templates/admin-panel-header.tpl.php') ?>

        <?php $table->views(); ?>
		<?php $table->display(); ?>

		</div><!-- end of .awpcp-main-content -->
	</div><!-- end of .page-content -->
</div><!-- end of #page_id -->
