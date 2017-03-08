jQuery(document).ready(function($)
{
	$("#validation-messages").hide();
	
	$("#pdq-form").validate({
		invalidHandler: function(event, validator){
			var errors = validator.numberOfInvalids();
			if(errors)
			{
				var message = 'There are errors in ' + errors + ' fields. They have been highlighted in red';
				$("#validation-messages").html(message);
				$("#validation-messages").show();
			}
			else
			{
				$("#validation-messages").hide();
			}
		},
		
		errorClass: "validation-error",
		
		errorPlacement: function(error, element){
		}
	});
	
	
	//Remove Meta Box Interactions
    $('.postbox .hndle').css('cursor', 'pointer');
    $('.postbox').removeClass('ui-sortable-handle');
});