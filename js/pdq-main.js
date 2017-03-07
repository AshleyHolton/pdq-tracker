jQuery(document).ready(function($)
{	
	//Remove Meta Box Interactions
    $('.postbox .hndle').css('cursor', 'pointer');
    $('.postbox').removeClass('ui-sortable-handle');
	
	//Date Pickers
	$("#showhow_date").datepicker({ 
            dateFormat: 'dd-mm-yy'
    });
	
	$("#customer_dob").datepicker({ 
            dateFormat: 'dd-mm-yy'
    });
	
	//Add Items Left Behind
	var max_items_left = 20;
	var items_left_wrapper = $(".left-items-table");
	var add_item_left_button = $(".add_new_left_item");

	$(add_item_left_button).click(function (e) {
	    e.preventDefault();
	    if ($(".left-items-table tr").length < max_items_left) {
	        $(items_left_wrapper).append('<tr valign="top"><td><input type="text" style="width: 100%;" id="left_item[]" name="left_item[]" placeholder="Item Name" /></td><td><a href="#" class="remove_field" style="width: 100%;">Remove</a></td></tr>');
	    }
	});

	$(items_left_wrapper).on("click", ".remove_field", function (e) {
	    e.preventDefault();
	    $(this).closest('tr').remove();
	});
    
	//Add Customer Emails
	var max_emails = 10;
	var emails_wrapper = $(".customer-email-table");
	var add_email_button = $(".add_new_email");

	$(add_email_button).click(function (e) {
	    e.preventDefault();
	    if ($(".customer-email-table tr").length < max_emails) {
	        $(emails_wrapper).append('<tr valign="top"><td><input type="email" class="regular-text" id="customer_email_address[]" name="customer_email_address[]" placeholder="Email Address" /></td><td><input type="text" class="regular-text" id="customer_email_password[]" name="customer_email_password[]" placeholder="Password" /></td><td><input type="hidden" id="customer_email_create[]" name="customer_email_create[]" value="off">Create? <input type="checkbox" id="customer_email_create[]" name="customer_email_create[]" value="on" /></td><td><a href="#" class="remove_field">Remove</a></td></tr>');
	    }
	});

	$(emails_wrapper).on("click", ".remove_field", function (e) {
	    e.preventDefault();
	    $(this).closest('tr').remove();
	});

    //Add Software
	var max_software = 20;
	var software_wrapper = $(".software-table");
	var add_software_button = $(".add_new_software");

	$(add_software_button).click(function (e) {
	    e.preventDefault();
	    if ($(".software-table tr").length < max_software) {
	        $(software_wrapper).append('<tr valign="top"><td><input type="text" style="width: 100%;" id="software[]" name="software[]" placeholder="Software Name" /></td><td><input type="number" style="width: 100%;" id="software_quantity[]" name="software_quantity[]" min="1" max="10" value="1" /></td><td><a href="#" class="remove_field" style="width: 100%;">Remove</a></td></tr>');
	    }
	});

	$(software_wrapper).on("click", ".remove_field", function (e) {
	    e.preventDefault();
	    $(this).closest('tr').remove();
	});
});