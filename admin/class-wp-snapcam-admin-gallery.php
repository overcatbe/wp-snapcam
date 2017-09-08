<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WP_Snapcam_Admin_Gallery extends WP_List_Table {


	private $wp_snapcam;

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

	private $hide_nonce_action = "hide_snap";
	private $show_nonce_action = "show_snap";
	private $delete_nonce_action = "delete_snap";
	private $update_link_nonce_action = "update_link_snap";
	private $update_name_nonce_action = "update_name_snap";

	public function __construct( $wp_snapcam ) {
		parent::__construct( array(
			'singular' => 'snap',
			'plural'   => 'snaps',
			'ajax'     => false,
		) );
		global $wpdb;
		$this->db =& $wpdb;
		$this->wp_snapcam = $wp_snapcam;
		$this->table = $this->wp_snapcam->wp_snapcam_settings['table'];
	}

    /**
	 * Get the current action (bulk or single).
	 * This method need to be public cause it's an override and the parent is public.
	 * I add one more action (action3) to handle some tricky actions in two times
	 * like editlink followed by updatelink.
     *
     * @access public
     *
     * @return string|false The action name or False if no action was selected
     */
    public function current_action() {
        if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
            return false;

        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return sanitize_key( $_REQUEST['action'] );

        if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
            return sanitize_key( $_REQUEST['action2'] );

        if ( isset( $_REQUEST['action3'] ) && -1 != $_REQUEST['action3'] )
            return sanitize_key( $_REQUEST['action3'] );

        return false;
    }

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'snap' => 'Snap',
			'name' => __( 'Name', 'wp-snapcam' ),
			'date' => __( 'Date', 'wp-snapcam' ),
			'link' => __( 'Link', 'wp-snapcam' ),
			'display' => __( 'Visible', 'wp-snapcam' )
		);

		return $columns;
	}

	protected function get_sortable_columns() {
		$sortable_columns = array(
			'name'	=> array( 'name', false ),
			'date'	  => array( 'date', false ),
			'link'	  => array( 'link', false ),
			'display' => array( 'display', false )
		);

		return $sortable_columns;
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'rating':
			case 'date':
				return $item[ $column_name ];
				break;
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
		}
	}

	public function column_cb( $item ){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s"/>',
			$this->_args[ "singular" ],
			$item[ "id" ]
		);
	}

	function column_snap($item) {
		$uploads = wp_get_upload_dir();
		$snap_image= $uploads['baseurl'] . '/wp-snapcam/' . $item['id'] . '.jpg';
		return sprintf('<img src="%1$s" height="120" width="160" alt="%2$s" title="%3$s"/>', $snap_image, $item['name'], $item['name']);
	}


	public function column_name( $item ) {
		/* Delete action */
		$delete_url = add_query_arg( array (
			'paged'  => $this->get_pagenum(),
			'page'   => sanitize_key( $_REQUEST['page'] ),
			'tab'    => sanitize_key( $_REQUEST['tab'] ),
			'snap'   => $item['id'],
			'action' => 'delete'
		) );
		$nonced_delete_url = wp_nonce_url( $delete_url, $this->delete_nonce_action, $this->delete_nonce_action );

		/* Edit name action */
		$editlink_url = add_query_arg( array(
			'paged'  => $this->get_pagenum(),
			'page'   => sanitize_key( $_REQUEST['page'] ),
			'tab'    => sanitize_key( $_REQUEST['tab'] ),
			'snap'   => $item['id'],
			'action' => 'editname'
		) );
		$actions = array (
			'delete'   => sprintf( '<a href="%1$s">%2$s</a>', $nonced_delete_url, __( 'Delete snap', 'wp-snapcam' ) ),
			'editname' => sprintf(	'<a href="%1$s#%2$s">%3$s</a>', $editlink_url, $item['id'], __( 'Edit', 'wp-snapcam' ) )
		);
	
		/* In case we need to edit this item */
		if ( $this->current_action() === 'editname' AND $_REQUEST['snap'] === $item['id'] AND ! isset( $_REQUEST['name'] ) ) {
			/* The hidden input paged is here to address a bug where when you just updated
			 * a name it send a paged=1 instead of the current page. Maybe I need to dig in
			 * this one to remove this ugly trick. This is the same for editlink action
			 */
			return sprintf(
						'<input type="hidden" name="action3" value="updatename">
						<input type="hidden" name="snap" value="%1$s">
						<input type="hidden" name="%2$s" value="%3$s">
						<input type="hidden" name="paged" value="%4$s">
						<input type="text" value="%5$s" name="name"><br />
						<input class="save button-primary" type="submit" value="Update">',
						$item['id'],
						$this->update_name_nonce_action,
						wp_create_nonce( $this->update_name_nonce_action ),
						$this->get_pagenum(),
						esc_html( $item['name'] )
					);
		} else {
			return sprintf( '<span id="%s">%s</span> %s', $item['id'], $item['name'], $this->row_actions( $actions ) );
		}
	}

	function column_link( $item ) {
		/* Edit link action */
		$editlink_url = add_query_arg( array(
			'paged'  => $this->get_pagenum(),
			'page'   => sanitize_key( $_REQUEST['page'] ),
			'tab'    => sanitize_key( $_REQUEST['tab'] ),
			'snap'   => $item['id'],
			'action' => 'editlink'
		) );
		$actions = array (
			'editlink'	=> sprintf(	'<a href="%1$s#%2$s">%3$s</a>', $editlink_url, $item['id'], __( 'Edit', 'wp-snapcam' ) ),
		);

		/* If there is no link in this item */
		if ( $item['link'] == 'none' ) {
			return __( 'No link', 'wp-snapcam' );

		/* In case we need to edit this item */
		} elseif ( $this->current_action() === 'editlink' AND $_REQUEST['snap'] === $item['id'] AND ! isset( $_REQUEST['link'] ) ) {
			return sprintf(
						'<input type="hidden" name="action3" value="updatelink">
						<input type="hidden" name="snap" value="%1$s">
						<input type="hidden" name="%2$s" value="%3$s">
						<input type="hidden" name="paged" value="%4$s">
						<input type="text" value="%5$s" name="link"><br />
						<input class="save button-primary" type="submit" value="Update">',
						$item['id'],
						$this->update_link_nonce_action,
						wp_create_nonce( $this->update_link_nonce_action ),
						$this->get_pagenum(),
						esc_html( $item['link'] )
					);
		} else {
			return sprintf('<a target="_blank" href="%1$s">%2$s</a> %3$s', htmlspecialchars($item['link']), htmlspecialchars($item['link']), $this->row_actions($actions));
		}
	}

	function column_display($item) {
		/* Hide action */
		$hide_url = add_query_arg( array (
			'paged'  => $this->get_pagenum(),
			'page'   => sanitize_key( $_REQUEST['page'] ),
			'tab'    => sanitize_key( $_REQUEST['tab'] ),
			'snap'   => $item['id'],
			'action' => 'hide'
		) );
		$nonced_hide_url = wp_nonce_url( $hide_url, $this->hide_nonce_action, $this->hide_nonce_action );
	
		/* Show action */
		$show_url = add_query_arg( array (
			'paged'  => $this->get_pagenum(),
			'page'   => sanitize_key( $_REQUEST['page'] ),
			'tab'    => sanitize_key( $_REQUEST['tab'] ),
			'snap'   => $item['id'],
			'action' => 'show'
		) );
		$nonced_show_url = wp_nonce_url( $show_url, $this->show_nonce_action, $this->show_nonce_action );

		$actions = array (
			'hide'	=> sprintf(	'<a href="%1$s">%2$s</a>', $nonced_hide_url, __( 'Hide', 'wp-snapcam' ) ),
			'show'	=> sprintf( '<a href="%1$s">%2$s</a>', $nonced_show_url, __( 'Show', 'wp-snapcam' ) )
		);

		if ( $item['display'] == 1 ) {
			unset( $actions['show'] );
			return sprintf( '%s %s', __( 'Yes', 'wp-snapcam' ), $this->row_actions( $actions ) );
		} else {
			unset( $actions['hide'] );
			return sprintf( '%s %s', __( 'No', 'wp-snapcam' ), $this->row_actions( $actions ) );
		}
	}



	protected function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'wp-snapcam' ),
			'show'   => __( 'Show', 'wp-snapcam' ),
			'hide'   => __( 'Hide', 'wp-snapcam' )
		);
		return $actions;
	}

	private function check_uniqid ( $array ) {
		foreach ( $array as $id ) {
			if ( ! preg_match( '/^[a-z0-9]{13}$/', $id ) ) {
				return false;
			}
		}
		return true;
	}

	public function process_action() {
		if ( !isset( $_REQUEST[ 'snap' ] ) )	{
			return;
		}
		if ( FALSE == ( $current_action = $this->current_action() ) ) {
			return;
		}

		/* Put id in array to treat single action and bulk action the same way */
		$array_id = (array) ( $_REQUEST['snap'] );

			
		/* If request does not match any of this conditions there's probably something wrong */
		if (
			( $this->current_action() === 'hide' && $this->check_uniqid( $array_id ) && wp_verify_nonce( $_REQUEST[ $this->hide_nonce_action ], $this->hide_nonce_action ) ) ||
			( $this->current_action() === 'show' && $this->check_uniqid( $array_id ) && wp_verify_nonce( $_REQUEST[ $this->show_nonce_action ], $this->show_nonce_action ) ) ||
			( $this->current_action() === 'delete' && $this->check_uniqid( $array_id ) && wp_verify_nonce( $_REQUEST[ $this->delete_nonce_action ], $this->delete_nonce_action ) ) ||
			( $this->current_action() === 'editlink' && $this->check_uniqid( $array_id ) ) ||
			( $this->current_action() === 'updatelink' && $this->check_uniqid( $array_id ) && isset( $_REQUEST['link'] ) && wp_verify_nonce( $_REQUEST[ $this->update_link_nonce_action ], $this->update_link_nonce_action ) ) ||
			( $this->current_action() === 'editname' && $this->check_uniqid( $array_id ) ) ||
			( $this->current_action() === 'updatename' && $this->check_uniqid( $array_id ) && isset( $_REQUEST['name'] ) && wp_verify_nonce( $_REQUEST[ $this->update_name_nonce_action ], $this->update_name_nonce_action ) ) ||
			( $this->current_action() === 'show' && $this->check_uniqid( $array_id ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-'.$this->_args['plural'] ) ) ||
			( $this->current_action() === 'hide' && $this->check_uniqid( $array_id ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-'.$this->_args['plural'] ) ) ||
			( $this->current_action() === 'delete' && $this->check_uniqid( $array_id ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-'.$this->_args['plural'] ) )

		) {
			/* Switch around all possible actions */
			switch ( $this->current_action() ) {
			case 'hide':
				$this->hide_snap( $array_id );
				return;
				break;

			case 'show':
				$this->show_snap( $array_id );
				return;
				break;

			case 'delete':
				$this->delete_snap( $array_id );
				return;
				break;

			/* Not a real action but will add a field and a button to update link so we have to make a case condition */ 
			case 'editlink':
				return;
				break;

			case 'updatelink':
				$this->update_link_snap( $array_id );
				return;
				break;

			/* Not a real action but will add a field and a button to update name so we have to make a case condition */ 
			case 'editname':
				return;
				break;

			case 'updatename':
				$this->update_name_snap( $array_id );
				return;
				break;
			}
		}
		wp_die( __( 'Sorry, you are not allowed to access this page.', 'wp-snapcam' ) );
	}

	private function hide_snap( $array_id ) {
		foreach ( $array_id as $id ) {
			$this->db->update(
				$this->table,
				array( 'display' => '0' ),
				array( 'id' => $id ), // WHERE clause
				array( '%d' ), // type of value
				array( '%s' ) // type in WHERE
			);
		}
	}

	private function show_snap( $array_id ) {
		foreach ( $array_id as $id ) {
			$this->db->update(
				$this->table,
				array( 'display' => '1' ),
				array( 'id' => $id ), // WHERE clause
				array( '%d' ), // type of value
				array('%s') // type in WHERE
			);
		}
	}

	private function delete_snap( $array_id ) {
		$uploads = wp_get_upload_dir();
		foreach ( $array_id as $id ) {
			$snap_path = $uploads['basedir'] . '/wp-snapcam/' . $id . '.jpg';
			/* If file is deleted, delete in database */
			if ( unlink( (string ) $snap_path ) ) {
				$this->db->delete(
					$this->table,
					array( 'id' => $id ),
					array('%s')
				);
			}
		}
	}

	private function update_link_snap( $array_id ) {
		$link = ( empty( $_REQUEST['link'] ) ) ? 'none' : esc_url_raw( $_REQUEST['link'] );
		$id = implode( '', $array_id );
		$this->db->update(
			$this->table,
			array( 'link' => $link ),
			array( 'id' => $id ), // WHERE clause
			array( '%s' ), // type of value
			array( '%s' ) // type in WHERE
		);
	}	
	
	private function update_name_snap( $array_id ) {
		$name = sanitize_text_field( $_REQUEST['name'] );
		$id = implode( '', $array_id );
		$this->db->update(
			$this->table,
			array( 'name' => $name ),
			array( 'id' => $id ), // WHERE clause
			array( '%s' ), // type of value
			array( '%s' ) // type in WHERE
		);
	}	


	function get_entries( $per_page = 5 ) {
		/* First SQL request to get all items */
		$sql = "SELECT * FROM $this->table";
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( sanitize_key( $_REQUEST['orderby'] ) );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( sanitize_key( $_REQUEST['order'] ) ) : ' ASC';
		} else {
			/* By default, we order by date desc */
			$sql .= ' ORDER BY date DESC';
		}
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

	function prepare_items() {
		/* If any, we process actions */
		$this->process_action();

		/* How many snaps per page */
		$per_page = $this->wp_snapcam->wp_snapcam_options['snaps_per_page_admin'];

		/* Build column headers */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		/* Populate the table */
		$items_infos = $this->get_entries( $per_page );
		$this->items = $items_infos['items'];

		/* Build pagination */
		$current_page = $this->get_pagenum();
		$total_items  = $items_infos['total_items'];
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	public function remove_parameters() {
		/* We don't need these parameters anymore to display snaps */
		$options = array(
			'snap',
			'action',
			'action2',
			'action3',
			'link',
			'name',
			'paged',
			$this->hide_nonce_action,
			$this->show_nonce_action,
			$this->delete_nonce_action,
			$this->update_link_nonce_action,
			$this->update_name_nonce_action
		);
		$_SERVER['REQUEST_URI'] = remove_query_arg( $options, $_SERVER['REQUEST_URI'] );
	}

}
