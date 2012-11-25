<?php 
/*
Plugin Name: Custom Field Snippet
Plugin URI: http://wp.php-web.net/?p=275
Description: This plugin creates and shows the snippets which display your custom field data. You can display your custom field data, by pasting these codes to your theme.
Version: 2.1
Author: Fumito MIZUNO
Author URI: http://wp.php-web.net/
License: GPL ver.2 or later
 */

DEFINE('KEYURL','http://wp.php-web.net/?p=275');

load_plugin_textdomain('custom-field-snippet', false, dirname(plugin_basename(__FILE__)).'/lang' );
$GLOBALS['cfs_tabs'] = array();

include('inc/class.php');
/*
	// You can add new tabs, by declaring this.
	// CLASS must be a subclass of Tabdata.
	if (function_exists('register_cfs_tabs')) {
	register_cfs_tabs('CLASS NAME HERE');
	}	
 */
function register_cfs_tabs($class)
{
	if (is_subclass_of($class,'Tabdata')) {
		$GLOBALS['cfs_tabs'][] = $class;
	}
}


function cfs_meta_box($post) {
?>
<div id="customfieldsnippet">
<?php
	if ('post' == get_post_type() || 'page' == get_post_type() || check_post_type_license() == -1 || check_post_type_license() >= date("Y")) {

		if (class_exists('Acf')) {
			register_cfs_tabs('Acftab');
			register_cfs_tabs('Acfshortcode');
		} 
		register_cfs_tabs('Defaulttab');	    
	} else {
		print '<p>';
		_e('Your License key is not active. ','custom-field-snippet');
		_e('If you want to use Custom Field Snippet in any post type, you need a license key. ','custom-field-snippet');
		printf(__('You can buy the key <a href="%s">here</a>','custom-field-snippet'),KEYURL);
		print '</p>';
	}
?>
	<script>
	jQuery("document").ready(function() {
		jQuery( "#tabs" ).tabs();
	});
	</script>
<?php
	if ('post' == get_post_type() || 'page' == get_post_type() || check_post_type_license() == -1 || check_post_type_license() >= date("Y")) {
?>
<div id="tabs">
	<ul>
<?php 
		// you can modify the output array of register_cfs_tabs.
		$cfs_tabs_class = apply_filters('cfs_tabs_class',$GLOBALS['cfs_tabs']);
		foreach($cfs_tabs_class as $class) {
			$obj = new $class();
			$cfs_tabs_obj[] = $obj;
		}
		foreach($cfs_tabs_obj as $obj) {
			print '    <li><a href="#tabs-'. esc_attr($obj->getname()) .'" class="nav-tab" style="float:left;">' . esc_html($obj->getlabel()) .'</a></li>';
			print '    </li>';
		} 
?>
	</ul>
<?php 
		foreach($cfs_tabs_obj as $obj) {
			print '    <div id="tabs-'. esc_attr($obj->getname()) .'">';
			print PHP_EOL;
			$tab_format = '    <textarea readonly style="min-height:200px;width:100%%;">%s</textarea>';
			$data = esc_html($obj->getdata());
			printf(apply_filters('cfs_tab_format',$tab_format),$data);
			print "<hr>";
			print PHP_EOL;
			_e('Please save the post before you paste these codes.','custom-field-snippet');
			print '    </div>';
		} 
?>
</div>
<?php
	}
?>

</div>
<?php
}

function check_post_type_license() {
	$key = array(
		'4824da174b4306f3998c05bce117f13c' => -1,
		'3c38cc649994368a380cc90aa8ae02af' => 2013,
		'bb155e7aade1f8746e04e0e3d3f2cc3a' => 2014,
		'5677c7727980f0a96201d74b05d16ec8' => 2017,
	);
	if ($license = get_option('cfs_license')) {
		$keynum = md5($license);
		if (array_key_exists($keynum,$key)) {
			return $key[$keynum];
		}
	}
	return false;
}
function cfs_custom_box() {
	$post_types = get_post_types( array( 'public' => true ) );
	foreach ($post_types as $post_type) {
		if (post_type_supports($post_type,'custom-fields')) {
			add_meta_box('customfieldsnippet', __('Custom Field Snippet'), 'cfs_meta_box', $post_type, 'normal', 'default'); 
		}
	}
}

add_action( 'add_meta_boxes', 'cfs_custom_box' );

function cfs_plugin_admin_page() {
	add_options_page( 'CFS Options', __('Custom Field Snippet','custom-field-snippet'), 'manage_options', 'custom-field-snippet', 'cfs_plugin_options' );
}
add_action( 'admin_menu', 'cfs_plugin_admin_page' );
function cfs_settings_api_init() {
	add_settings_section('cfs_setting_section',
		__('License for any Post Type','custom-field-snippet'),
		'cfs_setting_section_callback_function',
		'custom-field-snippet');

	add_settings_field('cfs_license',
		__('License Key','custom-field-snippet'),
		'cfs_setting_callback_function',
		'custom-field-snippet',
		'cfs_setting_section');

	register_setting('cfs-group','cfs_license', 'wp_filter_nohtml_kses');
}
add_action('admin_init', 'cfs_settings_api_init');

function cfs_setting_section_callback_function() {
	echo '<p>'. __('You can buy a key and enjoy custom field snippet on any Post Type','custom-field-snippet') . '</p>';
}

function cfs_setting_callback_function() {
	echo '<input name="cfs_license" id="cfs_license" type="text" value="'. esc_attr(get_option('cfs_license')) .'" class="code" />';
}

function cfs_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
?>
	<div class="wrap">
		<form action="options.php" method="post">
<?php settings_fields('cfs-group'); ?>
<?php do_settings_sections('custom-field-snippet'); ?>
<input name="Submit" type="submit" id="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
<p>
<?php
	if (check_post_type_license() == -1) {
		_e('Your License key is active. ','custom-field-snippet');
	} else if (check_post_type_license() >= date("Y")) {
		_e('Your License key is active. ','custom-field-snippet');
		printf(__('Valid until December 31st,  %d.','custom-field-snippet'),check_post_type_license());
	} else if (check_post_type_license()) {
		_e('Your License key is expired. ','custom-field-snippet');
		printf(__('You can buy the key <a href="%s">here</a>','custom-field-snippet'),KEYURL);
	} else {
		_e('Your License key is not active. ','custom-field-snippet');
		printf(__('You can buy the key <a href="%s">here</a>','custom-field-snippet'),KEYURL);
	}
?>
</p>
<?php
}
