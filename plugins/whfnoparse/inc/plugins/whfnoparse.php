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

$plugins->add_hook("parse_message_start", "whfnoparse_parse_message_start");
$plugins->add_hook("parse_message_end", "whfnoparse_parse_message_end");



function whfnoparse_info() {
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
        "name"           => "WHF NoParse",
        "description"    => "Provides the [noparse] MyCode",
        "website"        => "",
        "author"         => "gandro",
        "authorsite"     => "",
        "version"        => "1.0",
        "guid"           => "",
        "compatibility"  => "*"
    );
}

define('WHFNOPARSE_MAGIC', "\x01\x02WHFNOPARSE\x03\x04");
define('WHFNOPARSE_CODE_TITLE', "<div class=\"codeblock\">\n<div class=\"title\">");
define('WHFNOPARSE_CODE_PREFIX', "\n</div><div class=\"body\" dir=\"ltr\"><code>");
define('WHFNOPARSE_CODE_SUFFIX', "</code></div></div>\n");

function whfnoparse_insert_tag($match) {
    if($match[1] === 'noparse') {
        return '[code]'.WHFNOPARSE_MAGIC.$match[2].'[/code]';
    } else {
        return $match[0];
    }
}

function whfnoparse_parse_message_start($message) {
    /* replace [noparse], but not within closed [code] blocks */
    return preg_replace_callback(
        '#\[(code|php|noparse)\](.*?)\[/\\1\]#is',
        'whfnoparse_insert_tag',
        $message
    );
}

function whfnoparse_parse_message_end($message) {
    global $lang;

    return preg_replace(
        '#'.
            preg_quote(WHFNOPARSE_CODE_TITLE, '#').
            preg_quote($lang->code.'<br />', '#').
            preg_quote(WHFNOPARSE_CODE_PREFIX, '#').
            preg_quote(WHFNOPARSE_MAGIC, '#').'(.*?)'.
            preg_quote(WHFNOPARSE_CODE_SUFFIX, '#').
        '#is',
        '$1',
        str_replace('&nbsp;', ' ', $message)
    );
}

?>
