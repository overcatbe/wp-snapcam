<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1
 * @package    WP_Snapcam
 * @subpackage WP_Snapcam/includes
 */
class WP_Snapcam_Activator {

	/**
	 * All options in one single array. 
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array
     */
	private $wp_snapcam_options;

	/**
	 * All settings in one single array. 
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array
     */
	private $wp_snapcam_settings;

	/**
	 * Object to acces WordPress database. 
	 *
	 * @since    0.1
	 * @access   private
	 * @var      object
     */
	private $db;

	/**
	 * Name of the table used by WP_Snapcam. 
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string
     */
	private $table;

	/**
	 * Name of the table posts with prefix (eg : wp_posts).
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string.
     */
	private	$posts;

	/**
	 * All default options in one single array.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string.
     */
	private	$default_options;

	/**
	 * Set some properties.
	 *
	 * @access public
	 * @return object
	 * @since 0.1
	 */
	public function __construct() {
		global $wpdb;
		$this->db =& $wpdb;
		$this->table = $this->db->prefix . "snapcam";
		$this->posts = $this->db->prefix . "posts";
		/* Include default options */
		require_once plugin_dir_path( __FILE__ ) . 'default-options.php';
		$this->default_options = $GLOBALS['wp_snapcam_default_options'];
	}

	/**
	 * Fired during WP_Snapcam activation
	 *
	 * Create table, create options
	 *
	 * @since	0.1
	 */
	public function activate() {
		$this->create_update_options();
		$this->create_update_settings();
		$this->create_update_db();
		$this->create_wp_snapcam_page();
		$this->create_upload_folder();
		$this->insert_first_snap();

		/** Set a transient to keep an admin notice after plugin activation */
		set_transient( 'wp-snapcam-new-activation', true, 300 );
	}

	/**
	 *  Create or update WP_Snapcam options
	 *
	 *  @return void
	 *  @since 0.1
	 */
	private function create_update_options() {

		/* load options registred if the plugin was previously installed */
		$wp_snapcam_options = (array) get_option( 'wp_snapcam_options' );

		/* We build array with only name and default out of $this->default_options */
		$default_options = array();
		foreach ( $this->default_options as $value ) {
			$default_options[$value['name']] = $value['default'];
		}

		/* Array of delault options is merged with possible previous saved options */
		$wp_snapcam_options = array_merge( $default_options, $wp_snapcam_options );

		/* Store or update options */
		update_option( 'wp_snapcam_options', $wp_snapcam_options );

		/* Set it for future use */
		$this->wp_snapcam_options = $wp_snapcam_options;
	}

	/**
	 *  Create WP_Snapcam settings.
	 *  An array containing, table, posts, version, name, gallery_id, db_version
	 *
	 *  @return void
	 *  @since 0.1
	 */
	private function create_update_settings() {
		/* Get old settings if possible */
		$wp_snapcam_settings = get_option( 'wp_snapcam_settings' );
		$wp_snapcam_settings['table'] = $this->table;
		$wp_snapcam_settings['posts'] = $this->posts;
		$wp_snapcam_settings['version'] = WP_SNAPCAM_VERSION;
		$wp_snapcam_settings['name'] = WP_SNAPCAM_NAME;
		update_option('wp_snapcam_settings', $wp_snapcam_settings);

		/* Set it for future use */
		$this->wp_snapcam_settings = $wp_snapcam_settings;
	}

	/**
	 * Create or update db
	 *
	 * @return void
	 * @since 0.1
	 */
	private function create_update_db() {

		/* Explicitly set the character set and collation when creating the tables */
		$charset = ( defined( 'DB_CHARSET' && '' !== DB_CHARSET ) ) ? DB_CHARSET : 'utf8mb4';
		$collate = ( defined( 'DB_COLLATE' && '' !== DB_COLLATE ) ) ? DB_COLLATE : 'utf8mb4_general_ci';

		/* Table structure */
		$sql = "CREATE TABLE " . $this->table . " (
				id VARCHAR(13) NOT NULL,
				date DATETIME NOT NULL,
				name VARCHAR(40) COLLATE utf8mb4_general_ci NOT NULL,
				link VARCHAR(200) DEFAULT 'none' NOT NULL,
				display TINYINT(1) NOT NULL,
				trashed TINYINT(1) DEFAULT 0 NOT NULL,
				UNIQUE KEY id (id)
				) ENGINE=InnoDB CHARACTER SET $charset COLLATE $collate;";

		/* Create or Update database table */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		/* Switch to full support of utf8mb4 if possible */
		maybe_convert_table_to_utf8mb4( $this->table );

		/* Update db_version */
		$this->wp_snapcam_settings += array( 'db_version' => WP_SNAPCAM_DB_VERSION);
		update_option('wp_snapcam_settings', $this->wp_snapcam_settings);

	}

	/**
	 * Create the default page containing the gallery if page doesn't already exists.
	 *
	 * @return void
	 * @since 0.1
	 */
	private function create_wp_snapcam_page() {

		/* Check if a page already holds the shortcode [wp-snapcam], if not, we create it */
		if ( is_null( $this->db->get_var("SELECT ID FROM $this->posts WHERE post_content LIKE '%[wp-snapcam]%' AND post_type='page' AND post_status <> 'trash'") ) ) {
			$page = array(
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_content'   => '[wp-snapcam]',
			'post_name'      => 'wp-snapcam-gallery',
			'post_status'    => 'publish',
			'post_title'     => __( 'WP Snapcam Gallery', 'wp-snapcam' ),
			'post_type'      => 'page',
			);

			/* Post the page and store its id */
			$page_id = wp_insert_post( $page );

			/* Store gallery_id in settings */
			$this->wp_snapcam_settings['gallery_id'] = $page_id;
			update_option( 'wp_snapcam_settings', $this->wp_snapcam_settings );
		}
	}

	private function create_upload_folder() {
		$uploads = wp_get_upload_dir();
		if ( ! is_dir( $uploads['basedir'] . '/wp-snapcam/' ) ) {
			mkdir( $uploads['basedir'] . '/wp-snapcam/' );
		}
	}

	/**
	 *  Insert the first snap in database if it's first time WP_Snapcam is activated.
	 *
	 *  @return void
	 *  @since 0.1
	 */
	private function insert_first_snap() {


		/* Check if there is noting in table */
		if ( is_null( $this->db->get_var("SELECT id from $this->table LIMIT 1") ) ) {

			/* Add the first snap in database */
			$snap_id = uniqid();
			$snap_date = date( 'Y-m-d H:i:s' );

			/* Copy the first snap to respect the naming convention */
			$uploads = wp_get_upload_dir();
			copy( WP_PLUGIN_DIR . '/wp-snapcam/img/wp-snapcam.jpg', $uploads['basedir'] . '/wp-snapcam/' . $snap_id . '.jpg' );

			/* Insert the first snap in database */
			$this->db->insert(
				$this->table,
				array(
					'id' => $snap_id,
					'date'	  => $snap_date,
					'name'	=> 'Emma',
					'link'	  => 'none',
					'display' => 1
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%d'
				)
			);
		}
	}

}
