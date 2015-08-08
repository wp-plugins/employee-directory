<?php
/**
 * Entity Class
 *
 * @package EMPD_COM
 * @version 1.0.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Employee Class
 * @since WPAS 4.0
 */
class Emd_Employee extends Emd_Entity {
	protected $post_type = 'emd_employee';
	protected $textdomain = 'empd-com';
	protected $sing_label;
	protected $plural_label;
	protected $menu_entity;
	/**
	 * Initialize entity class
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function __construct() {
		add_action('init', array(
			$this,
			'set_filters'
		));
		add_action('admin_init', array(
			$this,
			'set_metabox'
		));
		add_filter('post_updated_messages', array(
			$this,
			'updated_messages'
		));
		add_action('manage_emd_employee_posts_custom_column', array(
			$this,
			'custom_columns'
		) , 10, 2);
		add_filter('manage_emd_employee_posts_columns', array(
			$this,
			'column_headers'
		));
	}
	/**
	 * Get column header list in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function column_headers($columns) {
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if (!in_array($fkey, Array(
					'wpas_form_name',
					'wpas_form_submitted_by',
					'wpas_form_submitted_ip'
				)) && !in_array($mybox_field['type'], Array(
					'textarea',
					'wysiwyg'
				)) && $mybox_field['list_visible'] == 1) {
					$columns[$fkey] = $mybox_field['name'];
				}
			}
		}
		$args = array(
			'_builtin' => false,
			'object_type' => Array(
				$this->post_type
			)
		);
		$taxonomies = get_taxonomies($args, 'objects');
		if (!empty($taxonomies)) {
			foreach ($taxonomies as $taxonomy) {
				$columns[$taxonomy->name] = $taxonomy->label;
			}
		}
		return $columns;
	}
	/**
	 * Get custom column values in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param int $column_id
	 * @param int $post_id
	 *
	 * @return string $value
	 */
	public function custom_columns($column_id, $post_id) {
		if (taxonomy_exists($column_id) == true) {
			$terms = get_the_terms($post_id, $column_id);
			$ret = array();
			if (!empty($terms)) {
				foreach ($terms as $term) {
					$url = add_query_arg(array(
						'post_type' => $this->post_type,
						'term' => $term->slug,
						'taxonomy' => $column_id
					) , admin_url('edit.php'));
					$ret[] = sprintf('<a href="%s">%s</a>', $url, $term->name);
				}
			}
			echo implode(', ', $ret);
			return;
		}
		$value = get_post_meta($post_id, $column_id, true);
		$type = "";
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($fkey == $column_id) {
					$type = $mybox_field['type'];
					break;
				}
			}
		}
		switch ($type) {
			case 'plupload_image':
			case 'image':
			case 'thickbox_image':
				$image_list = emd_mb_meta($column_id, 'type=image');
				if (!empty($image_list)) {
					$value = "";
					foreach ($image_list as $myimage) {
						$value.= "<img style='max-width:100%;height:auto;' src='" . $myimage['url'] . "' >";
					}
				}
			break;
			case 'user':
			case 'user-adv':
				$user_id = emd_mb_meta($column_id);
				if (!empty($user_id)) {
					$user_info = get_userdata($user_id);
					$value = $user_info->display_name;
				}
			break;
			case 'file':
				$file_list = emd_mb_meta($column_id, 'type=file');
				if (!empty($file_list)) {
					$value = "";
					foreach ($file_list as $myfile) {
						$fsrc = wp_mime_type_icon($myfile['ID']);
						$value.= "<a href='" . $myfile['url'] . "' target='_blank'><img src='" . $fsrc . "' title='" . $myfile['name'] . "' width='20' /></a>";
					}
				}
			break;
			case 'checkbox_list':
				$checkbox_list = emd_mb_meta($column_id, 'type=checkbox_list');
				if (!empty($checkbox_list)) {
					$value = implode(', ', $checkbox_list);
				}
			break;
			case 'select':
			case 'select_advanced':
				$select_list = get_post_meta($post_id, $column_id, false);
				if (!empty($select_list)) {
					$value = implode(', ', $select_list);
				}
			break;
			case 'checkbox':
				if ($value == 1) {
					$value = '<span class="dashicons dashicons-yes"></span>';
				} elseif ($value == 0) {
					$value = '<span class="dashicons dashicons-no-alt"></span>';
				}
			break;
		}
		echo $value;
	}
	/**
	 * Register post type and taxonomies and set initial values for taxs
	 *
	 * @since WPAS 4.0
	 *
	 */
	public static function register() {
		$labels = array(
			'name' => __('Employees', 'empd-com') ,
			'singular_name' => __('Employee', 'empd-com') ,
			'add_new' => __('Add New', 'empd-com') ,
			'add_new_item' => __('Add New Employee', 'empd-com') ,
			'edit_item' => __('Edit Employee', 'empd-com') ,
			'new_item' => __('New Employee', 'empd-com') ,
			'all_items' => __('All Employees', 'empd-com') ,
			'view_item' => __('View Employee', 'empd-com') ,
			'search_items' => __('Search Employees', 'empd-com') ,
			'not_found' => __('No Employees Found', 'empd-com') ,
			'not_found_in_trash' => __('No Employees Found In Trash', 'empd-com') ,
			'menu_name' => __('Employees', 'empd-com') ,
		);
		register_post_type('emd_employee', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('Employees are staff members working for your organization.', 'empd-com') ,
			'show_in_menu' => true,
			'menu_position' => 6,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => 'employees'
			) ,
			'can_export' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-businessman',
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_employee',
			'supports' => array(
				'title',
				'editor',
			)
		));
		$departments_nohr_labels = array(
			'name' => __('Departments', 'empd-com') ,
			'singular_name' => __('Department', 'empd-com') ,
			'search_items' => __('Search Departments', 'empd-com') ,
			'popular_items' => __('Popular Departments', 'empd-com') ,
			'all_items' => __('All', 'empd-com') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Department', 'empd-com') ,
			'update_item' => __('Update Department', 'empd-com') ,
			'add_new_item' => __('Add New Department', 'empd-com') ,
			'new_item_name' => __('Add New Department Name', 'empd-com') ,
			'separate_items_with_commas' => __('Seperate Departments with commas', 'empd-com') ,
			'add_or_remove_items' => __('Add or Remove Departments', 'empd-com') ,
			'choose_from_most_used' => __('Choose from the most used Departments', 'empd-com') ,
			'menu_name' => __('Departments', 'empd-com') ,
		);
		register_taxonomy('departments', array(
			'emd_employee'
		) , array(
			'hierarchical' => false,
			'labels' => $departments_nohr_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'departments'
			) ,
			'capabilities' => array(
				'manage_terms' => 'manage_departments',
				'edit_terms' => 'edit_departments',
				'delete_terms' => 'delete_departments',
				'assign_terms' => 'assign_departments'
			) ,
		));
		$jobtitles_nohr_labels = array(
			'name' => __('Job Titles', 'empd-com') ,
			'singular_name' => __('Job Title', 'empd-com') ,
			'search_items' => __('Search Job Titles', 'empd-com') ,
			'popular_items' => __('Popular Job Titles', 'empd-com') ,
			'all_items' => __('All', 'empd-com') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Job Title', 'empd-com') ,
			'update_item' => __('Update Job Title', 'empd-com') ,
			'add_new_item' => __('Add New Job Title', 'empd-com') ,
			'new_item_name' => __('Add New Job Title Name', 'empd-com') ,
			'separate_items_with_commas' => __('Seperate Job Titles with commas', 'empd-com') ,
			'add_or_remove_items' => __('Add or Remove Job Titles', 'empd-com') ,
			'choose_from_most_used' => __('Choose from the most used Job Titles', 'empd-com') ,
			'menu_name' => __('Job Titles', 'empd-com') ,
		);
		register_taxonomy('jobtitles', array(
			'emd_employee'
		) , array(
			'hierarchical' => false,
			'labels' => $jobtitles_nohr_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'jobtitles'
			) ,
			'capabilities' => array(
				'manage_terms' => 'manage_jobtitles',
				'edit_terms' => 'edit_jobtitles',
				'delete_terms' => 'delete_jobtitles',
				'assign_terms' => 'assign_jobtitles'
			) ,
		));
		if (!get_option('empd_com_emd_employee_terms_init')) {
			$set_tax_terms = Array(
				Array(
					'name' => __('Product Manager', 'empd-com') ,
					'slug' => sanitize_title('Product Manager')
				) ,
				Array(
					'name' => __('Sales Manager', 'empd-com') ,
					'slug' => sanitize_title('Sales Manager')
				) ,
				Array(
					'name' => __('Agent', 'empd-com') ,
					'slug' => sanitize_title('Agent')
				) ,
				Array(
					'name' => __('Contractor', 'empd-com') ,
					'slug' => sanitize_title('Contractor')
				) ,
				Array(
					'name' => __('Analyst', 'empd-com') ,
					'slug' => sanitize_title('Analyst')
				) ,
				Array(
					'name' => __('Developer', 'empd-com') ,
					'slug' => sanitize_title('Developer')
				) ,
				Array(
					'name' => __('Director', 'empd-com') ,
					'slug' => sanitize_title('Director')
				) ,
				Array(
					'name' => __('CEO', 'empd-com') ,
					'slug' => sanitize_title('CEO')
				) ,
				Array(
					'name' => __('President', 'empd-com') ,
					'slug' => sanitize_title('President')
				) ,
				Array(
					'name' => __('CFO', 'empd-com') ,
					'slug' => sanitize_title('CFO')
				) ,
				Array(
					'name' => __('HR Manager', 'empd-com') ,
					'slug' => sanitize_title('HR Manager')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'jobtitles');
			update_option('empd_com_emd_employee_terms_init', true);
		}
	}
	/**
	 * Set metabox fields,labels,filters, comments, relationships if exists
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function set_filters() {
		$search_args = Array();
		$filter_args = Array();
		$this->sing_label = __('Employee', 'empd-com');
		$this->plural_label = __('Employees', 'empd-com');
		$this->menu_entity = 'emd_employee';
		$this->boxes[] = array(
			'id' => 'emd_employee_info_emd_employee_0',
			'title' => __('Employee Info', 'empd-com') ,
			'pages' => array(
				'emd_employee'
			) ,
			'context' => 'normal',
		);
		list($search_args, $filter_args) = $this->set_args_boxes();
		if (!post_type_exists($this->post_type) || in_array($this->post_type, Array(
			'post',
			'page'
		))) {
			self::register();
		}
	}
	/**
	 * Initialize metaboxes
	 * @since WPAS 4.5
	 *
	 */
	public function set_metabox() {
		if (class_exists('EMD_Meta_Box') && is_array($this->boxes)) {
			foreach ($this->boxes as $meta_box) {
				new EMD_Meta_Box($meta_box);
			}
		}
	}
	/**
	 * Change content for created frontend views
	 * @since WPAS 4.0
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function change_content($content) {
		global $post;
		$layout = "";
		if (get_post_type() == $this->post_type && is_single()) {
			ob_start();
			emd_get_template_part($this->textdomain, 'single', 'emd-employee');
			$layout = ob_get_clean();
		}
		if ($layout != "") {
			$content = $layout;
		}
		return $content;
	}
}
new Emd_Employee;
