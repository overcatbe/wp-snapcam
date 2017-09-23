<?php

/**
 * This class add all menu pages and populate options submenu.
 *
 * @since 0.1
 * @package    WP_Snapcam
 * @subpackage WP_Snapcam/admin
 */
class WP_Snapcam_Menu {

	private $wp_snapcam_options;
	private $default_options;
	private $wp_snapcam;

	public function __construct( $wp_snapcam ) {
		$this->wp_snapcam_options = $wp_snapcam->wp_snapcam_options; 
		$this->default_options = $wp_snapcam->default_options;
		$this->wp_snapcam = $wp_snapcam;
	}

	public function add_menu_page() {
		add_menu_page(
			__( 'WP Snapcam options', 'wp-snapcam' ),
			__( 'WP Snapcam', 'wp-snapcam' ),
			'activate_plugins',
			'wp-snapcam', array( $this, 'wp_snapcam_menu_options' )
		);
		add_submenu_page(
			'wp-snapcam',
			__( 'WP Snapcam options',
			'wp-snapcam' ),
			__( 'Options', 'wp-snapcam' ),
			'activate_plugins',
			'wp-snapcam'
		);
}

	// called during admin_init to register all settings
	public function register_settings() {
		register_setting( 'wp-snapcam', 'wp_snapcam_options', array( $this, 'sanitize_options' ) );
		add_settings_section( 'wp-snapcam-options', '', array( $this, 'settings_section' ), 'wp-snapcam' );

		/* Loop around all default options to add all settings fields */
		foreach ( $this->default_options as $key => $option ) {
			foreach ( $option as $key => $value ) {
				if ( $key === 'name' ) {
					add_settings_field(
						$option['name'],
						$option['short_description'],
						array( $this, 'settings_field' ),
						'wp-snapcam',
						'wp-snapcam-options',
						array(
							'name' => $option['name'],
							'type' => $option['type'],
							'long_description' => $option['long_description'],
							'values' => $option['values']
						)
					);
				}
			}
		}
	}

	public function sanitize_options( $args ) {
		/* Format specific to checkbox inside this foreach */
		foreach ( $this->default_options as $key => $option ) {
			if ( $option['type'] === 'checkbox' ) {
				if ( ! isset( $args[$option['name']] ) ) {
					/* When checkbox is unchecked, form send nothing so we have to set it to 0 manually */
					$args[$option['name']] = 0;
				} else {
					/* Form sends string for checkbox so we have to cast it to int */
					$args[$option['name']] = intval( $args[$option['name']] );
				}
			}
		}
		return $args;
	}

	public function settings_section( $args ) {
	}	

	public function settings_field( $args ) {
		switch ( $args['type'] ) {

			case 'checkbox':
				?>
				<input type="checkbox" name="wp_snapcam_options[<?php echo esc_attr( $args['name'] ); ?>]" value="1" <?php checked( $this->wp_snapcam_options[$args['name']], 1, true ); ?>/>
				<p class="description">
					<?php echo esc_html( $args['long_description'] ); ?>
				</p>
				<?php
				break;

			case 'number':
				?>
					<input type="number" class="small-text" name="wp_snapcam_options[<?php echo esc_attr( $args['name'] ); ?>]" value="<?php echo $this->wp_snapcam_options[$args['name']]; ?>" min="<?php echo $args['values']['min']; ?>" max="<?php echo $args['values']['max']; ?>" step="<?php echo $args['values']['step']; ?>">
				<p class="description">
					<?php echo esc_html( $args['long_description'] ); ?>
				</p>
				<?php
				break;

			case 'select':
				?>
				<select name="wp_snapcam_options[<?php echo esc_attr( $args['name'] ); ?>]">
				<?php foreach ( $args['values'] as $value ) { ?>
				 <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->wp_snapcam_options[$args['name']], $value, true ); ?>>
				 	<?php echo esc_html( $value ); ?>
				 </option>
				<?php } ?>
				 </select>
				<p class="description">
					<?php echo esc_html( $args['long_description'] ); ?>
				</p>
				<?php
				break;

			case 'radio':
				?>
				<fieldset><p>
				<?php foreach ( $args['values'] as $value ) { ?>
					<label><input type="radio" name="wp_snapcam_options[<?php echo esc_attr( $args['name'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $this->wp_snapcam_options[$args['name']], $value, true ); ?>><?php echo esc_html( $value );?></label></br>
				<?php } ?>
				 </select>
				</p></fieldset>
				<p class="description">
					<?php echo esc_html( $args['long_description'] ); ?>
				</p>
				<?php
				break;
				
		}
}

	public function wp_snapcam_menu_options() {
		/* Check if user can be here */
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.', 'wp-snapcam' ) );
		}

		/* Add updated notice when update done */
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'wp_snapcam_messages', 'wp_snapcam_message', __( 'Settings Saved', 'wporg' ), 'updated' );
		}
		settings_errors( 'wp_snapcam_messages' );

		echo '<div class="wrap">';
		$tab = isset($_GET['tab']) ? sanitize_key( $_GET['tab'] ) : 'general';
		if ($tab == 'general') {
			$this->manage_tabs();
			?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wp-snapcam' );
				do_settings_sections( 'wp-snapcam' );
				submit_button();
				?>
			</form>
		<?php
		}
	if ($tab == 'gallery') {
		$this->manage_tabs();
		?>
	<div class="wrap">
		<form method="get">
			<input type="hidden" name="page" value="<?php echo sanitize_key( $_REQUEST['page'] ); ?>" />
			<input type="hidden" name="tab" value="gallery" />
			<?php $wp_snapcam_admin_gallery = new WP_Snapcam_Admin_Gallery( $this->wp_snapcam ); ?>
			<?php $wp_snapcam_admin_gallery->prepare_items(); ?>
			<?php $wp_snapcam_admin_gallery->remove_parameters(); ?>
			<?php $wp_snapcam_admin_gallery->display(); ?>
		</form>
	</div>
		<?php

		}
		echo '</div>';
	}

	private function manage_tabs() {

		// Settings tabs
		$settings_tabs = array();
		$settings_tabs['general'] = __( 'General Settings', 'wp-snapcam' );
		$settings_tabs['gallery'] = __( 'Gallery', 'wp-snapcam' );
	
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $settings_tabs as $tab_key => $tab_caption ) {
			$active = ( $current_tab == $tab_key ) ? ' nav-tab-active' : '';
			echo '<a class="nav-tab' . $active . '" href="?page=wp-snapcam&amp;tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
	}

}
