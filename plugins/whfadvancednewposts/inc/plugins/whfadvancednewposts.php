<?php

if(!defined('IN_MYBB')) {
    die('This file cannot be accessed directly.');
}

$plugins->add_hook("search_start", "whfadvancednewposts_search");
$plugins->add_hook("search_results_thread", "whfadvancednewposts_show_lastvisitbar");


function whfadvancednewposts_info() {

    return array(
        "name"              => "WHF Advanced New Posts",
        "description"       => "Advanced search for new posts",
        "website"           => "",
        "author"            => "gandro",
        "authorsite"        => "",
        "version"           => "1.0.0",
        "guid"              => "",
        "compatibility"     => "16*"
    );
}

function whfadvancednewposts_activate() {
    require_once MYBB_ROOT."inc/adminfunctions_templates.php";
    find_replace_templatesets(
        'search_results_threads_thread',
    	'#'.preg_quote('<tr>').'#i',
        '{$lastvisit_cut}'."\n".'<tr>'
    );
}

function whfadvancednewposts_deactivate() {
    require_once MYBB_ROOT."inc/adminfunctions_templates.php";
    find_replace_templatesets(
        'search_results_threads_thread',
    	'#'.preg_quote('{$lastvisit_cut}'."\n".'<tr>').'#i',
        '<tr>', 0
    );
}

function whfadvancednewposts_search() {
    global $db, $lang, $mybb, $session, $plugins, $permissioncache;
    if($mybb->input['action'] != "unread" || !$mybb->user['uid']) {
        return;
    }

    if(!is_array($permissioncache) || 
        (is_array($permissioncache) && ((count($permissioncache)==1) && 
        (isset($permissioncache['-1']) && ($permissioncache['-1'] = "1"))))
    ) {
       $permissioncache = forum_permissions();
    }
    
    $unsearchforums = get_unsearchable_forums();
    if($unsearchforums) {
        $where_sql .= " AND t.fid NOT IN ($unsearchforums)";
    }
    
    $inactiveforums = get_inactive_forums();
    if ($inactiveforums) {
        $where_sql .= " AND t.fid NOT IN ($inactiveforums)";
    }
    
    
    $global_threadreadcut = 0;
    
    if($mybb->settings['threadreadcut'] > 0) {
        $global_threadreadcut = TIME_NOW-$mybb->settings['threadreadcut']*60*60*24;
    }
    
   $query = $db->query("
        SELECT DISTINCT t.tid
        FROM ".TABLE_PREFIX."threads as t
        LEFT JOIN ".TABLE_PREFIX."threadsread as tr ON (t.tid=tr.tid AND tr.uid='{$mybb->user['uid']}')
        LEFT JOIN ".TABLE_PREFIX."forumsread  as fr ON (t.fid=fr.fid AND fr.uid='{$mybb->user['uid']}')
        WHERE 
            t.visible = 1
            AND t.lastpost > $global_threadreadcut
            AND (tr.dateline IS NULL OR t.lastpost > tr.dateline) 
            AND (fr.dateline IS NULL OR t.lastpost > fr.dateline)  
            $where_sql
        ");
        
    
    $querycache = array();
    
    while($tid = $db->fetch_array($query)) {
       $querycache[] = $tid['tid'];
    }
    
    if($querycache) {
        $querycache = implode(",", $querycache);
    } else {
        error($lang->error_nosearchresults);
    }
    
    $querycache = "t.tid IN ($querycache)";
    
    $sid = md5(uniqid(microtime(), 1));
    $searcharray = array(
        "sid" => $db->escape_string($sid),
        "uid" => $mybb->user['uid'],
        "dateline" => TIME_NOW,
        "ipaddress" => $db->escape_string($session->ipaddress),
        "threads" => '',
        "posts" => '',
        "resulttype" => "threads",
        "querycache" => $db->escape_string($querycache),
        "keywords" => ''
    );

    $plugins->run_hooks("search_do_search_process");
    $db->insert_query("searchlog", $searcharray);
    redirect("search.php?action=results&lastvisitcut=1&sid=".$sid, $lang->redirect_searchresults);
}

function whfadvancednewposts_show_lastvisitbar() {
    global $mybb, $thread, $lastvisit_cut;
    static $lastvisitbar_shown = false;
    
    $lastvisit_cut = '';
    
    if(!$mybb->input['lastvisitcut'] || $lastvisitbar_shown) {
    	return;
    }

    if($thread['lastpost'] < $mybb->user['lastvisit']) {
    	$lastvisit_cut = '<tr><td colspan="8" class="trow_sep hr"></td></tr>';
    	$lastvisitbar_shown = true;
    }
}
