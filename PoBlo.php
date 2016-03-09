<?php
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

require_once 'PoBloParser.php';
require_once 'settings.php';

$p = new PoBloParser();

class PoBlo {

    private $host;
    private $user;
    private $pass;
    private $name;
    private $prefix;
    private $db;
    private $p;
    const ARTICLE_LIMIT = 30;
    const MAX_TITLE_LEN = 150;

    public function __construct() {
        $this->p = new PoBloParser();

        $this->host = $GLOBALS['db_server'];
        $this->user = $GLOBALS['db_user'];
        $this->pass = $GLOBALS['db_pass'];
        $this->name = $GLOBALS['db_name'];
        $this->prefix = $GLOBALS['db_table_prefix'];

        try {
            $this->db = new PDO(
                "mysql:host=$this->host;dbname=$this->name",
                $this->user,
                $this->pass,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public function __destruct() {
        $this->db = null;
    }

    public function get_all_sources() {
        $query = 'SELECT * FROM ' . $this->prefix . 'sources';
        $sources = $this->read_db($query);
        return $sources;
    }

    public function get_all_articles() {
        $query = 'SELECT id, source_id, title, pubdate FROM ' . $this->prefix . 'articles';
        $articles = $this->read_db($query);
        return $result;
    }

    public function get_articles_by_source_id($source_id, $start = False, $amount = False) {
        $query = 'SELECT id, source_id, url, title, pubdate FROM ' . $this->prefix . 'articles WHERE source_id=? ORDER BY pubdate DESC';

        if ($start !== False && $amount !== False) {
            $start = (int) $start;
            $amount = (int) $amount;

            if ($amount > self::ARTICLE_LIMIT) {
                $amount = self::ARTICLE_LIMIT;
            }
            if ($start < 0) {
                $start = 0;
            }

            $query = $query . ' LIMIT ' . $start . ', ' . $amount;
        }
        $articles = $this->read_db($query, $source_id);

        return $articles;
    }

    public function get_articles_by_party_id($party_id, $start = False, $amount = False) {
        $party_id = (int) $party_id;
        $sources = $this->get_sources_by_party_id($party_id);

        if (count($sources) === 0) {
            return False;
        }

        $sources = array_column($sources, 'id');
        $sources = implode(', ', $sources);
        $query = 'SELECT s.id, source_id, url, title, pubdate, name, is_active FROM ' . $this->prefix . 'articles a JOIN ' . $this->prefix . 'sources s ON a.source_id = s.id WHERE a.source_id IN (' . $sources . ') ORDER BY pubdate DESC';

        if ($start !== False && $amount !== False) {
            $start = (int) $start;
            $amount = (int) $amount;

            if ($amount > self::ARTICLE_LIMIT) {
                $amount = self::ARTICLE_LIMIT;
            }
            if ($start < 0) {
                $start = 0;
            }

            $query = $query . ' LIMIT ' . $start . ', ' . $amount;
        }

        $articles = $this->read_db($query, $sources);
        return $articles;
    }

    public function get_articles_by_parties($start = False, $amount = False) {
        $query = 'SELECT id FROM sources WHERE is_party=1';
        $parties = $this->read_db($query);
        $parties = array_column($parties, 'id');
        $parties = implode(', ', $parties);
        $query = 'SELECT s.id, source_id, url, title, pubdate, name FROM ' . $this->prefix . 'articles a JOIN ' . $this->prefix . 'sources s ON a.source_id = s.id WHERE a.source_id IN (' . $parties . ') ORDER BY pubdate DESC';

        if ($start !== False && $amount !== False) {
            $start = (int) $start;
            $amount = (int) $amount;

            if ($amount > self::ARTICLE_LIMIT) {
                $amount = self::ARTICLE_LIMIT;
            }
            if ($start < 0) {
                $start = 0;
            }

            $query = $query . ' LIMIT ' . $start . ', ' . $amount;
        }

        $articles = $this->read_db($query, $parties);
        return $articles;
    }

    public function get_party_name_by_party_id($party_id) {
        $party_id = (int) $party_id;
        $query = 'SELECT party_name FROM parties WHERE id = ?';
        $party_name = $this->read_db($query, $party_id);
        return $party_name[0]['party_name'];
    }

    public function get_mps() {
        $query = 'SELECT * FROM sources WHERE is_party!=1 AND is_active=1';
        $mps = $this->read_db($query);
        return $mps;
    }

    public function get_short_party_name_by_party_id($party_id) {
        $party_id = (int) $party_id;
        $query = 'SELECT short_party_name FROM parties WHERE id = ?';
        $party_name = $this->read_db($query, $party_id);
        return $party_name[0]['short_party_name'];
    }

    public function get_party_by_source_id($source_id) {
        $source_id = (int) $source_id;
        $query = 'SELECT party_id FROM sources WHERE id = ?';
        $party = array();
        $party['id'] = $this->read_db($query, $source_id);
        $party['id'] = $party['id'][0]['party_id'];
        $party['name'] = $this->get_short_party_name_by_party_id($party['id']);
        return $party;
    }

    public function get_sources_by_party_id($party_id) {
        $query = 'SELECT id FROM sources WHERE party_id=?';
        $sources = $this->read_db($query, $party_id);
        return $sources;
    }

    public function get_source_by_id($source_id) {
        $query = 'SELECT * FROM sources WHERE id=? LIMIT 1';
        $source = $this->read_db($query, $source_id);
        return $source[0];
    }

    public function get_latest_article_by_source_id($source_id) {
        $query = 'SELECT id, source_id, url, title, pubdate FROM ' . $this->prefix . 'articles WHERE source_id=? ORDER BY pubdate DESC LIMIT 1';
        $article = $this->read_db($query, $source_id);
        if (!isset($article[0])) {
            $article[0] = False;
        }
        return $article[0];
    }

    public function get_source_id_by_feed_url($url) {
        $query = 'SELECT id FROM sources WHERE base_url=? LIMIT 1';
        $source_id = $this->read_db($query, $url);
        return $source_id[0]['id'];
    }

    public function get_source_id_by_name($name) {
        $query = 'SELECT id FROM sources WHERE name=? LIMIT 1';
        $source_id = $this->read_db($query, $url);
        return $source_id[0]['id'];
    }

    public function get_source_name_by_id($id) {
        $query = 'SELECT name FROM sources WHERE id=? LIMIT 1';
        $name = $this->read_db($query, $id);
        return $name[0]['name'];
    }

    public function get_source_names_by_ids($ids) {
        $ids = array_column($ids, 'id');
        $ids = implode(', ', $ids);
        $query = 'SELECT id, name FROM sources WHERE id IN (' . $ids . ')';
        $names = $this->read_db($query, $ids);
        return $names;
    }

    public function save_article($source, $article) {
        if (!isset($article['pubdate']) || !$article['pubdate']) {
            $article['pubdate'] = time();
        }
        if (!isset($article['content'])) {
            $article['content'] = '';
        }
        $article = array(
            $source,
            $article['url'],
            $article['title'],
            $article['content'],
            $article['pubdate'],
        );
        $query = 'INSERT INTO ' . $this->prefix . 'articles (source_id, url, title, content, pubdate) VALUES (?, ?, ?, ?, ?)';
        $article_id = $this->write_db($query, $article);
        return $article_id;
    }

    public function add_source_to_db($name, $url, $feed_url, $desc, $party_id, $is_party) {
        $source = array(
            $name,
            $desc,
            $url,
            $feed_url,
            $party_id,
            $is_party,
        );
        $query = 'INSERT INTO ' . $this->prefix . 'sources (name, description, base_url, feed_url, party_id, is_party) VALUES (?, ?, ?, ?, ?, ?)';
        $source_id = $this->write_db($query, $source);
        return $source_id;
    }

    public function update_source($id, $name, $url, $feed_url, $desc, $party_id, $is_party, $is_active) {
        $source = array(
            $name,
            $desc,
            $url,
            $feed_url,
            $party_id,
            $is_party,
            $is_active,
            $id,
        );
        $query = 'UPDATE ' . $this->prefix . 'sources SET name=?, description=?, base_url=?, feed_url=?, party_id=?, is_party=?, is_active=? WHERE id=?';
        $source_id = $this->write_db($query, $source);
        return $source_id;
    }

    public function get_latests($start = False, $amount = False) {
        $articles = False;
        #$query = 'SELECT source_id, title, url, pubdate, name, party_id, is_party FROM articles a LEFT JOIN sources s ON a.source_id = s.id ORDER BY a.pubdate DESC';
        $query = 'SELECT * FROM ' . $this->prefix . 'articles a JOIN sources s ON a.source_id = s.id JOIN parties p ON s.party_id = p.id ORDER BY a.pubdate DESC';
        if ($start !== False && $amount !== False) {
            $start = (int) $start;
            $amount = (int) $amount;
            if ($amount > self::ARTICLE_LIMIT) {
                $amount = self::ARTICLE_LIMIT;
            }
            if ($start < 0) {
                $start = 0;
            }
            $query = $query . ' LIMIT ' . $start . ', ' . $amount;
            $articles = $this->read_db($query);
        } else {
            $articles = $this->read_db($query);
        }
        return $articles;
    }

    public function get_newer_than($timestmap, $amount = False) {
        $articles = False;
        $query = 'SELECT * FROM ' . $this->prefix . 'articles a JOIN ' . $this->prefix . 'sources s ON a.source_id = s.id JOIN ' . $this->prefix . 'parties p ON s.party_id = p.id WHERE a.pubdate > ? ORDER BY a.pubdate DESC';
        if ($amount) {
            $query = $query . ' LIMIT 0, ' . $amount;
        }
        $articles = $this->read_db($query, $timestmap);
        return $articles;
    }

    public function get_latests_by_mps($start = False, $amount = False) {
        $articles = False;
        $query = 'SELECT * FROM ' . $this->prefix . 'articles a JOIN ' . $this->prefix . 'sources s ON a.source_id = s.id JOIN ' . $this->prefix . 'parties p ON s.party_id = p.id WHERE s.is_party != 1 ORDER BY a.pubdate DESC';
        if ($start !== False && $amount !== False) {
            $start = (int) $start;
            $amount = (int) $amount;
            if ($amount > self::ARTICLE_LIMIT) {
                $amount = self::ARTICLE_LIMIT;
            }
            if ($start < 0) {
                $start = 0;
            }
            $query = $query . ' LIMIT ' . $start . ', ' . $amount;
            $articles = $this->read_db($query);
        } else {
            $articles = $this->read_db($query);
        }
        return $articles;
    }

    public function save_new_articles($new_articles, $source) {
        $old_articles = $this->get_articles_by_source_id($source);
        $old_urls = array_column($old_articles, 'url');

        $article_ids = array();
        $new_articles = array_reverse($new_articles);
        foreach ($new_articles as $article) {
            if (!in_array($article['url'], $old_urls)) {
                # prevents adding articles withs same url multiple times
                # would love to do this on db level, but
                # #1071 - Specified key was too long; max key length is 767 bytes
                # when adding unique restriction to url column of article db
                $article_ids[] = $this->save_article($source, $article);
            }
        }
        return $article_ids;
    }

    public function get_parties() {
        $query = 'SELECT * FROM parties WHERE active=1';
        $parties = $this->read_db($query);
        return $parties;
    }

    private function read_db($query, $params = False) {
        if (isset($params) && !is_array($params)) {
            $params = array($params);
        }
        if ($params) {
            $statement = $this->db->prepare($query);
            $statement->execute(array_values($params));
        } else {
            $statement = $this->db->query($query);
        }
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $result = $statement->fetchAll();
        return $result;
    }

    private function write_db($query, $params) {
        if (isset($params) && !is_array($params)) {
            $params = array($params);
        }
        $statement = $this->db->prepare($query);
        $statement->execute(array_values($params));
        if ($statement) {
            $affected_ids = $this->db->lastInsertId();
        } else {
            $affected_ids = False;
        }
        return $affected_ids;
    }

    public function get_new_articles_from_feed($feed) {
        $source_url = $feed['link'];
        $source_id = $this->get_source_id_by_feed_url($source_url);
        $latest_saved = $this->get_latest_article_by_source_id($source_id);
        $latest_index = $this->array_search2d_by_field($latest_saved['url'], $feed, 'url');
        if ($latest_index === 0) {
            return False;
        } else {
            unset($feed['title']);
            unset($feed['description']);
            unset($feed['link']);
            if ($latest_index) {
                $new = array_slice($feed, 0, $latest_index);
            } else {
                $new = $feed;
            }
            return $new;
        }
    }

    public function delete_articles_by_id($id) {
        $id = (int) $id;
        $query = 'DELETE FROM articles WHERE source_id=?';
        $this->write_db($query, $id);
    }

    public function delete_source($id) {
        $id = (int) $id;
        $this->delete_articles_by_id($id);
        $query = 'DELETE FROM sources WHERE id=?';
        $this->write_db($query, $id);
    }

    public function array_search2d_by_field($needle, $haystack, $field) {
        foreach ($haystack as $index => $inner_array) {
            if (isset($inner_array[$field]) && $inner_array[$field] === $needle) {
                return $index;
            }
        }
        return False;
    }

    public function in_array_r($needle, $haystack, $strict = False) {
        foreach ($haystack as $item) {
            if (
                ($strict ? $item === $needle : $item == $needle) ||
                (is_array($item) && $this->in_array_r($needle, $item, $strict))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function to sort multidimensional array.
     * Returns given array in sorted order
     *
     * @access public
     * @param array &$array
     * @param string $key
     * @return array
     */
    function aasort(&$array, $sort_key) {
        $sorter = array();
        $sorted = array();
        reset($array);

        foreach ($array as $key => $va) {
            $sorter[$key] = $va[$sort_key];
        }

        asort($sorter);

        foreach ($sorter as $key => $va) {
            $sorted[$key] = $array[$key];
        }

        return $sorted;
    }

}