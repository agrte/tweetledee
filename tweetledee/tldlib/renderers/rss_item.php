<item>
    <title>[<?php echo $tweeter; ?>] <?php echo $tweetTitle; ?> </title>
    <author><?php echo $currentitem['user']['screen_name']?></author>
    <pubDate><?php echo reformatDate($currentitem['created_at']); ?></pubDate>
    <link>https://twitter.com/<?php echo $tweeter ?>/statuses/<?php echo $id_str; ?></link>
    <guid isPermaLink='false'><?php echo $currentitem['id_str']; ?></guid>
    <description>
<?php echo '<![CDATA['; ?>
<?php echo $renderer->render_tweet_html([
    'avatar' => $avatar,
    'rt' => $rt,
    'tweeter' => $tweeter,
    'fullname' => $fullname,
    'tweetTitle' => $tweetTitle,
    'currentitem' => $currentitem,
    'recursion_level' => 0,
    'parsedTweet' => $parsedTweet,
    'entities' => $entities
])?>

<?php echo ']]>'; ?>
</description>
    </item>
