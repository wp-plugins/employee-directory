<?php
/**
 * Enqueue Scripts Functions
 *
 * @package EMPD_COM
 * @version 1.0.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('admin_enqueue_scripts', 'empd_com_load_admin_enq');
/**
 * Enqueue style and js for each admin entity pages and settings
 *
 * @since WPAS 4.0
 * @param string $hook
 *
 */
function empd_com_load_admin_enq($hook) {
	global $typenow;
	$dir_url = EMPD_COM_PLUGIN_URL;
	if ($hook == 'edit-tags.php') {
		return;
	}
	if ($hook == 'toplevel_page_empd_com' || $hook == 'edirectory_page_empd_com_notify' || $hook == 'edirectory_page_empd_com_settings') {
		wp_enqueue_script('accordion');
		return;
	} else if (in_array($hook, Array(
		'edirectory_page_empd_com_store',
		'edirectory_page_empd_com_designs',
		'edirectory_page_empd_com_support'
	))) {
		wp_enqueue_style('admin-tabs', $dir_url . 'assets/css/admin-store.css');
		return;
	}
	if (in_array($typenow, Array(
		'emd_employee'
	))) {
		$theme_changer_enq = 1;
		$datetime_enq = 0;
		$date_enq = 0;
		$sing_enq = 0;
		$tab_enq = 0;
		if ($hook == 'post.php' || $hook == 'post-new.php') {
			$unique_vars['msg'] = __('Please enter a unique value.', 'empd-com');
			$unique_vars['reqtxt'] = __('required', 'empd-com');
			$unique_vars['app_name'] = 'empd_com';
			$ent_list = get_option('empd_com_ent_list');
			if (!empty($ent_list[$typenow])) {
				$unique_vars['keys'] = $ent_list[$typenow]['unique_keys'];
				if (!empty($ent_list[$typenow]['req_blt'])) {
					$unique_vars['req_blt_tax'] = $ent_list[$typenow]['req_blt'];
				}
			}
			$tax_list = get_option('empd_com_tax_list');
			if (!empty($tax_list[$typenow])) {
				foreach ($tax_list[$typenow] as $txn_name => $txn_val) {
					if ($txn_val['required'] == 1) {
						$unique_vars['req_blt_tax'][$txn_name] = Array(
							'hier' => $txn_val['hier'],
							'type' => $txn_val['type'],
							'label' => $txn_val['label'] . ' ' . __('Taxonomy', 'empd-com')
						);
					}
				}
			}
			wp_enqueue_script('unique_validate-js', $dir_url . 'assets/js/unique_validate.js', array(
				'jquery',
				'jquery-validate'
			) , EMPD_COM_VERSION, true);
			wp_localize_script("unique_validate-js", 'unique_vars', $unique_vars);
		}
		switch ($typenow) {
			case 'emd_employee':
				$date_enq = 1;
				$sing_enq = 1;
			break;
		}
		if ($datetime_enq == 1) {
			wp_enqueue_script("jquery-ui-timepicker", $dir_url . 'assets/ext/emd-meta-box/js/jqueryui/jquery-ui-timepicker-addon.js', array(
				'jquery-ui-datepicker',
				'jquery-ui-slider'
			) , EMPD_COM_VERSION, true);
			$tab_enq = 1;
		} elseif ($date_enq == 1) {
			wp_enqueue_script("jquery-ui-datepicker");
			$tab_enq = 1;
		}
		if ($sing_enq == 1) {
			wp_enqueue_script('radiotax', EMPD_COM_PLUGIN_URL . 'includes/admin/singletax/singletax.js', array(
				'jquery'
			) , EMPD_COM_VERSION, true);
		}
		if ($tab_enq == 1) {
			wp_enqueue_style('jq-css', EMPD_COM_PLUGIN_URL . 'assets/css/smoothness-jquery-ui.css');
		}
	}
}
add_action('wp_enqueue_scripts', 'empd_com_frontend_scripts');
/**
 * Enqueue style and js for each frontend entity pages and components
 *
 * @since WPAS 4.0
 *
 */
function empd_com_frontend_scripts() {
	$dir_url = EMPD_COM_PLUGIN_URL;
	$grid_vars = Array();
	$local_vars['ajax_url'] = admin_url('admin-ajax.php');
	$local_vars['validate_msg']['required'] = __('This field is required.', 'emd-plugins');
	$local_vars['validate_msg']['remote'] = __('Please fix this field.', 'emd-plugins');
	$local_vars['validate_msg']['email'] = __('Please enter a valid email address.', 'emd-plugins');
	$local_vars['validate_msg']['url'] = __('Please enter a valid URL.', 'emd-plugins');
	$local_vars['validate_msg']['date'] = __('Please enter a valid date.', 'emd-plugins');
	$local_vars['validate_msg']['dateISO'] = __('Please enter a valid date ( ISO )', 'emd-plugins');
	$local_vars['validate_msg']['number'] = __('Please enter a valid number.', 'emd-plugins');
	$local_vars['validate_msg']['digits'] = __('Please enter only digits.', 'emd-plugins');
	$local_vars['validate_msg']['creditcard'] = __('Please enter a valid credit card number.', 'emd-plugins');
	$local_vars['validate_msg']['equalTo'] = __('Please enter the same value again.', 'emd-plugins');
	$local_vars['validate_msg']['maxlength'] = __('Please enter no more than {0} characters.', 'emd-plugins');
	$local_vars['validate_msg']['minlength'] = __('Please enter at least {0} characters.', 'emd-plugins');
	$local_vars['validate_msg']['rangelength'] = __('Please enter a value between {0} and {1} characters long.', 'emd-plugins');
	$local_vars['validate_msg']['range'] = __('Please enter a value between {0} and {1}.', 'emd-plugins');
	$local_vars['validate_msg']['max'] = __('Please enter a value less than or equal to {0}.', 'emd-plugins');
	$local_vars['validate_msg']['min'] = __('Please enter a value greater than or equal to {0}.', 'emd-plugins');
	$local_vars['unique_msg'] = __('Please enter a unique value.', 'emd-plugins');
	$wpas_shc_list = get_option('empd_com_shc_list');
	wp_register_style('allview-css', $dir_url . '/assets/css/allview.css');
	$local_vars['search_employees'] = emd_get_form_req_hide_vars('empd_com', 'search_employees');
	wp_register_style('search-employees-forms', $dir_url . 'assets/css/search-employees-forms.css');
	wp_register_script('search-employees-forms-js', $dir_url . 'assets/js/search-employees-forms.js');
	wp_localize_script('search-employees-forms-js', 'search_employees_vars', $local_vars);
	wp_register_style("empd-com-default-single-css", EMPD_COM_PLUGIN_URL . 'assets/css/empd-com-default-single.css');
	wp_register_script('wpas-jvalidate-js', $dir_url . 'assets/ext/jvalidate1111/wpas.validate.min.js', array(
		'jquery'
	));
	wp_register_style('wpasui', EMPD_COM_PLUGIN_URL . 'assets/ext/wpas-jui/wpas-jui.min.css');
	wp_register_style('recent-employees-css', EMPD_COM_PLUGIN_URL . 'assets/css/recent-employees.css');
	wp_register_style('featured-employees-css', EMPD_COM_PLUGIN_URL . 'assets/css/featured-employees.css');
	wp_register_style('empd-com-allview-css', $dir_url . '/assets/css/allview.css');
	if (is_single() && get_post_type() == 'emd_employee') {
		wp_enqueue_style("empd-com-default-single-css");
	}
}
/**
 * Enqueue if allview css is not enqueued
 *
 * @since WPAS 4.5
 *
 */
function empd_com_enq_allview() {
	if (!wp_style_is('empd-com-allview-css', 'enqueued')) {
		wp_enqueue_style('empd-com-allview-css');
	}
}
