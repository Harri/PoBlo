<?php
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

class PoBloParser {

    const TITLE_MAX_LENGHT = 300;
    const CONTENT_MAX_LENGHT = 10000;
    const LINK_MAX_LENGHT = 500;
    const TIMEOUT = 5;
    const FEED_MAX_LENGHT = 1000000;

    public function read_feed($feed, $base_url) {
        $feed = trim($feed);
        # if $feed is url, let's fetch that. If not, assume it is RSS feed.
        if (filter_var($feed, FILTER_VALIDATE_URL)) {
            $ctx = stream_context_create(
                array('http' => array(
                    'timeout' => self::TIMEOUT,
                    'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:44.0) Gecko/20100101 Firefox/44.0',
                ))
            );
            # In order to handle timeouts and other error situation,
            # errors are supressed. But those must be handled!
            $feed = @file_get_contents($feed, false, $ctx, 0, self::FEED_MAX_LENGHT);
            if ($feed === False) {
                throw new Exception('Unable to fetch file: ' . $feed);
            }
        }

        # enable user error handling
        libxml_use_internal_errors(true);

        $feed = simplexml_load_string(trim($feed));

        $feed_type = $this->get_feed_type($feed);

        if ($feed_type === 'rss') {
            $feed = $this->read_rss($feed, $base_url);
        } else if ($feed_type === 'atom') {
            $feed = $this->read_atom($feed, $base_url);
        } else {
            throw new Exception('Not a feed: ' . $feed);
        }
        return $feed;
    }

    public function get_feed_type($feed) {
        $type = '';
        if (isset($feed->channel)) {
            $type = 'rss';
        } else if (isset($feed->entry)) {
            $type = 'atom';
        } else {
            $type = 'unknown';
        }
        return $type;
    }

    public function read_rss($feed, $base_url) {
        $posts = array();
        $posts['title'] = (string) $feed->channel->title;
        $posts['description'] = (string) $feed->channel->description;
        $posts['link'] = (string) $feed->channel->link;
        foreach ($feed->channel->item as $item) {
            $post = array();

            # post may contain title, description, pubdate and url
            # If

            if (isset($item->title) && mb_strlen($item->title) > 0) {
                $post['title'] = (string) $item->title;
            } else if (isset($item->description) && $item->description !== '') {
                $content_start = mb_substr((string) $item->description, 0, self::TITLE_MAX_LENGHT);
                $post['title'] = $content_start . '…';
            }

            if (isset($item->description)) {
                $post['content'] = (string) $item->description;
                $post['content'] = mb_substr($post['content'], 0, self::CONTENT_MAX_LENGHT);
            }
            # If pubdate is not set or is invalid, let's set it to False.
            # If pubdate is from future, it is set to False
            # False pubdate should be handled as "now" when saving item to DB.
            if (isset($item->pubDate)) {
                $post['pubdate'] = strtotime((string) $item->pubDate);
                if ($post['pubdate'] > time()) {
                    $post['pubdate'] = False;
                }
            } else {
                $post['pubdate'] = False;
            }
            # If isPermaLink is true,
            # let's assume that it is more reliable than link tag
            # if quid is not perma, but link is not set and guid is, lets use guid
            # and finally if all fails, lets use §base_url
            if (
                isset($item->guid) &&
                isset($item->guid->attributes()->isPermaLink) &&
                mb_strtolower($item->guid->attributes()->isPermaLink) === 'true'
            ) {
                $post['url'] = (string) $item->guid;
            } else {
                if (isset($item->link)) {
                    $post['url'] = (string) $item->link;
                } else if (isset($item->guid)) {
                    $post['url'] = (string) $item->guid;
                } else {
                    $post['url'] = $base_url;
                }
            }

            $post['url'] = mb_substr($post['url'], 0, self::LINK_MAX_LENGHT);
            $post['url'] = $this->fix_url($post['url'], $base_url);

            # if title is missing, title is url
            if (!isset($post['title']) || mb_strlen($post['title']) == 0) {
                $post['title'] = mb_substr($post['url'], 0, self::CONTENT_MAX_LENGHT);
                $post['title'] = $post['title'] . '…';
            }

            $post['title'] = strip_tags($post['title']);
            $posts[] = $post;
        }
        return $posts;
    }

    public function read_atom($feed, $base_url) {
        $posts = array();

        $posts['title'] = (string) $feed->title;
        $posts['description'] = (string) $feed->subtitle;
        $posts['link'] = (string) $feed->link->attributes()->href;
        foreach ($feed as $entry) {

            if (isset($entry->author)) {
                $post = array();

                if (isset($entry->title) && mb_strlen($entry->title) > 0) {
                    $post['title'] = (string) $entry->title;
                } else if (isset($entry->title) && $entry->content !== '') {
                    $content_start = mb_substr((string) $entry->content, 0, self::TITLE_MAX_LENGHT);
                    $post['title'] = $content_start . '…';
                }

                if (isset($entry->content)) {
                    $post['content'] = (string) $entry->content;
                    $post['content'] = mb_substr($post['content'], 0, self::CONTENT_MAX_LENGHT);
                } else if (isset($entry->summary)) {
                    $post['content'] = (string) $entry->summary;
                    $post['content'] = mb_substr($post['content'], 0, self::CONTENT_MAX_LENGHT);
                }

                if (isset($entry->published)) {
                    $post['pubdate'] = strtotime((string) $entry->published);
                    if ($post['pubdate'] > time()) {
                        $post['pubdate'] = False;
                    }
                } else {
                    $post['pubdate'] = False;
                }

                if (filter_var((string) $entry->link->attributes()->href, FILTER_VALIDATE_URL)) {
                    $post['url'] = (string) $entry->link->attributes()->href;
                } else if (filter_var((string) $entry->id, FILTER_VALIDATE_URL)) {
                    $post['url'] = (string) $entry->id;
                } else {
                    $post['url'] = $base_url;
                }

                $post['url'] = mb_substr($post['url'], 0, self::LINK_MAX_LENGHT);
                $post['url'] = $this->fix_url($post['url'], $base_url);

                # if title is missing, title is url
                if (!isset($post['title']) || mb_strlen($post['title']) == 0) {
                    $post['title'] = mb_substr($post['url'], 0, self::CONTENT_MAX_LENGHT);
                    $post['title'] = $post['title'] . '…';
                }

                $post['title'] = strip_tags($post['title']);

                $posts[] = $post;
            }

        }

        return $posts;
    }

    public function read_unknown($feed) {
        return False;
    }

    public function fix_url($raw_url, $base_url) {
        $raw_url = trim($raw_url);
        $raw_url = ltrim($raw_url, '/');

        if ($this->starts_with($raw_url, 'http')) {
            if (filter_var($raw_url, FILTER_VALIDATE_URL)) {
                $url = $raw_url;
            } else {
                $url = False;
            }
        } else {
            if (filter_var($raw_url, FILTER_VALIDATE_URL)) {
                if ($this->ends_with($base_url, '/')) {
                    $url = $base_url . $raw_url;
                } else {
                    $url = $base_url . '/' . $raw_url;
                }

            } else {
                $url = False;
            }
        }

        return $url;
    }

    /**
     * Helper function to verify if string ends with given substring
     *
     * @access public
     * @param string $haystack
     * @param string $needle
     * @return string
     */
    public function ends_with($haystack, $needle) {
        $length = mb_strlen($needle);
        $start = $length * -1;
        return (mb_substr($haystack, $start) === $needle);
    }

    /**
     * Helper function to verify if string starts with given substring
     *
     * @access public
     * @param string $haystack
     * @param string $needle
     * @return string
     */
    public function starts_with($haystack, $needle) {
        $length = mb_strlen($needle);
        return (mb_substr($haystack, 0, $length) === $needle);
    }

}
