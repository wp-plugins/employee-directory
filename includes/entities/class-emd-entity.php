<?php
/**
 * Emd Entity
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Entity Class
 *
 * Base class for entities
 *
 * @since WPAS 4.0
 */
class Emd_Entity {
	protected $post_type;
	protected $textdomain;
	protected $boxes = Array();
	/**
	 * Check to show tabs/accordions in admin entity add/edit pages
	 * @since WPAS 4.0
	 *
	 * @return bool
	 *
	 */
	private function maybe_show_tabs() {
		$desired_screen = 'edit-' . $this->post_type;
		// Exit early on autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}
		// Inline save?
		if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['screen']) && $desired_screen === $_POST['screen']) {
			return true;
		}
		if (!$screen = get_current_screen()) {
			global $pagenow;
			if ('post-new.php' === $pagenow || 'post.php' === $pagenow) {
				if (isset($_GET['post_type']) && $this->post_type === $_GET['post_type']) {
					return true;
				} elseif (isset($_GET['post']) && get_post_type($_GET['post']) === $this->post_type) {
					return true;
				} else if ('post' === $this->post_type) {
					return true;
				}
				return false;
			}
		}
		if (is_object($screen) && isset($screen->id)) {
			return $desired_screen === $screen->id;
		} else {
			return false;
		}
	}
	/**
	 * Include file for tabs/accordions in admin entity add/edit pages
	 * @since WPAS 4.0
	 *
	 * @return include file
	 *
	 */
	public function include_tabs_acc() {
		if (defined('DOING_AJAX') && DOING_AJAX) return;
		if ($this->maybe_show_tabs()) {
			$fname = str_replace("_", "-", $this->post_type);
			$inc_file = constant(strtoupper(str_replace("-", "_", $this->textdomain)) . "_PLUGIN_DIR") . '/includes/entities/' . $fname . '-tabs.php';
			if (file_exists($inc_file)) {
				require_once $inc_file;
			}
		}
	}
	/**
	 * Change title to post id or concat of unique keys
	 * @since WPAS 4.0
	 *
	 * @param int $post_id
	 * @param object $post
	 *
	 */
	public function change_title($post_id, $post) {
		if ($post->post_type == $this->post_type) {
			if (in_array($post->post_title, Array(
				'',
				'Auto Draft'
			))) {
				remove_action('save_post', array(
					$this,
					'change_title'
				) , 99, 2);
				wp_update_post(array(
					'ID' => $post_id,
					'post_title' => $post_id
				));
				add_action('save_post', array(
					$this,
					'change_title'
				) , 99, 2);
			} elseif (empty($_POST['form_name'])) {
				$new_title = $post->post_title;
				$app = str_replace("-", "_", $this->textdomain);
				$ent_list = get_option($app . '_ent_list');
				if (!empty($ent_list[$this->post_type]['unique_keys'])) {
					$uniq_keys = $ent_list[$this->post_type]['unique_keys'];
					if(count($uniq_keys) == 1 && isset($ent_list[$this->post_type]['user_key']) && $ent_list[$this->post_type]['user_key'] == $uniq_keys[0])
					{
						$tpart = emd_mb_meta($ent_list[$this->post_type]['user_key'], Array() , $post_id);
						$user_info = get_userdata($tpart);
						$new_title = $user_info->display_name;
					}
					else {
						$new_title = '';
						foreach ($uniq_keys as $mykey) {
							$tpart = emd_mb_meta($mykey, Array() , $post_id);
							if(!empty($tpart)){
								$new_title.= $tpart . " - ";
							}
						}
						$new_title = rtrim($new_title, ' - ');
					}
				}
				if ($post->post_title == $post_id ||  ($post->post_title != $new_title && $new_title != '')) {
					remove_action('save_post', array(
						$this,
						'change_title'
					) , 99, 2);
					wp_update_post(array(
						'ID' => $post_id,
						'post_title' => $new_title
					));
					add_action('save_post', array(
						$this,
						'change_title'
					) , 99, 2);
				}
			}
		}
	}
	/**
	 * Update admin messages for specific entity
	 * @since WPAS 4.0
	 *
	 * @param array $messages
	 *
	 * @return array $messages
	 */
	public function updated_messages($messages) {
		global $post, $post_ID;
		$messages[$this->post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf(__('%s updated. <a href="%s">View %s</a>', 'emd-plugins') , $this->sing_label, esc_url(get_permalink($post_ID)) , $this->sing_label) ,
			2 => __('Custom field updated.', 'emd-plugins') ,
			3 => __('Custom field deleted.', 'emd-plugins') ,
			4 => sprintf(__('%s updated.', 'emd-plugins') , $this->sing_label) ,
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'emd-plugins') , $this->sing_label, wp_post_revision_title((int)$_GET['revision'], false)) : false,
			6 => sprintf(__('%s published. <a href="%s">View %s</a>', 'emd-plugins') , $this->sing_label, esc_url(get_permalink($post_ID)) , $this->sing_label) ,
			7 => sprintf(__('%s saved.', 'emd-plugins') , $this->sing_label) ,
			8 => sprintf(__('%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'emd-plugins') , $this->sing_label, esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) , $this->sing_label) ,
			9 => sprintf(__('%s scheduled for: <strong>%s</strong>. <a target="_blank" href="%s">Preview %s</a>', 'emd-plugins') , $this->sing_label, date_i18n(__('M j, Y @ G:i','emd-plugins') , strtotime($post->post_date)) , esc_url(get_permalink($post_ID)) , $this->sing_label) ,
			10 => sprintf(__('%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'emd-plugins') , $this->sing_label, esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) , $this->sing_label) ,
		);
		return $messages;
	}
	/**
	 * Add operations button
	 * @since WPAS 4.0
	 *
	 *
	 */
	public function add_opt_button() {
		global $post_type;
		if ($post_type != $this->post_type) {
			return;
		}
		if (current_user_can('manage_operations_' . $this->post_type . "s")) {
?>
			<script type="text/javascript">
			jQuery(document).ready(function($){
					$('h2 a.add-new-h2').after('<a id="opt-<?php echo str_replace("_", "-", $this->post_type); ?>" class="add-new-h2" href="<?php echo admin_url('edit.php?post_type=' . $this->menu_entity . '&page=operations_' . $this->post_type); ?>" ><?php _e('Operations', 'emd-plugins'); ?></a>');
					$('li.opt_<?php echo $this->post_type; ?>').html('');
					});     
		</script>
		<?php
		}
	}
	/**
	 * Set initial taxonomy terms related to this entity
	 * @since WPAS 4.0
	 *
	 * @param array $set_tax_terms
	 * @param string $tax_name
	 *
	 */
	protected static function set_taxonomy_init($set_tax_terms, $tax_name) {
		foreach ($set_tax_terms as $my_tax_term) {
			$term_id_arr = term_exists($my_tax_term['slug'], $tax_name);
			$args = Array();
			if (!empty($my_tax_term['desc'])) {
				$args['description'] = $my_tax_term['desc'];
			}
			$args['slug'] = $my_tax_term['slug'];
			if (empty($term_id_arr)) {
				wp_insert_term($my_tax_term['name'], $tax_name, $args);
			} else {
				$args['name'] = $my_tax_term['name'];
				wp_update_term($term_id_arr['term_id'], $tax_name, $args);
			}
		}
		foreach ($set_tax_terms as $my_tax_term) {
			$args = Array();
			if (!empty($my_tax_term['parent'])) {
				$parent_term = term_exists($my_tax_term['parent'], $tax_name);
				if ($parent_term !== 0 && $parent_term !== null) {
					$args['parent'] = $parent_term['term_id'];
					$myterm = term_exists($my_tax_term['slug'], $tax_name);
					$term_id = $myterm['term_id'];
					wp_update_term($term_id, $tax_name, $args);
				}
			}
		}
		delete_option($tax_name . '_children');
	}
	/**
	 * Sets attributes and filter and columns 
	 * @since WPAS 4.4
	 *
	 * @return array $search_args
	 * @return array $filter_args
	 *
	 */
	protected function set_args_boxes(){
		$search_args = Array();
		$filter_args = Array();	
		$this->boxes[0]['validation'] = array(
			'onfocusout' => false,
			'onkeyup' => false,
			'onclick' => false
		);
		$myapp = str_replace("-", "_", $this->textdomain);
		$attr_list = get_option($myapp . '_attr_list');
		if (!empty($attr_list[$this->post_type])) {
			foreach ($attr_list[$this->post_type] as $kattr => $vattr) {
				if ($vattr['visible'] == 1) {
					$search_args[$kattr]['name'] = $vattr['label'];
					$search_args[$kattr]['meta'] = $kattr;
					$search_args[$kattr]['type'] = $vattr['display_type'];
					$search_args[$kattr]['cast'] = strtoupper($vattr['type']);
					if (!empty($vattr['options'])) {
						$search_args[$kattr]['options'] = $vattr['options'];
					}
					if (!empty($vattr['date_format'])) {
						$search_args[$kattr]['date_format'] = $vattr['date_format'];
					}
					if (!empty($vattr['desc'])) {
						$search_args[$kattr]['desc'] = $vattr['desc'];
					}
					$this->boxes[0]['fields'][$kattr]['name'] = $vattr['label'];
					$this->boxes[0]['fields'][$kattr]['list_visible'] = $vattr['list_visible'];
					$this->boxes[0]['fields'][$kattr]['id'] = $kattr;
					if ($vattr['display_type'] == 'user-adv') {
						$this->boxes[0]['fields'][$kattr]['type'] = 'user';
					} else {
						$this->boxes[0]['fields'][$kattr]['type'] = $vattr['display_type'];
					}
					if (isset($vattr['roles'])) {
						$this->boxes[0]['fields'][$kattr]['query_args']['role'] = $vattr['roles'];
					}
					if (isset($vattr['dformat'])) {
						$this->boxes[0]['fields'][$kattr]['js_options'] = $vattr['dformat'];
					}
					$attr_fields = Array(
						'hidden_func',
						'no_update',
						'autoinc_start',
						'autoinc_incr',
						'max_file_uploads',
						'multiple',
						'desc',
						'std',
						'options',
						'placeholder',
						'field_type',
						'address_field',
					);
					foreach ($attr_fields as $attr_field) {
						if (isset($vattr[$attr_field])) {
							$this->boxes[0]['fields'][$kattr][$attr_field] = $vattr[$attr_field];
						}
					}
					$this->boxes[0]['fields'][$kattr]['class'] = $kattr;
					//validation
					if ($vattr['required'] == 1) {
						$this->boxes[0]['validation']['rules'][$kattr]['required'] = true;
					} else {
						$this->boxes[0]['validation']['rules'][$kattr]['required'] = false;
					}
					$valid_rules = Array(
						'email',
						'url',
						'number',
						'minlength',
						'maxlength',
						'digits',
						'creditcard',
						'phoneUS',
						'phoneUK',
						'letterswithbasicpunc',
						'alphanumeric',
						'lettersonly',
						'nowhitespace',
						'zipcodeUS',
						'postcodeUK',
						'integer',
						'vinUS',
						'ipv4',
						'ipv6',
						'maxWords',
						'minWords',
						'patern',
						'max',
						'min',
						'mobileUK',
						'uniqueAttr'
					);
					foreach ($valid_rules as $vrule) {
						if (isset($vattr[$vrule])) {
							$this->boxes[0]['validation']['rules'][$kattr][$vrule] = $vattr[$vrule];
						}
					}
					if(!empty($vattr['conditional'])){
						$this->boxes[0]['conditional'][$kattr] = $vattr['conditional'];
						$this->boxes[0]['conditional'][$kattr]['type'] = $vattr['display_type'];
					}
					if ($vattr['filterable'] == 1) {
						$filter_args[$kattr]['name'] = $vattr['label'];
						$filter_args[$kattr]['meta'] = $kattr;
						$filter_args[$kattr]['type'] = $vattr['display_type'];
						$filter_args[$kattr]['cast'] = strtoupper($vattr['type']);
						if (!empty($vattr['desc'])) {
							$filter_args[$kattr]['desc'] = $vattr['desc'];
						}
						if (!empty($vattr['options'])) {
							$filter_args[$kattr]['options'] = $vattr['options'];
						}
						if (!empty($vattr['user_roles'])) {
							$filter_args[$kattr]['user_roles'] = $vattr['user_roles'];
						}
						if (!empty($vattr['dformat'])) {
							if (isset($vattr['dformat']['dateFormat'])) {
								$filter_args[$kattr]['date_format'] = $vattr['dformat']['dateFormat'];
							}
							if (isset($vattr['dformat']['timeFormat'])) {
								$filter_args[$kattr]['time_format'] = $vattr['dformat']['timeFormat'];
							}
						}
					}
				}
			}
		}
		$tax_list = get_option($myapp . '_tax_list');
		if (!empty($tax_list[$this->post_type])) {
			foreach ($tax_list[$this->post_type] as $ktax => $vtax) {
				if(!empty($vtax['conditional']['attr_rules']) || !empty($vtax['conditional']['tax_rules'])){
					$this->boxes[0]['tax_conditional'][$ktax] = $vtax['conditional'];
					$this->boxes[0]['tax_conditional'][$ktax]['type'] = $vtax['cond_type'];
				}
			}
		}
		return Array($search_args,$filter_args);
	}
}
