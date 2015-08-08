<?php
/**
 * Layout Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Retrieves a template part
 * @since WPAS 4.0
 *
 * Taken from bbPress,eaysdigitaldownloads
 *
 * @param string $app
 * @param string $slug
 * @param string $name Optional. Default null
 * @param bool   $load
 *
 * @return string
 *
 * @uses emd_locate_template()
 */
function emd_get_template_part($app, $slug, $name = null, $load = true) {
	// Setup possible parts
	$templates = array();
	if (isset($name)) $templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';
	// Allow template parts to be filtered
	$templates = apply_filters('emd_get_template_part', $templates, $slug, $name);
	// Return the part that is found
	return emd_locate_template($app, $templates, $load, false);
}
/**
 * Retrieves a template part
 * @since WPAS 4.0
 *
 * Taken from bbPress,eaysdigitaldownloads
 *
 * @param string $app
 * @param array $template_names
 * @param bool   $load
 * @param bool   $require_once
 *
 * @return string
 *
 * @uses load_template()
 */
function emd_locate_template($app, $template_names, $load = false, $require_once = true) {
	// No file found yet
	$located = false;
	// Try to find a template file
	foreach ((array)$template_names as $template_name) {
		// Continue if template is empty
		if (empty($template_name)) continue;
		// Trim off any slashes from the template name
		$template_name = ltrim($template_name, '/');
		$template_path = constant(strtoupper(str_replace("-", "_", $app)) . '_PLUGIN_DIR') . 'layouts/';
		if (file_exists($template_path . $template_name)) {
			$located = $template_path . $template_name;
			break;
		}
	}
	if ((true == $load) && !empty($located)) {
		load_template($located, $require_once);
	}
	return $located;
}
