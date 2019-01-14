<?php
if(!defined('IN_MYBB')) {
    die('This file cannot be accessed directly.');
}

$plugins->add_hook("index_end",             "whfrecenttopics_show_topics");
$plugins->add_hook("usercp_options_end",     "whfrecenttopics_show_options");
$plugins->add_hook("usercp_do_options_end", "whfrecenttopics_save_options");

function whfrecenttopics_info() {

    return array(
        "name"          => "WHF Recent Topics",
        "description"   => "Shows a list of recent topics on the index page",
        "website"       => "",
        "author"        => "gandro",
        "authorsite"    => "",
        "version"       => "1.0.0",
        "guid"          => "",
        "compatibility" => "1*"
    );
}


function whfrecenttopics_install() {
    global $db;

    $db->query("
        ALTER TABLE `".TABLE_PREFIX."users`
        ADD `whfrecenttopics_visit` INT  NOT NULL DEFAULT '0',
        ADD `whfrecenttopics_conf`  TEXT NOT NULL DEFAULT ''
    ");
}

function whfrecenttopics_is_installed() {
    global $db;
    return $db->field_exists('whfrecenttopics_conf', "users");
}

function whfrecenttopics_uninstall() {
    global $db;

    $db->query("
        ALTER TABLE `".TABLE_PREFIX."users`
        DROP COLUMN `whfrecenttopics_visit`,
        DROP COLUMN `whfrecenttopics_conf`
    ");
}

function whfrecenttopics_activate() {
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    global $db;

    /************************************************/
    /*************** AdminCP Settings ***************/
    /************************************************/

    $query = $db->query("
            SELECT `disporder` 
            FROM `".TABLE_PREFIX."settinggroups` 
            ORDER BY `disporder` DESC LIMIT 1
    ");
    $disporder = $db->fetch_field($query, "disporder") + 1;

    $insertarray = array(
        'name' => 'whfrecenttopics',
        'title' => 'WHF Recent Topics',
        'description' => 'Default user settings for the recent topics list.',
        'isdefault' => '0',
        'disporder' => $disporder
    );
    $gid = $db->insert_query("settinggroups", $insertarray);

    $insertarray = array(
        'name' => 'whfrecenttopics_pos',
        'title' => 'Position',
        'description' => 'Default position of the recent topics list.',
        'optionscode' => "radio \ntop=Top of page\nbottom=Bottom of page\nhide=Hide Recent Topics",
        'value' => 'top',
        'disporder' => '0',
        'gid' => $gid
    );
    $db->insert_query("settings", $insertarray);
    
    $insertarray = array(
        'name' => 'whfrecenttopics_count',
        'title' => 'Number of Topics',
        'description' => 'Default number of topics to be shown.',
        'optionscode' => 'text',
        'value' => '20',
        'disporder' => '1',
        'gid' => $gid
    );
    $db->insert_query("settings", $insertarray);

    $insertarray = array(
        'name' => 'whfrecenttopics_lidisplay',
        'title' => 'Less Important Forum Display',
        'description' => 'How to display threads from less important forums',
        'optionscode' => "radio \nnormal=Normal\nsemitransparent=Semi-transparent\nhidden=Hide threads",
        'value' => 'normal',
        'disporder' => '2',
        'gid' => $gid
    );
    $db->insert_query("settings", $insertarray);

    $insertarray = array(
        'name' => 'whfrecenttopics_lifid',
        'title' => 'Less Important Forums',
        'description' => 'Comma-separated list of less important FIDs.',
        'optionscode' => 'text',
        'value' => '',
        'disporder' => '23',
        'gid' => $gid
    );
    $db->insert_query("settings", $insertarray);
    
    rebuild_settings();

    /************************************************/
    /************ Template Modifications ************/
    /************************************************/


    $options_template = <<<EOT
<br />
<fieldset class="trow2">
<legend><strong>{\$lang->recenttopics}</strong></legend>
<table cellspacing="0" cellpadding="2">
<tr>
<td valign="top" width="1">
<input type="checkbox" class="checkbox" name="recenttopics_hideforum" 
        id="recenttopics_hideforum" value="1" {\$recenttopicshideforum} />
</td>
<td><span class="smalltext">
<label for="recenttopics_hideforum">{\$lang->recenttopics_hideforum}</label>
</span></td>
</tr>
<tr>
<td colspan="2"><span class="smalltext">{\$lang->recenttopics_count}</span></td>
</tr>
<tr>
<td colspan="2">
<select name="recenttopics_count">
<option value="0">{\$lang->use_default}</option>
<option value="5" {\$recenttopiccount['5']}>{\$lang->recenttopics_show_last_5_topics}</option>
<option value="10" {\$recenttopiccount['10']}>{\$lang->recenttopics_show_last_10_topics}</option>
<option value="15" {\$recenttopiccount['15']}>{\$lang->recenttopics_show_last_15_topics}</option>
<option value="20" {\$recenttopiccount['20']}>{\$lang->recenttopics_show_last_20_topics}</option>
<option value="30" {\$recenttopiccount['30']}>{\$lang->recenttopics_show_last_30_topics}</option>
<option value="40" {\$recenttopiccount['40']}>{\$lang->recenttopics_show_last_40_topics}</option>
<option value="50" {\$recenttopiccount['50']}>{\$lang->recenttopics_show_last_50_topics}</option>
</select>
</td>
</tr>
<tr>
<td colspan="2"><span class="smalltext">{\$lang->recenttopics_position}</span></td>
</tr>
<tr>
<td colspan="2">
<select name="recenttopics_position">
<option value="default">{\$lang->use_default}</option>
<option value="top" {\$recenttopicposition['top']}>{\$lang->recenttopics_position_top}</option>
<option value="bottom" {\$recenttopicposition['bottom']}>{\$lang->recenttopics_position_bottom}</option>
<option value="hide" {\$recenttopicposition['hide']}>{\$lang->recenttopics_position_hide}</option>
</select>
</td>
</tr>
<tr>
<td colspan="2"><span class="smalltext">{\$lang->recenttopics_lidisplay}</span></td>
</tr>
<tr>
<td colspan="2">
<select name="recenttopics_lidisplay">
<option value="default">{\$lang->use_default}</option>
<option value="normal" {\$recenttopiclidisplay['normal']}>{\$lang->recenttopics_lidisplay_normal}</option>
<option value="semitransparent" {\$recenttopiclidisplay['semitransparent']}>{\$lang->recenttopics_lidisplay_semitransparent}</option>
<option value="hidden" {\$recenttopiclidisplay['hidden']}>{\$lang->recenttopics_lidisplay_hidden}</option>
</select>
</td>
</tr>
</table>
</fieldset>
EOT;

    $insertarray = array(
        'title' => 'whfrecenttopics_options',
        'template' => $db->escape_string($options_template),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insertarray);

    find_replace_templatesets(
        "index", 
        array(
            "#".preg_quote('{$header}')."#",
            "#".preg_quote('{$forums}')."#"
        ),
        array(
            '{$header}'."\n".'{$recenttopics_top}',
            '{$forums}'."\n".'{$recenttopics_bottom}'
        )
    );
    
    find_replace_templatesets(
        'usercp_options',
        '#(date_time_options.*</fieldset>)(.*'.preg_quote('{$tppselect}').')#s',
        '$1'."\n".'{$whfrecenttopics_options}'.'$2'
    );
}

function whfrecenttopics_deactivate() {
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    global $db;

    $db->delete_query("settinggroups", "name='whfrecenttopics'");

    $db->delete_query("settings", "name IN(
        'whfrecenttopics_pos',
        'whfrecenttopics_count',
        'whfrecenttopics_lidisplay',
        'whfrecenttopics_lifid'
    )");
    
    rebuild_settings();

    $db->delete_query("templates", "title='whfrecenttopics_options'");

    find_replace_templatesets(
        "index",
        array(
            "#".preg_quote('{$header}'."\n".'{$recenttopics_top}')."#",
            "#".preg_quote('{$forums}'."\n".'{$recenttopics_bottom}')."#",
        ),
        array(
            '{$header}',
            '{$forums}'
        ),
        0
    );
    
    find_replace_templatesets(
        'usercp_options',
     '#(date_time_options.*</fieldset>)'.
     "\n".preg_quote('{$whfrecenttopics_options}').
     '(.*'.preg_quote('{$tppselect}').')#s',
     '$1$2',
     0
    );
}


function whfrecenttopics_show_options() {
    global $db, $mybb, $theme, $lang, $templates;
    global $whfrecenttopics_options;

    $lang->load('whfrecenttopics');

    $conf = whfrecenttopics_load_userconfig(false);

    $recenttopiccount = array();
    $recenttopicposition = array();
    $recenttopiclidisplay = array();

    $recenttopiccount[$conf['count']] = 'selected="selected"';
    $recenttopicposition[$conf['position']] = 'selected="selected"';
    $recenttopiclidisplay[$conf['lidisplay']] = 'selected="selected"';
    $recenttopicshideforum = ($mybb->cookies['recenttopics_hideforum'] == 'yes') ? 
                                'checked="checked"' : '';
                                
    eval('$whfrecenttopics_options = "'.$templates->get("whfrecenttopics_options").'";');
}

function whfrecenttopics_save_options() {
    global $db, $mybb, $page, $theme, $lang, $permissioncache;

    $count       = intval($mybb->input['recenttopics_count']);
    $position  = $mybb->input['recenttopics_position'];
    $lidisplay = $mybb->input['recenttopics_lidisplay'];
    
    if(!(
        $count == 5 || $count == 15 || 
        (($count%10 == 0) && $count >= 10 && $count <= 50)
    )) {
        $count = 0;
    }
    
    if(!($position == 'top' || $position == 'bottom' || $position == 'hide')) {
        $position = '';
    }
    
    if(!($lidisplay == 'normal' || $lidisplay == 'hidden' || $lidisplay == 'semitransparent')) {
        $lidisplay = '';
    }
    
    $conf = serialize(array(
        'position'     => $position,
        'count'        => $count,
        'lidisplay' => $lidisplay
    ));
    
    $hideforum = $mybb->input['recenttopics_hideforum'] ? 'yes' : 'no';
    my_setcookie('recenttopics_hideforum', $hideforum);
    
    $db->update_query('users', 
        array('whfrecenttopics_conf' => $db->escape_string($conf)),
        "uid='{$mybb->user['uid']}'"
    );
}

function whfrecenttopics_load_userconfig($loaddefaults = true) {
    global $db, $mybb;
    
    $data = false;
    $conf = array(
        'position'     => '',
        'count'        => 0,
        'lidisplay' => ''
    );
    
    if($mybb->user['uid'] != 0) {
        $data = unserialize($mybb->user["whfrecenttopics_conf"]);
    }
    
    if(is_array($data)) {
        $conf = array_merge($conf, $data);
    }
    
    if($loaddefaults) {
        if(!$conf['position']) {
            $conf['position'] = $mybb->settings['whfrecenttopics_pos'];
        }
        
        if(!$conf['count']) {
            $conf['count'] = $mybb->settings['whfrecenttopics_count'];
        }
        
        if(!$conf['lidisplay']) {
            $conf['lidisplay'] = $mybb->settings['whfrecenttopics_lidisplay'];
        }
    }

    return $conf;
}

function whfrecenttopics_show_topics() {
    global $db, $mybb, $theme, $lang, $permissioncache;
    global $recenttopics_top, $recenttopics_bottom;

    $lang->load('whfrecenttopics');

    require_once MYBB_ROOT."inc/functions_search.php";

    /************************************************/
    /************** Settings and Flags **************/
    /************************************************/
    
    $conf = whfrecenttopics_load_userconfig();
    switch($conf['position']) {
        case 'hide':
            return;
        case 'bottom':
            $recenttopics = &$recenttopics_bottom;
            break;
        case 'top':
        default:
            $recenttopics = &$recenttopics_top;
            break;
    }

    $liforums = trim($mybb->settings['whfrecenttopics_lifid']);
    if(!empty($liforums)) {
        $liforums = explode(',', $liforums);
    } else {
        $liforums = array();
    }

    $lastview = ($mybb->user['uid'] > 0) ? $mybb->user['whfrecenttopics_visit'] : 0;
    $hideforum = ($mybb->cookies['recenttopics_hideforum'] == 'yes');

    $ismobile = (
                   isset($mybb->settings['gomobile_theme_id']) &&
                   isset($mybb->user['style']) &&
                   $mybb->settings['gomobile_theme_id'] == $mybb->user['style']
    );

    /************************************************/
    /**************** Database Query ****************/
    /************************************************/

    if(!is_array($permissioncache) || 
        (is_array($permissioncache) && ((count($permissioncache)==1) && 
        (isset($permissioncache['-1']) && ($permissioncache['-1'] = "1"))))
    ) {
       $permissioncache = forum_permissions();
    }

    $where_sql = '';

    $unsearchforums = get_unsearchable_forums();
    if($unsearchforums) {
        $where_sql .= " AND t.fid NOT IN ($unsearchforums) ";
    }

    $inactiveforums = get_inactive_forums();
    if($inactiveforums) {
        $where_sql .= " AND t.fid NOT IN ($inactiveforums) ";
    }
    
    if($conf['lidisplay'] == 'hidden' && !empty($liforums)) {
        $where_sql .= " AND t.fid NOT IN (".implode(',', $liforums).") ";
    }

    $query = $db->query("
        SELECT
            t.tid, t.fid, t.subject, t.lastposteruid, t.lastposter, t.lastpost, t.replies,
            f.name as forum_name,
            tr.dateline as threadread, fr.dateline as forumread
        FROM ".TABLE_PREFIX."threads as t
        JOIN ".TABLE_PREFIX."forums as f ON (f.fid=t.fid)
        LEFT JOIN ".TABLE_PREFIX."threadsread as tr ON (t.tid=tr.tid AND tr.uid='{$mybb->user['uid']}')
        LEFT JOIN ".TABLE_PREFIX."forumsread  as fr ON (t.fid=fr.fid AND fr.uid='{$mybb->user['uid']}')
        WHERE 
            t.visible = 1
            AND t.closed NOT LIKE 'moved|%'
            $where_sql
        ORDER BY t.lastpost DESC LIMIT {$conf['count']}" 
    );
    
    if($lastview >= 0 && $mybb->user['uid'] > 0) {
        $db->update_query('users', 
            array('whfrecenttopics_visit' => TIME_NOW),
            "uid='{$mybb->user['uid']}'"
        );
    }

    /************************************************/
    /**************** Display Header ****************/
    /************************************************/

    if(!$ismobile) {
        global $headerinclude;
        $headerinclude .= <<<STYLESHEET
<style type="text/css">
#recenttopics {
    table-layout: fixed;
    width: 100%;
}

#recenttopics tbody {
    line-height: 1;
}

#recenttopics td.recenttopic {
    white-space: nowrap;
    overflow:hidden;
}

#recenttopics td.recenttopic a {
    margin-right: 4em;
}

#recenttopics td.recentforum {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

#recenttopics a.lidisplay {
      opacity: 0.6;
    filter: alpha(opacity = 60);
}
</style>
STYLESHEET;
    }

    $recenttopics .= '<table id="recenttopics" border="0"
                        cellspacing="'.$theme['borderwidth'].'"
                        cellpadding="'. $theme['tablespace']. '"
                        class="tborder">';
                        
    if(!$ismobile) {
        if($hideforum) {
            $recenttopics .= '<colgroup>
                                <col span="1"/>
                                <col span="1"/>
                                <col span="1" style="width: 4em;"/>
                              </colgroup>';
        } else {
            $recenttopics .= '<colgroup>
                                <col span="1" style="width: 42em;"/>
                                <col span="1"/>
                                <col span="1"/>
                                <col span="1" style="width: 4em;"/>
                              </colgroup>';
        }
    
        $colcount = ($hideforum) ? 3 : 4;
    } else {
        $colcount = 2;
    }

    $recenttopics .='<thead>
                    <tr>
                    <td class="thead" colspan="'.$colcount.'">
                    <div><strong>'.$lang->recenttopics.'</strong></div>
                    </td>
                    </tr>
                    </thead>
                    <tbody>';

    /************************************************/
    /**************** Display Content ***************/
    /************************************************/
  
    $rowcount = 0;

    while($threadRow = $db->fetch_array($query)) {
        
        if($lastview > 0 && $threadRow['lastpost'] < $lastview) {
            if($rowcount > 0) {
                $recenttopics .= '<tr><td colspan="'.$colcount.'" class="trow_sep hr"></td></tr>';
            }
            $lastview = 0;
        }
    
        $recenttopics .= '<tr class="'.$trcss.'">';
        
        $subject = $threadRow['subject'];
        if(my_strlen($subject) > 65 && !$ismobile) {
            $subject = my_substr($subject, 0, 62)."...";
        }

        $subject = htmlspecialchars_uni($subject);
        //$postdate = my_date($mybb->settings['dateformat'], $threadRow['lastpost']);
        $postdate = my_date('d. M', $threadRow['lastpost']);
        $posttime = my_date($mybb->settings['timeformat'], $threadRow['lastpost']);
        
        if(TIME_NOW - $threadRow['lastpost'] > 24*60*60) {
            $timestamp = $postdate;
        } else {
            $timestamp = $posttime;
        }
        
        $forumread = intval($threadRow['forumread']);
        $threadread = intval($threadRow['threadread']);
        if(!$threadread) {
            if($mybb->settings['threadreadcut'] > 0) {
                $threadread = TIME_NOW-$mybb->settings['threadreadcut']*60*60*24;
            } else {
                $threadread_cookie = my_get_array_cookie("threadread", $post['tid']);
                if($threadread_cookie) {
                    $threadread = $threadread_cookie;
                } else {
                    $threadread = $mybb->user['lastvisit'];
                }
            }
        }
        
        $lastread = ($forumread > $threadread) ? $forumread : $threadread;
        $unread = $threadRow['lastpost'] > $lastread;

        $linkcss = ($unread ? 'subject_new' : '');
        if($conf['lidisplay'] == 'semitransparent' && in_array($threadRow['fid'], $liforums)) {
            $linkcss .= ' lidisplay';
        }    

        if(!$ismobile) {
            /**********************/
            /**** Desktop View ****/
            /**********************/
            
            $recenttopics .= '
                <td class="trow1 recenttopic">
                <div class="float_right">
                    '.$timestamp.'
                </div>
                <a class="'.$linkcss.'"
                    href="'.get_thread_link(
                        $threadRow['tid'], 0, 
                        ($unread ? 'newpost' : 'lastpost')
                    ).'">
                    '.$subject.'
                </a>
                 </td>
                <td class="trow2">'.
                    $lang->recenttopics_by.' '.
                    build_profile_link(
                        $threadRow['lastposter'],
                        $threadRow['lastposteruid']
                    ).
                '</td>'.
                ($hideforum ? '' : '<td class="trow1 smalltext recentforum">
                    <a href="'.get_forum_link($threadRow['fid']).'">'
                    .$threadRow['forum_name'].'</a>
                </td>').
                '<td class="'.($hideforum ? 'trow1' : 'trow2').' smalltext" align="center">'
                    .$threadRow['replies'].
                '</td>';
        } else {
            /*********************/
            /**** Mobile View ****/
            /*********************/
            
            $recenttopics .= '<td class="trow1">
                <a class="'.$linkcss.'"
                    href="'.get_thread_link(
                        $threadRow['tid'], 0, 
                        ($unread ? 'newpost' : 'lastpost')
                    ).'">
                    '.$subject.'
                </a>
                <div class="lastbytext">
                    '.$timestamp.' '.
                    $lang->recenttopics_by.' '.
                    build_profile_link(
                        $threadRow['lastposter'],
                        $threadRow['lastposteruid']
                    ).
                '</div>
            </td>
            <td class="trow2 lastbytext" width="1" align="center">'
                .$threadRow['replies'].
            '</td>';
        }

            
        $recenttopics .= '</tr>';
        
        $rowcount++;
    }


    $recenttopics .= "</tbody></table><br />";
}


?>
