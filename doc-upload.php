<?php

/** Load WordPress Administration Bootstrap */
if(file_exists('../../../wp-load.php')) {
	require_once('../../../wp-load.php');
}
else if(file_exists('../../wp-load.php')) {
	require_once('../../wp-load.php');
}
else if(file_exists('../wp-load.php')) {
	require_once('../wp-load.php');
}
else if(file_exists('wp-load.php')) {
	require_once('wp-load.php');
}
else if(file_exists('../../../../wp-load.php')) {
	require_once('../../../../wp-load.php');
}
else if(file_exists('../../../../wp-load.php')) {
	require_once('../../../../wp-load.php');
}
else {
	if(file_exists('../../../wp-config.php')) {
		require_once('../../../wp-config.php');
	}
	else if(file_exists('../../wp-config.php')) {
		require_once('../../wp-config.php');
	}
	else if(file_exists('../wp-config.php')) {
		require_once('../wp-config.php');
	}
	else if(file_exists('wp-config.php')) {
		require_once('wp-config.php');
	}
	else if(file_exists('../../../../wp-config.php')) {
		require_once('../../../../wp-config.php');
	}
	else if(file_exists('../../../../wp-config.php')) {
		require_once('../../../../wp-config.php');
	}
	else {
		die('<p>Failed to load bootstrap.</p>');
	}
}

// include plugin configuration and helper functions
include_once(dirname(__FILE__).'/config.php');

// make sure everything we need is defined
global $wp_db_version;
if($wp_db_version < 8201) {
	// Pre 2.6 compatibility (By Stephen Rider)
	if(!defined('WP_CONTENT_URL')) {
		if(defined('WP_SITEURL')) {
			define('WP_CONTENT_URL', WP_SITEURL . '/wp-content');
		}
		else {
			define('WP_CONTENT_URL', get_option('url') . '/wp-content');
		}
	}
	if(!defined('WP_CONTENT_DIR')) {
		define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
	}
	if(!defined('WP_PLUGIN_URL')) {
		define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
	}
	if(!defined('WP_PLUGIN_DIR')) {
		define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
	}
}

// load admin bootstrap
require_once(ABSPATH.'wp-admin/admin.php');

// load localizations
load_plugin_textdomain('txtbear', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/languages/', basename(dirname(__FILE__)) . '/languages/');
load_plugin_textdomain('txtbear', '/');

// REPLACE ADMIN URL
if (function_exists('admin_url')) {
	wp_admin_css_color('classic', __('Blue'), admin_url('css/colors-classic.css'), array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), admin_url('css/colors-fresh.css'), array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
} else {
	wp_admin_css_color('classic', __('Blue'), get_bloginfo('wpurl').'/wp-admin/css/colors-classic.css', array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), get_bloginfo('wpurl').'/wp-admin/css/colors-fresh.css', array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
}

// we need Flash uploader here
wp_enqueue_script('common');
wp_enqueue_script('swfupload-all');

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

if (!current_user_can('edit_posts')) {
	wp_die(__('You do not have permission to embed ebooks.', 'txtbear'));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Upload document'); ?> &#8212; <?php _e('WordPress'); ?></title>
	<?php
		wp_enqueue_style('global');
		wp_enqueue_style('wp-admin');
		wp_enqueue_style('colors');
		wp_enqueue_style('media');
	?>
	<script type="text/javascript">
	//<![CDATA[
	function addLoadEvent(func) {if ( typeof wpOnload!='function'){wpOnload=func;}else{ var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}}
	//]]>
	</script>
	<?php
	do_action('admin_print_styles');
	
	// themes or plugins might hook this
	// do_action('admin_print_scripts');
	echo '<script type="text/javascript" '.
		'src="'.$_GET['adminurl'].'/load-scripts.php?c=1&amp;load=jquery,swfupload-all&amp;ver='.md5(time()).'"></script>';
	
	do_action('admin_head');
	if(is_string($content_func))
		do_action("admin_head_{$content_func}");
	?>
	<script type="text/javascript">
	/* <![CDATA[ */
	var tbApiUrl = "http://api.txtbear.com/",
		updateInterval,
		bid = 0,
		uid = '',
		auth = '',
		title = '',
		uploadFilesize = 0,
		bookUrl = '';
	(function($) {
		$(function() {
			$("#url")
				.attr("oval", $("#url").val()) // store original text
				.focus(function() {
					if($(this).val() == $(this).attr("oval")) {
						$(this).val("http://").removeClass("blank").select();
					}
				})
				.blur(function() {
					if(!$(this).val() || $(this).val() == "http://") {
						$(this).addClass("blank").val($(this).attr("oval"));
					}
				});
			function showProgress(fnCallback) {
				$(".step1").animate({height: 0});
				$(".step1b").hide().removeClass("hidden").slideDown(fnCallback);
			}
			function setProgress(intPercent, strMessage) {
				if(intPercent) {
					$(".progress div").width(intPercent + "%");
				}
				$(".step1b h3").html(strMessage);
			}
			$(".step1 form").submit(function() { // TxtBear from remote URL
				if(!$("#url").val().match(/^https?:\/\/.+/)) {
					$("#url").select().focus();
					return false;
				}
				showProgress(function() {callTxtbear($("#url").val())});
				return false;
			});
			function callTxtbear(strUrl) {
				setProgress(90, "<?php _e('Connecting...', 'txtbear'); ?>");
				$.getJSON(tbApiUrl + "book/create.json?url=" + strUrl + "&callback=?", function(data) {
					bid = data.bid;
					bookUrl = data.url;
					auth = data.auth;
					title = data.bid + "." + data.ext; // we need something more intelligent there soon
					$("#url").val(bookUrl);
					setProgress(95, "<?php _e('Creating viewer...', 'txtbear'); ?>");
					updateInterval = setInterval(updateStatus, 2000);
				});
			}
			function updateStatus() {
				$.getJSON(tbApiUrl + "book/status.json?bid=" + bid + "&callback=?", function (data) {
					switch (data.phase) {
						case "unknown":
							break;
						case "binary":
						case "process":
							if(data.message) {
								setProgress(null, data.message);
							}
							break;
						case "done":
							clearInterval(updateInterval);
							setProgress(100, "<?php _e('Successfully created.', 'txtbear'); ?>");
							bookUrl = data.url;
							showEmbedOptions();
							break;
						case "error":
							clearInterval(updateInterval);
							$(".progress").hide();
							$(".step1b h3").html(data.message).addClass("error");
							break;
					}
				});
			}
			function showEmbedOptions() {
				var intLastSlash = bookUrl.substr(0, bookUrl.length - 1).lastIndexOf("/");
				// split long URLs
				$(".step2 .url a").attr("href", bookUrl).html(
					bookUrl.substr(0, intLastSlash) 
					+ " &nbsp;&nbsp;<img src='data:image/gif;base64,R0lGODlhCQAHAIAAACuRr//"
					+ "//yH5BAEAAAEALAAAAAAJAAcAAAIOhG+hu8CuYEgxIjnxYwUAOw==' width='' height='' /> <br />" 
					+ bookUrl.substr(intLastSlash));
				$(".step1b").slideUp();
				$(".step2").hide().removeClass("hidden").slideDown();
				if($(".step2 .url a").width() > $(".step2 div").width() - $(".step2 strong").width() - 5) {
					$(".step2 .url").width($(".step2 div").width() - $(".step2 strong").width() - 5);
					$("<em></em>").text("...").appendTo($(".step2 .url"));
				}
				else {
					$(".step2 .url").width($(".step2 .url a").width());
				}
				$(".step2 div").width($(".step2 .url").width() + $(".step2 strong").width());
			}
			$(".insert").click(function() {
				var strAlignment = $("input:radio[name=align]:checked").attr("id").match(/([a-z]+)$/)[1];
				if(strAlignment != "none") {
					strAlignment = " align=" + strAlignment;
				}
				else {
					strAlignment = "";
				}
				var strMode = $("input:radio[name=mode]:checked").attr("id").match(/([a-z]+)$/)[1];
				if(strMode != "preview") {
					strMode = " mode=" + strMode;
				}
				else {
					strMode = "";
				}
				var objWin = window.dialogArguments || opener || parent || top;
				// we dont need the auth param at the moment, but we might later on, so send it to the post
				objWin.send_to_editor("[doc title=\"" + title + "\" txtbear=" + bid + strAlignment + strMode + " auth=" + auth + "]");
				return false;
			});
			var swfu;
			var objUpload = null;
			SWFUpload.onload = function () {
				objUpload = new SWFUpload({
					flash_url : "<?php echo includes_url('js/swfupload/swfupload.swf'); ?>",
					upload_url: "<?php echo esc_attr(admin_url('async-upload.php')); ?>",
					file_size_limit : "<?php echo wp_max_upload_size(); ?>b",
					file_post_name: "async-upload",
					post_params : {
						"post_id" : "<?php echo $_GET['post_id']; ?>",
						"auth_cookie" : "<?php if ( is_ssl() ) echo $_COOKIE[SECURE_AUTH_COOKIE]; else echo $_COOKIE[AUTH_COOKIE]; ?>",
						"logged_in_cookie": "<?php echo $_COOKIE[LOGGED_IN_COOKIE]; ?>",
						"_wpnonce" : "<?php echo wp_create_nonce('media-form'); ?>",
						"type" : "<?php echo $type; ?>",
						"tab" : "<?php echo $tab; ?>",
						"short" : "1"
					},
					file_types : "*.pdf;*.html;*.odt;*.docx;*.doc;*.odp;*.xlsx;*.xls;*.ods;*.txt;*.rtf;*.pptx;*.ppt",
					file_types_description : "All documents",
					debug: false,
					button_placeholder_id : "browseflash",
					button_image_url : "swfupload/button.gif",
					button_width : "100",
					button_height : "30",
					button_action : SWFUpload.BUTTON_ACTION.SELECT_FILE,
					button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
					file_queued_handler : clsUpload.FileQueued,
					upload_start_handler : clsUpload.UploadStart,
					upload_progress_handler : clsUpload.UploadProgress,
					upload_error_handler : clsUpload.UploadError,
					upload_success_handler : clsUpload.UploadSuccess,
					swfupload_loaded_handler: clsUpload.Loaded,
					minimum_flash_version : "9.0.28",
					swfupload_load_failed_handler: clsUpload.LoadFailed
				});
			};
			var clsUpload = {
				FileQueued: function(objFile) {
					showProgress(function(){objUpload.startUpload()}); // auto-upload after browsed
				},
				UploadStart: function(objFile) {
				},
				UploadProgress: function(objFile, intBytesSent, intBytesTotal) {
					setProgress(85 * intBytesSent / intBytesTotal,
						"<?php _e('Uploading %x of %y...', 'txtbear'); ?>"
							.replace("%x", humanSize(intBytesSent)).replace("%y", humanSize(intBytesTotal)));
				},
				UploadError: function(objFile, intError, strError) {
					$(".progress").hide();
					$(".step1b h3").html(strError).addClass("error");
				},
				UploadSuccess: function(objFile, a, boolReceivedResponse) {
					$.get("<?php echo esc_attr(admin_url('async-upload.php')); ?>",
						{attachment_id: a, fetch: 1},
						function(a) {
							var strUrl = a.match(/\[url\]\' value\=\'(.+)\'/)[1];
							callTxtbear(strUrl);
						}
					);
				},
				Loaded : function() {
					clsUpload.IsFlash = true;
				},
				LoadFailed : function() {
					alert("<?php _e('Something might be wrong with your Flash Player.', 'txtbear'); ?>");
				},
				IsFlash : false
			};

			function humanSize(intBytes) {
				if(intBytes < 1024)
					return intBytes + " <?php _e('bytes', 'txtbear'); ?>";
				else if(intBytes < 1024^2)
					return (Math.floor(intBytes / 1024)) + " <?php _e('kB', 'txtbear'); ?>";
				else if(intBytes < 1024^3)
					return (Math.floor(intBytes / 1024^2 * 10) / 10) + " <?php _e('MB', 'txtbear'); ?>";
				else if(intBytes < 1024^4)
					return (Math.floor(intBytes / 1024^3 * 100) / 100) + " <?php _e('GB', 'txtbear'); ?>";
			}
		});
	})(jQuery);
	/* ]]> */
	</script>
	<style type="text/css">
	.wrap {
		margin: 100px auto 0 auto;
		text-align: center;
	}
	h3 {
		margin: 0 2em;
		padding: 1em 0 1em 0;
	}
	.step1 {
		overflow: hidden;
	}
	p.url {
		left: -50px;
		margin: 0 auto;
		position: relative;
		width: 45em;
	}
	.blank#url {
		font-style: italic;
	}
	.browse {
		position: absolute;
		top: 1px;
		width: 100px;
	}
	.browse input {
		width: 84px;
	}
	.browse object {
		left: 0;
		margin: 0;
		position: absolute;
		top: 0;
		width: 100%;
	}
	#submit {
		width: 100px;
	}
	.progressspin {
		background: url(<?php echo admin_url('images/wpspin_light.gif'); ?>) no-repeat right center;
		height: 16px;
		margin: 0 auto;
		width: 225px;
	}
	.progress {
		background: #fff;
		border: 1px solid #999;
		border-radius: 3px;
		-moz-border-radius: 3px;
		height: 16px;
		position: relative;
		width: 200px;
	}
	.progress div {
		background: #ccc;
		height: 100%;
		left: 0;
		position: absolute;
		top: 0;
	}
	.step2 form div {
		margin: 0 auto;
		position: relative;
	}
	.step2 form div strong {
		float: left;
		text-align: left;
		width: 10em;
	}
	.step2 form div span {
		display: block;
		margin-left: 10em;
		text-align: left;
		white-space: nowrap;
		position: relative;
	}
	.step2 form div span em {
		font-style: normal;
		display: block;
		position: absolute;
		top: 0;
		right: 0;
		background: #fff;
		padding-left: .3em;
	}
	h3.error {
		margin: 0 2em;
		padding: 1em 0 1em 0;
	}
	</style>
</head>
<body id="media-upload">
	<div id="media-upload-header">
		<?php include_once('menu.php'); ?>
	</div>

	<div class="wrap">
		<p><img src="http://res.txtbear.com.s3.amazonaws.com/images/tb_logo_small.png" width="206" height="60" /></p>
		<div class="step1">
			<h3 class="media-title"><?php _e('Step 1: Pick a document to embed', 'txtbear'); ?></h3>
			<form>
				<div>
					<p class="url">
						<input type="text" size="40" id="url" class="blank" value="<?php _e('Enter URL or click Browse to upload a file', 'txtbear'); ?>" />
						<span class="browse">
							<input type="button" class="button-secondary" value="<?php _e('Browse...', 'txtbear'); ?>" />
							<span id="browseflash"></span>
						</span>
					</p>
					<p><input id="submit" type="submit" class="button-primary" value="<?php _e('Upload', 'txtbear'); ?>" /></p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p><small>&copy; <?php echo date('Y'); ?> &ndash;
						<a href="http://www.txtbear.com/comingsoon.html" target="_blank"><?php _e('Privacy', 'txtbear'); ?></a></small></p>
				</div>
			</form>
		</div>
		<div class="step1b hidden">
			<h3 class="media-title">&nbsp;</h3>
			<div class="progressspin">
				<div class="progress">
					<div></div>
				</div>
			</div>
		</div>
		<div class="step2 hidden">
			<h3 class="media-title"><?php _e('Step 2: Embed your document', 'txtbear'); ?></h3>
			<form>
				<div>
					<strong><?php _e('TxtBear URL', 'txtbear'); ?>:</strong>
						<span class="url"><a href="#"></a></span>
					<strong><?php _e('Alignment', 'txtbear'); ?>:</strong>
						<span>
						<input type="radio" name="align" id="align-none" checked="checked" />
						<label for="align-none"><?php _e('None', 'txtbear'); ?> (<?php _e('default', 'txtbear'); ?>)</label><br />
						<input type="radio" name="align" id="align-left" />
						<label for="align-left"><?php _e('Left', 'txtbear'); ?></label><br />
						<input type="radio" name="align" id="align-center" />
						<label for="align-center"><?php _e('Center', 'txtbear'); ?></label><br />
						<input type="radio" name="align" id="align-right" />
						<label for="align-right"><?php _e('Right', 'txtbear'); ?></label>
						</span>
					<strong><?php _e('Show as', 'txtbear'); ?>:</strong>
						<span>
						<input type="radio" name="mode" id="mode-preview" checked="checked" />
						<label for="mode-preview"><?php _e('Title &amp; Thumbnail', 'txtbear'); ?> (<?php _e('default', 'txtbear'); ?>)</label><br />
						<input type="radio" name="mode" id="mode-link" />
						<label for="mode-link"><?php _e('Text-only link', 'txtbear'); ?></label><br />
						<br />
						<input type="button" class="button-primary insert" value="<?php _e('Insert', 'txtbear'); ?> &raquo;" />
						</span>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
