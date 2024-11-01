<?php
/*
Plugin Name: TxtBear
Plugin URI: http://www.txtbear.com/
Description: Embed documents into your posts.
Author: eload24 ag
Author URI: http://www.eload24.com/
Version: 1.1.2223.2113
*/

// load plugin configuration
include(dirname(__FILE__).'/config.php');

$strPluginsFolder = WP_PLUGIN_URL;
if(is_ssl()) {
    $strPluginsFolder = preg_replace('/^http:\/\//', 'https://', $strPluginsFolder);
}

// load localizations
load_plugin_textdomain('txtbear', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/languages/', basename(dirname(__FILE__)).'/languages/');
load_plugin_textdomain('txtbear', '/');

// http://codex.wordpress.org/Embed
if(function_exists('wp_oembed_add_provider')) {
    wp_oembed_add_provider('http://view.txtbear.com/*', 'http://api.txtbear.com/book/oembed.{format}');
}

define('TXTBEAR_URL', $strPluginsFolder.'/'.basename(dirname(__FILE__)).'/');

function txtbear_activate() {
    global $wpdb;
    if(!$wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}txtbear_thumb_cache'")) {
        $wpdb->query("CREATE TABLE `{$wpdb->prefix}txtbear_thumb_cache` (
            `id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `url` VARCHAR( 50 ) NOT NULL ,
            `thumb` VARCHAR( 60 ) NOT NULL ,
            `width` INT( 1 ) NOT NULL ,
            `height` INT( 1 ) NOT NULL ,
            `time` INT( 1 ) NOT NULL ,
            UNIQUE ( `url` )
            ) ENGINE = MYISAM");
    }
    // find out if user is in a German country
    $strCountry = '';
    $arrReply = get('http://api.ipinfodb.com/v3/ip-country/?key=d5057ac258b55fc74f9911081be1bb34b0c29ae9a1811a28fdd16f4463d6d754&ip='.$_SERVER['REMOTE_ADDR']);
    if($arrReply['status'] == 200) {
        $arrData = explode(';', $arrReply['data']);
        $strCountry = '';
        if ($arrData[0] == 'OK') {
            $strCountry = $arrData[3];
        }
    }
    add_option('txtbear_admin_country', $strCountry);
}

function txtbear_upgrade() {
    global $wpdb;
    // modify field length of categories name (32 is too short)

    $strQuery = "SHOW FIELDS FROM `{$wpdb->prefix}txtbear_thumb_cache`";
    $arrRows = $wpdb->get_results($strQuery);
    foreach($arrRows as $arrField) {

        if($arrField->Field != 'thumb')
            continue;

        if($arrField->Type != 'varchar(60)')
            continue;

        $strQuery = "UPDATE `{$wpdb->prefix}txtbear_thumb_cache` CHANGE `thumb` `thumb` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
        $wpdb->query($strQuery);
        $strQuery = "DELETE FROM `{$wpdb->prefix}txtbear_thumb_cache`";
        $wpdb->query($strQuery);
    }
}

function txtbear_deactivate() {
    global $wpdb;
    $wpdb->query("DROP TABLE `{$wpdb->prefix}txtbear_thumb_cache`");
    delete_option('txtbear_admin_country');
}

// display media toolbar buttons
function txtbear_button() {
    global $post_ID, $temp_ID;
    $strFrameUrl = TXTBEAR_URL.'%s.php?post_id='.(0 == $post_ID ? $temp_ID : $post_ID).'&amp;adminurl=%s&amp;TB_iframe=true';
    $strAdminUrl = rawurlencode(dirname($_SERVER['REQUEST_URI']));
    if(is_ssl()) {
        $strFrameUrl = preg_replace('/^http:\/\//', 'https://', $strFrameUrl);
    }
    echo '<a href="'.sprintf($strFrameUrl, 'doc-upload', $strAdminUrl).'" class="thickbox" title="'.__('Embed document', 'txtbear').'"><img src="'.TXTBEAR_URL.'images/txtbear-button.png" alt="'.__('Embed document', 'txtbear').'" width="15" height="12" /></a>';
    if(txtbear_admin_is_german()) {
        echo '<a href="'.sprintf($strFrameUrl, 'search-eload24', $strAdminUrl).'" class="thickbox" title="'.__('Embed eload24 ebook', 'txtbear').'"><img src="'.TXTBEAR_URL.'images/eload24-button.png" alt="'.__('Embed eload24 ebook', 'txtbear').'" width="17" height="12" /></a>';
    }
}

function txtbear_getjpegsize($img_loc) {
    $handle = fopen($img_loc, "rb") or die("Invalid file stream.");
    $new_block = NULL;
    if(!feof($handle)) {
        $new_block = fread($handle, 32);
        $i = 0;
        if($new_block[$i]=="\xFF" && $new_block[$i+1]=="\xD8" && $new_block[$i+2]=="\xFF" && $new_block[$i+3]=="\xE0") {
            $i += 4;
            if($new_block[$i+2]=="\x4A" && $new_block[$i+3]=="\x46" && $new_block[$i+4]=="\x49" && $new_block[$i+5]=="\x46" && $new_block[$i+6]=="\x00") {
                // Read block size and skip ahead to begin cycling through blocks in search of SOF marker
                $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                $block_size = hexdec($block_size[1]);
                while(!feof($handle)) {
                    $i += $block_size;
                    $new_block .= fread($handle, $block_size);
                    if($new_block[$i]=="\xFF") {
                        // New block detected, check for SOF marker
                        $sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
                        if(in_array($new_block[$i+1], $sof_marker)) {
                            // SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
                            $size_data = $new_block[$i+2] . $new_block[$i+3] . $new_block[$i+4] . $new_block[$i+5] . $new_block[$i+6] . $new_block[$i+7] . $new_block[$i+8];
                            $unpacked = unpack("H*", $size_data);
                            $unpacked = $unpacked[1];
                            $height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
                            $width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
                            return array($width, $height);
                        } else {
                            // Skip block marker and read block size
                            $i += 2;
                            $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                            $block_size = hexdec($block_size[1]);
                        }
                    } else {
                        return FALSE;
                    }
                }
            }
        }
    }
    return FALSE;
}

function txtbear_thumb($strUrl) {
    global $wpdb;
    $strUrlDb = mysql_real_escape_string($strUrl);
    $objThumb = $wpdb->get_row("
        SELECT thumb, width, height, time
        FROM {$wpdb->prefix}txtbear_thumb_cache
        WHERE url = '$strUrlDb'");
    if(!$objThumb || $objThumb->time < time() - 86400) {
        $arrData = get("http://api.txtbear.com/book/oembed.sphp?url=$strUrl");
        if($arrData['status'] == 200) {
            $arrData = unserialize($arrData['data']);
            if(!isset($arrData['fail'])) {
                $strEmbed = $arrData['html'];
                $arrMatch = array();
                preg_match('/<img class="cover" src="(.+)" alt=".+"/', $strEmbed, $arrMatch);
                $strThumb = $arrMatch[1];
                $arrSize = txtbear_getjpegsize($strThumb);
                $intWidth = $arrSize[0];
                $intHeight = $arrSize[1];
                if($intWidth < $intHeight) { // portrait
                    if($intHeight > 200) {
                        $intWidth = intval($intWidth * 200 / $intHeight);
                        $intHeight = 200;
                    }
                }
                else { // landscape
                    if($intWidth > 200) {
                        $intHeight = intval($intHeight * 200 / $intWidth);
                        $intWidth = 200;
                    }
                }
                $wpdb->query("
                    REPLACE INTO {$wpdb->prefix}txtbear_thumb_cache SET
                    id = NULL,
                    url = '$strUrlDb',
                    thumb = '$strThumb',
                    width = $intWidth,
                    height = $intHeight,
                    time = UNIX_TIMESTAMP()");
                $objThumb = new stdClass;
                $objThumb->thumb = $strThumb;
                $objThumb->width = $intWidth;
                $objThumb->height = $intHeight;
                $objThumb->time = time();
                return $objThumb;
            }
            return false;
        }
        return false;
    }
    return $objThumb;
}

function txtbear_insertembed($strText) {
    // auto embed
    $arrMatch = array();
    preg_match_all('/\[doc( [^\]]+)\]/', $strText, $arrMatch);
    for($i = 0; $i < count($arrMatch[0]); $i++) {
        $strParams = $arrMatch[1][$i];
        // txtbear id
        $strTxtbear = false;
        $arrMatch2 = array();
        if(preg_match('/ txtbear=(\w+)/', $strParams, $arrMatch2)) {
            $strTxtbear = $arrMatch2[1];
        }
        // display mode
        $strDisplayMode = 'preview';
        $arrMatch2 = array();
        if(preg_match('/ mode=(preview|link)/', $strParams, $arrMatch2)) {
            $strDisplayMode = $arrMatch2[1];
        }
        // ebook title
        $strTitle = '';
        $arrMatch2 = array();
        if(preg_match('/ title="(.+)"/', $strParams, $arrMatch2)) {
            $strTitle = $arrMatch2[1];
            $strTitle = str_replace('"', '&quot;', $strTitle);
        }
        // align
        $strAlign = '';
        $arrMatch2 = array();
        if(preg_match('/ align=(left|center|right)/', $strParams, $arrMatch2)) {
            $strAlign = $arrMatch2[1];
        }
        // ebook id
        $intEbook = 0;
        $arrMatch2 = array();
        if(preg_match('/ eload24=(\d+)/', $strParams, $arrMatch2)) {
            $intEbook = $arrMatch2[1];
        }
        // extension
        $strExtend = '';
        $arrMatch2 = array();
        if(preg_match('/ extend="(.+)"/', $strParams, $arrMatch2)) {
            $strExtend = $arrMatch2[1];
        }
        // if eload24
        if($intEbook && $strTxtbear) {
            // build URL
            $strUrl = 'http://view.txtbear.com/'.$strTxtbear.'/?pid='.$intEbook.'#plugins=http://res.txtbear.com.s3.amazonaws.com/plugins/eload24.js' . ($strExtend ? '&'.$strExtend : '');
            // build embed
            $strEbook = sprintf('%05d', $intEbook);
            switch($strDisplayMode) {
                case 'preview':
                    $strEmbed = '<a href="'.$strUrl.'"><img class="cover" src="http://d1ekua9ie1ovig.cloudfront.net/cover/'.$strEbook.'.jpg" width="200" height="147" alt="'.$strTitle.'" /></a>';
                    switch($strAlign) {
                        case 'left':
                            $strEmbed = '<div style="float: left; margin: 0 2em 1em 0"><div class="TxtBearEmbed">'.$strEmbed.'</div></div>';
                            break;
                        case 'right':
                            $strEmbed = '<div style="float: right; margin: 0 0 1em 2em"><div class="TxtBearEmbed">'.$strEmbed.'</div></div>';
                            break;
                        case 'center':
                            $strEmbed = '<div style="position: relative; left: 50%; margin: 1em 0 1em -100px; width: 147px"><div class="TxtBearEmbed">'.$strEmbed.'</div></div>';
                            break;
                        default:
                            $strEmbed = '<div class="TxtBearEmbed">'.$strEmbed.'</div>';
                            break;
                    }
                    break;
                case 'link':
                    if(!$strTitle) {
                        $strTitle = __('View ebook for free', 'txtbear');
                    }
                    $strEmbed = '<a class="TxtBearEmbedInline" href="'.$strUrl.'" title="'.__('View ebook for free', 'txtbear').'">'.$strTitle.'</a>';
                    if($strAlign) {
                        $strEmbed = '<span style="display: block; text-align: '.$strAlign.'; margin: 1em 0">'.$strEmbed.'</span>';
                    }
                    break;
            }
            $strEmbed .= '<script type="text/javascript">if (!window.TxtBearEmbed) { document.write(unescape("%3Cscript src=\'http://static.txtbear.com/v/embed.js\' type=\'text/javascript\'%3E%3C/script%3E")); window.TxtBearEmbed = true; }</script>';
        }
        // if valid doc
        elseif($strTxtbear) {
            // build URL
            $strUrl = 'http://view.txtbear.com/'.$strTxtbear.'/' . ($strExtend ? '#'.$strExtend : '');
            // build embed
            $objThumb = txtbear_thumb($strUrl);
            switch($strDisplayMode) {
                case 'preview':
                    $strEmbed = '<a href="'.$strUrl.'"><img class="cover" src="'.$objThumb->thumb.'" width="'.$objThumb->width.'" height="'.$objThumb->height.'" alt="'.$strTitle.'" /></a>';
                    switch($strAlign) {
                        case 'left':
                            $strEmbed = '<span style="display: block; float: left; height: '.$objThumb->height.'px; margin: 0 2em 1em 0 !important" class="TxtBearEmbed">'.$strEmbed.'</span>';
                            break;
                        case 'right':
                            $strEmbed = '<span style="display: block; float: right; height: '.$objThumb->height.'px; margin: 0 0 1em 2em !important" class="TxtBearEmbed">'.$strEmbed.'</span>';
                            break;
                        case 'center':
                            $strEmbed = '<div style="position: relative; left: 50%; margin: 1em 0 1em -'.($objThumb->width/2).'px; width: '.$objThumb->height.'px"><div class="TxtBearEmbed">'.$strEmbed.'</div></div>';
                            break;
                        default:
                            $strEmbed = '<span style="display: block; height: '.$objThumb->height.'px" class="TxtBearEmbed">'.$strEmbed.'</span>';
                            break;
                    }
                    break;
                case 'link':
                    if(!$strTitle) {
                        $strTitle = __('View document for free', 'txtbear');
                    }
                    $strEmbed = '<a class="TxtBearEmbedInline" href="'.$strUrl.'" title="'.__('View document for free', 'txtbear').'">'.$strTitle.'</a>';
                    if($strAlign) {
                        $strEmbed = '<span style="display: block; text-align: '.$strAlign.'; margin: 1em 0">'.$strEmbed.'</span>';
                    }
                    break;
            }
            $strEmbed .= '<script type="text/javascript">if (!window.TxtBearEmbed) { document.write(unescape("%3Cscript src=\'http://static.txtbear.com/v/embed.js\' type=\'text/javascript\'%3E%3C/script%3E")); window.TxtBearEmbed = true; }</script>';
        }
        else {
            $strEmbed = ' ('.__('Invalid embed code parameters.', 'txtbear').') ';
        }
        // insert
        $strText = str_replace($arrMatch[0][$i], $strEmbed, $strText);
    }
    return $strText;
}

register_activation_hook(__FILE__, 'txtbear_activate');
register_deactivation_hook(__FILE__, 'txtbear_deactivate');
txtbear_upgrade();
add_action('media_buttons', 'txtbear_button', 20);
add_action('the_content', 'txtbear_insertembed');

?>