<?php
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

# list.php without query params is the same as index.html
if (
    !isset($_GET['start']) &&
    !isset($_GET['source']) &&
    !isset($_GET['party']) &&
    !isset($_GET['unread'])
) {
    echo file_get_contents('static_index.html');
    die;
}

require_once 'PoBlo.php';
require_once 'mustache.php';
require_once 'templates.php';

$poblo = new PoBlo();
$m = new Mustache_Engine;

if (isset($_GET['start']) && (int) $_GET['start'] > 0) {
    $start = (int) $_GET['start'];
} else {
    $start = 0;
}

if (
    !isset($_GET['source']) &&
    !isset($_GET['party']) &&
    !isset($_GET['unread'])
) {
    echo get_latests($start);
} else if (isset($_GET['source'])) {
    echo get_latests_from_source($start);
} else if (isset($_GET['party'])) {
    echo get_latests_from_party($start);
} else if (isset($_GET['unread'])) {
    echo get_unread($start);
} else {
    # when in doubt, default to index
    echo file_get_contents('static_index.html');
}

function get_latests($start) {
    $latests = $GLOBALS['poblo']->get_latests($start, PoBlo::ARTICLE_LIMIT);

    $articles = array();
    $current_year = date('Y');
    $current_day = date('j.n.');

    foreach ($latests as $article) {
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
        'prev_start' => $start - PoBlo::ARTICLE_LIMIT,
        'next_start' => $start + PoBlo::ARTICLE_LIMIT,
        'page_title' => $GLOBALS['front_page_title'],
    );

    # Figuring out if we need next and/or prev links
    # and should be nav item have link or not.
    if ($start <= 0) {
        if (count($articles) === PoBlo::ARTICLE_LIMIT) {
            $template = $GLOBALS['main_front_with_next'];
        } else {
            $template = $GLOBALS['main_front_no_next_prev'];
        }

    } else if ($start > 0) {
        if (count($articles) >= PoBlo::ARTICLE_LIMIT) {
            $template = $GLOBALS['main_list_with_next_prev'];
        } else {
            $template = $GLOBALS['main_list_with_prev'];
        }
    }

    return $GLOBALS['m']->render($template, $page);
}

function get_latests_from_source($start) {
    $articles_by_source = $GLOBALS['poblo']->get_articles_by_source_id(
        $_GET['source'],
        $start,
        PoBlo::ARTICLE_LIMIT
    );
    if ($articles_by_source === False || $articles_by_source === array()) {
        $page = file_get_contents('blog/error.html');
        header("HTTP/1.0 404 Not Found");
        return $page;
    }
    $name = $GLOBALS['poblo']->get_source_name_by_id($_GET['source']);

    $articles = array();
    $current_year = date('Y');
    $current_day = date('j.n.');

    foreach ($articles_by_source as $article) {
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
            'title' => mb_substr($article['title'], 0, PoBlo::MAX_TITLE_LEN),
            'url' => $article['url'],
            'pubdate' => $date,
            'timestamp' => $article['pubdate'],
        );
    }

    $party = $GLOBALS['poblo']->get_party_by_source_id($_GET['source']);

    $page = array(
        'articles' => $articles,
        'prev_start' => $start - PoBlo::ARTICLE_LIMIT,
        'next_start' => $start + PoBlo::ARTICLE_LIMIT,
        'source' => $name,
        'party_id' => $party['id'],
        'party_name' => $party['name'],
        'source_id' => (int) $_GET['source'],
        'page_title' => $name,
    );

    if ($start <= 0) {
        if (count($articles) === PoBlo::ARTICLE_LIMIT) {
            $template = $GLOBALS['main_source_with_next'];
        } else {
            $template = $GLOBALS['main_source_no_next_prev'];
        }

    } else if ($start > 0) {
        if (count($articles) >= PoBlo::ARTICLE_LIMIT) {
            $template = $GLOBALS['main_source_with_next_prev'];
        } else {
            $template = $GLOBALS['main_source_with_prev'];
        }
    }

    return $GLOBALS['m']->render($template, $page);

}

function get_latests_from_party($start) {
    $articles_by_party = $GLOBALS['poblo']->get_articles_by_party_id(
        $_GET['party'],
        $start,
        PoBlo::ARTICLE_LIMIT
    );

    if ($articles_by_party === False || $articles_by_party === array()) {
        $page = file_get_contents('blog/error.html');
        header("HTTP/1.0 404 Not Found");
        return $page;
    }

    $articles = array();
    $current_year = date('Y');
    $current_day = date('j.n.');

    if ($articles_by_party) {
        foreach ($articles_by_party as $article) {
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
                'source_id' => $article['source_id'],
                'timestamp' => $article['pubdate'],
            );
        }
    }

    $party_name = $GLOBALS['poblo']->get_party_name_by_party_id($_GET['party']);

    $page = array(
        'articles' => $articles,
        'prev_start' => $start - PoBlo::ARTICLE_LIMIT,
        'next_start' => $start + PoBlo::ARTICLE_LIMIT,
        'party_name' => $party_name,
        'party_id' => (int) $_GET['party'],
        'page_title' => $party_name,
    );

    if ($start <= 0) {
        if (count($articles) === PoBlo::ARTICLE_LIMIT) {
            $template = $GLOBALS['main_party_with_next'];
        } else {
            $template = $GLOBALS['main_party_no_next_prev'];
        }

    } else if ($start > 0) {
        if (count($articles) >= PoBlo::ARTICLE_LIMIT) {
            $template = $GLOBALS['main_party_with_next_prev'];
        } else {
            $template = $GLOBALS['main_party_with_prev'];
        }
    }

    return $GLOBALS['m']->render($template, $page);
}

function get_unread($start) {
    $latest_ts = (int) $_GET['unread'];

    if ($latest_ts === 0) {
        $latest_ts = time();
    }
    $latests = $GLOBALS['poblo']->get_newer_than($latest_ts, PoBlo::ARTICLE_LIMIT * 3);

    $articles = array();
    $current_year = date('Y');
    $current_day = date('j.n.');

    foreach ($latests as $article) {
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
        'prev_start' => $start - PoBlo::ARTICLE_LIMIT,
        'next_start' => $start + PoBlo::ARTICLE_LIMIT,
        'page_title' => 'Lukemattomat artikkelit',
    );

    return $GLOBALS['m']->render($GLOBALS['unread'], $page);
}
