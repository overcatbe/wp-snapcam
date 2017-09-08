<?php

/**
 * The public gallery.
 *
 * @package    WP_Snapcam
 * @subpackage WP_Snapcam/public
 */
class WP_Snapcam_Public_Gallery {

	/**
	 * Object to acces WordPress database. 
	 *
	 * @since	 0.1
	 * @access	 private
	 * @var		 object
	 */
	private $db;

	/**
	 * Name of the table used by WP_Snapcam. 
	 *
	 * @since	 0.1
	 * @access	 private
	 * @var		 string
	 */
	private $table;

	private $pagination_args;
	private $items;

	private $wp_snapcam;
	/**
	 * Initialize the class.
	 *
	 * @since	 0.1
	 */
	public function __construct( $wp_snapcam ) {
		$this->wp_snapcam = $wp_snapcam;
		global $wpdb;
		$this->db =& $wpdb;
		$this->wp_snapcam = $wp_snapcam;
		$this->table = $this->wp_snapcam->wp_snapcam_settings['table'];
	}

	/**
	 * Get the current page number
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['snap_paged'] ) ? absint( $_REQUEST['snap_paged'] ) : 0;

		if ( isset( $this->pagination_args['total_pages'] ) && $pagenum > $this->pagination_args['total_pages'] )
			$pagenum = $this->pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

	private function get_entries( $per_page = 5 ) {
		/* First SQL request to get all visible items */
		$sql = "SELECT * FROM $this->table WHERE display=1 ORDER BY date DESC";
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $this->get_pagenum() - 1 ) * $per_page;
		$items = $this->db->get_results( $sql, 'ARRAY_A' );

		/* Second SQL request to get number of rows */
		$sql = "SELECT COUNT(*) FROM $this->table";
		$total_items = $this->db->get_var( $sql );

		/* Return an array with both results */
		return array(
			'items' => $items,
			'total_items' => $total_items
		);
	}

	private function prepare_items() {
		$per_page = $this->wp_snapcam->wp_snapcam_options['snaps_per_page_public'];
		$items_infos = $this->get_entries( $per_page );
		$this->items = $items_infos['items'];

		/* Build pagination */
		$current_page = $this->get_pagenum();
		$total_items  = $items_infos['total_items'];
		$this->pagination_args = array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			);
	}

	public function display() {
		/* We need thickbox */
		add_thickbox();

		/* First we collect snaps to display */
		$this->prepare_items();

		/* In case there is no snaps */
		if (empty( $this->items ) ) {
			echo '<div id="wp-snapcam-public-gallery">' . __( 'Sorry, no snap in this gallery', 'wp-snapcam' ) . '</div>';
			//TODO maybe you want to add yours ?
			return;
		}

		$uploads = wp_get_upload_dir();
		?>
		<div id="wp-snapcam-public-gallery">
		<?php foreach ( $this->items as $item ) { ?>
			<div id="snap">
			<figure>
				<a class="thickbox" href="<?php echo $uploads['baseurl'] . '/wp-snapcam/' . $item['id'] . '.jpg'; ?>">
					<img src="<?php echo $uploads['baseurl'] . '/wp-snapcam/' . $item['id'] . '.jpg'; ?>" alt="<?php echo $item['name']; ?>">
				</a>
			<figcaption>
				<?php if ( ( $this->wp_snapcam->wp_snapcam_options['link'] == 'gallery only' OR $this->wp_snapcam->wp_snapcam_options['link'] == 'both') AND $item['link'] != "none") { ?>
				<a target="_blank" href="<?php echo esc_html( $item['link'] ); ?>"><?php echo $item['name']; ?></a>
				<?php } else { ?>
				<span><?php echo $item['name']; ?></span>
				<?php } ?>
			</figcaption>
			</figure>
			</div>
		<?php } ?>
		</div>
		<div id="wp-snapcam-public-pagination">
		<?php $this->pagination(); ?>
		</div>
		<?php
	}

	/**
	 * Display the pagination inside public gallery.
	 *
	 * @since 0.1
	 * @access private
	 *
	 */
	private function pagination() {
		if ( empty( $this->pagination_args ) ) {
			return;
		}

		$total_items = $this->pagination_args['total_items'];
		$total_pages = $this->pagination_args['total_pages'];

		$current = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		$first_page = __( 'first', 'wp-snapcam' );
		$last_page = __( 'last', 'wp-snapcam' );

		if ( $current == 1 ) {
			$disable_first = true;
			$disable_prev = true;
		}
		if ( $current == 2 ) {
			$disable_first = true;
		}
		if ( $current == $total_pages ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $current == $total_pages - 1 ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			printf( '<span class="wp-snapcam-pagination" id="deactivated">%s</span>', $first_page );
		} else {
			printf( '<a class="wp-snapcam-pagination" href="%s"><span>%s</span></a>',
				 esc_url( remove_query_arg( 'snap_paged', $current_url ) ),
				$first_page );
		}

		if ( $disable_prev ) {
			printf( '<span class="wp-snapcam-pagination" id="deactivated">%s</span>', '&lsaquo;' );
		} else {
			printf( '<a class="wp-snapcam-pagination" href="%s"><span>%s</span></a>',
				 esc_url( add_query_arg( 'snap_paged', max( 1, $current-1 ), $current_url ) ),
				'&lsaquo;' );
		}

		printf( '<span class="wp-snapcam-pagination">%s %s %s</span>',
			$current,
			__( 'of', 'wp-snapcam' ),
			$total_pages );

		if ( $disable_next ) {
			printf( '<span class="wp-snapcam-pagination" id="deactivated">%s</span>', '&rsaquo;' );
		} else {
			printf( '<a class="wp-snapcam-pagination" href="%s"><span>%s</span></a>',
				 esc_url( add_query_arg( 'snap_paged', max( 1, $current+1 ), $current_url ) ),
				'&rsaquo;' );
		}
			
		if ( $disable_last ) {
			printf( '<span class="wp-snapcam-pagination" id="deactivated">%s</span>', $last_page );
		} else {
			printf( '<a class="wp-snapcam-pagination" href="%s"><span>%s</span></a>',
				esc_url( add_query_arg( 'snap_paged', $total_pages, $current_url ) ),
				$last_page );
		}
	}

}
