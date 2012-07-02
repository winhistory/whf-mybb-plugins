<?php
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


$plugins->add_hook("postbit", "whfshowdeletedposts_postbit");
$plugins->add_hook("showthread_linear", "whfshowdeletedposts_showthread_linear");


function whfshowdeletedposts_info() {
    return array(
        "name" => 'WHF Show Deleted Posts',
        "description" => 'Shows deleted posts from undo delete plugin',
        "website" => "",
        "author" => "gandro",
        "authorsite" => "",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "16*"
    );
}

$deleted_posts = null;

function whfshowdeletedposts_get_deleted_posts() {
    global $db;
    global $pids, $tid;
    global $page, $pages;
    global $deleted_posts;

    $query = $db->simple_select("posts p", "MIN(p.dateline) firstpost, MAX(p.dateline) lastpost", $pids);

    $query = $db->fetch_array($query);
    $firstpost = $query['firstpost'];
    $lastpost = $query['lastpost'];

    $notpids = str_replace('pid IN(', 'pid NOT IN(', $pids);

    $query = $db->simple_select("posts p",
                    "MAX(p.dateline) dateline",
                    "p.tid='$tid'
                    AND $notpids
                    AND p.dateline <= {$firstpost}"
    );

    $previous_dateline = 0;
    if($db->num_rows($query) == 1) {
        $previous_dateline = intval($db->fetch_field($query, 'dateline'));
    }

    $where_sql = "p.dateline >= {$previous_dateline}";

    $islastpage = (intval($page) == intval($pages));
    if(!$islastpage) {
        $where_sql .= " AND p.dateline < $lastpost";
    }

    $query = $db->query("
        SELECT u.*, u.username AS userusername, p.*, f.*
        FROM ".TABLE_PREFIX.BACKUPTABLE_PREFIX."posts p
        LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
        LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
        WHERE tid='$tid' AND $where_sql
        ORDER BY p.dateline
    ");

    $deleted_posts = array();
    while($post = $db->fetch_array($query))    {
        $deleted_posts[] = $post;
    }

}

function whfshowdeletedposts_insert_deleted_posts($dateline_cut = 0) {
    global $posts, $theme, $lang;
    global $deleted_posts, $ismod;

    $lang->load("editpost");

    $headerattrs = '';
    if($ismod) {
        $headerattrs = 'onclick="this.up(\'table\').down(\'tbody\').toggle()" style="cursor:pointer"';
    }

    $deleted = current($deleted_posts);
    while($deleted) {
        if($dateline_cut > 0 && $deleted['dateline'] > $dateline_cut) {
            break;
        }
        $posts .= <<<HTML
        <table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder" style="margin: 5px 0">
        <thead>
            <tr>
                <td class="tcat" {$headerattrs}>
                    <div class="smalltext">{$lang->post_deleted} {$lang->by} {$deleted['username']}</div>
                </td>
            </tr>
        </thead>
HTML;
        if($ismod) {
            $posts .= '
            <tbody  style="display: none">
            <tr>
                <td class="trow1">';

            $posts .= build_postbit($deleted, 1);
            $posts .= '</td></tr></tbody>';
        }
        $posts .= '</table>';

        $deleted = next($deleted_posts);
    }
}

function whfshowdeletedposts_postbit($post) {
    global $deleted_posts;
    if(THIS_SCRIPT != "showthread.php") return;

    if(is_null($deleted_posts)) {
        whfshowdeletedposts_get_deleted_posts();
    }

    whfshowdeletedposts_insert_deleted_posts($post['dateline']);
}

function whfshowdeletedposts_showthread_linear($post) {
    global $deleted_posts;

    if(is_null($deleted_posts)) return;

    whfshowdeletedposts_insert_deleted_posts();
}
?>
