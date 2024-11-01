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

include_once(dirname(__FILE__).'/config.php');

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

require_once(ABSPATH.'wp-admin/admin.php');

load_plugin_textdomain('txtbear', WP_PLUGIN_URL.'/txtbear/languages/', 'txtbear/languages/');
load_plugin_textdomain('txtbear', '/');

// REPLACE ADMIN URL
if (function_exists('admin_url')) {
	wp_admin_css_color('classic', __('Blue'), admin_url('css/colors-classic.css'), array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), admin_url('css/colors-fresh.css'), array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
} else {
	wp_admin_css_color('classic', __('Blue'), get_bloginfo('wpurl').'/wp-admin/css/colors-classic.css', array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), get_bloginfo('wpurl').'/wp-admin/css/colors-fresh.css', array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
}

wp_enqueue_script('common');

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

if (!current_user_can('edit_posts')) {
	wp_die(__('You do not have permission to embed ebooks.', 'txtbear'));
}

if(!isset($_GET['tab'])) {
	$_GET['tab'] = 'eload24';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Embed eload24 ebook', 'txtbear'); ?> &#8212; <?php _e('WordPress'); ?></title>
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
	(function($) {
		$(function() {
			$("#ebooks-filter").submit(function() {
				$("body").animate({opacity: .8}).css("cursor", "wait");
			});
			if(!$(".insertebook").length) {
				$(".blanksearch a").click(function() {
					$($("input#post-search-input").val($(this).html()).select().focus().get(0).form).submit();
					return false;
				});
				$(".fade").hide();
				$("body").mouseover(function() {
					$(".fade").fadeIn("slow");
				});
				$("input#post-search-input").focus();
			}
			$(".insertdetails").click(function() {
				$("a.insertdetails").not($(this).slideUp()).slideDown();
				$("tr.alternate div").not($(this).closest("tr").find("div").slideDown()).slideUp();
				return false;
			});
			$(".ebooktitle").click(function() {
				$(this).closest("tr").find(".insertdetails").click();
				return false;
			});
			$("tr.alternate div").hide();
			$(".insertebook").click(function() {
				var strTitle = $(this).closest("tr").find(".ebooktitle").html();
				var intEbook = $(this).closest("tr").find("input.ebook").val();
				var strTxtbear = $(this).parent().parent().find("input.txtbear").val();
				var strAlignment = $("input:radio[name=align-" + intEbook + "]:checked").attr("id").match(/([a-z]+)$/)[1];
				if(strAlignment != "none") {
					strAlignment = " align=" + strAlignment;
				}
				else {
					strAlignment = "";
				}
				var strMode = $("input:radio[name=mode-" + intEbook + "]:checked").attr("id").match(/([a-z]+)$/)[1];
				if(strMode != "preview") {
					strMode = " mode=" + strMode;
				}
				else {
					strMode = "";
				}
				var objWin = window.dialogArguments || opener || parent || top;
				objWin.send_to_editor("[doc title=\"" + strTitle + "\" eload24=" + intEbook + " txtbear=" + strTxtbear + strAlignment + strMode + "]");
				return false;
			});
		});
	})(jQuery);
	/* ]]> */
	</script>
	<style type="text/css">
	.blanksearch {
		margin: 120px auto 0 auto;
		text-align: center;
	}
	.blanksearch #post-search-input {
		font-size: 1.3em;
		width: 60%;
	}
	.contentpadding {
		padding: 5px 10px;
	}
	p.search-box,
	.tablenav .tablenav-pages {
		float: none;
	}
	p.search-box img {
		margin: 0 2em 0 0;
		vertical-align: middle;
	}
	p.search-box #post-search-input {
		width: 30%;
	}
	h3 {
		margin: 0.2em 0 0.5em 0;
	}
	table.widefat {
		width: 100% !important;
	}
	thead th {
		text-align: left;
		vertical-align: middle;
	}
	.insertdetails {
		display: block;
	}
	td.detailcol {
		width: 260px;
	}
	td.detailcol div {
		position: relative;
		text-align: left;
	}
	td.detailcol div span {
		display: block;
		float: left;
		width: 49%;
	}
	td.detailcol div label {
		white-space: nowrap;
	}
	.insertebook {
		margin-top: 10px;
	}
	div.tablenav {
		text-align: center;
	}
	</style>
</head>
<body id="media-upload">
	<div id="media-upload-header">
		<?php include_once('menu.php'); ?>
	</div>
	<?php
	$strSearch = '';
	if(isset($_REQUEST['s']) && trim($_REQUEST['s']) != '') {
		$strSearch = get_magic_quotes_gpc() ? stripslashes($_REQUEST['s']) : $_REQUEST['s'];
	}
	if(!$strSearch) { ?>
		<div class="blanksearch">
			<p><img src="http://res.eload24.com/gx/logo.gif" width="150" height="60" /></p>
			<form id="ebooks-filter" action="search-eload24.php?post_id=<?php 
			echo $_GET['post_id']; ?>&amp;adminurl=<?php echo $_GET['adminurl']; ?>" method="post">
				<p><input type="text" name="s" id="post-search-input" /></p>
				<p><input type="submit" value="<?php _e('Search ebooks', 'txtbear'); ?>" class="button-primary" /></p>
			</form>
			<div class="fade">
				<p>&nbsp;</p>
				<p><?php printf(__('Please enter a search term, such as %sWindows 7%s or %sOffice%s.', 'txtbear'),
					'<a href="#">', '</a>', '<a href="#">', '</a>'); ?></p>
				<p>&nbsp;</p>
				<p><small>&copy; <?php echo date('Y'); ?> &ndash;
					<a href="http://www.eload24.com/page/show/datenschutz" target="_blank"><?php _e('Privacy', 'txtbear'); ?></a></small></p>
			</div>
		</div>
		<?php
	}
	else {
		?>
		<form id="ebooks-filter" action="search-eload24.php?post_id=<?php 
			echo $_GET['post_id']; ?>&amp;adminurl=<?php echo $_GET['adminurl']; ?>" method="post">
			<p class="search-box">
				<img src="http://res.eload24.com/gx/logo.gif" width="100" height="40" />
				<label class="hidden" for="post-search-input"><?php _e('Search ebooks', 'txtbear'); ?>:</label>
				<input class="search-input" id="post-search-input" name="s" value="<?php echo $_REQUEST['s']; ?>" type="text" />
				<input value="<?php _e('Search ebooks', 'txtbear'); ?>" class="button" type="submit" />
			</p>
		</form>
		<div class="clear"></div>
		<?php
			$intPage = 1;
			if(isset($_REQUEST['p'])) {
				$intPage = intval($_REQUEST['p']);
			}
			$strSort = 'title';
			if(isset($_REQUEST['sort']) && in_array($_REQUEST['sort'], array())) {
				$sort = $_REQUEST['sort'];
			}

			$arrResults = array();
			$arrReply = get('http://api.eload24.com/SearchEbooks.php?search='.rawurlencode($strSearch).'&embedonly=true&limit=50');
			if($arrReply['status'] != 200) {
				?>
				<div class="blanksearch">
					<p><?php
						_e('An error occurred while performing your search. Please try again later.', 'txtbear');
						echo ' ('.$arrReply['status'].')'; ?></p>
				</div></body></html>
				<?php
				die();
			}
			$arrResults = unserialize($arrReply['data']);

			// Figure out the limit for the query based on the current page number.
			$intFrom = $j = (($intPage * 10) - 10);
			$intResults = count($arrResults);

			// Figure out the total number of pages. Always round up using ceil()
			$intPages = ceil($intResults / 10);

			$arrResults = array_slice($arrResults, $intFrom, 10);
			$intResults = count($arrResults);
			for($i = 0; $i < $intResults; $i++) {
				$arrReply = get('http://api.eload24.com/GetEbookTxtbear.php?ebook='.$arrResults[$i]['id']);
				if($arrReply['status'] != 200) {
					?>
					<div class="blanksearch">
						<p><?php
							_e('An error occurred while performing your search. Please try again later.', 'txtbear');
							echo ' ('.$arrReply['status'].')'; ?></p>
					</div></body></html>
					<?php
					die();
				}
				$arrTxtbear = unserialize($arrReply['data']);
				if(empty($arrTxtbear)) {
					continue;
				}
				$strTxtbear = $arrTxtbear[0]['url'];
				$arrMatch = array();
				preg_match('/^http:\/\/view\.txtbear\.com\/(\w+)\//', $strTxtbear, $arrMatch);
				$arrResults[$i]['txtbear'] = $arrMatch[1];
			}

			if(empty($arrResults)) { ?>
				<div class="blanksearch">
					<p><?php _e('Your search term did not match any ebooks.', 'txtbear'); ?></p>
					<p><?php printf(__('Please enter a search term, such as %sWindows 7%s or %sOffice%s.', 'txtbear'),
							'<a href="#">', '</a>', '<a href="#">', '</a>'); ?></p>
				</div>
				<?php
			}
			else {
			?>
			<div class="contentpadding">
			<h3><?php _e('Search results', 'txtbear'); ?></h3>
			<form>
			<table class="widefat" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="num"> </th>
						<th scope="col"><?php _e('Title', 'txtbear'); ?></th>
						<th scope="col" class="num"> </th>
					</tr>
				</thead>
				<tbody id="the-list">
				<?php
				foreach ($arrResults as $d) {
					$strUrl = 'http://view.txtbear.com/'.$d['txtbear'].'/?pid='.$d['id'].'#plugins=http://res.txtbear.com.s3.amazonaws.com/plugins/eload24.js';
					$id = $d['id'];
					?>
					<tr class="alternate item-<?php echo $id; ?>">
					<td class="num"><?php echo ++$j; ?>.</td>
					<td><a class="ebooktitle" href="#"><?php echo $d['name']; ?></a>
						<div style="display: none">
							<a href="<?php echo $strUrl; ?>" target="_blank"><img src="http://d1ekua9ie1ovig.cloudfront.net/cover/<?php printf('%05d', $d['id']); ?>.jpg" width="200" height="147" alt="<?php echo $d['name']; ?>" /></a>
						</div>
					</td>
					<td class="num detailcol"><a href="#" class="button insertdetails"
						><?php _e('Insert', 'txtbear'); ?></a>
						<input type="hidden" class="ebook" value="<?php echo $id; ?>" />
						<input type="hidden" class="txtbear" value="<?php echo $d['txtbear']; ?>" />
						<div style="display: none">
							<span>
								<?php _e('Alignment', 'txtbear'); ?>:<br />
								<input type="radio" name="align-<?php echo $id; ?>" id="align-<?php echo $id; ?>-none" checked="checked" />
								<label for="align-<?php echo $id; ?>-none"><?php _e('None', 'txtbear'); ?></label><br />
								<input type="radio" name="align-<?php echo $id; ?>" id="align-<?php echo $id; ?>-left" />
								<label for="align-<?php echo $id; ?>-left"><?php _e('Left', 'txtbear'); ?></label><br />
								<input type="radio" name="align-<?php echo $id; ?>" id="align-<?php echo $id; ?>-center" />
								<label for="align-<?php echo $id; ?>-center"><?php _e('Center', 'txtbear'); ?></label><br />
								<input type="radio" name="align-<?php echo $id; ?>" id="align-<?php echo $id; ?>-right" />
								<label for="align-<?php echo $id; ?>-right"><?php _e('Right', 'txtbear'); ?></label>
							</span>
							<span>
								<?php _e('Show as', 'txtbear'); ?>:<br />
								<input type="radio" name="mode-<?php echo $id; ?>" id="mode-<?php echo $id; ?>-preview" checked="checked" />
								<label for="mode-<?php echo $id; ?>-preview"><?php _e('Title &amp; Thumbnail', 'txtbear'); ?></label><br />
								<input type="radio" name="mode-<?php echo $id; ?>" id="mode-<?php echo $id; ?>-link" />
								<label for="mode-<?php echo $id; ?>-link"><?php _e('Text-only link', 'txtbear'); ?></label>
							</span>
							<button class="button-primary insertebook"><?php _e('Insert', 'txtbear'); ?> &raquo;</button>
						</div>
					</td></tr>
				<?php } ?>
				</tbody>
			</table>
			</form>
			<script type="text/javascript">if (!window.TxtBearEmbed) { document.write(unescape("%3Cscript src='http://static.txtbear.com/v/embed.js' type='text/javascript'%3E%3C/script%3E")); window.TxtBearEmbed = true; }</script>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php
					if($intPages > 1) {
						if($intPage > 1) {
							$intPrevious = $intPage - 1;
							echo '<a href="?s='.htmlentities($strSearch).'&amp;p='.$intPrevious.'&amp;sort='.$strSort.'&amp;post_id='.
								$_GET['post_id'] .'&amp;adminurl='.$_GET['adminurl'].'">&laquo; '.__('Previous', 'txtbear').'</a> ';
						}

						for($i = 1; $i <= $intPages; $i++) {
							if($intPage == $i) {
								echo ' <span class="page-numbers current">'.$i.'</span> ';
							}
							else {
								echo ' <a href="?s='.htmlentities($strSearch).'&amp;p='.$i.'&amp;sort='.$strSort.'&amp;post_id='.
									$_GET['post_id'] .'&amp;adminurl='.$_GET['adminurl'].'">'.$i.'</a> ';
							}
						}

						if($intPage < $intPages) {
							$intNext = $intPage + 1;
							echo '<a href="?s='.htmlentities($strSearch).'&amp;p='.$intNext.'&amp;sort='.$strSort.'&amp;post_id='.
								$_GET['post_id'] .'&amp;adminurl='.$_GET['adminurl'].'">'.__('Next', 'txtbear').' &raquo;</a> ';
						}
					}
					?>
				</div>
			<br style="clear: both; margin-bottom:1px; height:2px; line-height:2px;" />
			<p><small>&copy; <?php echo date('Y'); ?> &ndash;
				<a href="http://www.eload24.com/page/show/datenschutz" target="_blank"><?php _e('Privacy', 'txtbear'); ?></a></small></p>
			</div>
			</div>
			<?php
		}
	}
	?>
</body>
</html>
