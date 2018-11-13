<?php
/*
    Plugin Name: Universal Product Sort
    Plugin URI: http://wordpress.com/
    Description: Universal Product Sort
    Author: Mindfire solutions
    Author URI: http://mindfiresolutions.com
    Version: 0.1
*/
///////////////////
function ups_create_new() {
    echo "<h2>" . __( 'Create new', 'menu-test' ) . "</h2>";
}

function ups_settings() {
    echo "<h2>" . __( 'Settings', 'menu-test' ) . "</h2>";
}



if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ViewFilters extends WP_List_Table {

    
    /**
    * Retrieve customer’s data from the database
    *
    * @param int $per_page
    * @param int $page_number
    *
    * @return mixed
    */
    public static $table_name = 'posts';

    public static function get_filters( $per_page = 5, $page_number = 1 ) {
       
        global $wpdb;

        $offset = ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $sql = "SELECT * FROM {$wpdb->prefix}".self::$table_name." WHERE post_type='ups_filter'";

        if(isset($_POST['s']))
        {
            $s = esc_sql($_POST['s']);
            $sql .= " AND (`post_title` LIKE '%".$s."%' OR `post_content` LIKE '%".$s."%' OR `post_status` LIKE '%".$s."%')";
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
    * Delete a customer record.
    *
    * @param int $id customer ID
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
    'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
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

function get_columns() {
  $columns = [
    'cb'      => '<input type="checkbox" />',
    'post_title'    => __( 'Filter', 'sp' ),
    'post_content' => __( 'Content', 'sp' ),
    'post_status'    => __( 'Status', 'sp' )
  ];

  return $columns;
}

public function get_sortable_columns() {
  $sortable_columns = array(
    'post_title' => array( 'post_title', true ),
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

  $per_page     = $this->get_items_per_page( 'customers_per_page', 5 );
  $current_page = $this->get_pagenum();
  $total_items  = self::record_count();

  $this->set_pagination_args( [
    'total_items' => $total_items, //WE have to calculate the total number of items
    'per_page'    => $per_page //WE have to determine how many items to show on a page
  ] );


  $this->items = self::get_filters( $per_page, $current_page );
}

public function process_bulk_action() {

  //Detect when a bulk action is being triggered...
  if ( 'delete' === $this->current_action() ) {

    // In our file that handles the request, verify the nonce.
    $nonce = esc_attr( $_REQUEST['_wpnonce'] );

    if ( ! wp_verify_nonce( $nonce, 'sp_delete_filter' ) ) {
      die( 'Session destroyed' );
    }
    else {
      self::delete_filter( absint( $_GET['customer'] ) );

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





} // end of class customers


class UPS_Plugin {

    // class instance
    static $instance;

    // customer WP_List_Table object
    public $customers_obj;

    // class constructor
    public function __construct() {
        add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
        add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
    }
    public static function set_screen( $status, $option, $value ) {
    return $value;
}
public function plugin_menu() {

    $hook = add_menu_page(
        'Universal Product Sort (UPS)', // page title
        'Universal Product Sort', // menu title
        'manage_options', // capability
        'ups-view-all', // slug
        [ $this, 'ups_view_all' ], // method call
        'https://s.w.org/favicon.ico?2' // menu image
    );

    add_action( "load-$hook", [ $this, 'screen_option' ] );


    add_submenu_page('ups-view-all', 'View All', 'View All Filters', 'manage_options', 'ups-view-all', 'ups_view_all');

  
    add_submenu_page('ups-view-all', 'Add New', 'Add New', 'manage_options', 'ups-create-new', 'ups_create_new');

    add_submenu_page('ups-view-all', __('Settings','menu-test'), __('Settings','menu-test'), 'manage_options', 'ups-settings', 'ups_settings');


}

public function screen_option() {

    $option = 'per_page';
    $args   = [
        'label'   => 'Number of records',
        'default' => 5,
        'option'  => 'customers_per_page'
    ];

    add_screen_option( $option, $args );

    $this->flter = new ViewFilters();
}

public function ups_view_all() {
    ?>
    <div class="wrap">
        <h2>List of filters</h2>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-3">
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">

                    <form>
                    <p class="search-box">
                        <label class="screen-reader-text" for="post-search-input">Search:</label>
                        <input type="search" id="post-search-input" name="s" value="<?php echo @$_POST['s']; ?>">
                        <input type="submit" id="search-submit" class="button" value="Search">
                    </p>
                    </form>
                    
                        <form method="post">
                            <?php
                            $this->flter->prepare_items();
                            $this->flter->display(); ?>
                        </form>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
<?php
}



public static function get_instance() {
    if ( ! isset( self::$instance ) ) {
        self::$instance = new self();
    }

    return self::$instance;
}

} // end of class sp


add_action( 'plugins_loaded', function () {
    UPS_Plugin::get_instance();
});