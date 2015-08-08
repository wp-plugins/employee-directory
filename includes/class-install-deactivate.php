<?php
/**
 * Install and Deactivate Plugin Functions
 * @package EMPD_COM
 * @version 1.0.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (!class_exists('Empd_Com_Install_Deactivate')):
	/**
	 * Empd_Com_Install_Deactivate Class
	 * @since WPAS 4.0
	 */
	class Empd_Com_Install_Deactivate {
		private $option_name;
		/**
		 * Hooks for install and deactivation and create options
		 * @since WPAS 4.0
		 */
		public function __construct() {
			$this->option_name = 'empd_com';
			$curr_version = get_option($this->option_name . '_version', 1);
			$new_version = constant(strtoupper($this->option_name) . '_VERSION');
			if (version_compare($curr_version, $new_version, '<')) {
				$this->set_options();
				update_option($this->option_name . '_version', $new_version);
			}
			register_activation_hook(EMPD_COM_PLUGIN_FILE, array(
				$this,
				'install'
			));
			register_deactivation_hook(EMPD_COM_PLUGIN_FILE, array(
				$this,
				'deactivate'
			));
			add_action('admin_init', array(
				$this,
				'setup_pages'
			));
			add_action('admin_notices', array(
				$this,
				'install_notice'
			));
			add_action('generate_rewrite_rules', 'emd_create_rewrite_rules');
			add_filter('query_vars', 'emd_query_vars');
			if (is_admin()) {
				$this->stax = new Emd_Single_Taxonomy('empd-com');
			}
			add_action('before_delete_post', array(
				$this,
				'delete_post_file_att'
			));
			add_filter('tiny_mce_before_init', array(
				$this,
				'tinymce_fix'
			));
			add_filter('get_media_item_args', 'emd_media_item_args');
		}
		/**
		 * Runs on plugin install to setup custom post types and taxonomies
		 * flushing rewrite rules, populates settings and options
		 * creates roles and assign capabilities
		 * @since WPAS 4.0
		 *
		 */
		public function install() {
			Emd_Employee::register();
			flush_rewrite_rules();
			$this->set_roles_caps();
			$this->set_options();
		}
		/**
		 * Runs on plugin deactivate to remove options, caps and roles
		 * flushing rewrite rules
		 * @since WPAS 4.0
		 *
		 */
		public function deactivate() {
			flush_rewrite_rules();
			$this->remove_caps_roles();
			$this->reset_options();
		}
		/**
		 * Sets caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function set_roles_caps() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'add');
			}
		}
		/**
		 * Removes caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function remove_caps_roles() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'remove');
			}
		}
		/**
		 * Set , reset capabilities
		 *
		 * @since WPAS 4.0
		 * @param object $wp_roles
		 * @param string $type
		 *
		 */
		public function set_reset_caps($wp_roles, $type) {
			$caps['enable'] = Array(
				'delete_private_emd_employees' => Array(
					'administrator'
				) ,
				'edit_others_emd_employees' => Array(
					'administrator'
				) ,
				'assign_departments' => Array(
					'administrator'
				) ,
				'manage_jobtitles' => Array(
					'administrator'
				) ,
				'edit_departments' => Array(
					'administrator'
				) ,
				'publish_emd_employees' => Array(
					'administrator'
				) ,
				'delete_others_emd_employees' => Array(
					'administrator'
				) ,
				'delete_emd_employees' => Array(
					'administrator'
				) ,
				'edit_published_emd_employees' => Array(
					'administrator'
				) ,
				'delete_jobtitles' => Array(
					'administrator'
				) ,
				'delete_departments' => Array(
					'administrator'
				) ,
				'edit_jobtitles' => Array(
					'administrator'
				) ,
				'read_private_emd_employees' => Array(
					'administrator'
				) ,
				'view_empd_com_dashboard' => Array(
					'administrator'
				) ,
				'assign_jobtitles' => Array(
					'administrator'
				) ,
				'edit_private_emd_employees' => Array(
					'administrator'
				) ,
				'delete_published_emd_employees' => Array(
					'administrator'
				) ,
				'manage_departments' => Array(
					'administrator'
				) ,
				'edit_emd_employees' => Array(
					'administrator'
				) ,
			);
			foreach ($caps as $stat => $role_caps) {
				foreach ($role_caps as $mycap => $roles) {
					foreach ($roles as $myrole) {
						if (($type == 'add' && $stat == 'enable') || ($stat == 'disable' && $type == 'remove')) {
							$wp_roles->add_cap($myrole, $mycap);
						} else if (($type == 'remove' && $stat == 'enable') || ($type == 'add' && $stat == 'disable')) {
							$wp_roles->remove_cap($myrole, $mycap);
						}
					}
				}
			}
		}
		/**
		 * Set app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function set_options() {
			update_option($this->option_name . '_setup_pages', 1);
			$ent_list = Array(
				'emd_employee' => Array(
					'label' => __('Employees', 'empd-com') ,
					'sortable' => 0,
					'unique_keys' => Array(
						'emd_employee_number'
					) ,
					'req_blt' => Array(
						'blt_title' => Array(
							'msg' => __('Title', 'empd-com')
						) ,
					) ,
				) ,
			);
			update_option($this->option_name . '_ent_list', $ent_list);
			$shc_list['app'] = 'Employee Directory';
			$shc_list['forms']['search_employees'] = Array(
				'name' => 'search_employees',
				'type' => 'search',
				'ent' => 'emd_employee',
				'page_title' => __('Employee Search', 'empd-com')
			);
			if (!empty($shc_list)) {
				update_option($this->option_name . '_shc_list', $shc_list);
			}
			$attr_list['emd_employee']['emd_employee_photo'] = Array(
				'visible' => 1,
				'label' => __('Photo', 'empd-com') ,
				'display_type' => 'thickbox_image',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'desc' => __('Photo of the employee. 250x250 is the preferred size.', 'empd-com') ,
				'type' => 'char',
				'max_file_uploads' => 1,
			);
			$attr_list['emd_employee']['emd_employee_featured'] = Array(
				'visible' => 1,
				'label' => __('Featured', 'empd-com') ,
				'display_type' => 'checkbox',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'desc' => __('Sets employee as featured which can be used to select employees in available views using Visual Shortcode Builder and Featured employee widget.', 'empd-com') ,
				'type' => 'binary',
				'options' => array(
					1 => 1
				) ,
			);
			$attr_list['emd_employee']['emd_employee_number'] = Array(
				'visible' => 1,
				'label' => __('Employee No', 'empd-com') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'desc' => __('The unique identifier for an employee', 'empd-com') ,
				'type' => 'char',
				'alphanumeric' => true,
				'uniqueAttr' => true,
			);
			$attr_list['emd_employee']['emd_employee_hiredate'] = Array(
				'visible' => 1,
				'label' => __('Hire Date', 'empd-com') ,
				'display_type' => 'date',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'desc' => __('Date the employee is hired', 'empd-com') ,
				'type' => 'date',
				'dformat' => array(
					'dateFormat' => 'mm-dd-yy'
				) ,
				'date_format' => 'm-d-Y',
				'time_format' => '',
			);
			$attr_list['emd_employee']['emd_employee_birthday'] = Array(
				'visible' => 1,
				'label' => __('Birthday', 'empd-com') ,
				'display_type' => 'date',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'type' => 'date',
				'dformat' => array(
					'dateFormat' => 'mm-dd-yy'
				) ,
				'date_format' => 'm-d-Y',
				'time_format' => '',
			);
			$attr_list['emd_employee']['emd_employee_primary_address'] = Array(
				'visible' => 1,
				'label' => __('Mailing Address', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'desc' => __('Primary mailing address of an employee', 'empd-com') ,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_phone'] = Array(
				'visible' => 1,
				'label' => __('Phone', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_extension'] = Array(
				'visible' => 1,
				'label' => __('Extension', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_mobile'] = Array(
				'visible' => 1,
				'label' => __('Mobile', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_email'] = Array(
				'visible' => 1,
				'label' => __('Email', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'type' => 'char',
				'email' => true,
			);
			$attr_list['emd_employee']['emd_employee_facebook'] = Array(
				'visible' => 1,
				'label' => __('Facebook', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_google'] = Array(
				'visible' => 1,
				'label' => __('Google+', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_twitter'] = Array(
				'visible' => 1,
				'label' => __('Twitter', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_linkedin'] = Array(
				'visible' => 1,
				'label' => __('Linkedin', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'type' => 'char',
			);
			$attr_list['emd_employee']['emd_employee_github'] = Array(
				'visible' => 1,
				'label' => __('Github', 'empd-com') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'type' => 'char',
			);
			if (!empty($attr_list)) {
				update_option($this->option_name . '_attr_list', $attr_list);
			}
			if (!empty($glob_list)) {
				update_option($this->option_name . '_glob_init_list', $glob_list);
				if (get_option($this->option_name . '_glob_list') === false) {
					update_option($this->option_name . '_glob_list', $glob_list);
				}
			}
			$glob_forms_list['search_employees']['captcha'] = 'never-show';
			$glob_forms_list['search_employees']['emd_employee_number'] = Array(
				'show' => 1,
				'row' => 1,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['search_employees']['blt_title'] = Array(
				'show' => 1,
				'row' => 2,
				'req' => 0,
				'size' => 12,
				'label' => __('Full Name', 'empd-com')
			);
			$glob_forms_list['search_employees']['emd_employee_email'] = Array(
				'show' => 1,
				'row' => 3,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['search_employees']['jobtitles'] = Array(
				'show' => 1,
				'row' => 4,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['search_employees']['departments'] = Array(
				'show' => 1,
				'row' => 5,
				'req' => 0,
				'size' => 12,
			);
			if (!empty($glob_forms_list)) {
				update_option($this->option_name . '_glob_forms_init_list', $glob_forms_list);
				if (get_option($this->option_name . '_glob_forms_list') === false) {
					update_option($this->option_name . '_glob_forms_list', $glob_forms_list);
				}
			}
			$tax_list['emd_employee']['departments'] = Array(
				'label' => __('Departments', 'empd-com') ,
				'default' => '',
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'required' => 0,
				'srequired' => 0
			);
			$tax_list['emd_employee']['jobtitles'] = Array(
				'label' => __('Job Titles', 'empd-com') ,
				'default' => '',
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'required' => 0,
				'srequired' => 0
			);
			if (!empty($tax_list)) {
				update_option($this->option_name . '_tax_list', $tax_list);
			}
			if (!empty($rel_list)) {
				update_option($this->option_name . '_rel_list', $rel_list);
			}
			$emd_activated_plugins = get_option('emd_activated_plugins');
			if (!$emd_activated_plugins) {
				update_option('emd_activated_plugins', Array(
					'empd-com'
				));
			} elseif (!in_array('empd-com', $emd_activated_plugins)) {
				array_push($emd_activated_plugins, 'empd-com');
				update_option('emd_activated_plugins', $emd_activated_plugins);
			}
			//conf parameters for incoming email
			//conf parameters for inline entity
			//action to configure different extension conf parameters for this plugin
			do_action('emd_extension_set_conf');
		}
		/**
		 * Reset app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function reset_options() {
			delete_option($this->option_name . '_ent_list');
			delete_option($this->option_name . '_shc_list');
			delete_option($this->option_name . '_attr_list');
			delete_option($this->option_name . '_tax_list');
			delete_option($this->option_name . '_rel_list');
			delete_option($this->option_name . '_adm_notice1');
			delete_option($this->option_name . '_adm_notice2');
			delete_option($this->option_name . '_setup_pages');
			$emd_activated_plugins = get_option('emd_activated_plugins');
			if (!empty($emd_activated_plugins)) {
				$emd_activated_plugins = array_diff($emd_activated_plugins, Array(
					'empd-com'
				));
				update_option('emd_activated_plugins', $emd_activated_plugins);
			}
		}
		/**
		 * Show install notices
		 *
		 * @since WPAS 4.0
		 *
		 * @return html
		 */
		public function install_notice() {
			if (isset($_GET[$this->option_name . '_adm_notice1'])) {
				update_option($this->option_name . '_adm_notice1', true);
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_adm_notice1') != 1) {
?>
<div class="updated">
<?php
				printf('<p><a href="%1s" target="_blank"> %2$s </a>%3$s<a style="float:right;" href="%4$s"><span class="dashicons dashicons-dismiss" style="font-size:15px;"></span>%5$s</a></p>', 'https://docs.emdplugins.com/docs/employee-directory-community-documentation/?pk_campaign=empd-com&pk_source=plugin&pk_medium=link&pk_content=notice', __('New To Employee Directory? Review the documentation!', 'wpas') , __('&#187;', 'wpas') , esc_url(add_query_arg($this->option_name . '_adm_notice1', true)) , __('Dismiss', 'wpas'));
?>
</div>
<?php
			}
			if (isset($_GET[$this->option_name . '_adm_notice2'])) {
				update_option($this->option_name . '_adm_notice2', true);
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_adm_notice2') != 1) {
?>
<div class="updated">
<?php
				printf('<p><a href="%1s" target="_blank"> %2$s </a>%3$s<a style="float:right;" href="%4$s"><span class="dashicons dashicons-dismiss" style="font-size:15px;"></span>%5$s</a></p>', 'https://emdplugins.com/plugins/employee-directory-professional/?pk_campaign=empd-com&pk_source=plugin&pk_medium=link&pk_content=notice', __('Upgrade to Professional Version Now!', 'wpas') , __('&#187;', 'wpas') , esc_url(add_query_arg($this->option_name . '_adm_notice2', true)) , __('Dismiss', 'wpas'));
?>
</div>
<?php
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_setup_pages') == 1) {
				echo "<div id=\"message\" class=\"updated\"><p><strong>" . __('Welcome to Employee Directory', 'empd-com') . "</strong></p>
           <p class=\"submit\"><a href=\"" . add_query_arg('setup_empd_com_pages', 'true', admin_url('index.php')) . "\" class=\"button-primary\">" . __('Setup Employee Directory Pages', 'empd-com') . "</a> <a class=\"skip button-primary\" href=\"" . add_query_arg('skip_setup_empd_com_pages', 'true', admin_url('index.php')) . "\">" . __('Skip setup', 'empd-com') . "</a></p>
         </div>";
			}
		}
		/**
		 * Setup pages for components and redirect to dashboard
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function setup_pages() {
			if (!is_admin()) {
				return;
			}
			global $wpdb;
			if (!empty($_GET['setup_' . $this->option_name . '_pages'])) {
				$shc_list = get_option($this->option_name . '_shc_list');
				$types = Array(
					'forms',
					'charts',
					'shcs',
					'datagrids',
					'integrations'
				);
				foreach ($types as $shc_type) {
					if (!empty($shc_list[$shc_type])) {
						foreach ($shc_list[$shc_type] as $keyshc => $myshc) {
							if (isset($myshc['page_title'])) {
								$pages[$keyshc] = $myshc;
							}
						}
					}
				}
				foreach ($pages as $key => $page) {
					$found = "";
					$page_content = "[" . $key . "]";
					$found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%"));
					if ($found != "") {
						continue;
					}
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'page',
						'post_author' => get_current_user_id() ,
						'post_title' => $page['page_title'],
						'post_content' => $page_content,
						'comment_status' => 'closed'
					);
					$page_id = wp_insert_post($page_data);
				}
				delete_option($this->option_name . '_setup_pages');
				wp_redirect(admin_url('index.php?empd-com-installed=true'));
				exit;
			}
			if (!empty($_GET['skip_setup_' . $this->option_name . '_pages'])) {
				delete_option($this->option_name . '_setup_pages');
				wp_redirect(admin_url('index.php?'));
				exit;
			}
		}
		/**
		 * Delete file attachments when a post is deleted
		 *
		 * @since WPAS 4.0
		 * @param $pid
		 *
		 * @return bool
		 */
		public function delete_post_file_att($pid) {
			$entity_fields = get_option($this->option_name . '_attr_list');
			$post_type = get_post_type($pid);
			if (!empty($entity_fields[$post_type])) {
				//Delete fields
				foreach (array_keys($entity_fields[$post_type]) as $myfield) {
					if (in_array($entity_fields[$post_type][$myfield]['display_type'], Array(
						'file',
						'image',
						'plupload_image',
						'thickbox_image'
					))) {
						$pmeta = get_post_meta($pid, $myfield);
						if (!empty($pmeta)) {
							foreach ($pmeta as $file_id) {
								wp_delete_attachment($file_id);
							}
						}
					}
				}
			}
			return true;
		}
		public function tinymce_fix($init) {
			$init['wpautop'] = false;
			return $init;
		}
	}
endif;
return new Empd_Com_Install_Deactivate();
