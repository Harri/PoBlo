<?php
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

require_once 'PoBlo.php';
require_once 'PoBloParser.php';
require_once 'settings.php';

header('Content-Type: text/html; charset=utf-8');

$poblo = new PoBlo();
$pp = new PoBloParser();

$sources = $poblo->get_all_sources();
shuffle($sources);
$new_article_ids = array();

# this loop takes time, but not CPU, since fetching feeds is not async.
# so most of the time it just waits for server to respond.
foreach ($sources as $source) {
    if ($source['is_active'] === '1') {
        try {
            if (isset($_GET['progress'])) {
                echo $source['name'];
                ob_flush();
                flush();
            }
            $feed = $pp->read_feed($source['feed_url'], $source['base_url']);
            $feed['link'] = $source['base_url'];
            $new_articles = $poblo->get_new_articles_from_feed($feed);
            if ($new_articles) {
                $new_article_ids = $poblo->save_new_articles($new_articles, $source['id']);
            }
        } catch (Exception $e) {
            if (isset($_GET['progress'])) {
                echo 'FAIL: ' . $source['feed_url'];
            }

            error_log('Failed to fetch ' . $source['feed_url']);

        }
    }
}

file_put_contents('static_parties.html', file_get_contents($site_url . 'parties.php?start=0'));
file_put_contents('static_members.html', file_get_contents($site_url . 'members.php?start=0'));
file_put_contents('static_index.html', file_get_contents($site_url . 'list.php?start=0'));
