<?php
class UPSSettings
{
	public function isFieldNameExist($val)
	{
		global $wpdb;

		if(trim($val))
		{
			$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}posts WHERE `post_title` = '".esc_sql($val)."' AND `post_type` = 'ups_field' AND `post_status` = 'publish'" );
			if(! empty($row->ID))
			{
				return true;
			}
		}
		return false;
	}
	public function getField($post_id)
	{
		global $wpdb;

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}posts WHERE `ID` = ".(int) $post_id );
		if(! empty($row->ID))
			return $row;

		return false;
	}
	public function getAllFields()
	{
		global $wpdb;
		$rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE `post_type` = 'ups_field' AND `post_status` = 'publish'");
		if(count($rows)) {
		?>
			<div class="all-fields-listings">
		    	<ul class="fields-list">
					<?php foreach($rows as $row) { ?>
													
							<li>
								<?php echo $row->post_title; ?>
							</li>
						
						<?php } ?>
					</ul>
		    </div>
		<?php
		}	
	}
		public function getAllFieldsSortable()
		{
		global $wpdb;
		$rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE `post_type` = 'ups_field' AND `post_status` = 'publish' ORDER BY `menu_order`");
		if(count($rows)) {
		?>
			<div class="all-fields-listings-sortable">		    	
					<ul id="sortable" class="fields-list-sortable">
					<?php foreach($rows as $row) { ?>
													
							<li class="ui-state-default" id="item-<?php echo $row->ID; ?>">
								<?php echo $row->post_title; ?>
							</li>						
						<?php } ?>

						</ul>					
		    </div>
		<?php
		}	
	}
	public function getPostStatusByPostType($post_type)
	{
		global $wpdb;
		$data = array();

		$post =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}posts WHERE `post_type` = '".$post_type."'");

		if( ! empty( $post->ID ))
		{
			$data['post_status'] = $post->post_status;
			$data['ID'] = $post->ID;		
		}
		else
		{
			$data['post_status'] = 'draft';
			$data['ID'] = 0;
		}

			
			return $data;
	}
}