<?php
function ups_settings() {
	$setting = new UPSSettings();

	$post = $setting->getPostStatusByPostType('ups_csv_added_cart');
    ?>
    <h2>OTHER SETTINGS</h2>
    <div class="other-settings">
    	<form id="form_other_settings">
		    <p><input <?php if($post['post_status'] == 'publish') echo "checked"; ?> type="checkbox" name="is_csv_file_added_to_cart" id="is_csv_file_added_to_cart" value="1"> Each time you add an item to your cart(filter settings), a CSV file should be added to the cart to the submitted item</p>
		    <p><input type="checkbox"> Logic query separator line</p>
		    <p><input type="submit" value="Save" class="button button-primary"></p>
		    <input type="hidden" name="action" value="other_settings">
	    </form>
    </div>

	<hr>



    <h2>EXTRA PRODUCT SETUP</h2>
    <div id="col-container">
	    <div id="col-left">
		    <form method="post" id="form_add_field">
		    	<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label>Field Name:</label></th>
						<td>
							<input type="text" name="field_name" class="field_name regular-text"/>
						</td>
						</tr>

						<tr>
							<th scope="row"><label>Description:</label></th>
						<td>
							<textarea name="description" class="description large-text"></textarea>
						</td>
						</tr>

						<tr>
							<th scope="row"><label>Content:</label></th>
						<td><span class="content">
							<input type="radio" name="content" checked value="text">Text &nbsp;  
							<input type="radio" name="content" value="number">Number &nbsp;  
							<input type="radio" name="content" value="range">Range &nbsp; 
							<input type="radio" name="content" value="checkbox">Checkbox &nbsp;
							</span>
						</td>
						</tr>
						<tr>
							<th scope="row"><input type="submit" value="Add New Field" class="button button-primary"></th>
						<td>
							<span class="message"></span>
						</td>
						</tr>
					</tbody>
				</table>
		    	<input type="hidden" name="action" value="add_field">
		    	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'adding_field' ); ?>">
		    </form>
		    <?php $setting->getAllFields(); ?>
	    </div>
	    <div id="col-right">
	    	<p><b>Preview & order of fields arrangement on a product (just drag & drop)</b></p>
	    	<div>
	    	<?php $setting->getAllFieldsSortable(); ?>
	    	</div>
	    </div>   	
    </div>
    <?php
}



function adding_field() {
	$setting = new UPSSettings();
	$featured = new UPSFeatured();

	global $wpdb; 
	$data = array();
	$content_array = array('text','number','range','checkbox');

	$nonce = esc_attr( $_POST['_wpnonce'] );
	if ( ! wp_verify_nonce( $nonce, 'adding_field' ) ) {
      $data['error']['message'] = 'Unauthorized access!';
    }
    
    if( $featured->isEmpty($_POST['field_name']))
    {
    	$data['error']['field_name'] = 'Field name can not be blank!';
    }
    elseif( $setting->isFieldNameExist($_POST['field_name']) )
    {
    	$data['error']['field_name'] = 'Field name already exist!';
    }

    if( $featured->isEmpty($_POST['content']))
    {
    	$data['error']['content'] = 'Content type required!';
    }
    elseif( ! in_array($_POST['content'],$content_array) )
    {
    	$data['error']['content'] = 'Content type is not valid!';	
    }

    if(!isSet($data['error']))
    {
    	$current_time = current_time( 'mysql' );
    	$current_time_gmt = current_time( 'mysql', 1 );
    	$current_user = get_current_user_id();

		    	$wpdb->insert("{$wpdb->prefix}posts", 
				array(  
				'post_title' => $featured->safeStr($_POST['field_name']),	
				'post_content' => $featured->safeStr($_POST['description']),
				'post_type' => 'ups_field',
				'post_author' => $current_user,
				'post_date' => $current_time,
				'post_date_gmt' => $current_time_gmt,
				'post_modified' => $current_time,
				'post_modified_gmt' => $current_time_gmt,  
			), 
			array('%s') 
		);

		$post_id = $wpdb->insert_id;

		add_post_meta( $post_id, 'ups_field_content', $_POST['content'] );

    	$data['success']['message'] = 'Field successfully created!';

    	$row = $setting->getField($post_id);
    	$data['success']['field'] = $row;

    }
	
	echo json_encode($data);
	wp_die();
}

add_action( 'wp_ajax_add_field', 'adding_field' );


function sort_filters()
{
	global $wpdb; 
	$i=1;
		foreach ($_POST['item'] as $value) {
			$post_id =str_replace("item-","",$value);
	    
			$my_post = array(
      'ID'           => $post_id,
      'menu_order'   => $i,
  );

  wp_update_post( $my_post );


	    $i++;
	}
	wp_die();
}

add_action( 'wp_ajax_sort_filters', 'sort_filters' );


function save_other_settings()
{
	global $wpdb;
	$setting = new UPSSettings();
	$featured = new UPSFeatured();
	$current_time = current_time( 'mysql' );
	$current_time_gmt = current_time( 'mysql', 1 );
	$current_user = get_current_user_id();


	$status = $featured->isEmpty($_POST['is_csv_file_added_to_cart']) ? 'draft' : 'publish';

	$post = $setting->getPostStatusByPostType('ups_csv_added_cart');	

	$my_post = array(
		      'ID'           => $post['ID'],
		      'post_type' => 'ups_csv_added_cart',
		      'post_status'   => $status,
		      'post_date' => $current_time,
			  'post_date_gmt' => $current_time_gmt,
			  'post_modified' => $current_time,
			  'post_modified_gmt' => $current_time_gmt, 
		  );

  		wp_insert_post( $my_post );

	wp_die();
}




add_action('wp_ajax_other_settings', 'save_other_settings');