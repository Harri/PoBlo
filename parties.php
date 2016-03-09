<?php
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

# parties.php without query params is the same as static_parties.html
if (!isset($_GET['start'])) {
    echo file_get_contents('static_parties.html');
    die;
}

require_once 'PoBlo.php';
require_once 'mustache.php';
require_once 'templates.php';

$poblo = new PoBlo();
$m = new Mustache_Engine;

$parties = array();

if (isset($_GET['start']) && (int) $_GET['start'] > 0) {
    $start = (int) $_GET['start'];
} else {
    $start = 0;

    $party_list = $poblo->get_parties();
    $party_list = $poblo->aasort($party_list, 'party_name');

    foreach ($party_list as $party) {
        if ($party['party_name'] != 'Muu') {
            $parties[] = array(
                'party_name' => $party['party_name'],
                'id' => $party['id'],
            );
        }
    }
}

$articles_by_parties = $poblo->get_articles_by_parties($start, PoBlo::ARTICLE_LIMIT);
$articles = array();
$current_year = date('Y');
$current_day = date('j.n.');

foreach ($articles_by_parties as $article) {
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
        'pubdate' => $date,
        'timestamp' => $article['pubdate'],
        'source_id' => $article['source_id'],
    );
}

$page = array(
    'parties' => $parties,
    'articles' => $articles,
    'prev_start' => $start - PoBlo::ARTICLE_LIMIT,
    'next_start' => $start + PoBlo::ARTICLE_LIMIT,
    'page_title' => 'Puolueet',
);

if ($start <= 0) {
    if (count($articles) === PoBlo::ARTICLE_LIMIT) {
        $template = $parties_front;
    } else {
        $template = $parties_no_next_prev;
    }

} else {
    if (count($articles) >= PoBlo::ARTICLE_LIMIT) {
        $template = $parties_with_next_prev;
    } else {
        $template = $parties_with_prev;
    }
}

echo $m->render($template, $page);
