<?php

/**
 * @name: TinyWiki
 * @version: 1.1
 * @since: 2012-04-30
 * @license: GPL
 * @author: Marcus Gnaß
 * @copyright: 2012 (Marcus Gnaß)
 * @filesource: wiki.php
 * @abstract: A small Wiki which is small enough to fit into a single file.
 * It is a "fork" of MiniWiki written by Laurenz van Gaalen.
 */

error_reporting(E_ALL|E_STRICT);
date_default_timezone_set('Europe/Berlin');
define('DIR', 'data/');
define('Q_CMD', 'cmd');
define('Q_PAGE', 'page');
define('Q_CONTENT', 'content');

/**
 * Returns the filename for a given pagename.
 *
 * Sicherstellen, daß $page nur Zeichen aus der Menge [a-z0-9] enthält
 * um das Eskalieren im Filesystem (z.B. ../etc/pwd) bzw. Auslesen
 * versteckter Dateien (z.B. .htaccess) zu unterbinden.
 *
 * Der Dateiname entspricht dem Seitennamen mit dem Suffix 'html'.
 */
function get_file_name($page) {
    $page = preg_replace('/[^a-z0-9]+/i', '_', $page);
    return DIR.$page.'.html';
}

/**
 */
function print_header($page, $file) {
    $href = $_SERVER['PHP_SELF'] . '?' . Q_CMD . '=edit';
    if (strlen($page))
        $href .= '&amp;' . Q_PAGE . '=' . $page;
    # $href .= strlen($page) ? : '&amp;' . Q_PAGE . '=' . $page;
    echo '<nav>';
    echo '<a href="' . $_SERVER['PHP_SELF'] . '">TinyWiki</a>';
    echo ' | ';
    echo '<a href="' . $href . '">Edit</a>';
    echo '</nav>';
}

/**
 */
function print_footer($page, $file) {
    if (file_exists($file))
        $date = date('d-m-Y, H:i', filemtime($file));
    else
        $date = 'n/a';
    echo "<footer>Last changed: $date</footer>";
}

/**
 */
function do_edit($page) {
    $file = get_file_name($page);
    // get content of file
    $content = '';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('<br />' . chr(13) . chr(10), chr(13) . chr(10), $content);
        $content = preg_replace('/<a href="\?page=(.+?)">(.+?)<\/a>/i', '[[$1]]', $content);
        $content = preg_replace('/<h1>(.+?)<\/h1>/i', '=== $1 ===', $content);
        $content = preg_replace('/<h2>(.+?)<\/h2>/i', '== $1 ==', $content);
        $content = preg_replace('/<h3>(.+?)<\/h3>/i', '= $1 =', $content);
        $content = preg_replace('/<code><pre>(.+?)<\/pre><\/code>/si', '[code]$1[/code]', $content);
    }
    print_header($page, $file);
    // print editor
    $action = $_SERVER['PHP_SELF'] . '?' . Q_CMD . '=save';
    if (strlen($page))
        $action .= '&amp;' . Q_PAGE . '=' . $page;
    echo "
        <form name=\"editform\" action=\"$action\" method=\"post\" enctype=\"multipart/form-data\">
        <input type=\"submit\" name=\"submit\" value=\"Save\" class=\"btn\" />
        <textarea name=\"" . Q_CONTENT . "\" cols=\"5\" rows=\"40\">$content</textarea>
        </form>";
}

/**
 * =Header= beißt sich mit zwei = in Link.
 */
function do_save($page) {
    $file = get_file_name($page);
    // prepare content
    $content = $_POST[Q_CONTENT];
    $content = stripslashes($content);
    $content = strip_tags($content, '<h1><p><br><b><u><i><s><a><ul><ol><li><del><img>');
    $content = str_replace(chr(13) . chr(10), '<br />' . chr(13) . chr(10), $content);
    $content = preg_replace('/\[\[(.+?)\]\]/i', '<a href="?page=$1">$1</a>', $content);
    $content = preg_replace('/===\s?(.+?)\s?===<br \/>/i', '<h1>$1</h1>', $content);
    $content = preg_replace('/==\s?(.+?)\s?==<br \/>/i', '<h2>$1</h2>', $content);
    $content = preg_replace('/=\s+(.+?)\s+=<br \/>/i', '<h3>$1</h3>', $content);
    $content = preg_replace('/\[code\](.+?)\[\/code\]/si', '<code><pre>$1</pre></code>', $content);
    // create file if it does not exist
    if (!file_exists($file)) {
        if ($handle = fopen($file, 'wb')) {
            chmod ($file, 0777);
            fclose($handle);
        }
    }
    // save file
    file_put_contents($file, $content);
    // redirect to this pages view
    $url = $_SERVER['PHP_SELF'];
    if (strlen($page))
        $url .= '?' . Q_PAGE . '=' . $page;
    if (true)
        echo '<meta http-equiv="refresh" content="0; url=' . $url . '" />';
    else
        header('Location: ' . $url);
    exit();
}

/**
 */
function do_view($page) {
    $file = get_file_name($page);
    print_header($page, $file);
    echo '<article>';
    if (file_exists($file))
        include($file);
    echo '</article>';
    print_footer($page, $file);
}

$page = isset($_GET[Q_PAGE]) ? $_GET[Q_PAGE] : 'start';
echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>';

?><!DOCTYPE html><html><head>
<title><?= $page ?> &mdash; TinyWiki</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="generator" content="TinyWiki 1.0" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style type="text/css">
body,table{font-family:monospace;font-size:13px;margin:0}
input,textarea{border:1px solid #969696;margin:3px;font-size:13px;width:100%}
a:link {color: #E61428}
a:visited {color: #820A14}
del {color: green}
del a:link {color: blue}
code {background:#EEE;color:#444;display:block;margin:0 15px;padding:1px 15px}
h1{font-size:2em};h2{font-size:1.4em};h3{font-size:1.1em}
article, nav,footer{padding:20px}
nav{border-bottom:2px solid #969696;text-align:left}
footer{border-top:2px solid #969696;text-align:right}
</style>
</head><body><?php

switch (isset($_GET[Q_CMD]) ? $_GET[Q_CMD] : 'view') {
    case 'edit': do_edit($page); break;
    case 'save': do_save($page); break;
    default: do_view($page);
}

?></body></html>
