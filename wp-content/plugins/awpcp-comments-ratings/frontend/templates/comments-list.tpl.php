<h2><?php _e('Comments', 'awpcp-comments-ratings' ) ?></h2>
<ul class="awpcp-comments">
<?php foreach($items as $i => $item): ?>
    <?php include('comments-list-item.tpl.php'); ?>
<?php endforeach; ?>
</ul>
