<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @package    WP_Snapcam
 * @subpackage WP_Snapcam/public
 */
class WP_Snapcam_Public {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1
	 */
	public function __construct() {
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1
	 */
	public function enqueue_styles() {

		if ( WP_SNAPCAM_LOAD_CSS === true ) {
			wp_enqueue_style( WP_SNAPCAM_NAME, plugin_dir_url( __FILE__ ) . 'css/wp-snapcam-public.css', array(), WP_SNAPCAM_VERSION, 'all' );
		}
	}

	public function widget_init() {
		/**
		 * The class responsible for defining widget functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-snapcam-widget.php';
		register_widget( 'WP_Snapcam_Widget' );
	}

	public function thickbox_content() {
		/* Verify nonce before echoing content */
		if ( ! wp_verify_nonce( $_GET['wp-snapcam-thickbox-nonce'], 'wp-snapcam-thickbox-nonce' ) ) {
			exit();
		}	

	?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<style>
		* {
			margin: 0;
			padding: 0;
			background: #2c3e50;
		}
		#thickbox-container {
			padding: 20px;
			color: #fff;
		}
		#wp-snapcam-form {
			padding-top: 20px;
			color: #fff;
		}
		#webcamjs { margin: 0 auto; }
		#wp-snapcam-form label,
		#wp-snapcam-form input {
			border: 0;
			margin-bottom: 5px;
			display: block;
		}
		#wp-snapcam-form input {
			height: 25px;
			line-height: 25px;
			background: #fff;
			color: #000;
			padding: 0 6px;
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
			-moz-border-radius: 4px;
			-webkit-border-radius: 4px;
			border-radius: 4px;
			width: 100%;
		}
		#wp-snapcam-form #button {
			height: 30px;
			line-height: 30px;
			background: #e67e22;
			color: #fff;
			margin-top: 10px;
			cursor: pointer;
			-moz-border-radius: 4px;
			-webkit-border-radius: 4px;
			border-radius: 4px;
		}
		</style>
	</head>
	<body>
		<div id="thickbox-container">
			<div id="webcamjs"></div>
			<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) . 'webcamjs/webcam.min.js'; ?>"></script>
			<script language="JavaScript">
				Webcam.set({
					width: 320,
					height: 240,
					image_format: 'jpeg',
					jpeg_quality: 90,
					swfURL: '<?php echo plugin_dir_url( __FILE__ ) . 'webcamjs/webcam.swf'; ?>'
				});
				Webcam.attach( '#webcamjs' );
			</script>
			<form id="wp-snapcam-form" name="wp-snapcam-form">
				<div id="pre_take_buttons">
					<label for="snap-name"><?php _e( 'Name', 'wp-snapcam' ); ?></label>
					<input id="snap-name" name="snap-name" type="text" placeholder="<?php _e( 'John', 'wp-snapcam' ); ?>">
					<label for="snap-link"><?php _e( 'Website (not required)', 'wp-snapcam' ); ?></label>
					<input id="snap-link" type="url" placeholder="<?php _e( 'www.example.com', 'wp-snapcam' ); ?>" onchange="check_url(this)">
					<input id="snap-id" type="hidden" value="<?php echo uniqid(); ?>">
					<input id="button" type="button" value="<?php _e( 'Take snapshot', 'wp-snapcam' ); ?>" onClick="preview_snapshot()">
				</div>
				<div id="post_take_buttons" style="display:none">
				<input id="button" type="button" value="&lt; <?php _e( 'Take another', 'wp-snapcam' ); ?> ?" onClick="cancel_preview()">
				<input id="button" type="button" value="<?php _e( 'Send it', 'wp-snapcam' ); ?> ! &gt;" onClick="save_photo()" style="font-weight:bold;">
				</div>
			</form>
		
			<script language="JavaScript">
				var shutter = new Audio();
				shutter.autoplay = false;
				shutter.src = navigator.userAgent.match(/Firefox/) ? '<?php echo plugin_dir_url( __FILE__ ) . 'webcamjs/shutter.ogg'; ?>' : '<?php echo plugin_dir_url( __FILE__ ) . 'webcamjs/shutter.mp3'; ?>';
	
				function check_url( url ) {
					var string = url.value;
					if (!~string.indexOf("http")) {
						string = "http://" + string;
					}
					url.value = string;
					return url;
				}
		
				function preview_snapshot() {
					var name=document.forms["wp-snapcam-form"]["snap-name"].value;
					if ( name == "" ) {
						alert( "<?php _e( 'Add a name !', 'wp-snapcam' ); ?>" );
						return false;
					} else if ( name.length < 3 ) {
						alert( "<?php _e( 'Your name is too short. Min 3 characters.', 'wp-snapcam' ); ?>" );
						return false;
					} else if ( name.length > 40 ) {
						alert( "<?php _e( 'Your name is too long. Max 40 characters.', 'wp-snapcam' ); ?>" );
						return false;
					}
	
					var link=document.forms["wp-snapcam-form"]["snap-link"].value;
					if ( link != "" ) {
						if ( ! /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test( link ) ) {
							alert( "<?php _e( 'Website is invalid.', 'wp-snapcam' ); ?>" );
							return false;
						} else if ( link.length > 200 ) {
							alert( "<?php _e( 'Website is too long. Max 200 characters.', 'wp-snapcam' ); ?>" );
							return false;
						}
					}
		
					shutter.play();
					Webcam.freeze();
					document.getElementById('pre_take_buttons').style.display = 'none';
					document.getElementById('post_take_buttons').style.display = '';
				}
				
				function cancel_preview() {
					Webcam.unfreeze();
					document.getElementById('pre_take_buttons').style.display = '';
					document.getElementById('post_take_buttons').style.display = 'none';
				}
				
				function save_photo() {
					Webcam.snap( function(data_uri) {
						Webcam.on( 'uploadComplete', function(code, text) {
							if ( text == "snap_inserted" ) {
								self.parent.tb_remove();
								self.parent.location.reload();
							} else {
								alert ( "<?php _e( 'Something gone wrong, your snap was not properly sent. Try send it again. The error code was: ', 'wp-snapcam' ); ?>" + text );
							}
						} );
						var snap_name=document.forms["wp-snapcam-form"]["snap-name"].value;
						var snap_link=document.forms["wp-snapcam-form"]["snap-link"].value;
						Webcam.upload( data_uri, '<?php echo admin_url( 'admin-post.php'); ?>' + '?action=snap_add&snap_name=' + snap_name + '&snap_nonce=<?php echo wp_create_nonce( 'wp-snapcam' ); ?>' + '&snap_link=' + snap_link  + '&snap_id=<?php echo uniqid(); ?>' );
					} );
				}
			</script>
		</div>
	</body>
</html>
	<?php

	/* Kill WP after echoing this */
	exit;
	}

}
