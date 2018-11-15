jQuery(document).ready(function(){
	// adding field
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
					jQuery('.fields-list-sortable').append('<li class="ui-state-default" id="item-'+json.success.field.ID+'">'+json.success.field.post_title+'</li>');
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


});

jQuery( function() {
    jQuery( "#sortable" ).sortable({
    	axis: 'y',
	    update: function (event, ui) {
	        var data = $(this).sortable('serialize')+"&action=sort_filters";

	    jQuery.ajax({
            data: data,
            method: 'post',
			url: ajax_path.ajax_url,
			success: function(res)
			{

			}
        });
	        
	    }
    });

    jQuery( "#sortable" ).disableSelection();
  } );