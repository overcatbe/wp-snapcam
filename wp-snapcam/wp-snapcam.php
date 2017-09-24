<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             0.1
 * @package           WP_Snapcam
 *
 * @wordpress-plugin
 * Plugin Name:       WP Snapcam
 * Plugin URI:        https://wordpress.org/plugins/wp-snapcam/
 * Description:       WP Snapcam allows your visitors to take a snap using webcam and send it to your WordPress server.
 * Version:           0.2
 * Author:            Infogérance Serveur
 * Author URI:        https://mnt-tech.fr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-snapcam
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define version, name and db_version
 */
define( 'WP_SNAPCAM_VERSION', '0.2' );
define( 'WP_SNAPCAM_NAME', 'WP_SNAPCAM' );
define( 'WP_SNAPCAM_DB_VERSION', '0.1' );

if ( ! defined( 'WP_SNAPCAM_LOAD_CSS' ) ) {
	define( 'WP_SNAPCAM_LOAD_CSS', true );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-snapcam-activator.php
 */
function activate_wp_snapcam() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-snapcam-activator.php';
	$wp_snapcam_activator = new WP_Snapcam_Activator();
	$wp_snapcam_activator->activate();
}
register_activation_hook( __FILE__, 'activate_wp_snapcam' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-snapcam-deactivator.php
 */
function deactivate_wp_snapcam() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-snapcam-deactivator.php';
	WP_Snapcam_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_wp_snapcam' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-snapcam.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1
 */
$wp_snapcam = new WP_Snapcam();
