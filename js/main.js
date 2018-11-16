jQuery(document).ready(function(){
	// adding field on extra product field setup - settings
	jQuery('body').on('submit','#form_add_field',function(e){
		var form = jQuery(this);
		form.find(".button").attr('disabled');
		jQuery.ajax({
			method: 'post',
			url: ajax_path.ajax_url,
			data: form.serialize(),
			success: function(res){

				form.find(".button").removeAttr('disabled');
				jQuery('.errs, .sucss').remove();
				var json = jQuery.parseJSON(res);
				console.log(json);
				if(json.success)
				{
					form.trigger('reset');
					jQuery('.message').after('<p class="sucss">'+json.success.message+'</p>');
					jQuery('.fields-list').append('<li>'+json.success.field.post_title+'</li>');
					jQuery('.fields-list-sortable').prepend('<li class="ui-state-default" id="item-'+json.success.field.ID+'">'+json.success.field.post_title+'</li>');
				}
				else if(json.error)
				{
					jQuery.each(json.error,function(k,v){
						jQuery('.'+k).after('<p class="errs">'+v+'</p>');
					})
				}
				else
				{
					jQuery('.message').after('<p class="errs">Something not right!</p>');
				}
			}

		});

		e.preventDefault();
	})



	//Save other settings
	jQuery('body').on('submit','#form_other_settings',function(e){
		var form = jQuery(this);
		form.find(".button").attr('disabled');
		jQuery.ajax({
			method: 'post',
			url: ajax_path.ajax_url,
			data: form.serialize(),
			success: function(res){
				form.find(".button").removeAttr('disabled');
				console.log(res);
				
			}

		});

		e.preventDefault();
	})




}); // document.ready ends


// Reordering fields ra product field setup - settings
jQuery( function() {
    jQuery( "#sortable" ).sortable({
    	axis: 'y',
	    update: function (event, ui) {
	    	jQuery('#sortable li').css({'cursor':'wait'});
	        var data = $(this).sortable('serialize')+"&action=sort_filters";

	    jQuery.ajax({
            data: data,
            method: 'post',
			url: ajax_path.ajax_url,
			success: function(res)
			{
				jQuery('#sortable li').css({'cursor':'grab'});
			}
        });
	        
	    }
    });

    jQuery( "#sortable" ).disableSelection();
  });