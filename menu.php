<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<ul id="sidemenu">
<?php

$arrMenu = array(
	'doc-upload' => __('Upload document', 'txtbear'),
	'search-eload24' => __('eload24.com', 'txtbear')
);

if(!txtbear_admin_is_german()) {
	unset($arrMenu['search-eload24']);
}

foreach($arrMenu as $strName => $strLabel) { ?>
	<li id="tab-<?php echo $strName; ?>"><a href="<?php echo $strName; ?>.php?post_id=<?php 
		echo $_GET['post_id']; ?>&amp;adminurl=<?php echo $_GET['adminurl']; ?>"
		<?php
		if(basename($_SERVER['PHP_SELF']) == $strName . '.php') {
			echo 'class="current"';
		}
		?>><?php echo $strLabel; ?></a></li>
	<?php
} ?>
</ul>
