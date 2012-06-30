<?php
/**
 * WHF Editor in QuickReply
 *
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("showthread_start", "whfquickreplyeditor_showthread_start");

function whfquickreplyeditor_info()
{    
    return array(
        "name" => 'WHF Editor in QuickReply',
        "description" => 'Shows editor with buttons in quick reply.',
        "website" => "",
        "author" => "gandro",
        "authorsite" => "",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "16*"
    );
}

$stylesheet = '';

function whfquickreplyeditor_activate() {
    global $stylesheet;
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    
    $find = array(
    	"#</textarea>#i", 
        "#".preg_quote('<br /><br />')."#i", 
        '#rows="8"#i'
    );
    
    $replace = array(
    	"</textarea>\n{\$codebuttons}",
        '</span><br />{$clickablesmilies}<span class="smalltext"><br />',
        'rows="16"'
    );
    
    find_replace_templatesets(
        "showthread_quickreply", 
        $find, $replace
    );
}

function whfquickreplyeditor_deactivate() {   
    global $stylesheet;
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    
    $find = array(
        "#".preg_quote('</span><br />{$clickablesmilies}<span class="smalltext"><br />')."#i", 
        "#".preg_quote("\n{\$codebuttons}")."#i",
        '#rows="16"#i'
    );
    
    $replace = array(
        '<br /><br />',
        '',
        'rows="8"'
    );
    
    find_replace_templatesets(
        "showthread_quickreply", 
        $find, $replace, 0
    );
}

function whfquickreplyeditor_showthread_start() {
    global $mybb, $forumpermissions, $thread, $fid, $forum, $codebuttons, 
            $clickablesmilies, $headerinclude;

    $ismobile = (
        isset($mybb->settings['gomobile_theme_id']) &&
        isset($mybb->user['style']) &&
        $mybb->settings['gomobile_theme_id'] == $mybb->user['style']
    );


    if($forumpermissions['canpostreplys'] != 0 &&$mybb->user['suspendposting'] != 1 && 
        ($thread['closed'] != 1 || is_moderator($fid)) && 
        $mybb->settings['quickreply'] != 0 &&
        $mybb->user['showquickreply'] != '0' && 
        $forum['open'] != 0 &&
        $mybb->user['showcodebuttons'] != 0 &&
        !$ismobile
    ) {
        $headerinclude .= '<style type="text/css">#message_old { display: none }</style>';
    
        if($forum['allowsmilies'] != 0) {
            $clickablesmilies = build_clickable_smilies();
        }
        
        $codebuttons = build_mycode_inserter();

    }
}
?>
