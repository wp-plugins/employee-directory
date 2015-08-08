<?php
/**
 * Entity Widget Classes
 *
 * @package EMPD_COM
 * @version 1.0.0
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Entity widget class extends Emd_Widget class
 *
 * @since WPAS 4.0
 */
class empd_com_recent_employees_widget extends Emd_Widget {
	public $title;
	public $text_domain = 'empd-com';
	public $class_label;
	public $class = 'emd_employee';
	public $type = 'entity';
	public $has_pages = false;
	public $css_label = 'recent-employees';
	public $id = 'empd_com_recent_employees_widget';
	public $query_args = array(
		'post_type' => 'emd_employee',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'context' => 'empd_com_recent_employees_widget',
	);
	public $filter = '';
	public $header = '';
	public $footer = '';
	/**
	 * Instantiate entity widget class with params
	 *
	 * @since WPAS 4.0
	 */
	public function __construct() {
		parent::__construct($this->id, __('Recent Employees', 'empd-com') , __('Employees', 'empd-com') , __('The most recent employees', 'empd-com'));
	}
	/**
	 * Get header and footer for layout
	 *
	 * @since WPAS 4.6
	 */
	protected function get_header_footer() {
		$this->header = '<div class="emplist-group">';
		$this->footer = '</div>';
	}
	/**
	 * Enqueue css and js for widget
	 *
	 * @since WPAS 4.5
	 */
	protected function enqueue_scripts() {
		if (is_active_widget(false, false, $this->id_base) && !is_admin()) {
			wp_enqueue_style('recent-employees-css');
		}
	}
	/**
	 * Returns widget layout
	 *
	 * @since WPAS 4.0
	 */
	public static function layout() {
		global $post;
		$ent_attrs = get_option('empd_com_attr_list');
		$layout = "<div class=\"full-name\"><a href=\"" . get_permalink() . "\">" . get_the_title() . "</a></div>
<div class=\"tax-jobtitle\">" . get_the_term_list(get_the_ID() , 'jobtitles', '', ' ', '') . "</div>
<div class=\"attr-hiredate\">" . esc_html(emd_translate_date_format($ent_attrs['emd_employee']['emd_employee_hiredate'], emd_mb_meta('emd_employee_hiredate') , 1)) . "</div>";
		return $layout;
	}
}
/**
 * Entity widget class extends Emd_Widget class
 *
 * @since WPAS 4.0
 */
class empd_com_featured_employees_widget extends Emd_Widget {
	public $title;
	public $text_domain = 'empd-com';
	public $class_label;
	public $class = 'emd_employee';
	public $type = 'entity';
	public $has_pages = false;
	public $css_label = 'featured-employees';
	public $id = 'empd_com_featured_employees_widget';
	public $query_args = array(
		'post_type' => 'emd_employee',
		'post_status' => 'publish',
		'orderby' => 'menu_order',
		'order' => 'DESC',
		'context' => 'empd_com_featured_employees_widget',
	);
	public $filter = 'attr::emd_employee_featured::is::1';
	public $header = '';
	public $footer = '';
	/**
	 * Instantiate entity widget class with params
	 *
	 * @since WPAS 4.0
	 */
	public function __construct() {
		parent::__construct($this->id, __('Featured Employees', 'empd-com') , __('Employees', 'empd-com') , __('The most recent employees', 'empd-com'));
	}
	/**
	 * Get header and footer for layout
	 *
	 * @since WPAS 4.6
	 */
	protected function get_header_footer() {
		$this->header = '<div class="emplist-group">';
		$this->footer = '</div>';
	}
	/**
	 * Enqueue css and js for widget
	 *
	 * @since WPAS 4.5
	 */
	protected function enqueue_scripts() {
		if (is_active_widget(false, false, $this->id_base) && !is_admin()) {
			wp_enqueue_style('featured-employees-css');
		}
	}
	/**
	 * Returns widget layout
	 *
	 * @since WPAS 4.0
	 */
	public static function layout() {
		global $post;
		$ent_attrs = get_option('empd_com_attr_list');
		$layout = "<div class=\"full-name\"><a href=\"" . get_permalink() . "\">" . get_the_title() . "</a></div>
<div class=\"tax-department\">" . get_the_term_list(get_the_ID() , 'departments', '', ' ', '') . "</div>
<div class=\"tax-jobtitle\">" . get_the_term_list(get_the_ID() , 'jobtitles', '', ' ', '') . "</div>";
		return $layout;
	}
}
$access_views = get_option('empd_com_access_views', Array());
if (empty($access_views['widgets']) || (!empty($access_views['widgets']) && in_array('recent_employees', $access_views['widgets']) && current_user_can('view_recent_employees'))) {
	register_widget('empd_com_recent_employees_widget');
}
if (empty($access_views['widgets']) || (!empty($access_views['widgets']) && in_array('featured_employees', $access_views['widgets']) && current_user_can('view_featured_employees'))) {
	register_widget('empd_com_featured_employees_widget');
}
