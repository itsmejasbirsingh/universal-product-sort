<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ViewFilters extends WP_List_Table {

    
    
    public static $table_name = 'posts';


    public static function search_condition($s)
    {
            $s = esc_sql($_GET['s']);
            $sql = " AND (`post_title` LIKE '%".$s."%' OR `post_content` LIKE '%".$s."%' OR `post_status` LIKE '%".$s."%')";
            return $sql;
    }

    /**
    * Retrieve filters data from the database
    *
    * @param int $per_page
    * @param int $page_number
    *
    * @return records
    */
    public static function get_filters( $per_page = 5, $page_number = 1 ) {
      
        global $wpdb;

        $offset = ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $sql = "SELECT * FROM {$wpdb->prefix}".self::$table_name." WHERE post_type='ups_filter'";

        if(isset($_GET['s']))
        {
            $sql .= self::search_condition($_GET['s']);
            $offset = '';
        }


        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";

        $sql .= $offset;

       
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    /**
    * Delete a record.
    *
    * @param int $id record ID
    */
    public static function delete_filter( $id ) {
        global $wpdb;

        $wpdb->delete(
        "{$wpdb->prefix}".self::$table_name,
        [ 'ID' => $id ],
        [ '%d' ]
        );
    }

    /**
    * Returns the count of records in the database.
    *
    * @return null|string
    */
    public static function record_count() {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}".self::$table_name." WHERE post_type='ups_filter'";
        if(isset($_GET['s']))
        {
            $sql .= self::search_condition($_GET['s']);
        }

        return $wpdb->get_var( $sql );
    }
    public function no_items() {
  _e( 'No filter avaliable.', 'sp' );
}

/**
 * Method for name column
 *
 * @param array $item an array of DB data
 *
 * @return string
 */
function column_name( $item ) {

  // create a nonce
  $delete_nonce = wp_create_nonce( 'sp_delete_filter' );

  $title = '<strong>' . $item['post_title'] . '</strong>';

  $actions = [
    'delete' => sprintf( '<a href="?page=%s&action=%s&record=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
    'edit' => sprintf( '<a href="?page=%s&action=%s&edit=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['ID'] ))
  ];

  return $title . $this->row_actions( $actions );
}

public function column_default( $item, $column_name ) {
  switch ( $column_name ) {
    case 'post_content':
    case 'post_status':
      return $item[ $column_name ];
    default:
      return print_r( $item, true ); //Show the whole array for troubleshooting purposes
  }
}

function column_cb( $item ) {
  return sprintf(
    '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
  );
}

//Here column `name` will be as it is, for showing default one
function get_columns() {
  $columns = [
    'cb'      => '<input type="checkbox" />',
    'name'    => __( 'Filter', 'sp' ),
    'post_content' => __( 'Content', 'sp' ),
    'post_status'    => __( 'Status', 'sp' )
  ];

  return $columns;
}

public function get_sortable_columns() {
  $sortable_columns = array(
    'name' => array( 'post_title', true ),
    'post_status' => array( 'post_status', false )
  );

  return $sortable_columns;
}

public function get_bulk_actions() {
  $actions = [
    'bulk-delete' => 'Delete'
  ];

  return $actions;
}

public function prepare_items() {

  $this->_column_headers = $this->get_column_info();

  /** Process bulk action */
  $this->process_bulk_action();

  $per_page     = $this->get_items_per_page( 'records_per_page', 5 );
  $current_page = $this->get_pagenum();
  $total_items  = self::record_count();

  $this->set_pagination_args( [
    'total_items' => $total_items, //calculate the total number of items
    'per_page'    => $per_page //determine how many items to show on a page
  ] );


  $this->items = self::get_filters( $per_page, $current_page );
}

public function process_bulk_action() {

  //Detect when a bulk action is being triggered...
  if ( 'delete' === $this->current_action() ) {

    // In our file that handles the request, verify the nonce.
    $nonce = esc_attr( $_REQUEST['_wpnonce'] );

    if ( ! wp_verify_nonce( $nonce, 'sp_delete_filter' ) ) {
      die( 'Session ends!' );
    }
    else {
      self::delete_filter( absint( $_GET['record'] ) );

      wp_redirect( esc_url( add_query_arg() ) );
      exit;
    }

  }

  // If the delete bulk action is triggered
  if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
       || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
  ) {

    $delete_ids = esc_sql( $_POST['bulk-delete'] );

    // loop over the array of record IDs and delete them
    foreach ( $delete_ids as $id ) {
      self::delete_filter( $id );

    }

    wp_redirect( esc_url( add_query_arg() ) );
    exit;
  }
}





}