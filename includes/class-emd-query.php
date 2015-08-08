<?php
/**
 * Emd Query
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Query Class
 *
 * Setup meta , tax and relationship query args for wp query
 *
 * @since WPAS 4.0
 */
class Emd_Query {
	var $args = Array();
	var $rel_args = Array();
	var $rel_posts = Array();
	var $entity = "";
	var $app = "";
	var $context = "";
	var $has_rel = 0;
	var $filter = Array();
	var $add_join_filter = 0;
	var $add_where_filter = 0;
	/**
	 * Instantiate emd query class
	 * Set entity and app names
	 * @since WPAS 4.0
	 *
	 * @param string $entity
	 * @param string $myapp
	 *
	 */
	public function __construct($entity, $myapp, $context='') {
		$this->entity = $entity;
		$this->app = $myapp;
		$this->context = $context;
	}
	/**
	 * Get filter list and create wp query args
	 * @since WPAS 4.0
	 *
	 * @param string $filter
	 *
	 */
	public function args_filter($filter) {
		$filter_list = explode(";", $filter);
		foreach ($filter_list as $myfilter) {
			if (!empty($myfilter)) {
				$field_list = explode("::", $myfilter);
				if (count($field_list) == 4) {
					switch ($field_list[0]) {
						case 'attr':
							$this->args['meta_query'][] = $this->get_meta_query($field_list);
						break;
						case 'tax':
							$this->args['tax_query'][] = $this->get_tax_query($field_list);
						break;
						case 'rel':
							$this->has_rel = 1;
							$this->get_rel_query($field_list);
						break;
					}
				}
			}
		}
		if (isset($this->args['meta_query']) && count($this->args['meta_query']) > 1) {
			$this->args['meta_query']['relation'] = 'AND';
		}
		if (isset($this->args['tax_query']) && count($this->args['tax_query']) > 1) {
			$this->args['tax_query']['relation'] = 'AND';
		}
		if ($this->has_rel == 1) {
			if (empty($this->args['post__in'])) {
				$this->args['post__in'] = Array(
					0
				);
			}
		}
		if($this->add_where_filter === 1){
			add_filter('posts_where',array($this,'get_meta_posts_where'),10,2);
		}
		elseif($this->add_join_filter === 1){
			add_filter('posts_join',array($this,'get_meta_posts_join'),10,2);
		}
	}
	/**
	 * Remove posts where and join filters
	 * @since WPAS 4.6
	 *
	 */
	public function remove_filters(){
		remove_filter('posts_where',array($this,'get_meta_posts_where'),10,2);
		remove_filter('posts_join',array($this,'get_meta_posts_join'),10,2);
	}
		
	/**
	 * Get fields list and create meta query
	 * @since WPAS 4.0
	 *
	 * @param array $field_list
	 *
	 * @return array $meta_query
	 */
	public function get_meta_query($field_list) {
		$ent_attrs = get_option($this->app . '_attr_list');
		$type = "char";
		$value = $field_list[3];
		$compare = emd_get_meta_operator($field_list[2]);
		if (isset($ent_attrs[$this->entity][$field_list[1]]['type'])) {
			$type = $ent_attrs[$this->entity][$field_list[1]]['type'];
		}
		if (in_array($type, Array(
			'date',
			'time',
			'datetime'
		))) {
			switch($value){
				case 'current_year':
					$start = date("Y-01-01");
					$end = date("Y-12-31");
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'last_year':
					$start = date("Y-m-d",strtotime("first day of January last year"));
					$end = date("Y-m-d",strtotime("last day of December last year"));
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'next_year':
					$start = date("Y-m-d",strtotime("first day of January next year"));
					$end = date("Y-m-d",strtotime("last day of December next year"));
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'current_month_year':
					$start = date("Y-m-01");
					$end = date("Y-m-t");
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'last_month_year':
					$start = date("Y-m-d",strtotime("first day of last month"));
					$end = date("Y-m-d",strtotime("last day of last month"));
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'next_month_year':
					$start = date("Y-m-d",strtotime("first day of next month"));
					$end = date("Y-m-d",strtotime("last day of next month"));
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'current_week_year':
					$start = date("Y-m-d",strtotime('monday this week'));
					$end = date("Y-m-d",strtotime('sunday this week'));
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'last_week_year':
					$start = date("Y-m-d",strtotime('monday last week'));
					$end = date("Y-m-d",strtotime('sunday last week'));
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'next_week_year':
					$start = date("Y-m-d",strtotime('monday next week'));
					$end = date("Y-m-d",strtotime('sunday next week'));
					$value = array($start,$end);
					$compare ="BETWEEN";
					$this->add_where_filter = 1;
					break;
				case 'current_date':
					$value = date("Y-m-d");
					$compare = emd_get_meta_operator($field_list[2]);
					$this->add_where_filter = 1;
					break;
				case 'yesterday':
					$value = date('Y-m-d', strtotime("-1 days"));
					$compare = emd_get_meta_operator($field_list[2]);
					$this->add_where_filter = 1;
					break;
				case 'tomorrow':
					$value = date('Y-m-d', strtotime("+1 days"));
					$compare = emd_get_meta_operator($field_list[2]);
					$this->add_where_filter = 1;
					break;
				case 'current_day_month':
					$this->add_join_filter = 1;
					$this->filter[] = $field_list;
					return Array();
					break;
				case 'current_week':
					$this->add_join_filter = 1;
					$this->filter[] = $field_list;
					return Array();
					break;
				case 'current_month':
					$this->add_join_filter = 1;
					$this->filter[] = $field_list;
					return Array();
					break;
				default:	
					$value = emd_translate_date_format($ent_attrs[$this->entity][$field_list[1]], $field_list[3]);
					$compare = emd_get_meta_operator($field_list[2]);
					break;
			}
		}
		if (is_array($value) && $compare != 'BETWEEN') {
			$compare = "IN";
		}
		$meta_query = array(
			'key' => $field_list[1],
			'value' => $value,
			'compare' => $compare,
			'type' => $type,
		);
		return $meta_query;
	}
	/**
	 * Add join query to meta query
	 * @since WPAS 4.6
	 *
	 * @param string $join
	 *
	 * @return string $join
	 */
	public function get_meta_posts_join($join, $wp_query){
		global $wpdb;
		if($wp_query->get('context') != $this->context) return;
		$join .= "JOIN " . $wpdb->postmeta . " ON  $wpdb->posts.ID = $wpdb->postmeta.post_id ";
		foreach($this->filter as $myfilter){
			$join .= " AND " . $wpdb->postmeta . ".meta_key='" . $myfilter[1] . "' AND ";
			switch($myfilter[3]){
				case 'current_week':
					$join .= " DAYOFYEAR(DATE_ADD(" . $wpdb->postmeta . ".meta_value, INTERVAL (YEAR(NOW()) - YEAR(" . $wpdb->postmeta . ".meta_value)) YEAR)) BETWEEN (DAYOFYEAR(CURDATE())+1-DAYOFWEEK(CURDATE())) AND (DAYOFYEAR(CURDATE())+7- DAYOFWEEK(CURDATE())) ";
					break;
				case 'current_day_month':
					$join .= " MONTH(" . $wpdb->postmeta . ".meta_value) = " . date('m') . " AND DAY(" . $wpdb->postmeta . ".meta_value) = " . date('d');
					break;
				case 'current_month':
					$join .= " MONTH(" . $wpdb->postmeta . ".meta_value) = " . date('m');
					break;
			}
		}
		return $join;
	}
	/**
	 * Add where query to meta query
	 * @since WPAS 4.6
	 *
	 * @param string $where
	 *
	 * @return string $where
	 */
	public function get_meta_posts_where($where, $wp_query){
		global $wpdb;
		if($wp_query->get('context') != $this->context) return;
		foreach($this->filter as $myfilter){
			$where .= " AND " . $wpdb->postmeta . ".meta_key='" . $myfilter[1] . "' AND ";
			switch($myfilter[3]){
				case 'current_week':
					$where .= " DAYOFYEAR(DATE_ADD(" . $wpdb->postmeta . ".meta_value, INTERVAL (YEAR(NOW()) - YEAR(" . $wpdb->postmeta . ".meta_value)) YEAR)) BETWEEN (DAYOFYEAR(CURDATE())+1-DAYOFWEEK(CURDATE())) AND (DAYOFYEAR(CURDATE())+7- DAYOFWEEK(CURDATE())) ";
					break;
				case 'current_day_month':
					$where .= " MONTH(" . $wpdb->postmeta . ".meta_value) = " . date('m') . " AND DAY(" . $wpdb->postmeta . ".meta_value) = " . date('d');
					break;
				case 'current_month':
					$where .= " MONTH(" . $wpdb->postmeta . ".meta_value) = " . date('m');
					break;
			}
		}
		return $where;
	}
	/**
	 * Get fields list and create tax query
	 * @since WPAS 4.0
	 *
	 * @param array $field_list
	 *
	 * @return array $tax_query
	 */
	public function get_tax_query($field_list) {
		$tax_query = array(
			'taxonomy' => $field_list[1],
			'field' => 'slug',
			'terms' => explode(',', $field_list[3]) ,
			'operator' => 'IN',
		);
		return $tax_query;
	}
	/**
	 * Get fields list and create post_in arg for query
	 * @since WPAS 4.0
	 *
	 * @param array $field_list
	 *
	 * @return array $args['post__in']
	 */
	public function get_rel_query($field_list) {
		if (!isset($this->args['post__in']) || (isset($this->args['post__in']) && !empty($this->args['post__in']))) {
			$rel_query = "";
			$this->rel_args['connected_type'] = $field_list[1];
			$this->rel_args['connected_items'] = explode(',', $field_list[3]);
			if (!empty($this->rel_posts)) {
				$this->rel_args['post__in'] = $this->rel_posts;
			}
			$this->rel_args['post_type'] = $this->entity;
			$this->rel_args['posts_per_page'] = '-1';
			$rel_list = get_option($this->app . '_rel_list');
			if($rel_list['rel_'.$field_list[1]]['from'] == $rel_list['rel_'.$field_list[1]]['to']){
				$this->rel_args['connected_direction'] = 'to';
			}
			$rel_query = new WP_Query($this->rel_args);
			$this->rel_posts = Array();
			if ($rel_query->have_posts()) {
				while ($rel_query->have_posts()) {
					$rel_query->the_post();
					$in_post_id = get_the_ID();
					if (!in_array($in_post_id, $this->rel_posts)) {
						$this->rel_posts[] = get_the_ID();
					}
				}
			}
			wp_reset_query();
			$this->args['post__in'] = $this->rel_posts;
		}
	}
}
