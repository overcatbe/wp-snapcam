<?php

class WP_Snapcam_About {

	public function redirect_about_page() {
		/* Only admins need to see this */
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		/* This transient is set during activation */
		if ( ! get_transient( 'wp-snapcam-new-activation' ) ) {
			return;
		}

		/* Only one time so we delete the transient after redirection */
		delete_transient( 'wp-snapcam-new-activation' );
		wp_safe_redirect( admin_url( 'admin.php?page=wp-snapcam-about') );
		exit;
	}
	
	public function add_about_page() {
		add_submenu_page( 'wp-snapcam',
			__( 'About', 'wp-snapcam' ),
			__( 'About', 'wp-snapcam' ),
			'activate_plugins',
			'wp-snapcam-about',
			array( $this, 'about' )
		);
	}


	private function manage_tabs() {
		$settings_tabs = array();
		$settings_tabs['howtouseit'] = __( 'How to use it ?', 'wp-snapcam' );
		$settings_tabs['informations'] = __( 'Informations', 'wp-snapcam' );
		$settings_tabs['helpme'] = __( 'Help me', 'wp-snapcam' );

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'howtouseit';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $settings_tabs as $tab_key => $tab_caption ) {
			$active = ( $current_tab == $tab_key ) ? ' nav-tab-active' : '';
			echo '<a class="nav-tab' . $active . '" href="?page=wp-snapcam-about&amp;tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
	}

	public function about() {
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to WP Snapcam %s', 'wp-snapcam' ), WP_SNAPCAM_VERSION ); ?></h1>
			<p class="about-text"><?php printf( __( 'Thank you for using WP Snapcam %s, be careful, this version is still in beta.', 'wp-snapcam' ), WP_SNAPCAM_VERSION ); ?></p>
		<?php
		$tab = isset($_GET['tab']) ? sanitize_key( $_GET['tab'] ) : 'howtouseit';
		if ( $tab == 'howtouseit' ) {
			$this->manage_tabs();
			$this->about_howtouseit();
		} elseif ( $tab == 'informations' ) {
			$this->manage_tabs();
			$this->about_informations();
		} else {
			$this->manage_tabs();
			$this->about_helpme();
		}
		?>
		</div>
		<?php
	}

	private function about_howtouseit() {
		?>
		<h2><?php _e( 'You just installed WP Snapcam, Let me explain how to use it in two simple steps !', 'wp-snapcam' ); ?></h2>
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( '1. Add the widget in one of your sidebar.', 'wp-snapcam' ); ?></h3>
				<p><?php _e( 'It all starts from the widget. This is (for the moment), the only place to display a link to take a new snap and a link to the public gallery. Something different will come like shortcode or any better idea but for the moment you absolutely need it ! So go to Appereance &raquo; Widgets and drag and drop the WP Snapcam widget to one of your sidebar. ', 'wp-snapcam' ); ?></p>
				<p><a href="<?php echo admin_url( 'widgets.php' ); ?>"><?php _e( 'Widgets menu', 'wp-snapcam' ); ?></a></p>
			</div>
			<div class="col">
				<h3><?php _e( '2. Go to options menu to adjust settings.', 'wp-snapcam' ); ?></h3>
				<p><?php _e( 'I know, for the moment there is few options but some are pretty important like the number of snaps in admin and plublic galleries and where you want links appear. You have few other options you can play with. For example, you can choose if you want to moderate snaps before they go live. It works the same way as comments. Once a snap is sent, you need to go to admin gallery and to switch it from hide to visible and it will appear on public gallery. ', 'wp-snapcam' ); ?></p>
				<p><a href="<?php echo admin_url( 'admin.php?page=wp-snapcam' ); ?>"><?php _e( 'WP Snapcam options', 'wp-snapcam' ); ?></a></p>
			</div>
		</div>
		<hr>
		<h2><?php _e( "You're done, have fun !", 'wp-snapcam' ); ?></h2>
		<?php
	}

	private function about_informations() {
		?>
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'Who the heck is Emma ?!', 'wp-snapcam' ); ?></h3>
				<p><?php _e( 'When you first install WP Snapcam it adds a fake snap in your database to make it looks a little better. I randomly chose this name and this picture (Open source) so there is no real answer to this question ;) If you prefer, you can safely delete it or you can keep it as a reminder when you first install WP Snapcam.', 'wp-snapcam' ); ?></p>
			</div>
			<div class="col">
				<h3><?php _e( 'Shortcodes.', 'wp-snapcam' ); ?></h3>
				<p><?php _e( 'When you first install WP Snapcam it creates a simple page containing the public gallery. This public gallery is then linked to the widget for visitors can see it. Under the hood this page is simply using a shortcode you can use anywhere to display public gallery. You just need to add this to one post or page : ', 'wp-snapcam' ); ?></p>
				<p><code>[wp-snapcam]</code></p>
			</div>
			<div class="col">
				<h3><?php _e( 'For developers.', 'wp-snapcam' ); ?></h3>
				<p><?php printf( esc_html__( 'For now I have two options for developers, the first one is an option to avoid loading of public CSS when public gallery is displayed. You have to add the content of %1$s inside your own CSS or the public gallery will be broken and not responsive. You can activate this options by addind this line in your %2$s : ', 'wp-snapcam' ), '<code>wp-snapcam-public.css</code>', '<code>wp-config.php </code>' ); ?></p>
				<p><code>define( 'WP_SNAPCAM_LOAD_CSS', false);</code></p>
				<p><?php printf( esc_html__( 'The second one is an action hook you can trigger after a snap is succesfully inserted in database. This is action is %1$s and you ca use it like this : ', 'wp-snapcam' ), '<code>wp_snapcam_after_snap_inserted</code>' ); ?></p>
				<p><code>
					function after_wp_snapcam_success() {<br />
					&nbsp;&nbsp;&nbsp;&nbsp;// DO WHAT YOU NEED TO DO<br />
					}<br />
					add_action( 'wp_snapcam_after_snap_inserted', 'after_wp_snapcam_success' );
				</code></p>
			</div>
		</div>
		<?php
	}

	private function about_helpme() {
		?>
		<h2><?php _e( "Stay here, I'm not going to ask you money !", 'wp-snapcam' ); ?></h2>
		<hr>
		<h4><?php _e( 'How can you help me then ? You have different possibilities...', 'wp-snapcam'); ?></h4>
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'First things first, track bugs !', 'wp-snapcam' ); ?></h3>
				<p><?php _e( "As you can see, this is a beta version so I'm pretty sure there are bugs somewhere. If you find one, please open a thread in Support forum on WordPress to inform me about it. Please, use as many details as you can to help me reproduce it. I'll do my best to correct it. If this is a security issue, please use my email : kevin at mnt dash tech dot fr ", 'wp-snapcam' ); ?></p>
				<p><a href="https://wordpress.org/support/plugin/wp-snapcam"><?php _e( 'WP Snapcam support forum', 'wp-snapcam' ); ?></a></p>
			</div>
			<div class="col">
				<h3><?php _e( 'Translate this plugin', 'wp-snapcam' ); ?></h3>
				<p><?php _e( 'If you can translate in your own language this will help me a lot ! The only thing you need to use is the file called wp-snapcam.pot in the languages folder. Then you can create a new project with Poedit or your preferred po editor. By the way, english is not my native tongue so I probably make mistakes or some sentences could be weird. Yous can help me with that too. You just have to send it to my email : kevin at mnt dash tech dot fr. ', 'wp-snapcam' ); ?></p>
			</div>
			<div class="col">
				<h3><?php _e( 'Any ideas ?', 'wp-snapcam' ); ?></h3>
				<p><?php _e( "If you have any idea to improve this plugin, new option, new feature, anything. Let me know ! If this is a good one and I can make it I'll add it. For anything like this, use Wordpress support forum here : ", 'wp-snapcam' ); ?></p>
				<p><a href="https://wordpress.org/support/plugin/wp-snapcam"><?php _e( 'WP Snapcam support forum', 'wp-snapcam' ); ?></a></p>
			</div>
			<div class="col">
				<h3><?php _e( 'Link', 'wp-snapcam' ); ?></h3>
				<p><?php _e( "If you want to help me but have no time to do one of this things you can really help me to get more time by adding a simple link to your website. It could be inside a post, a sidebar, a footer, anywhere. It will help me to better rank on search engines and spend less time to search for new clients (I'm a freelance sysadmin in Paris). Here is an example in French but you can modify the anchor according to your language :", 'wp-snapcam' ); ?></p>
				<p><code>&lt;a href="https://mnt-tech.fr/"&gt;Infogerance serveur&lt;/a&gt;</code></p>
			</div>
		</div>
		<?php
	}

}

