<?php
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

# members.php without query params is the same as static_members.html
if (!isset($_GET['start'])) {
    echo file_get_contents('static_members.html');
    die;
}

require_once 'PoBlo.php';
require_once 'mustache.php';
require_once 'templates.php';

$poblo = new PoBlo();
$m = new Mustache_Engine;

$mps = array();

if (isset($_GET['start']) && (int) $_GET['start'] > 0) {
    $start = (int) $_GET['start'];
} else {
    $start = 0;

    $all_mps = $poblo->get_mps();
    $all_mps = $poblo->aasort($all_mps, 'name');

    foreach ($all_mps as $mp) {
        $mps[] = array(
            'id' => $mp['id'],
            'name' => $mp['name'],
        );

    }

}

$articles_by_mps = $poblo->get_latests_by_mps($start, PoBlo::ARTICLE_LIMIT);

$articles = array();
$current_year = date('Y');
$current_day = date('j.n.');

foreach ($articles_by_mps as $article) {
    $article_year = date('Y', $article['pubdate']);
    $article_day = date('j.n.', $article['pubdate']);
    if ($current_year === $article_year) {
        if ($current_day === $article_day) {
            $date = date('H:i', $article['pubdate']);
        } else {
            $date = date('j.n.', $article['pubdate']);
        }

    } else {
        $date = date('j.n.Y', $article['pubdate']);
    }
    $articles[] = array(
        'source' => $article['name'],
        'title' => mb_substr($article['title'], 0, PoBlo::MAX_TITLE_LEN),
        'url' => $article['url'],
        'source_id' => $article['source_id'],
        'pubdate' => $date,
        'timestamp' => $article['pubdate'],
        'is_party' => $article['is_party'],
        'party_name' => $article['short_party_name'],
        'party_id' => $article['party_id'],
    );
}

$page = array(
    'articles' => $articles,
    'mps' => $mps,
    'prev_start' => $start - PoBlo::ARTICLE_LIMIT,
    'next_start' => $start + PoBlo::ARTICLE_LIMIT,
    'page_title' => 'Edustajat',
);

if ($start <= 0) {
    if (count($articles) == PoBlo::ARTICLE_LIMIT) {
        $template = $mps_front;
    } else {
        $template = $mps_no_next_prev;
    }

} else {
    if (count($articles) >= PoBlo::ARTICLE_LIMIT) {
        $template = $mps_with_next_prev;
    } else {
        $template = $mps_with_prev;
    }
}

echo $m->render($template, $page);
