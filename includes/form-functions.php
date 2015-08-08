<?php
/**
 * Form Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
/**
 * Check min max value for int/decimal fields
 *
 * @since WPAS 4.0
 * @param int $value
 * @param int $minvalue
 * @param int $maxvalue
 * @param bool
 *
 * @return string $operator
 */
function emd_check_min_max_value($value, $minvalue, $maxvalue, $required) {
	if ($required == 0 && $value == '') return true;
	if ($value < $minvalue) return false;
	if ($maxvalue != 0 && $value > $maxvalue) return false;
	return true;
}
/**
 * Check min max word count for textarea fields
 *
 * @since WPAS 4.0
 * @param string $value
 * @param int $minwords
 * @param int $maxwords
 * @param bool $required
 *
 * @return bool
 */
function emd_check_min_max_words($value, $minwords, $maxwords, $required) {
	if ($required == 0 && str_word_count($value) == 0) return true;
	if ($maxwords != 0 && str_word_count($value) > $maxwords) return false;
	if (str_word_count($value) < $minwords) return false;
	return true;
}
/**
 * Unlink uploads
 *
 * @since WPAS 4.0
 * @param array $uploads
 *
 */
function emd_delete_uploads($uploads) {
	foreach ($uploads as $myupload) {
		foreach ($myupload as $upfile) {
			if (isset($upfile['path']) && file_exists($upfile['path'])) {
				unlink($upfile['path']);
			}
		}
	}
}
/**
 * Process search form and return layout
 *
 * @since WPAS 4.0
 * @param string $myapp
 * @param string $myentity
 * @param string $myform
 * @param string $myview
 * @param string $noresult_msg
 * @param string $path
 *
 * @return string $res_layout html
 */
function emd_search_form($myapp, $myentity, $myform, $myview, $noresult_msg, $path) {
	global $blts;
	$args = Array();
	$search_fields = Array();
	$attrs = Array();
	$txns = Array();
	$rels = Array();
	$oprs = Array();
	$blts = Array();
	$rel_count = 0;
	$res_layout = $noresult_msg;
	$myattr_list = Array();
	$mytxn_list = Array();
	$myrel_list = Array();
	$attr_list = get_option($myapp . '_attr_list', Array());
	$txn_list = get_option($myapp . '_tax_list', Array());
	$rel_list = get_option($myapp . '_rel_list', Array());
	if (isset($attr_list[$myentity])) {
		$myattr_list = array_keys($attr_list[$myentity]);
	}
	if (isset($txn_list[$myentity])) {
		$mytxn_list = array_keys($txn_list[$myentity]);
	}
	if (isset($rel_list)) {
		$myrel_list = array_keys($rel_list);
	}
	foreach ($_POST as $postkey => $postval) {
		if (!empty($postkey) && !is_array($postval)) {
			$postval = sanitize_text_field(urldecode($postval));
			$postval = html_entity_decode($postval);
		}
		if (!empty($postval)) {
			if (in_array($postkey, $mytxn_list)) {
				$txns[$postkey] = $postval;
			} elseif (in_array($postkey, $myrel_list)) {
				$rel_key = preg_replace("/rel_/", "", $postkey, 1);
				$rels[$rel_count] = Array(
					'key' => $rel_key,
					'val' => $postval
				);
				$rel_count++;
			} elseif (in_array($postkey, $myattr_list)) {
				$attrs[$postkey] = $postval;
			} elseif (preg_match('/^opr__/', $postkey)) {
				$opr_key = preg_replace("/opr__/", "", $postkey, 1);
				$oprs[$opr_key] = $postval;
			} elseif (in_array($postkey, Array(
				'blt_title',
				'blt_content',
				'blt_excerpt'
			))) {
				$blts[$postkey] = $postval;
			}
		}
	}
	if (!empty($blts)) {
		foreach ($blts as $bltkey => $bltval) {
			$args[$bltkey] = $bltval;
			if (!empty($oprs) && isset($oprs[$myform . '_' . $bltkey])) {
				$args['opr__' . $bltkey] = emd_get_meta_operator($oprs[$myform . '_' . $bltkey]);
			} else {
				$args['opr__' . $bltkey] = "=";
			}
		}
	}
	$filter = "";
	if (!empty($attrs)) {
		foreach ($attrs as $key => $myattr) {
			$filter.= "attr::" . $key . "::";
			if (!empty($oprs) && isset($oprs[$myform . '_' . $key])) {
				$filter.= $oprs[$myform . '_'  . $key];
			} else {
				$filter.= "is";
			}
			if (is_array($myattr) && !empty($myattr)) {
				$filter.= "::" . implode(',', $myattr) . ";";
			} else {
				$filter.= "::" . $myattr . ";";
			}
		}
	}
	if (!empty($txns)) {
		foreach ($txns as $keytxn => $mytxn) {
			if (is_array($mytxn) && !empty($mytxn)) {
				$filter.= "tax::" . $keytxn . "::is::" . implode(",", $mytxn) . ";";
			} elseif (!empty($mytxn)) {
				$filter.= "tax::" . $keytxn . "::is::" . $mytxn . ";";
			}
		}
	}
	if (!empty($rels)) {
		foreach ($rels as $vrel) {
			if (is_array($vrel['val']) && !empty($vrel['val'])) {
				$filter.= "rel::" . $vrel['key'] . "::is::" . implode(',', $vrel['val']) . ";";
			} elseif (!empty($vrel['val'])) {
				$filter.= "rel::" . $vrel['key'] . "::is::" . $vrel['val'] . ";";
			}
		}
	}
	$emd_query = new Emd_Query($myentity, $myapp);
	$emd_query->args_filter($filter);
	$args = array_merge($args,$emd_query->args);
	$args['post_type'] = $myentity;
	if (!empty($attrs) || !empty($txns) || !empty($rels) || !empty($blts)) {
		$func_layout = $myapp . "_" . $myview . "_set_shc";
		$list_layout = $func_layout('', $args, $myform);
		if ($list_layout != '') {
			$res_layout = "<div id='" . $myview . $myentity . "-results'>";
			$res_layout.= $list_layout;
		}
	}
	$emd_query->remove_filters();
	return $res_layout;
}
/**
 * Process submit form and redirect to given url or show errors if form doesn't validate
 *
 * @since WPAS 4.0
 * @param string $form_name
 * @param string $myapp
 * @param string $myentity
 * @param string $post_status
 * @param string $redirect_url
 * @param string $error_msg
 * @param bool $file_attr_exists
 *
 */
function emd_submit_redirect_form($form_name, $myapp, $myentity, $post_status, $visitor_post_status, $redirect_url, $error_msg, $file_attr_exists) {
	check_admin_referer($form_name, $form_name . '_nonce');
	$set_fname = $myapp . "_set_" . $form_name;
	$form = $set_fname();
	if ($file_attr_exists) {
		emd_set_file_upload();
	}
	if ($form->validate()) {
		$result = emd_submit_form($myapp, $myentity, $post_status, $visitor_post_status, $form);
		if ($result !== false) {
			$rel_uniqs = $result['rel_uniqs'];
			do_action('emd_notify', $myapp, $result['id'], 'entity', 'front_add', $rel_uniqs);
			wp_redirect($redirect_url);
			exit;
		} else {
			$_SESSION['form_errors'] = Array(
				'error' => Array(
					'0' => $error_msg
				)
			);
		}
	} else {
		if ($file_attr_exists) {
			emd_unset_file_upload();
		}
		$_SESSION['form_errors'] = $form->errors;
	}
}
/**
 * Set files variable from session uploads
 *
 * @since WPAS 4.0
 *
 */
function emd_set_file_upload() {
	if (!empty($_FILES)) {
		foreach ($_FILES as $key_attr => $myfile) {
			if (!empty($_SESSION['uploads']) && isset($_SESSION['uploads'][$key_attr])) {
				$_FILES[$key_attr] = $_SESSION['uploads'][$key_attr];
			} else {
				unset($_FILES[$key_attr]);
				$_FILES[$key_attr][0] = $myfile;
			}
		}
	}
	if (!empty($_SESSION['uploads']) && empty($_FILES)) {
		foreach ($_SESSION['uploads'] as $key_attr => $files) {
			$_FILES[$key_attr] = $files;
		}
	}
}
/**
 * Unsets session uploads
 *
 * @since WPAS 4.0
 *
 */
function emd_unset_file_upload() {
	if (isset($_SESSION['uploads'])) {
		//emd_delete_uploads($_SESSION['uploads']);
		unset($_SESSION['uploads']);
	}
}
/**
 * Process Search form and return layout and form
 *
 * @since WPAS 4.0
 * @param string $form_name
 * @param string $myapp
 * @param string $myentity
 * @param string $noresult_msg
 * @param string $view_name
 *
 * @return string $layout
 */
function emd_search_php_form($form_name, $myapp, $myentity, $noresult_msg, $view_name) {
	$set_fname = $myapp . "_set_" . $form_name;
	$form = $set_fname();
	$paged = (get_query_var('pageno')) ? get_query_var('pageno') : 0;
	$path = constant(strtoupper($myapp) . "_PLUGIN_DIR");
	$form_file = str_replace("_", "-", $form_name);
	if (empty($_POST) && $paged != 0 && isset($_SESSION[$form_name . '_args'])) {
		$layout = "<div style='position:relative;' class='emd-container'>";
		$layout.= $form->render($path . 'forms/' . $form_file . '.php', true);
		$args = $_SESSION[$form_name . '_args'];
		$set_layout_func = $myapp . "_" . $view_name . "_set_shc";
		$list_layout = $set_layout_func('', $args, $form_name, $paged);
		if ($list_layout != '') {
			$layout.= "<div id='" . $view_name . $myentity . "-results'>";
			$layout.= $list_layout;
		}
		$layout.= "</div>";
		return $layout;
	}
	if (!empty($_POST) && !empty($_POST['form_name']) && $_POST['form_name'] == $form_name) {
		check_admin_referer($form_name, $form_name . '_nonce');
		if ($form->validate()) {
			$layout = "<div style='position:relative;' class='emd-container'>";
			$layout.= $form->render($path . 'forms/' . $form_file . '.php', true);
			$layout.= emd_search_form($myapp, $myentity, $form_name, $view_name, $noresult_msg, $path);
			$layout.= "</div>";
			return $layout;
		} else {
			if (isset($_SESSION['form_errors'])) {
				$form->errors = $_SESSION['form_errors'];
			}
			$layout = "<div style='position:relative;' class='emd-container'>";
			$layout.= $form->render($path . 'forms/' . $form_file . '.php', true);
			$layout.= "</div><!--container-end-->";
			return $layout;
		}
	} else {
		$layout = "<div style='position:relative;' class='emd-container'>";
		$layout.= $form->render($path . 'forms/' . $form_file . '.php', true);
		$layout.= "</div><!--container-end-->";
		return $layout;
	}
}
/**
 * Process Submit form and return layout and form
 *
 * @since WPAS 4.0
 * @param string $form_name
 * @param string $myapp
 * @param string $myentity
 * @param string $post_status
 * @param string $success_msg
 * @param string $error_msg
 * @param bool $clear_hide_form
 * @param bool $file_attr_exists
 *
 * @return string $layout
 */
function emd_submit_php_form($form_name, $myapp, $myentity, $post_status, $visitor_post_status, $success_msg, $error_msg, $clear_hide_form, $file_attr_exists) {
	$set_fname = $myapp . "_set_" . $form_name;
	$form = $set_fname();
	$form_file = str_replace("_", "-", $form_name);
	$path = constant(strtoupper($myapp) . "_PLUGIN_DIR");
	if (!empty($_POST) && $_POST['form_name'] == $form_name) {
		check_admin_referer($form_name, $form_name . '_nonce');
		if ($file_attr_exists) {
			emd_set_file_upload();
		}
		if ($form->validate()) {
			$result = emd_submit_form($myapp, $myentity, $post_status, $visitor_post_status, $form);
			if ($file_attr_exists) {
				emd_unset_file_upload();
			}
			if ($result !== false) {
				$rel_uniqs = $result['rel_uniqs'];
				do_action('emd_notify', $myapp, $result['id'], 'entity', 'front_add', $rel_uniqs);
				$layout = "<div style='position:relative;' class='emd-container'>";
				$layout.= $form->render($path . 'forms/' . $form_file . '.php', true, '', $success_msg, '', $clear_hide_form);
				$layout.= "</div>";
				return $layout;
			} else {
				$layout = "<div style='position:relative;' class='emd-container'>";
				$layout.= $form->render($path . 'forms/' . $form_file . '.php', true, '', '', $error_msg);
				$layout.= "</div>";
				return $layout;
			}
		} else {
			if ($file_attr_exists) {
				emd_unset_file_upload();
			}
			if (isset($_SESSION['form_errors'])) {
				$form->errors = $_SESSION['form_errors'];
			}
			$layout = "<div style='position:relative;' class='emd-container'>";
			$layout.= $form->render($path . 'forms/' . $form_file . '.php', true);
			$layout.= "</div><!--container-end-->";
			return $layout;
		}
	} else {
		if ($file_attr_exists) {
			emd_unset_file_upload();
		}
		$layout = "<div style='position:relative;' class='emd-container'>";
		$layout.= $form->render($path . 'forms/' . $form_file . '.php', true);
		$layout.= "</div><!--container-end-->";
		return $layout;
	}
}
/**
 * Process Submit form, insert ent, attribute and tax and rel values
 *
 * @since WPAS 4.0
 * @param string $myapp
 * @param string $myentity
 * @param string $post_status
 * @param string $form
 *
 * @return array $ret
 */
function emd_submit_form($myapp, $myentity, $post_status, $visitor_post_status, $form) {
	$user_conf = Array();
	$entity_post = Array();
	$entity_fields = Array();
	$txn_fields = Array();
	$rel_fields = Array();
	$user_email_val = "";
	$myattr_list = Array();
	$mytxn_list = Array();
	$myrel_list = Array();
	$default_list = Array();
	$default_txns = Array();
	$blts = Array(
		'blt_title' => '',
		'blt_content' => '',
		'blt_excerpt' => ''
	);
	$attr_list = get_option($myapp . '_attr_list', Array());
	$txn_list = get_option($myapp . '_tax_list', Array());
	$rel_list = get_option($myapp . '_rel_list', Array());
	if(!empty($txn_list[$myentity])){
		foreach ($txn_list[$myentity] as $mykey => $mytxn) {
			if (!empty($mytxn['default'])) {
				$default_txns[$mykey] = $mytxn['default'];
			}
		}
	}
	foreach ($attr_list[$myentity] as $mykey => $myattr) {
		if (isset($myattr['default'])) {
			$default_list[$mykey] = $myattr['default'];
		}
	}
	if (isset($attr_list[$myentity])) {
		$myattr_list = array_keys($attr_list[$myentity]);
	}
	if (isset($txn_list[$myentity])) {
		$mytxn_list = array_keys($txn_list[$myentity]);
	}
	if (isset($rel_list)) {
		$myrel_list = array_keys($rel_list);
	}
	foreach ($_POST as $postkey => $mypost) {
		if (!empty($mypost) && !is_array($mypost)) {
			$mypost = sanitize_text_field(urldecode($mypost));
			$mypost = html_entity_decode($mypost);
		}
		if (!empty($mypost)) {
			if (in_array($postkey, $mytxn_list)) {
				$txn_fields[$postkey] = $mypost;
			} elseif (in_array($postkey, $myrel_list)) {
				$postkey = preg_replace("/rel_/", "", $postkey, 1);
				$rel_fields[$postkey] = $mypost;
				if (isset($user_conf['email_type']) && $user_conf['email_type'] == 'rel' && $user_conf['email_field'] != '') {
					$email_arr = explode("__rel__", $user_conf['email_field']);
					if ($email_arr[0] == $postkey) {
						if (is_array($mypost)) {
							foreach ($mypost as $mypostid) {
								$user_email_val[$mypostid] = get_post_meta($mypostid, $email_arr[1], true);
							}
						} else {
							$user_email_val[$mypost] = get_post_meta($mypost, $email_arr[1], true);
						}
					}
				}
			} elseif (in_array($postkey, $myattr_list)) {
				$entity_fields[$postkey] = $mypost;
				if (isset($user_conf['email_type']) && $user_conf['email_type'] == 'ent' && $user_conf['email_field'] != '' && $postkey == $user_conf['email_field']) {
					$user_email_val[$postkey] = $mypost;
				}
			} elseif (in_array($postkey, Array(
				'blt_title',
				'blt_content',
				'blt_excerpt'
			))) {
				$blts[$postkey] = $mypost;
			}
		}
	}
	$entity_post['post_type'] = $myentity;
	$published_cap = get_post_type_object($myentity)->cap->edit_published_posts;
	$current_user_id = get_current_user_id();

        if (current_user_can($published_cap)) {
		$entity_post['post_status'] = $post_status;
		$entity_post['post_author'] = $current_user_id;
	}
	else {
		$entity_post['post_status'] = $visitor_post_status;
		$entity_post['post_author'] = $current_user_id;
	}
	if (!empty($blts)) {
		foreach ($blts as $blt_key => $blt_val) {
			$key = str_replace("blt_", "post_", $blt_key);
			$entity_post[$key] = $blt_val;
		}
	}
	if (empty($entity_post['post_title'])) {
		$wpas_ent_list = get_option($myapp . '_ent_list');
		if (!empty($wpas_ent_list[$myentity]['unique_keys'])) {
			$uniq_keys = $wpas_ent_list[$myentity]['unique_keys'];
			$new_title = '';
			foreach ($uniq_keys as $mykey) {
				if($entity_fields[$mykey] != 'emd_autoinc'){
					$new_title.= $entity_fields[$mykey] . " - ";
				}
			}
			$entity_post['post_title'] = rtrim($new_title, ' - ');
			$blts['blt_title'] = $entity_post['post_title'];
		}
	}
	$id = wp_insert_post($entity_post);
	if(empty($entity_post['post_title'])){
		wp_update_post(Array('ID' => $id,'post_title'=>$id));
	}
	if (!empty($id)) {
		if (!empty($default_list)) {
			foreach ($default_list as $def_key => $def_value) {
				if (!in_array($def_key, array_keys($entity_fields))) {
					$def_value_arr = explode(",", $def_value);
					if (count($def_value_arr) > 1) {
						foreach ($def_value_arr as $dvalue) {
							$dvalue = rtrim($dvalue, "'");
							$dvalue = ltrim($dvalue, "'");
							add_post_meta($id, $def_key, $dvalue);
						}
					} else {
						$def_value = rtrim($def_value, "'");
						$def_value = ltrim($def_value, "'");
						add_post_meta($id, $def_key, $def_value);
					}
				}
			}
		}
		foreach ($entity_fields as $meta_key => $meta_value) {
			if ($meta_value == 'emd_uid') {
				$meta_value = uniqid($id, false);
			}
			elseif($meta_value == 'emd_autoinc'){
				$autoinc_start = $attr_list[$myentity][$meta_key]['autoinc_start'];
				$autoinc_incr = $attr_list[$myentity][$meta_key]['autoinc_incr'];
				$meta_value = get_option($meta_key . "_autoinc",$autoinc_start);
				$new = $meta_value + $autoinc_incr;
				update_option($meta_key . "_autoinc", $new);
			}
			if (is_array($meta_value)) {
				foreach ($meta_value as $mvalue) {
					add_post_meta($id, $meta_key, $mvalue);
				}
			} else {
				$meta_value = emd_translate_date_format($attr_list[$myentity][$meta_key], $meta_value);
				add_post_meta($id, $meta_key, $meta_value);
			}
		}
		if (!empty($default_txns)) {
			foreach ($default_txns as $def_key => $def_value) {
				if (!in_array($def_key, array_keys($txn_fields))) {
					foreach ($def_value as $dvalue) {
						$def = get_term_by('name', $dvalue, $def_key);
						if (!empty($def)) {
							$new_def_value[] = $def->term_id;
						}
					}
					wp_set_object_terms($id, $new_def_value, $def_key);
				}
			}
		}
		foreach ($txn_fields as $txn_key => $txn_value) {
			wp_set_object_terms($id, $txn_value, $txn_key);
		}
		foreach ($rel_fields as $rel_key => $rel_value) {
			if (!empty($rel_value)) {
				if (is_array($rel_value)) {
					foreach ($rel_value as $rvalue) {
						if(!empty($rvalue)){
							p2p_type($rel_key)->connect($rvalue, $id);
						}
					}
				} else {
					p2p_type($rel_key)->connect($rel_value, $id);
				}
			}
		}
		if (!empty($form->file_upload)) {
			$emd_file_upload = wp_upload_dir();
			foreach ($form->file_upload as $key_upload => $uploads) {
				if (is_array($uploads)) {
					foreach ($uploads as $myfileupload) {
						if (isset($myfileupload['path']) && $myfileupload['error'] == 0) {
							if(!$myfileupload['type']){
								$filetype = wp_check_filetype(basename($myfileupload['name']));
								$pmtype = $filetype['type'];
							}
							else {
								$pmtype = $myfileupload['type'];
							}
							$guid = $emd_file_upload['url'] . '/' . $myfileupload['name'];
							$attachment = array(
								'post_mime_type' => $pmtype,
								'guid' => $guid,
								'post_title' => basename($myfileupload['name']) ,
								'post_content' => '',
								'post_status' => 'inherit',
							);
							require_once (ABSPATH . 'wp-admin/includes/image.php');
							$insert_id = wp_insert_attachment($attachment, $myfileupload['path'], $id);
							if (!is_wp_error($insert_id)) {
								wp_update_attachment_metadata($insert_id, wp_generate_attachment_metadata($insert_id, $myfileupload['path']));
								// Save file ID in meta field
								add_post_meta($id, $key_upload, $insert_id, false);
							}
						}
					}
				}
			}
		}
		$ret['id'] = $id;
		$ret['user_send_to'] = $user_email_val;
		$ret['rel_uniqs'] = $rel_fields;
		if (!empty($blts)) {
			$ret['blts'] = $blts;
		}
		$ret['blts']['post_author'] = $entity_post['post_author'];
		return $ret;
	}
	return false;
}
/**
 * Create where clause for builtins
 *
 * @since WPAS 4.0
 * @param string $where
 * @param object $wp_query
 *
 * @return string $where
 */
function emd_builtin_posts_where($where, &$wp_query) {
	global $wpdb, $blts;
	if (!empty($blts)) {
		foreach ($blts as $bltkey => $bltval) {
			$key = str_replace('blt_', '', $bltkey);
			$where.= ' AND ' . $wpdb->posts . '.post_' . $key . ' ' . $wp_query->get('opr__' . $bltkey) . ' \'' . esc_sql($wp_query->get($bltkey));
			if ($wp_query->get('opr__' . $bltkey) == 'LIKE' || $wp_query->get('opr__' . $bltkey) == 'NOT LIKE') {
				$where.= '%';
			}
			$where.= '\'';
		}
	}
	return $where;
}
function emd_get_form_req_hide_vars($app,$fname){
	$shc_list = get_option($app . '_shc_list');
	$post_type = $shc_list['forms'][$fname]['ent'];
	$attr_list = get_option($app . '_attr_list');
	$glob_forms_list = get_option($app . '_glob_forms_list');
	$req_arr= Array();
	$hide_arr= Array();
	if(!empty($glob_forms_list[$fname])){
		foreach($glob_forms_list[$fname] as $fkey => $fval){
			if(!empty($fval['req']) && $fval['req'] == 1){
				if(!empty($attr_list[$post_type][$fkey]) && $attr_list[$post_type][$fkey]['display_type'] == 'checkbox'){
					$req_arr[] = $fkey . "_1";
				}
				elseif($fkey != 'btn') {
					$req_arr[] = $fkey;
				}
			}
			if(!empty($fval['show']) && $fval['show'] == 0){
				$hide_arr[] = $fkey;
			}
		}
	}
	$ret['req'] = $req_arr;
	$ret['hide'] = $hide_arr;
	return $ret;
}
