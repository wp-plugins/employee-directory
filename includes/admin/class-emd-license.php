<?php
/**
 * Emd License
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_License Class
 * Save license and call automatic updater
 *
 * @since WPAS 4.0
 */
class Emd_License {
	var $app = "";
	var $type = "";
	/**
	 * Instantiate license class
	 * Add filter and action for settings
	 * @since WPAS 4.0
	 *
	 * @param string $app
	 *
	 */
	public function __construct($app, $type, $name) {
		$this->app = $app;
		$this->type = $type;
		$this->name = $name;

		$this->settings();
	}

	/**
	 * Add this license to emd license list
	 * @since WPAS 4.2
	 *
	 * @return array $settings
	 */
	public function settings(){
		$settings = get_option('emd_license_settings',Array());
		$settings[$this->app] = Array('type' => $this->type,'name' => $this->name);
		update_option('emd_license_settings',$settings);
		if(get_option($this->app . '_license_status') === false){
			//check if any license saved
			$licenses = get_option('emd_licenses',Array());
			if(!empty($licenses)){
				$new_licenses = Array();
				foreach($licenses as $lkey => $lval){
					if(preg_match('/_license_status$/',$lkey)){
						$match = str_replace("_license_status","",$lkey);
						update_option($match . '_license_status',$lval);
					}
					else {
						$new_licenses[$lkey] = $lval;
					}
				}
				update_option('emd_licenses',$new_licenses);
			}
		}
	}
			
	/**
	 * Calls Edd software licensing to check for updates on author's site
	 * @since WPAS 4.2
	 *
	 */
	public function license_updater() {
		$emd_licenses = get_option('emd_licenses');
		$license_status = get_option($this->app . '_license_status');
		if($license_status === false || 'valid' !== $license_status) return;
		if (empty($emd_licenses[$this->app . '_license_key'])) return;
		$edd_updater = new EDD_SL_Plugin_Updater(constant(strtoupper($this->app) . '_EDD_STORE_URL') , constant(strtoupper($this->app) . '_PLUGIN_FILE') , array(
			'version' => constant(strtoupper($this->app) . '_VERSION') ,
			'license' => $emd_licenses[$this->app . '_license_key'],
			'item_name' => constant(strtoupper($this->app) . '_EDD_ITEM_NAME') ,
			'author' => constant(strtoupper($this->app) . '_AUTHOR')
		));
	}
}
