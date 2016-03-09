<?php
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

require_once 'PoBlo.php';

$poblo = new PoBlo();

if (isset($_POST['feed_url'])) {

    if (!isset($_POST['is_party']) || $_POST['is_party'] === '0') {
        $_POST['is_party'] = 0;
    } else {
        $_POST['is_party'] = 1;
    }

    if (!isset($_POST['is_active']) || $_POST['is_active'] === '0') {
        $_POST['is_active'] = 0;
    } else {
        $_POST['is_active'] = 1;
    }
    $updated = $poblo->update_source(
        $_POST['id'],
        $_POST['publisher_name'],
        $_POST['url'],
        $_POST['feed_url'],
        $_POST['desc'],
        $_POST['party'],
        $_POST['is_party'],
        $_POST['is_active']
    );
}

if (isset($_POST['delete']) && $_POST['delete'] === 'on') {
    $poblo->delete_source($_POST['id']);
}

if (isset($_POST['source_id'])) {

    $mp = $poblo->get_source_by_id($_POST['source_id']);

    $parties = $poblo->get_parties();
    $parties_select = '<select name="party">';

    foreach ($parties as $party) {
        if ($party['id'] == $mp['party_id']) {
            $selected = ' selected="selected"';
        } else {
            $selected = '';
        }
        $parties_select .= '<option value="' . $party['id'] . '"' . $selected . '>' . $party['short_party_name'] . '</option>';
    }

    $parties_select .= '</select>';

    if ($mp['is_party'] == 1) {
        $mp['is_party'] = ' checked="checked"';
    } else {
        $mp['is_party'] = '';
    }

    if ($mp['is_active'] == 1) {
        $mp['is_active'] = ' checked="checked"';
    } else {
        $mp['is_active'] = '';
    }

    echo '<!doctype html>
    <html lang="en">
      <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
      <title>Edarit</title>
      <link rel="stylesheet" href="fonts.css">
      <link rel="stylesheet" href="normalize.css">
      <link rel="stylesheet" href="style.css">
      <link rel="stylesheet" href="lib/highlight/styles/pojoaque.css">
      <link rel="shortcut icon" href="favicon.ico">
      <!--[if lt IE 9]>
      <script>
        var e = ("abbr,article,aside,audio,canvas,datalist,details," +
          "figure,footer,header,hgroup,mark,menu,meter,nav,output," +
          "progress,section,time,video").split(",");
        for (var i = 0; i < e.length; i++) {
          document.createElement(e[i]);
        }
      </script>
      <![endif]-->
    </head>
    <body>';
    echo '<form action="edit.php" method="post">';
    echo '<input type="hidden" id="id" name="id" value="' . $mp['id'] . '">';
    echo '<input type="text" id="feed_url" name="feed_url" placeholder="feed url" value="' . $mp['feed_url'] . '">';
    echo '<input type="text" id="url" name="url" placeholder="url" value="' . $mp['base_url'] . '">';
    echo '<input type="text" id="publisher_name" name="publisher_name" placeholder="publisher name" value="' . $mp['name'] . '">';
    echo '<input type="text" id="desc" name="desc" placeholder="desc" value="' . $mp['description'] . '">';
    echo $parties_select;
    echo '<input type="checkbox" name="is_party"' . $mp['is_party'] . ' id="is_party"><label for="is_party">Is a party</label>';
    echo '<input type="checkbox" name="delete" id="delete"><label for="delete">Delete</label>';
    echo '<input type="checkbox" id="is_active" name="is_active"' . $mp['is_active'] . '><label for="is_active">Is active</label>';
    echo '<button>Submit</button>';
    echo '</form>';

    echo '</body></html>';
} else {
    $sources = $poblo->get_all_sources();
    $sources = $poblo->aasort($sources, 'name');

    $sources_select = '<select name="source_id">';
    foreach ($sources as $source) {
        $sources_select .= '<option value="' . $source['id'] . '">' . $source['name'] . '</option>';
    }
    $sources_select .= '</select>';
    echo '<!doctype html>
    <html lang="en">
      <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
      <title>Edarit</title>
      <link rel="stylesheet" href="fonts.css">
      <link rel="stylesheet" href="normalize.css">
      <link rel="stylesheet" href="style.css">
      <link rel="stylesheet" href="lib/highlight/styles/pojoaque.css">
      <link rel="shortcut icon" href="favicon.ico">
      <!--[if lt IE 9]>
      <script>
        var e = ("abbr,article,aside,audio,canvas,datalist,details," +
          "figure,footer,header,hgroup,mark,menu,meter,nav,output," +
          "progress,section,time,video").split(",");
        for (var i = 0; i < e.length; i++) {
          document.createElement(e[i]);
        }
      </script>
      <![endif]-->
    </head>
    <body>';

    echo '<form action="edit.php" method="post">';
    echo $sources_select;
    echo '<button>Submit</button>';
    echo '</form>';
    echo '</body></html>';
}