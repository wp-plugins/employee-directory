<?php
/**
 * Add-On Page Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.2
 */
if (!defined('ABSPATH')) exit;
/**
 * Show emdplugins plugins and extensions
 *
 * @param string $textdomain
 * @since WPAS 4.2
 *
 * @return html page content
 */
function emd_display_store($textdomain) {
	global $title;
	ob_start(); ?>
<div class="wrap">
<h2><?php echo $title;?> &nbsp;&mdash;&nbsp;<a href="https://emdplugins.com/plugins?pk_source=plugin-addons-page&pk_medium=plugin&pk_campaign=<?php echo $textdomain;?>-addonspage&pk_content=browseall" class="button-primary" title="<?php _e( 'Browse All', 'emd-plugins' ); ?>" target="_blank"><?php _e( 'Browse All', 'emd-plugins' ); ?></a>
</h2>
<p><?php _e('The following plugins extend and expand the functionality of your app.','emd-plugins'); ?></p>
                <?php echo emd_add_ons('addons'); ?>
        </div>
        <?php
        echo ob_get_clean();
}
/**
 * Show emdplugins designs
 *
 * @param string $textdomain
 * @since WPAS 4.3
 *
 * @return html page content
 */
function emd_display_design($textdomain) {
	global $title;
	ob_start(); ?>
<div class="wrap">
<h2><?php echo $title;?> &nbsp;&mdash;&nbsp;<a href="https://emdplugins.com/designs?pk_source=plugin-designs-page&pk_medium=plugin&pk_campaign=<?php echo $textdomain;?>-designpage&pk_content=browseall" class="button-primary" title="<?php _e( 'Browse All', 'emd-plugins' ); ?>" target="_blank"><?php _e( 'Browse All', 'emd-plugins' ); ?></a>
</h2>
<p><?php printf(__('The following <a href="%s" title="WP App Studio Prodev" target="_blank">WP App Studio Prodev</a> plugin designs can be used as a template:','emd-plugins'),'https://wpas.emdplugins.com?pk_source=wpas-design-page&pk_medium=plugin&pk_campaign=wpas-design&pk_content=prodevlink');?>
<ul><li><span class="dashicons dashicons-yes"></span>
<?php _e('To customize the functionality of their corresponding plugins','wpas'); ?>
</li>
<li><span class="dashicons dashicons-yes"></span>
<?php _e('To create your own plugin','wpas');?>
</li>
</ul>
</p>
                <?php echo emd_add_ons('plugin-designs'); ?>
        </div>
        <?php
        echo ob_get_clean();
}
/**
 * Get plugin and extension list from emdplugins site and save it in a transient
 *
 * @since WPAS 4.2
 *
 * @return $cache html content
 */
function emd_add_ons($type) {
        //if ( false === ( $cache = get_transient( 'emd_store_feed' ) ) ) {
                $feed = wp_remote_get( 'https://emd-plugin-site.s3.amazonaws.com/' . $type . '.html');
                if ( ! is_wp_error( $feed ) ) {
                        if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
                                $cache = wp_remote_retrieve_body( $feed );
                                //set_transient( 'emd_store_feed', $cache, 3600 );
                        }
                } else {
                        $cache = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'emd-plugins' ) . '</div>';
                }
        //}
        return $cache;
}
/**
 * Show support info
 *
 * @param string $textdomain
 * @since WPAS 4.3
 *
 * @return html page content
 */
function emd_display_support($textdomain,$show_review,$rev=''){
	global $title;
	ob_start(); ?>
	<div class="wrap">
	<h2><?php echo $title;?></h2>
	<div id="support-header"><?php printf(__('Thanks for installing %s.','emd-plugins'),constant(strtoupper(str_replace("-", "_", $textdomain)) . '_NAME'));?> &nbsp; <?php  printf(__('All support requests are accepted through <a href="%s" target="_blank">our support site.</a>','emd-plugins'),'https://support.emarketdesign.com?pk_source=wpas-support-page&pk_medium=plugin&pk_campaign=wpas-support&pk_content=supportlink'); ?>
<?php 
	switch($show_review){
		case '1':
		//if prodev or freedev generation
		emd_display_review('wp-app-studio');
		break;
		case '2':
		//eMarketDesign free plugin
		emd_display_review($rev);
		break;
		default:
		echo "<br></br>";
		break;
	}
	echo '</div>';
	echo emd_add_ons('plugin-support'); 
	echo '</div>';
	echo ob_get_clean();
}
function emd_display_review($plugin){
?>
<div id="plugin-review">
<div class="plugin-review-text"><a href="https://wordpress.org/support/view/plugin-reviews/<?php echo $plugin; ?>" target="_blank"><?php _e('Like our plugin? Leave us a review','emd-plugins'); ?></a>
</div><div class="plugin-review-star"><span class="dashicons dashicons-star-filled"></span>
<span class="dashicons dashicons-star-filled"></span>
<span class="dashicons dashicons-star-filled"></span>
<span class="dashicons dashicons-star-filled"></span>
<span class="dashicons dashicons-star-filled"></span>
</div>
</div>
<?php
}
