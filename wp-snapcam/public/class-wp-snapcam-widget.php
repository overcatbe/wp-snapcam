<?php

/**
 * The widget class.
 *
 * This is used to manage the widget.
 *
 * @since      0.1
 * @package    WP_Snapcam
 * @subpackage WP_Snapcam/public
 */
class WP_Snapcam_Widget extends WP_Widget {

	/**
	 * All settings in one single array.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array
	 */
	private $wp_snapcam_settings;

	/**
	 * All options in one single array.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      WP_Snapcam
	 */
	private $wp_snapcam_options;

	/**
	 * The core function of this widget. Where we set title, description, settings
	 * and fire all methods to make it work.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct(
			/*  base ID of the widget */
			'wp-snapcam-widget',
			/* Widget name */
        	__( 'WP Snapcam Widget', 'wp-snapcam' ),
			/* Description */
			array ( 'description' => __( 'You need this widget to use WP Snapcam. It will display link to take a snap, last or random snap, link to the gallery.', 'wp-snapcam' ) )
		);
		$this->init();
	}

	private function init() {
		$this->wp_snapcam_options = (array) get_option( 'wp_snapcam_options' );
		$this->wp_snapcam_settings = (array) get_option( 'wp_snapcam_settings' );
		if ( $this->wp_snapcam_options['ajax'] === 1 ) {
			if ( ! is_admin() ) {
				wp_enqueue_script( 'jquery' );
			}
			add_action('wp_ajax_nopriv_widget_content', array($this, 'widget_content'));
			add_action('wp_ajax_widget_content', array($this, 'widget_content'));
		}
	}

	private function load_js_css() {
		wp_enqueue_script('jquery');
		add_thickbox();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget($args, $instance) {

		/* Scripts and CSS are loaded only when the widget is displayed */
		$this->load_js_css();

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			if ( $this->wp_snapcam_options['support_me'] === 1 ) {
				echo $args['before_title'] . '<a href="https://mnt-tech.fr/wp-snapcam/">' . apply_filters( 'widget_title', $instance['title'] ) . '</a>' . $args['after_title'];
			} else {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}
		}

		/* If widget is ajaxed print the js which will fill it lately
		 * else, print out the content directly calling widget_content()
		 */
		if ( $this->wp_snapcam_options['ajax'] === 1 ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery.post(
					<?php echo '"' . admin_url('admin-ajax.php') . '"'; ?>,
					{action : 'widget_content'},
						function( response ) {
							jQuery(response.widget_id).append(response.widget_content);
						}
					);
				});
			</script>
			<?php
		} else {
			$this->widget_content();
		}
		echo $args['after_widget'];
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'WP Snapcam', 'wp-snapcam' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}


	/**
	 * Echoes widget content.
	 *
	 * Used directly by widget() if widget is not using ajax and via
	 * wp_ajax add_action if widget is ajaxed
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @return void
	 */
	public function widget_content() {

		global $wpdb;
		$table = $this->wp_snapcam_settings['table'];

		if ( $this->wp_snapcam_options['widget_snap'] == 'random' ) {
			$snap = $wpdb->get_row( "SELECT * FROM $table WHERE display = 1 ORDER BY RAND() LIMIT 0, 1" );
		} else {
			$snap = $wpdb->get_row( "SELECT * FROM $table WHERE display = 1 ORDER BY date DESC LIMIT 0, 1 " );
		}

		$gallery_id = site_url() . "/?p=" . $this->wp_snapcam_settings['gallery_id'];
		$snap_id = $snap->id;

		$uploads = wp_get_upload_dir();
		$out = '<ul style="list-style-type: none;"><li><img src="' . $uploads['baseurl'] . '/wp-snapcam/' . $snap_id . '.jpg'  . '" alt="' . esc_html( $snap->name ) . '"/></li>';
		if ( ( $this->wp_snapcam_options['link'] == 'widget only' OR $this->wp_snapcam_options['link'] == 'both') AND $snap->link != "none") {
			$out .= '<li><a href="' . esc_html( $snap->link ) . '">' . esc_html( $snap->name ) . '</a></li>';
		} else {
			$out .= '<li>' . esc_html( $snap->name ) . '</li>';
		}
		$out .= '<li><a class="thickbox" href="' . site_url() . '/?wp-snapcam-thickbox=1&amp;wp-snapcam-thickbox-nonce=' . wp_create_nonce( 'wp-snapcam-thickbox-nonce' ) . '&amp;TB_iframe=true&amp;width=340&amp;height=440"' . ' title="' .esc_html( __( 'WP Snapcam', 'wp-snapcam' ) ) . '">' . __( 'Take a snap !', 'wp-snapcam' ) . '</a></li>';
		$out .= '<li><a href="' . $gallery_id . '">' . __( 'Gallery', 'wp-snapcam' ) . '</a></li>';
		$out .= '</ul>';

		/* If widget is ajaxed we send a json with content */
		if ( $this->wp_snapcam_options['ajax'] === 1 ) {
			$widget_id = '#' . $this->id;
			$array_response = array("widget_id" => $widget_id, "widget_content" => $out);
			$response = json_encode( $array_response );
			header( "Content-Type: application/json" );
			echo $response;
			exit;
		} else {
			echo $out;
		}
	}

}
?>
