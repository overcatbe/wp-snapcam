<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    WP_Snapcam
 * @subpackage WP_Snapcam/admin
 */
class WP_Snapcam_Admin {

	public function snap_add() {
		/* We try not to be fucked here */
		/* Nonce verification */
		if ( ! wp_verify_nonce( $_GET['snap_nonce'], 'wp-snapcam' ) ) {
			die( 'Security check failed on nonce' );
		}

		/* id verification */
		if ( ! isset( $_GET['snap_id'] ) && ! preg_match( '/^[a-z0-9]{13}$/', $id ) ) {
			die( 'Security check failed on id' );
		}

		/* Image verification */
		if ( ! exif_imagetype( $_FILES['webcam']['tmp_name'] ) == IMAGETYPE_JPEG ) {
			die( 'Security check failed on image' );
		}
		/* Name verification */
		if ( ! isset( $_GET['snap_name'] ) && ! strlen( $_GET['snap_name'] ) <= 40 ) {
			die( 'Security check failed on name' );
		}
		/* Link verification */
		if ( ! empty( $_GET['snap_link'] ) ) {
			if ( ! strlen( $_GET['snap_link'] ) <= 200 && ! filter_var( $_GET['snap_link'], FILTER_VALIDATE_URL ) ) {
				die( 'Security check failed on link' );
			}
		}

		/* Insert data in db and move image in uploads folder */
		$snap_id = esc_sql( $_GET['snap_id'] );
		$snap_date = date( 'Y-m-d H:i:s' );
		$snap_name = sanitize_text_field(  $_GET['snap_name'] );
		$wp_snapcam_settings = (array) get_option('wp_snapcam_settings');
		$wp_snapcam_options = (array) get_option('wp_snapcam_options');
		$snap_link = ( empty( $_GET['snap_link'] ) ) ? 'none' : esc_url_raw( $_GET['snap_link'] );
		$table = $wp_snapcam_settings['table'];

		/* Directory where the snap are located */
		$uploads = wp_get_upload_dir();
		$upload_dir = $uploads['basedir'] . '/wp-snapcam/';
		move_uploaded_file( $_FILES['webcam']['tmp_name'], $upload_dir . $snap_id . '.jpg' );

		/* If admin wants to moderate before display snaps */
		if ( $wp_snapcam_options['moderate'] === 0 ) {
			$display = 1;
		} else {
			$display = 0;
		}
		
		global $wpdb;
		$result = $wpdb->insert( 
			$table, 
			array( 
				'id' => $snap_id, 
				'date'    => $snap_date,
				'name'  => $snap_name,
				'link'    => $snap_link,
				'display' => $display
			), 
			array( 
				'%s', 
				'%s',
				'%s',
				'%s',
				'%d'
			) 
		);
		if ( $result === false ) {
			die( $wpdb->last_error  );
		} else {
			/* This string is required to close thickbox and reload page */
			echo 'snap_inserted';
			do_action( 'wp_snapcam_after_snap_inserted' );
			exit();
		}
	}

}
