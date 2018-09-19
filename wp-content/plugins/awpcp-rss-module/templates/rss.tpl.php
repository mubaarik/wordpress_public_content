<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
                   xmlns:wfw="http://wellformedweb.org/CommentAPI/"
                   xmlns:dc="http://purl.org/dc/elements/1.1/"
                   xmlns:atom="http://www.w3.org/2005/Atom"
                   xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
                   xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <channel>
        <title><?php echo $title; ?></title>
        <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
        <link><?php bloginfo_rss( 'url' ) ?></link>
        <description><?php bloginfo_rss( "description" ) ?></description>
        <lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', date('Y-m-d H:i:s', time() ), false ); ?></lastBuildDate>
        <language><?php echo substr(get_bloginfo( 'language' ), 0, 2); ?></language>
        <sy:updatePeriod><?php echo apply_filters(  'rss_update_period', 'hourly'  ); ?></sy:updatePeriod>
        <sy:updateFrequency><?php echo apply_filters(  'rss_update_frequency', 1  ); ?></sy:updateFrequency>

    <?php foreach ($items as $item): ?>

        <item>
            <title><?php echo $item['title']; ?></title>
            <link><?php echo $item['link']; ?></link>
            <pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', $item['start_date'], false ); ?></pubDate>
            <dc:creator><?php bloginfo_rss( 'name' ); ?></dc:creator>
            <category><?php echo $item['category_name']; ?></category>
            <guid isPermaLink="true"><?php echo $item['link']; ?></guid>
            <description><![CDATA[
                <?php echo $item['image']; ?> <div><?php echo $item['excerpt']; ?></div> <?php echo $item['extra-fields']; ?>
            ]]></description>
        </item>

    <?php endforeach; ?>

    </channel>
</rss>
