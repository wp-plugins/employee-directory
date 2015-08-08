<?php
/**
 * Common Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
global $wp_version;
/**
 * Generates hidden func value
 *
 * @since WPAS 4.0
 * @param string $hidden_func
 *
 * @return string $val
 */
function emd_get_hidden_func($hidden_func) {
	global $current_user;
	get_currentuserinfo();
	$val = "";
	switch ($hidden_func) {
		case 'user_login':
			$val = $current_user->user_login;
		break;
		case 'user_email':
			$val = $current_user->user_email;
		break;
		case 'user_firstname':
			$val = $current_user->user_firstname;
		break;
		case 'user_lastname':
			$val = $current_user->user_lastname;
		break;
		case 'user_displayname':
			$val = $current_user->display_name;
		break;
		case 'user_id':
			$val = $current_user->ID;
		break;
		case 'date_mm_dd_yyyy':
			//$val = date("m-d-Y");
			$val = date("Y-m-d");
		break;
		case 'date_dd_mm_yyyy':
			//$val = date("d-m-Y");
			$val = date("Y-m-d");
		break;
		case 'current_year':
			$val = date("Y");
		break;
		case 'current_month':
			$val = date("F");
		break;
		case 'current_month_num':
			$val = date("m");
		break;
		case 'current_day':
			$val = date("d");
		break;
		case 'now':
			$val = date("Y-m-d H:i:s");
		break;
		case 'current_time':
			$val = date("H:i:s");
		break;
		case 'user_ip':
			$val = $_SERVER['REMOTE_ADDR'];
		break;
		case 'unique_id':
			$val = 'emd_uid';
		break;
		case 'autoinc':
			$val = 'emd_autoinc';
		break;
	}
	if ($current_user->ID == 0 && $val == '') {
		$val = "Visitor";
	}
	return $val;
}
/**
 * Sets query parameters for author and search on frontend
 *
 * @since WPAS 4.0
 * @param string $app
 * @param object $query
 * @param bool $has_limit_by
 *
 * @return object $query
 */
function emd_limit_author_search($app, $query, $has_limitby = 0) {
	$current_ptype = isset($query->query_vars['post_type']) ? $query->query_vars['post_type'] : Array();
	$cap_post_types = get_post_types();
	foreach ($cap_post_types as $ptype) {
		if (!in_array($ptype, Array(
			'attachment',
			'revision',
			'nav_menu_item'
		)) && current_user_can('edit_' . $ptype . 's')) {
			$set_types[] = $ptype;
		}
	}
	if (!empty($set_types)) {
		$latest_ptypes = array_unique(array_merge($current_ptype, $set_types));
		$query->set('post_type', array_values($latest_ptypes));
		if ($has_limitby == 1) {
			$pids = Array();
			foreach (array_values($latest_ptypes) as $ptype) {
				$pids = apply_filters('emd_limit_by', $pids, $app, $ptype);
			}
			$query->set('post__in', $pids);
		}
	}
	return $query;
}
/**
 * Has_shortcode func if wp version is < 3.6
 *
 * @since WPAS 4.0
 * @param string $content
 * @param string $shc
 *
 * @return bool
 */
if (version_compare($wp_version, "3.6", "<")) {
	function has_shortcode($content, $shc) {
		global $shortcode_tags;
		if (array_key_exists($shc, $shortcode_tags)) {
			preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
			if (empty($matches)) return false;
			foreach ($matches as $shortcode) {
				if ($shc === $shortcode[2]) return true;
			}
		}
		return false;
	}
}
/**
 * Parse and replace template tags with values in email subject and messages
 *
 * @since WPAS 4.3
 * moved from notify-actions file
 *
 * @param string $app
 * @param string $message
 * @param string $pid
 *
 * @param string $message
 */
function emd_parse_template_tags($app, $message, $pid) {
	global $wpdb;
	$rel_list = get_option($app . "_rel_list");
	$mypost = get_post($pid);
	$permlink = get_permalink($pid);
	$access_views = get_option($app . "_access_views");
	if (!empty($access_views['single'])) {
		foreach ($access_views['single'] as $single) {
			if ($single['obj'] == $mypost->post_type) {
				$permlink = wp_login_url(esc_url(add_query_arg('fr_emd_notify',1,get_permalink($pid))));
			}
		}
	}
	if (in_array($mypost->post_status, Array(
		'pending',
		'draft'
	))) {
		$preview_link = add_query_arg('preview', 'true', get_permalink($pid));
		$permlink = wp_login_url(add_query_arg('fr_emd_notify',1,$preview_link));
	}

	$builtins = Array(
		'title' => $mypost->post_title,
		'permalink' => $permlink,
		'edit_link' => get_edit_post_link($pid) ,
		'delete_link' => esc_url(add_query_arg('frontend', 'true', get_delete_post_link($pid))) ,
		'excerpt' => $mypost->post_excerpt,
		'content' => $mypost->post_content,
		'author_dispname' => get_the_author_meta('display_name',$mypost->post_author),
		'author_nickname' => get_the_author_meta('nickname',$mypost->post_author),
		'author_fname' => get_the_author_meta('first_name',$mypost->post_author),
		'author_lname' => get_the_author_meta('last_name',$mypost->post_author),
		'author_login' => get_the_author_meta('user_login',$mypost->post_author),
		'author_bio' => get_the_author_meta('description',$mypost->post_author),
		'author_googleplus' => get_the_author_meta('googleplus',$mypost->post_author),
		'author_twitter' => get_the_author_meta('twitter',$mypost->post_author),
	);

	$glob_list = get_option($app . "_glob_list");
	if(!empty($glob_list)){
		foreach($glob_list as $kglob => $vglob){
			$globs[$kglob] = emd_glob_val($app,$kglob);
		}
	}
	
	//first get each template tag
	if (preg_match_all('/\{([^}]*)\}/', $message, $matches)) {
		foreach ($matches[1] as $match_tag) {
			//replace if builtin
			if (in_array($match_tag, array_keys($builtins))) {
				$message = str_replace('{' . $match_tag . '}', $builtins[$match_tag], $message);
			} elseif (!empty($globs) && in_array($match_tag, array_keys($globs))) {
				$message = str_replace('{' . $match_tag . '}', $globs[$match_tag], $message);
			} elseif (preg_match('/^wpas_/', $match_tag)) {
				$message = str_replace('{' . $match_tag . '}', emd_mb_meta($match_tag, array() , $pid) , $message);
			} elseif (preg_match('/^emd_/', $match_tag)) {
				$new = emd_get_attr_val($app, $pid, $mypost->post_type, $match_tag);
				$message = str_replace('{' . $match_tag . '}', $new, $message);
			} elseif (preg_match('/^com_/', $match_tag)) {
				$new_match_tag = preg_replace('/^com_/', '', $match_tag);
				$mycomments = get_comments(array(
					'post_id' => $pid,
					'type' => $new_match_tag,
					'status'=> 'approve',
				));
				$new = '<ul class="commentlist">';
				foreach($mycomments as $mycomm){
					$new .= "<li><div>" . __("Author:","emd-plugins") . $mycomm->comment_author . "&nbsp;&nbsp;";
					$new .= get_comment_date(sprintf(__('%s \a\t %s', 'emd-plugins'),get_option('date_format'),get_option('time_format')),$mycomm->comment_ID) . "</div>";
					$new .= "<p>" . $mycomm->comment_content . "</p>";
					$new .= "</li>";
				}
                		$new .= '</ul>';
				$message = str_replace('{' . $match_tag . '}', $new, $message);
			} elseif (preg_match('/^rel_/', $match_tag)) {
				$new_rel = "";
				$new_match_tag = preg_replace('/^rel_/', '', $match_tag);
				$myrel = $rel_list[$match_tag];
				$from_to = "from";
				$other = "to";
				if ($myrel['from'] == $mypost->post_type) {
					$from_to = "to";
					$other = "from";
				}
				$conns = $wpdb->get_results("SELECT p2p_" . $from_to . " as pid FROM {$wpdb->p2p} WHERE p2p_type='" . $new_match_tag . "' AND p2p_" . $other . "='" . $pid . "'", ARRAY_A);
				foreach ($conns as $mycon) {
					$rpost = get_post($mycon['pid']);
					$new_rel.= $rpost->post_title . ",";
				}
				$new_rel = rtrim($new_rel, ",");
				$message = str_replace('{' . $match_tag . '}', $new_rel, $message);
			} else {
				if (preg_match('/_nl$/', $match_tag)) {
					$new_match_tag = preg_replace('/_nl$/', '', $match_tag);
					$new = strip_tags(get_the_term_list($pid, $new_match_tag, '', ' ', ''));
				} else {
					$new = get_the_term_list($pid, $match_tag, '', ' ', '');
				}
				if (!is_wp_error($new)) {
					$message = str_replace('{' . $match_tag . '}', $new, $message);
				}
			}
		}
	}
	return $message;
}
/**
 * Get attribute value by attribute type
 *
 * @since WPAS 4.3
 * moved from notify-actions file
 *
 * @param string $app
 * @param string $pid
 * @param string $ptype
 * @param string $attr_id
 *
 * @return string $val
 *
 */
function emd_get_attr_val($app, $pid, $ptype, $attr_id) {
	$attr_list = get_option($app . "_attr_list");
	$val = "";
	$mult = 0;
	if(!empty($attr_list[$ptype][$attr_id])){
		$attr = $attr_list[$ptype][$attr_id];
		$dtype = $attr['display_type'];
		if (isset($attr['multiple'])) {
			$mult = 1;
		}
		if ($dtype == 'checkbox_list' || $dtype == 'select' && $mult == 1) {
			$emd_mb_list = emd_mb_meta($attr_id, 'type=checkbox_list', $pid);
			if (!empty($emd_mb_list)) {
				$val = implode(', ', $emd_mb_list);
			}
		} elseif ($dtype == 'file') {
			$emd_mb_file = emd_mb_meta($attr_id, 'type=file', $pid);
			if (!empty($emd_mb_file)) {
				foreach ($emd_mb_file as $info) {
					$val.= "<a href='" . $info['url'] . "' title='" . $info['title'] . "'>" . $info['name'] . "</a><br/>";
				}
			}
		} elseif (in_array($dtype, Array(
			'image',
			'plupload_image',
			'thickbox_image'
		))) {
			$images = emd_mb_meta($attr_id, 'type=plupload_image', $pid);
			if (!empty($images)) {
				foreach ($images as $image) {
					$val.= "<a href='" . $image['full_url'] . "' title='" . $image['title'] . "' rel=\'thickbox\'>
						<img src='" . $image['url'] . "' width='" . $image['width'] . "' height='" . $image['height'] . "' alt='" . $image['alt'] . "'/></a>";
				}
			}
		} elseif (in_array($dtype, Array(
			'date',
			'datetime',
			'time'
		))) {
			$val = emd_translate_date_format($attr, emd_mb_meta($attr_id, array() , $pid) , 1);
		} else {
			$val = emd_mb_meta($attr_id, array() , $pid);
		}
	}
	return $val;
}
/**
 * Operator list for search moved from form-functions
 *
 * @since WPAS 4.3 
 * @param string $opr
 *
 * @return string $operator
 */
function emd_get_meta_operator($opr) {
	$operators['is'] = '=';
	$operators['is_not'] = '!=';
	$operators['like'] = 'LIKE';
	$operators['not_like'] = 'NOT LIKE';
	$operators['less_than'] = '<';
	$operators['greater_than'] = '>';
	$operators['less_than_eq'] = '<=';
	$operators['greater_than_eq'] = '>=';
	return $operators[$opr];
}
add_action('wp_ajax_emd_check_unique','emd_check_unique');
/**
 * Check unique keys
 *
 * @since WPAS 4.0
 *
 * @return bool $response
 */
function emd_check_unique() {
	$response = false;
	$post_id = '';
	$form_data = isset($_GET['data_input']) ? $_GET['data_input'] : '';
	$post_type = isset($_GET['ptype']) ? $_GET['ptype'] : '';
	$myapp = isset($_GET['myapp']) ? $_GET['myapp'] : '';
	$ent_list = get_option($myapp . "_ent_list");
	$uniq_fields = $ent_list[$post_type]['unique_keys'];
	if(!is_array($form_data)){
		parse_str(stripslashes($form_data),$form_arr);
	}
	else {
		$form_arr = $form_data;
	}
	foreach ($form_arr as $fkey => $myform_field) {
		if (in_array($fkey, $uniq_fields)) {
			$data[$fkey] = $myform_field;
		}
		if ($fkey == 'post_ID') {
			$post_id = $myform_field;
		}
	}
	if (!empty($data) && !empty($post_type)) {
		$response = emd_check_uniq_from_wpdb($data, $post_id, $post_type);
	}
	echo $response;
	die();
}
/**
 * Sql query to check unique keys
 *
 * @since WPAS 4.0
 *
 * @return bool $response
 */
function emd_check_uniq_from_wpdb($data, $post_id, $post_type) {
	global $wpdb;
	$where = "";
	$join = "";
	$count = 1;
	foreach ($data as $key => $val) {
		$join.= " LEFT JOIN " . $wpdb->postmeta . " pm" . $count . " ON p.ID = pm" . $count . ".post_id";
		$where.= " pm" . $count . ".meta_key='" . $key . "' AND pm" . $count . ".meta_value='" . $val . "' AND ";
		$count++;
	}
	$where = rtrim($where, "AND");
	$result_arr = $wpdb->get_results("SELECT p.ID FROM " . $wpdb->posts . " p " . $join . " WHERE " . $where . " p.post_type = '" . $post_type . "'", ARRAY_A);
	if (empty($result_arr)) {
		return true;
	} elseif (!empty($post_id) && $result_arr[0]['ID'] == $post_id) {
		return true;
	}
	return false;
}
/**
 * Show insert into post button for media uploads
 *
 * @since WPAS 4.5
 * @param array $args
 * @return array $args
 *
 */
function emd_media_item_args($args){
        $args['send'] = true;
        return $args;
}
