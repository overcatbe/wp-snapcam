<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      0.1
 * @package    WP_Snapcam
 * @subpackage WP_Snapcam/includes
 */
class WP_Snapcam {
	
	private static $_instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      WP_Snapcam_Loader
	 */
	protected $loader;

	/**
	 * All settings in one single array. 
	 *
	 * @since    0.1
	 * @access   public
	 * @var      array
	 */
    public $wp_snapcam_settings = array();

	/**
	 * All options in one single array. 
	 *
	 * @since    0.1
	 * @access   public
	 * @var      array
	 */
    public $wp_snapcam_options = array();

	/**
	 * All default options in one array. 
	 *
	 * @since    0.1
	 * @access   public
	 * @var      array
	 */
    public $default_options = array();

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1
	 */
	public function __construct( $default_options = null ) {
		$this->default_options = $default_options;
		$this->load_settings();
		$this->load_options();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_settings() {
		$this->wp_snapcam_settings = (array) get_option('wp_snapcam_settings');
	}

	private function load_options() {
		$this->wp_snapcam_options = (array) get_option('wp_snapcam_options');
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Snapcam_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Snapcam_i18n. Defines internationalization functionality.
	 * - WP_Snapcam_Admin. Defines all hooks for the admin area.
	 * - WP_Snapcam_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-snapcam-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-snapcam-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-snapcam-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-snapcam-menu.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-snapcam-about.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-snapcam-admin-gallery.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-snapcam-public-gallery.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Snapcam_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new WP_Snapcam_i18n();
		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$menu = new WP_Snapcam_Menu( $this );
		$admin = new WP_Snapcam_Admin();
		$about = new WP_Snapcam_About();

		add_action( 'admin_post_nopriv_snap_add', array( $admin, 'snap_add' ) );
		add_action( 'admin_post_snap_add', array( $admin, 'snap_add' ) );
		add_action( 'admin_menu', array( $menu, 'add_menu_page' ) );
		add_action( 'admin_init', array( $menu, 'register_settings' ) );
		add_action( 'admin_init', array( $about, 'redirect_about_page' ) );
		add_action( 'admin_menu', array( $about, 'add_about_page' ) );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function define_public_hooks() {
		$public = new WP_Snapcam_Public();
		$public_gallery = new WP_Snapcam_Public_Gallery( $this );
		add_action( 'wp_enqueue_scripts', array( $public, 'enqueue_styles' ) );
		add_action( 'widgets_init', array( $public, 'widget_init' ) );
		add_action( 'wp_ajax_nopriv_wp_snapcam_thickbox_content', array( $public, 'thickbox_content' ) );
		add_action( 'wp_ajax_wp_snapcam_thickbox_content', array ( $public, 'thickbox_content' ) );
		add_shortcode ( 'wp-snapcam', array( $public_gallery, 'display' ) );

		if ( isset( $_GET['wp-snapcam-thickbox']  ) && $_GET['wp-snapcam-thickbox'] === '1' ) {
			add_action( 'template_redirect', array( $public, 'thickbox_content' ) );
		}
	}

}
