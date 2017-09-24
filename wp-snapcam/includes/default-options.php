<?php

/**
 * Define all options
 */
$GLOBALS['wp_snapcam_default_options'] = array(
	array(
		'name'              => 'snaps_per_page_public',
		'type'              => 'number',
		'default'           => '12',
		'short_description' => __( 'Snaps per page in public gallery', 'wp-snapcam' ),
		'long_description'  => __( 'The public gallery is fully responsive so the only thing you want to adjust is the number of snap per page. Then number of columns and row will automatically be adjusted to fit your theme.', 'wp-snapcam' ),
		'values'            => array (
								'min'  => 1,
								'max'  => 100,
								'step' => 1
							)
	),
	array(
		'name'              => 'widget_snap',
		'type'              => 'radio',
		'default'           => 'last',
		'short_description' => __( 'Snap in widget', 'wp-snapcam' ),
		'long_description'  => __( 'Choose wether you want to print last snap in widget or a random one.', 'wp-snapcam' ),
		'values'            => array (
								__( 'last', 'wp-snapcam' ),
								__( 'random', 'wp-snapcam' )
							)
	),
	array(
		'name'              => 'link',
		'type'              => 'radio',
		'default'           => 'both',
		'short_description' => __( 'Where links work', 'wp-snapcam' ),
		'long_description'  => __( 'Choose where user submited links will be displayed.', 'wp-snapcam' ),
		'values'            => array (
								__( 'widget only', 'wp-snapcam' ),
								__( 'gallery only', 'wp-snapcam' ),
								__( 'both', 'wp-snapcam')
							)
	),
	array(
		'name'              => 'moderate',
		'type'              => 'checkbox',
		'default'           => 0,
		'short_description' => __( 'Snaps must be manually approved', 'wp-snapcam' ),
		'long_description'  => __( "Tick this option if you prefer to review snaps before they go live. You'll have to change visibility in admin gallery for every snap.", 'wp-snapcam' ),
		'values'            => array ( 0, 1 )
	),
	array(
		'name'              => 'ajax',
		'type'              => 'checkbox',
		'default'           => 1,
		'short_description' => __( 'Load widget using ajax', 'wp-snapcam' ),
		'long_description'  => __( "This option is usefull if you're using cache plugin like W3 Total Cache or WP Super Cache. In doubt, just let this option ticked.", 'wp-snapcam' ),
		'values'            => array ( 0, 1 )
	),
	array(
		'name'              => 'snaps_per_page_admin',
		'type'              => 'number',
		'default'           => '5',
		'short_description' => __( 'Snaps per page in admin gallery', 'wp-snapcam' ),
		'long_description'  => __( 'This option sets the number of snaps you want to show per page in admin gallery. Nothing related to the public gallery !', 'wp-snapcam' ),
		'values'            => array (
								'min'  => 1,
								'max'  => 100,
								'step' => 1
							)
	),
	array(
		'name'              => 'support_me',
		'type'              => 'checkbox',
		'default'           => 0,
		'short_description' => __( 'Support me', 'wp-snapcam' ),
		'long_description'  => __( "Thick this option if you want to help me. This will add a link to plugin page in widget's title", 'wp-snapcam' ),
		'values'            => array ( 0, 1 )
	)
);
?>
