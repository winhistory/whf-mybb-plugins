<?php
/**
 * Simple HTTPS/HTTP Dual Mode Plugin
 *
 */
 
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("global_start",  "https_global",  5);
$plugins->add_hook("archive_start", "https_archive", 5);
$plugins->add_hook("parse_message", "https_parse_message", 5);


function https_info() {
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
        "name"           => "HTTPS/HTTP Dual Mode",
        "description"    => "A simple plugin which rewrites the bburl",
        "website"        => "",
        "author"         => "gandro",
        "authorsite"     => "",
        "version"        => "1.0",
        "guid"           => "",
        "compatibility"  => "*"
    );
}

function https_global() {
    global $settings;
    $start = strpos($settings['bburl'], '://')+3;
    
    if($_SERVER['HTTPS']) {
        $settings['bburl'] = 'https://'.substr($settings['bburl'], $start);
        $settings['cookiepath'] = $settings['cookiepath']."; Secure";
    } else {
        $settings['bburl'] = 'http://'.substr($settings['bburl'], $start);
    }
}

function https_archive() {
    global $settings, $base_url, $archiveurl;

    $old_bburl = $settings['bburl'];
    https_global();
    $new_bburl = $settings['bburl'];
    
    $base_url   = str_replace($old_bburl, $new_bburl, $base_url);
    $archiveurl = str_replace($old_bburl, $new_bburl, $archiveurl);
}

function https_parse_message($message) {
    global $settings;
    $start = strpos($settings['bburl'], '://')+3;
    if($_SERVER['HTTPS']) {
        $other_bburl = 'http://'.substr($settings['bburl'], $start);
    } else {
        $other_bburl = 'https://'.substr($settings['bburl'], $start);
    }
    return str_replace($other_bburl, $settings['bburl'], $message);
}

?>
