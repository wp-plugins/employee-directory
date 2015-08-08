<?php
/**
 *  Uninstall Employee Directory
 *
 * Uninstalling deletes notifications and terms initializations
 *
 * @package EMPD_COM
 * @version 1.0.0
 * @since WPAS 4.0
 */
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
if (!current_user_can('activate_plugins')) return;
function empd_com_uninstall() {
	//delete options
	$options_to_delete = Array(
		'empd_com_notify_list',
		'empd_com_ent_list',
		'empd_com_attr_list',
		'empd_com_shc_list',
		'empd_com_tax_list',
		'empd_com_rel_list',
		'empd_com_license_key',
		'empd_com_license_status',
		'empd_com_comment_list',
		'empd_com_access_views',
		'empd_com_limitby_auth_caps',
		'empd_com_limitby_caps',
		'empd_com_has_limitby_cap',
		'empd_com_setup_pages',
		'empd_com_emd_employee_terms_init'
	);
	if (!empty($options_to_delete)) {
		foreach ($options_to_delete as $option) {
			delete_option($option);
		}
	}
	$emd_activated_plugins = get_option('emd_activated_plugins');
	if (!empty($emd_activated_plugins)) {
		$emd_activated_plugins = array_diff($emd_activated_plugins, Array(
			'empd-com'
		));
		update_option('emd_activated_plugins', $emd_activated_plugins);
	}
}
if (is_multisite()) {
	global $wpdb;
	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	if ($blogs) {
		foreach ($blogs as $blog) {
			switch_to_blog($blog['blog_id']);
			empd_com_uninstall();
		}
		restore_current_blog();
	}
} else {
	empd_com_uninstall();
}
