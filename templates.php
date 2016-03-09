 <?php
mb_internal_encoding("UTF-8");

$main = '<!doctype html>
  <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{page_title}} - Edarit</title>
    <link rel="stylesheet" href="' . $site_dir . 'fonts.css">
    <link rel="stylesheet" href="' . $site_dir . 'normalize.css">
    <link rel="stylesheet" href="' . $site_dir . 'style.css">
    <link rel="shortcut icon" href="' . $site_dir . 'favicon.ico">
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
  <body>
  <div id="all">
    <div class="colors">
      <div class="vas"></div>
      <div class="sdp"></div>
      <div class="vihr"></div>
      <div class="ps"></div>
      <div class="kesk"></div>
      <div class="kd"></div>
      <div class="kok"></div>
      <div class="rkp"></div>
      <div class="muu"></div>
    </div>
    <article id="content">
      <header>
        <h1>Edarit</h1>
      </header>';

$navi_front = '<li><a href="' . $site_dir . '">Etusivu</a></li>';
$navi_front_current = '<li><span>Etusivu</span></li>';
$navi_parties = '<li><a href="' . $site_dir . 'puolueet/">Puolueet</a></li>';
$navi_parties_current = '<li><span>Puolueet</span></li>';
$navi_mps = '<li><a href="' . $site_dir . 'edustajat/">Edustajat</a></li>';
$navi_mps_current = '<li><span>Edustajat</span></li>';
$navi_info = '<li class="about"><a href="' . $site_dir . 'tietoa">?</a></li>';
$navi_info_current = '<li class="about"><span>?</span></li>';

$party_list = '
      <h2>Puolueiden uusimmat artikkelit</h2>
      <ul id="poblo_list">
      {{#articles}}
        <li data-ts="{{timestamp}}" data-source="{{source_id}}">
          <p class="title"><a href="{{url}}" rel="nofollow" target="_blank">{{title}}</a></p>
          <p>
            <span class="source"><a href="' . $site_dir . 'edustaja/{{source_id}}">{{source}}</a>, </span>
            <span class="date">{{pubdate}}</span>
          </p>
        </li>
      {{/articles}}
      </ul>';

$mp_list = '
      <h2>Kansanedustajien uusimmat artikkelit</h2>
      <ul id="poblo_list">
      {{#articles}}
        <li data-ts="{{timestamp}}" data-source="{{source_id}}">
          <p class="title"><a href="{{url}}" rel="nofollow" target="_blank">{{title}}</a></p>
          <p>
            <span class="source"><a href="' . $site_dir . 'edustaja/{{source_id}}">{{source}}</a>, </span>
            <span class="party"><a href="' . $site_dir . 'puolue/{{party_id}}">{{party_name}}</a>, </span>
            <span class="date">{{pubdate}}</span>
          </p>
        </li>
      {{/articles}}
      </ul>';

$front_list = '
      <h2>Puolueiden ja kansanedustajien uusimmat artikkelit</h2>
      <ul id="poblo_list">
      {{#articles}}
        <li class="type_{{is_party}}" data-ts="{{timestamp}}" data-source="{{source_id}}">
          <p class="title"><a href="{{url}}" rel="nofollow" target="_blank">{{title}}</a></p>
          <p>
            <span class="source"><a href="' . $site_dir . 'edustaja/{{source_id}}">{{source}}</a>, </span>
            <span class="party"><a href="' . $site_dir . 'puolue/{{party_id}}">{{party_name}}</a>, </span>
            <span class="date">{{pubdate}}</span>
          </p>
        </li>
      {{/articles}}
      </ul>';

$unread_list = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
      </ul>

      <h2>Lukemattomat artikkelit</h2>
      <ul id="poblo_list">
      {{#articles}}
        <li class="type_{{is_party}}" data-ts="{{timestamp}}" data-source="{{source_id}}">
          <p class="title"><a href="{{url}}" rel="nofollow" target="_blank">{{title}}</a></p>
          <p>
            <span class="source"><a href="' . $site_dir . 'edustaja/{{source_id}}">{{source}}</a>, </span>
            <span class="party"><a href="' . $site_dir . 'puolue/{{party_id}}">{{party_name}}</a>, </span>
            <span class="date">{{pubdate}}</span>
          </p>
        </li>
      {{/articles}}
      </ul>';

$parties_front = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties_current . $navi_mps . $navi_info . '
      </ul>

      <h2>Puolueet</h2>
      <ul id="party_list">
        {{#parties}}
          <li><a href="' . $site_dir . 'puolue/{{id}}">{{party_name}}</a></li>
        {{/parties}}
      </ul>

      ' . $party_list;

$parties = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
      </ul>

      ' . $party_list;

$mps_front = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties . $navi_mps_current . $navi_info . '
      </ul>


      <h2>Kansanedustajat</h2>
      <ul id="mp_list">
        {{#mps}}
          <li><a href="' . $site_dir . 'edustaja/{{id}}">{{name}}</a></li>
        {{/mps}}
      </ul>

      ' . $mp_list;

$mps = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
      </ul>

      ' . $mp_list;

$front = '

      <ul id="navigation">
          ' . $navi_front_current . $navi_parties . $navi_mps . $navi_info . '
      </ul>

      ' . $front_list;

$list = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
      </ul>

      ' . $front_list;

$party = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
      </ul>

      <h2>{{party_name}}</h2>
      <ul id="poblo_list">
      {{#articles}}
        <li class="type_{{is_party}}" data-ts="{{timestamp}}" data-source="{{source_id}}">
          <p class="title"><a href="{{url}}" rel="nofollow" target="_blank">{{title}}</a></p>
          <p>
            <span class="source"><a href="' . $site_dir . 'edustaja/{{source_id}}">{{source}}</a>, </span>
            <span class="date">{{pubdate}}</span>
          </p>
        </li>
      {{/articles}}
      </ul>';

$source = '

      <ul id="navigation">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
      </ul>

      <h2>{{source}}, <a href="' . $site_dir . 'puolue/{{party_id}}">{{party_name}}</a></h2>
      <ul id="poblo_list">
      {{#articles}}
        <li data-ts="{{timestamp}}" data-source="{{source_id}}">
          <p class="title"><a href="{{url}}">{{title}}</a></p>
          <p>
            <span class="date">{{pubdate}}</span>
          </p>
        </li>
      {{/articles}}
      </ul>';

$footer = '
      <footer>
        <ul id="footer_nav">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
        </ul>
        <p>Jokin pielessä? Edustaja puuttuu? Meilaa <a href="mailto:edarit.fi@gmail.com">edarit.fi@gmail.com</a>.</p>
      </footer>
    </article>
    <div class="colors bottom">
      <div class="vas"></div>
      <div class="sdp"></div>
      <div class="vihr"></div>
      <div class="ps"></div>
      <div class="kesk"></div>
      <div class="kd"></div>
      <div class="kok"></div>
      <div class="rkp"></div>
      <div class="muu"></div>
    </div>
  </div>
  </body>
  </html>';

$front_footer = '
      <footer>
        <ul id="footer_nav">
          ' . $navi_front . $navi_parties . $navi_mps . $navi_info . '
        </ul>
        <p>Jokin pielessä? Edustaja puuttuu? Meilaa <a href="mailto:edarit.fi@gmail.com">edarit.fi@gmail.com</a>.</p>
      </footer>
    </article>
    <div class="colors bottom">
      <div class="vas"></div>
      <div class="sdp"></div>
      <div class="vihr"></div>
      <div class="ps"></div>
      <div class="kesk"></div>
      <div class="kd"></div>
      <div class="kok"></div>
      <div class="rkp"></div>
      <div class="muu"></div>
    </div>
  </div>
  <script src="/edarit.js"></script>
  </body>
  </html>';

$prev = '
      <div id="next_prev">
        <a href="' . $site_dir . 'kaikki/{{prev_start}}" class="prev">Uudemmat</a>
';
$next = '
        <a href="' . $site_dir . 'kaikki/{{next_start}}" class="next">Vanhemmat</a>
      </div>
';

$party_prev = '
      <div id="next_prev">
        <a href="' . $site_dir . 'puolue/{{party_id}}/{{prev_start}}" class="prev">Uudemmat</a>
';
$party_next = '
      <a href="' . $site_dir . 'puolue/{{party_id}}/{{next_start}}" class="next">Vanhemmat</a>
    </div>
';

$source_prev = '
      <div id="next_prev">
        <a href="' . $site_dir . 'edustaja/{{source_id}}/{{prev_start}}" class="prev">Uudemmat</a>
';
$source_next = '
        <a href="' . $site_dir . 'edustaja/{{source_id}}/{{next_start}}" class="next">Vanhemmat</a>
      </div>
';

$parties_prev = '
      <div id="next_prev">
        <a href="' . $site_dir . 'puolueet/{{prev_start}}" class="prev">Uudemmat</a>
';
$parties_next = '
        <a href="' . $site_dir . 'puolueet/{{next_start}}" class="next">Vanhemmat</a>
      </div>
';

$mps_prev = '
      <div id="next_prev">
        <a href="' . $site_dir . 'edustajat/{{prev_start}}" class="prev">Uudemmat</a>
';
$mps_next = '
        <a href="' . $site_dir . 'edustajat/{{next_start}}" class="next">Vanhemmat</a>
      </div>
';

$empty_prev = '
      <div id="next_prev">
        <span class="prev">Uudemmat</span>
';
$empty_next = '
        <span class="next">Vanhemmat</span>
      </div>
';

$main_list_with_next_prev = $main . $list . $prev . $next . $footer;
$main_list_with_next = $main . $list . $empty_prev . $next . $footer;
$main_list_with_prev = $main . $list . $prev . $empty_next . $footer;
$main_list_no_next_prev = $main . $list . $empty_prev . $empty_next . $footer;

$main_front_with_next_prev = $main . $front . $prev . $next . $front_footer;
$main_front_with_next = $main . $front . $empty_prev . $next . $front_footer;
$main_front_with_prev = $main . $front . $prev . $empty_next . $front_footer;
$main_front_no_next_prev = $main . $front . $empty_prev . $empty_next . $front_footer;

$main_party_with_next_prev = $main . $party . $party_prev . $party_next . $footer;
$main_party_with_next = $main . $party . $empty_prev . $party_next . $footer;
$main_party_with_prev = $main . $party . $party_prev . $empty_next . $footer;
$main_party_no_next_prev = $main . $party . $empty_prev . $empty_next . $footer;

$main_source_with_next_prev = $main . $source . $source_prev . $source_next . $footer;
$main_source_with_next = $main . $source . $empty_prev . $source_next . $footer;
$main_source_with_prev = $main . $source . $source_prev . $empty_next . $footer;
$main_source_no_next_prev = $main . $source . $empty_prev . $empty_next . $footer;

$parties_front = $main . $parties_front . $empty_prev . $parties_next . $footer;
$parties_no_next_prev = $main . $parties . $empty_prev . $empty_next . $footer;
$parties_with_prev = $main . $parties . $parties_prev . $empty_next . $footer;
$parties_with_next_prev = $main . $parties . $parties_prev . $parties_next . $footer;

$mps_front = $main . $mps_front . $empty_prev . $mps_next . $footer;
$mps_no_next_prev = $main . $mps . $empty_prev . $empty_next . $footer;
$mps_with_prev = $main . $mps . $mps_prev . $empty_next . $footer;
$mps_with_next_prev = $main . $mps . $mps_prev . $mps_next . $footer;

$unread = $main . $unread_list . $empty_prev . $empty_next . $footer;
