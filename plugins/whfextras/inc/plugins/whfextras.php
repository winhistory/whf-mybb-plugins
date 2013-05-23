<?php
/**
 * WHF Extras
 *
 */
 
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function whfextras_info() {
    /**
     * Array of information about the plugin.
     * name: The name of the plugin
     * description: Description of what the plugin does
     * website: The website the plugin is maintained at (Optional)
     * author: The name of the author of the plugin
     * authorsite: The URL to the website of the author (Optional)
     * version: The version number of the plugin
     * guid: Unique ID issued by the MyBB Mods site for version checking
     * compatibility: A CSV list of MyBB versions supported. Ex, "121,123", "12*". Wildcards supported.
     */
    return array(
        "name"           => "WHF Extras",
        "description"    => "WHF-specific template modifications",
        "website"        => "",
        "author"         => "gandro",
        "authorsite"     => "",
        "version"        => "1.0",
        "guid"           => "",
        "compatibility"  => "16*"
    );
}

$plugins->add_hook("global_start", "whfextras_start");
$plugins->add_hook("pre_output_page", "whfextras_pre_output_page");
$plugins->add_hook("misc_start", "whfextras_changestyle");
$plugins->add_hook("member_profile_end", "whfextras_member_nocontact");
$plugins->add_hook("showthread_start", "whfextras_showthread");
$plugins->add_hook("showthread_ismod", "whfextras_showthread_ismod");


$plugins->add_hook("usercp_options_end", "whfextras_options_show");
$plugins->add_hook("usercp_do_options_end", "whfextras_options_parse");

function whfextras_install() {
    global $db;

    $db->query("
        ALTER TABLE `".TABLE_PREFIX."users`
        ADD `whfextras` TEXT NOT NULL DEFAULT ''
    ");
}

function whfextras_is_installed() {
    global $db;
    return $db->field_exists('whfextras', "users");
}

function whfextras_uninstall() {
    global $db;

    $db->query("
        ALTER TABLE `".TABLE_PREFIX."users`
        DROP COLUMN `whfextras`
    ");
}

function whfextras_activate() {
    require  MYBB_ROOT.'/inc/adminfunctions_templates.php';
    global $db, $lang;

$popup_template = '
<a name="useful_links_popup"></a>
<div id="useful_links_popup" class="trow1" style="display:none;">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" 
    cellpadding="{$theme[\'tablespace\']}" class="tborder" style="min-width: 25ex;">
<tbody>
<tr>
    <td class="tcat"><strong class="smalltext">{$lang->forumbit_posts}</strong></td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="{$mybb->settings[\'bburl\']}/search.php?action=getdaily">{$lang->welcome_todaysposts}</a>
    </td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="{$mybb->settings[\'bburl\']}/search.php?action=getnew">{$lang->welcome_newposts}</a>
    </td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="{$mybb->settings[\'bburl\']}/misc.php?action=markread&amp;my_post_key={$mybb->post_code}">{$lang->markread}</a>
    </td>
</tr>
<tr>
    <td class="tcat"><strong class="smalltext">{$lang->whf_community}</strong></td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="http://tiny.cc/whirc" target="_blank">IRC Channel</a>
    </td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="http://pinkiserver.de/stats/" target="_blank">IRC Statistiken</a>
    </td>
</tr>
<tr>
    <td class="tcat"><strong class="smalltext">{$lang->whf_uploadservices}</strong></td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="http://www.pixelbanane.de/yafu/" target="_blank">chiakis YAFU</a>
    </td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="http://myblackbox.net/quickupload/" target="_blank">QuickUpload</a>
    </td>
</tr>
<tr>
    <td class="trow1 smalltext">
        <a href="http://upload.euda.org/" target="_blank">EudaShare</a>
    </td>
</tr>
</tbody>
</table>
</div>
<script type="text/javascript">
if(typeof PopupMenu !== "undefined") {
    new PopupMenu("useful_links");
}
</script>
';

    $insert_array = array(
        'title' => 'whfextras_useful_links_popup',
        'template' => $db->escape_string($popup_template),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
    

    find_replace_templatesets(
        'header_welcomeblock_member',
        '#'.preg_quote('<a href="{$mybb->settings[\'bburl\']}/search.php?action=getnew">{$lang->welcome_newposts}</a>').'#',
        '<a href="{$mybb->settings[\'bburl\']}/search.php?action=unread">{$lang->whf_unread_posts}</a>'
    );

    find_replace_templatesets(
        'header_welcomeblock_member',
        '#'.preg_quote('<a href="{$mybb->settings[\'bburl\']}/search.php?action=getdaily">{$lang->welcome_todaysposts}</a>').'#',
        '<a href="#useful_links_popup" id="useful_links">{$lang->whf_useful_links}</a>'
    );
    
    find_replace_templatesets(
        'footer',
        '#^#',
        '<!--{WHFEXTRAS_POPUP}-->'
    );
    
    find_replace_templatesets(
        'footer',
        '#'.preg_quote('{$lang_select}').'#',
        '<!--{WHFEXTRAS_STYLESELECT}-->'
    );

    $usercp_prefix = '{$lang->show_avatars}</label></span></td>'."\n".'</tr>';

    find_replace_templatesets(
        'usercp_options',
        '#'.preg_quote($usercp_prefix).'#s',
        $usercp_prefix."\n".'{$whfextras_avatar_options}'
    );

    whfextras_disable_stuff(true);
}

function whfextras_deactivate() {
    require  MYBB_ROOT.'/inc/adminfunctions_templates.php';
    global $db;

    whfextras_disable_stuff(false);

    find_replace_templatesets(
        'header_welcomeblock_member',
        '#'.preg_quote('<a href="{$mybb->settings[\'bburl\']}/search.php?action=unread">{$lang->whf_unread_posts}</a>', '#').'#',
        '<a href="{$mybb->settings[\'bburl\']}/search.php?action=getnew">{$lang->welcome_newposts}</a>',
        0
    );

    find_replace_templatesets(
        'header_welcomeblock_member',
        '#'.preg_quote('<a href="#useful_links_popup" id="useful_links">{$lang->whf_useful_links}</a>', '#').'#',
        '<a href="{$mybb->settings[\'bburl\']}/search.php?action=getdaily">{$lang->welcome_todaysposts}</a>',
        0
    );
    
    
    find_replace_templatesets(
        'footer',
        '#'.preg_quote('<!--{WHFEXTRAS_STYLESELECT}-->').'#',
        '{$lang_select}',
        0
    );
    
    find_replace_templatesets(
        'footer',
        '#^'.preg_quote('<!--{WHFEXTRAS_POPUP}-->').'#',
        '',
        0
    );
    
    $usercp_prefix = '{$lang->show_avatars}</label></span></td>'."\n".'</tr>';

    find_replace_templatesets(
        'usercp_options',
        '#'.preg_quote($usercp_prefix."\n".'{$whfextras_avatar_options}').'#s',
        $usercp_prefix, 0
    );

    $db->delete_query("templates", "title='whfextras_useful_links_popup'");
}

function whfextras_disable_stuff($remove) {
    
    $params = array(
        array('postbit_classic',         "{\$post['userstars']}"),
        array('postbit',                 "{\$post['userstars']}"),
        array('member_profile',          "{\$userstars}<br />"),
        array('memberlist_user',         "{\$user['userstars']}"),
        array('calendar_event',          "{\$event['userstars']}"),
        array('calendar_dayview_event',  "{\$event['userstars']}"),
        array('showthread',
            '<a href="showthread.php?mode=threaded&amp;tid={$tid}&amp;pid={$pid}#pid{$pid}">{$lang->threaded}</a> | '),
        array('usercp_options', 
            '<option value="threaded" {$threadview[\'threaded\']}>{$lang->threaded}</option>')
    );
    
    foreach($params as $param) {
        $template = $param[0];
        if($remove) {
            $search = $param[1];
            $replace = '<!--'.$param[1].'-->';
            $autocreate = 1;
        } else {
            $search = '<!--'.$param[1].'-->';
            $replace = $param[1];
            $autocreate = 0;
        }
        find_replace_templatesets(
                        $template, 
                        '#'.preg_quote($search, '#').'#', 
                        $replace, $autocreate
        );
    }

}

function whfextras_options_show() {
    global $whfextras_avatar_options, $lang, $mybb;
    
    $resizeavatarscheck = 'checked="checked"';
    if(isset($mybb->user['whfextras']['resizeavatars']) && $mybb->user['whfextras']['resizeavatars'] === false) {
        $resizeavatarscheck = '';
    }
    
$whfextras_avatar_options = <<<TEMPLATE
<tr>
<td valign="top" width="1"><input type="checkbox" class="checkbox" 
name="whf_resizeavatars" id="whf_resizeavatars" value="1" {$resizeavatarscheck} /></td>
<td><span class="smalltext"><label for="whf_resizeavatars">{$lang->whf_resize_avatars}</label></span></td>
</tr>
TEMPLATE;
}

function whfextras_options_parse() {
    global $mybb, $db;
    
    $resizeavatars = (boolean) $mybb->input['whf_resizeavatars'];
    
    $mybb->user['whfextras']['resizeavatars'] = $resizeavatars;
    
    $opts = serialize($mybb->user['whfextras']);
    
    $db->update_query('users', 
        array('whfextras' => $db->escape_string($opts)),
        "uid='{$mybb->user['uid']}'"
    );
}

function whfextras_start() {
    global $lang, $mybb;

    $lang->load("whfextras");
    $lang->load("index");
    $lang->load("usercp");

    $mybb->settings['showlanguageselect'] = 0;
    if($mybb->user['uid'] == 0 && intval($mybb->cookies['styleid']) > 0) {
        $mybb->user['style'] = intval($mybb->cookies['styleid']);
    }
    
    if($mybb->user['uid'] != 0) {
        $opts = unserialize($mybb->user["whfextras"]);
        if(is_array($opts)) {
            $mybb->user['whfextras'] = $opts;
        } else {
            $mybb->user['whfextras'] = array();
        }
        
        if(isset($mybb->user['whfextras']['resizeavatars'])) {
            if($mybb->user['whfextras']['resizeavatars'] == false) {
                $mybb->settings['postmaxavatarsize'] = '500x500';
            }
        }
    }
}

function whfextras_changestyle() {
    global $mybb, $db, $lang;
    
    if(
        $mybb->input['action'] == 'changestyle' &&
        isset($mybb->input['style']) && 
        $mybb->request_method == "post" &&
        ($mybb->user['uid'] == 0 ||
        verify_post_check($mybb->input['my_post_key'], true))
    ) {
        if($mybb->user['uid']) { 
            $db->update_query(
                "users", array('style' => intval($mybb->input['style'])), 
                "uid='{$mybb->user['uid']}'"
            );
        } else {
            if(intval($mybb->input['style']) == 0) {
                my_unsetcookie('styleid');
            } else {
                my_setcookie('styleid', intval($mybb->input['style']));
            }
        }
            
        $url = $_SERVER['HTTP_REFERER'];
        if(empty($url)) {
            $url = $mybb->settings['bburl'].'/index.php';
        }
            
        redirect($url, $lang->redirect_optionsupdated);
    }
}

function whfextras_pre_output_page($page) {
    global $mybb, $theme, $templates, $lang;
    global $gobutton;
    
    /* popup */
    eval('$popup = "'.$templates->get("whfextras_useful_links_popup").'";');
    
    /* style select */
    $name = "style";
    
    $styleselect = '<form method="post" action="'.$mybb->settings['bburl'].'/misc.php?action=changestyle">'.
                    '<input type="hidden" name="my_post_key" value="'.$mybb->post_code.'" />';
    $styleselect .= str_replace(
                    array('<select name="'.$name.'">', '</select>'),
                    array('<select name="'.$name.'" onchange="this.form.submit()">'.
                        '<optgroup label="'.$lang->style.'">'
                        ,
                        '</optgroup></select>'
                    ),
                    build_theme_select($name, $mybb->user['style'])
    );
    $styleselect .= $gobutton;
    $styleselect .= '</form>';

    eval('$wrapper = "'.$templates->get("footer_languageselect").'";');
    
    $styleselect = preg_replace('#<form .*</form>#s', $styleselect, $wrapper);
    
    return str_replace(
        array('<!--{WHFEXTRAS_POPUP}-->', '<!--{WHFEXTRAS_STYLESELECT}-->'),
        array($popup, $styleselect),
        $page
    );
}

function whfextras_member_nocontact() {
    global $mybb, $memprofile, $profilefields;
    if($mybb->user['uid'] == 0) {
        $memprofile['aim']   = "";
        $memprofile['yahoo'] = "";
        $memprofile['msn']   = "";
        $memprofile['icq']   = "";
    }
}

function whfextras_showthread() {
    global $mybb, $lang;
    $mybb->input['mode'] = 'linear';
    
    if(
        $mybb->settings['bblanguage'] == 'deutsch_du' ||
        $mybb->settings['bblanguage'] == 'deutsch_sie'
    ) {
        $lang->load('showthread');
        $lang->post_reply_img = "Neue Antwort schreiben";
    }
}

function whfextras_showthread_ismod() {
    global $closeoption;
    global $lang, $closelinkch, $stickch;

    $closeoption = "<br /><label><input type=\"checkbox\" class=\"checkbox\" ".
                       "name=\"modoptions[closethread]\" value=\"1\"".
                       "{$closelinkch} />&nbsp;<strong>".$lang->close_thread.
                       "</strong></label>";

    $closeoption .= "<br /><label><input type=\"checkbox\" class=\"checkbox\" ".
                        "name=\"modoptions[stickdummy]\" value=\"1\"".
                        "{$stickch} disabled=\"disabled\"/>&nbsp;<strong>".
                        $lang->stick_thread."</strong></label>";

    if(!empty($stickch)) {
        $closeoption .= "<input type=\"hidden\" ".
                            "name=\"modoptions[stickthread]\" value=\"1\" />";
    }
}
?>
