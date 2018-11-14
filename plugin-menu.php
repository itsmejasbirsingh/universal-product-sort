<?php
class UPS_Plugin {

    // class instance
    static $instance;

    // customer WP_List_Table object
    public $flter;

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
        'option'  => 'records_per_page'
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

                    <form action="<?php echo admin_url( 'admin.php' ); ?>">
                    <p class="search-box">
                        <label class="screen-reader-text" for="post-search-input">Search:</label>
                        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>">
                        <input type="search" id="post-search-input" name="s" value="<?php echo @$_GET['s']; ?>">
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

}


add_action( 'plugins_loaded', function () {
    UPS_Plugin::get_instance();
});