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
	
	$.validator.addMethod("receipt", function(value, element, param){
		
		if(value.length == 6)
		{
			if($.isNumeric(value))
			{
				return true;
			}
		}
		else if(value.length == 13)
		{
			var re = new RegExp("^(CUR|PCW)[0-9]{10}$");
			
			console.log(re.test(value));
			
			return re.test(value);
		}
		
		return false;
		
	}, $.validator.format("Please enter a valid receipt number."));
	
	
	//Remove Meta Box Interactions
    $('.postbox .hndle').css('cursor', 'pointer');
    $('.postbox').removeClass('ui-sortable-handle');
});