<?php
require 'renderer.php';

class RssRenderer extends AbstractRenderer
{

    public function __construct($recursion_limit = 0)
    {
        $this->recursion_limit = $recursion_limit;
    }

    public function render_parsed_tweet($currentitem, $parsedTweet)
    {
        $args = array_merge(get_defined_vars(), $this->prepare_data_array($currentitem));
        $args['renderer'] = $this;
        return template('tldlib/renderers/rss_item.php', $args);
    }

    public function prepare_data_array($currentitem)
    {
        if (isset($currentitem['retweeted_status'])) {
            return [
                'avatar' => $currentitem['retweeted_status']['user']['profile_image_url_https'],
                'rt' => '&nbsp;&nbsp;&nbsp;&nbsp;[<em style="font-size:smaller;">Retweeted by '
                    . $currentitem['user']['name'] . ' - '
                    . ' <a href=\'http://twitter.com/' . $currentitem['user']['screen_name'] . '\'>@' . $currentitem['user']['screen_name'] . '</a>'
                    . ' - '
                    . ' <a href=\'http://twitter.com/' . $currentitem['user']['screen_name'] . '/' . $currentitem['id_str'] . '\'>See RT</a>'
                    . '</em>]',
                'tweeter' => $currentitem['retweeted_status']['user']['screen_name'],
                'fullname' => $currentitem['retweeted_status']['user']['name'],
                'tweetTitle' => $currentitem['retweeted_status']['full_text'],
                'entities' => $currentitem['retweeted_status']['entities'],
                'id_str' => $currentitem['retweeted_status']['id_str']
            ];
        } else {
            return [
                'avatar' => $currentitem['user']['profile_image_url_https'],
                'rt' => '',
                'tweeter' => $currentitem['user']['screen_name'],
                'fullname' => $currentitem['user']['name'],
                'tweetTitle' => $currentitem['full_text'],
                'entities' => $currentitem['entities'],
                'id_str' => $currentitem['id_str']
            ];
        }
    }

    public function render_feed($config, $tweets)
    {
        $args = [];
        $args['renderer'] = $this;
        $args['tweets'] = $tweets;
        $args = array_merge($args, $config, $tweets);
        return template('tldlib/renderers/rss_feed.php', $args);
    }

    public function render_tweet_html($args)
    {
        $args['renderer'] = $this;
        return template('tldlib/renderers/rss_item_html_enclosure.php', $args);
    }

    public function render_quoted_content($url, $recursion_level = 0)
    {
        if (array_key_exists('expanded_url', $url)) {
            if (strpos($url['expanded_url'], 'twitter.com')) {
                if ($recursion_level < $this->recursion_limit) {
                    try {
                        $content = $this->client->get_remote_content($url);
                        // If there is something in array, it must be a tweet !
                        // Else don't show anything
                        if (empty($content)) {
                            return "";
                        } else {
                            $args = $this->prepare_data_array($content);
                            $args['renderer'] = $this;
                            $args['currentitem'] = $content;
                            $args['parsedTweet'] = $this->create_parsed_tweet($content);
                            $args['recursion_level'] = $recursion_level+1;
                            return template('tldlib/renderers/rss_item_html_enclosure.php', $args);
                        }
                    } catch(Exception $e) {
                        return $e->getMessage();
                    }
                } else {
                    return "";
                }
            } else {
                return template('tldlib/renderers/rss_item_html_external_url.php', $url);
            }
        } else {
            return "I don't know what to do ...";
        }
    }
}
?>
