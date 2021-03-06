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
		console.log(param);
		if((param == "pay_and_collect") && (value.length == 13))
		{
			var re = new RegExp("^(CUR|PCW)[0-9]{10}$");
			
			return re.test(value);
		}
		else if((value.length == 6) && ($.isNumeric(value)) && (param != "pay_and_collect"))
		{
			return true;
		}

		return false;
		
	}, $.validator.format("Please enter a valid receipt number."));
	
	$.validator.addMethod("postcode", function(value, element)
	{
		return this.optional(element) || /^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))$/i.test(value);
	}, "Please specify a valid UK postcode");
	
	//Remove Meta Box Interactions
    $('.postbox .hndle').css('cursor', 'pointer');
    $('.postbox').removeClass('ui-sortable-handle');
});