<?php
/**
 * WHF Image Resize
 *
 */
 
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("showthread_start", "whfimgresize_showthread");
$plugins->add_hook("private_read", "whfimgresize_private");
$plugins->add_hook("private_send_start", "whfimgresize_private");
$plugins->add_hook("newreply_start", "whfimgresize_newreply");
$plugins->add_hook("editpost_start", "whfimgresize_editpost");


function whfimgresize_info() {
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
        "name"           => "WHF Image Resize",
        "description"    => "Resizes all the images",
        "website"        => "",
        "author"         => "gandro",
        "authorsite"     => "",
        "version"        => "1.0",
        "guid"           => "",
        "compatibility"  => "*"
    );
}

function whfimgresize_addtopage($page) {
    global $headerinclude;
    $headerinclude .= '
<script type="text/javascript" src="jscripts/whfimgresize.js"></script>
<script type="text/javascript">WHFIMGRESIZE_PAGE = "'.$page.'";</script>';
}

function whfimgresize_showthread() {
    whfimgresize_addtopage('showthread');
}

function whfimgresize_private() {
    whfimgresize_addtopage('private');
}

function whfimgresize_newreply() {
    whfimgresize_addtopage('newreply');
}

function whfimgresize_editpost() {
    whfimgresize_addtopage('editpost');
}

?>
