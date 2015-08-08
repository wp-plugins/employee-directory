<?php
/**
 * Setup and Process submit and search forms
 * @package EMPD_COM
 * @version 1.0.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (is_admin()) {
	add_action('wp_ajax_nopriv_emd_check_unique', 'emd_check_unique');
}
if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
	add_filter('posts_where', 'emd_builtin_posts_where', 10, 2);
}
add_action('init', 'empd_com_form_shortcodes', -2);
/**
 * Start session and setup upload idr and current user id
 * @since WPAS 4.0
 *
 */
function empd_com_form_shortcodes() {
	global $file_upload_dir;
	$upload_dir = wp_upload_dir();
	$file_upload_dir = $upload_dir['basedir'];
	if (!session_id() && !headers_sent()) {
		session_start();
	}
}
add_shortcode('search_employees', 'empd_com_process_search_employees');
/**
 * Set each form field(attr,tax and rels) and render form
 *
 * @since WPAS 4.0
 *
 * @return object $form
 */
function empd_com_set_search_employees() {
	global $file_upload_dir;
	$show_captcha = 0;
	$form_variables = get_option('empd_com_glob_forms_list');
	if (!empty($form_variables['search_employees']['captcha'])) {
		switch ($form_variables['search_employees']['captcha']) {
			case 'never-show':
				$show_captcha = 0;
			break;
			case 'show-always':
				$show_captcha = 1;
			break;
			case 'show-to-visitors':
				if (is_user_logged_in()) {
					$show_captcha = 0;
				} else {
					$show_captcha = 1;
				}
			break;
		}
	}
	$req_hide_vars = emd_get_form_req_hide_vars('empd_com', 'search_employees');
	require_once EMPD_COM_PLUGIN_DIR . '/assets/ext/zebraform/Zebra_Form.php';
	$form = new Zebra_Form('search_employees', 0, 'POST', '', array(
		'class' => 'form-container wpas-form wpas-form-stacked'
	));
	$form->form_properties['csrf_storage_method'] = false;
	if (!in_array('emd_employee_number', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_employee_number', 'emd_employee_number', __('Employee No', 'empd-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('text', 'emd_employee_number', '', array(
			'class' => 'input-md form-control',
			'placeholder' => __('Employee No', 'empd-com')
		));
		$zrule = Array(
			'dependencies' => array() ,
			'alphanumeric' => array(
				'_',
				'error',
				__('Employee No: Letters, numbers, and underscores only please')
			) ,
		);
		if (in_array('emd_employee_number', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Employee No is required', 'empd-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('blt_title', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_blt_title', 'blt_title', __('Full Name', 'empd-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('text', 'blt_title', '', array(
			'class' => 'input-md form-control',
			'placeholder' => __('Full Name', 'empd-com')
		));
		$zrule = Array();
		if (in_array('blt_title', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Full Name is required', 'empd-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_employee_email', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_employee_email', 'emd_employee_email', __('Email', 'empd-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('text', 'emd_employee_email', '', array(
			'class' => 'input-md form-control',
			'placeholder' => __('Email', 'empd-com')
		));
		$zrule = Array(
			'dependencies' => array() ,
			'email' => array(
				'error',
				__('Email: Please enter a valid email address', 'empd-com')
			) ,
		);
		if (in_array('emd_employee_email', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Email is required', 'empd-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('jobtitles', $req_hide_vars['hide'])) {
		$form->add('label', 'label_jobtitles', 'jobtitles', __('Job Title', 'empd-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('selectadv', 'jobtitles[]', __('Please Select', 'empd-com') , array(
			'multiple' => 'multiple',
			'class' => 'input-md'
		) , '', '{"allowClear":true,"placeholder":"' . __("Please Select", "empd-com") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_obj = get_terms('jobtitles', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('jobtitles', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Job Title is required!', 'empd-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('departments', $req_hide_vars['hide'])) {
		$form->add('label', 'label_departments', 'departments', __('Department', 'empd-com') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('selectadv', 'departments[]', __('Please Select', 'empd-com') , array(
			'multiple' => 'multiple',
			'class' => 'input-md'
		) , '', '{"allowClear":true,"placeholder":"' . __("Please Select", "empd-com") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_obj = get_terms('departments', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('departments', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Department is required!', 'empd-com')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	$form->assign('show_captcha', $show_captcha);
	if ($show_captcha == 1) {
		//Captcha
		$form->add('captcha', 'captcha_image', 'captcha_code', '', '<span style="font-weight:bold;" class="refresh-txt">Refresh</span>', 'refcapt');
		$form->add('label', 'label_captcha_code', 'captcha_code', __('Please enter the characters with black color.', 'empd-com'));
		$obj = $form->add('text', 'captcha_code', '', array(
			'placeholder' => __('Code', 'empd-com')
		));
		$obj->set_rule(array(
			'required' => array(
				'error',
				__('Captcha is required', 'empd-com')
			) ,
			'captcha' => array(
				'error',
				__('Characters from captcha image entered incorrectly!', 'empd-com')
			)
		));
	}
	$form->add('submit', 'singlebutton_search_employees', '' . __('Search', 'empd-com') . ' ', array(
		'class' => 'wpas-button wpas-juibutton-secondary wpas-button-large btn-block col-md-12 col-lg-12 col-xs-12 col-sm-12'
	));
	return $form;
}
/**
 * Process each form and show error or success
 *
 * @since WPAS 4.0
 *
 * @return html
 */
function empd_com_process_search_employees() {
	$show_form = 1;
	$access_views = get_option('empd_com_access_views', Array());
	if (!current_user_can('view_search_employees') && !empty($access_views['forms']) && in_array('search_employees', $access_views['forms'])) {
		$show_form = 0;
	}
	if ($show_form == 1) {
		wp_enqueue_style('wpasui');
		wp_enqueue_script('jquery');
		wp_enqueue_script('wpas-jvalidate-js');
		wp_enqueue_style('search-employees-forms');
		wp_enqueue_script('search-employees-forms-js');
		$noresult_msg = __('No results found.', 'empd-com');
		return emd_search_php_form('search_employees', 'empd_com', 'emd_employee', $noresult_msg, 'search_employee');
	} else {
		return "<div class='alert alert-info not-authorized'>" . __('You are not allowed to access to this area. Please contact the site administrator.', 'empd-com') . "</div>";
	}
}
