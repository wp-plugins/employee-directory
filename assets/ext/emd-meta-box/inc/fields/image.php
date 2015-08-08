<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Image_Field' ) )
{
	class EMD_MB_Image_Field extends EMD_MB_File_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			// Enqueue same scripts and styles as for file field
			parent::admin_enqueue_scripts();

			wp_enqueue_style( 'emd-mb-image', EMD_MB_CSS_URL . 'image.css', array(), EMD_MB_VER );
			wp_enqueue_script( 'emd-mb-image', EMD_MB_JS_URL . 'image.js', array( 'jquery-ui-sortable' ), EMD_MB_VER, true );
		}

		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			// Do same actions as file field
			parent::add_actions();

			// Reorder images via Ajax
			add_action( 'wp_ajax_emd_mb_reorder_images', array( __CLASS__, 'wp_ajax_reorder_images' ) );
		}

		/**
		 * Ajax callback for reordering images
		 *
		 * @return void
		 */
		static function wp_ajax_reorder_images()
		{
			$field_id = isset( $_POST['field_id'] ) ? $_POST['field_id'] : 0;
			$order    = isset( $_POST['order'] ) ? $_POST['order'] : 0;
			$post_id  = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;

			check_ajax_referer( "emd-mb-reorder-images_{$field_id}" );

			parse_str( $order, $items );

			delete_post_meta( $post_id, $field_id );
			foreach ( $items['item'] as $item )
			{
				add_post_meta( $post_id, $field_id, $item, false );
			}
			wp_send_json_success();
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$i18n_title = apply_filters( 'emd_mb_image_upload_string', _x( 'Upload Images', 'image upload', 'emd-plugins' ), $field );
			$i18n_more  = apply_filters( 'emd_mb_image_add_string', _x( '+ Add new image', 'image upload', 'emd-plugins' ), $field );

			// Uploaded images
			$html = self::get_uploaded_images( $meta, $field );

			// Show form upload
			$html .= sprintf(
				'<h4>%s</h4>
				<div class="new-files">
					<div class="file-input"><input type="file" name="%s[]" /></div>
					<a class="emd-mb-add-file" href="#"><strong>%s</strong></a>
				</div>',
				$i18n_title,
				$field['id'],
				$i18n_more
			);

			return $html;
		}

		/**
		 * Get HTML markup for uploaded images
		 *
		 * @param array $images
		 * @param array $field
		 *
		 * @return string
		 */
		static function get_uploaded_images( $images, $field )
		{
			$reorder_nonce = wp_create_nonce( "emd-mb-reorder-images_{$field['id']}" );
			$delete_nonce = wp_create_nonce( "emd-mb-delete-file_{$field['id']}" );
			$classes = array( 'emd-mb-images', 'emd-mb-uploaded' );
			if ( count( $images ) <= 0  )
				$classes[] = 'hidden';
			$ul = '<ul class="%s" data-field_id="%s" data-delete_nonce="%s" data-reorder_nonce="%s" data-force_delete="%s" data-max_file_uploads="%s">';
			$html = sprintf(
				$ul,
				implode( ' ', $classes ),
				$field['id'],
				$delete_nonce,
				$reorder_nonce,
				$field['force_delete'] ? 1 : 0,
				$field['max_file_uploads']
			);

			foreach ( $images as $image )
			{
				$html .= self::img_html( $image );
			}

			$html .= '</ul>';

			return $html;
		}

		/**
		 * Get HTML markup for ONE uploaded image
		 *
		 * @param int $image Image ID
		 *
		 * @return string
		 */
		static function img_html( $image )
		{
			$i18n_delete = apply_filters( 'emd_mb_image_delete_string', _x( 'Delete', 'image upload', 'emd-plugins' ) );
			$i18n_edit   = apply_filters( 'emd_mb_image_edit_string', _x( 'Edit', 'image upload', 'emd-plugins' ) );
			$li = '
				<li id="item_%s">
					<img src="%s" />
					<div class="emd-mb-image-bar">
						<a title="%s" class="emd-mb-edit-file" href="%s" target="_blank">%s</a> |
						<a title="%s" class="emd-mb-delete-file" href="#" data-attachment_id="%s">&times;</a>
					</div>
				</li>
			';

			$src  = wp_get_attachment_image_src( $image, 'thumbnail' );
			$src  = str_replace('http:','',$src[0]);
			$link = get_edit_post_link( $image );

			return sprintf(
				$li,
				$image,
				$src,
				$i18n_edit, $link, $i18n_edit,
				$i18n_delete, $image
			);
		}

	}
}
