<?php

class Customer_Details_Box extends PDQ_Box
{
	public $slug = 'customer-details';
	public $title = 'Customer Details';
	public $meta_priority = 10;
	public $print_priority = 5;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = false;
	public $table_data = array("customer_name VARCHAR(200) NOT NULL", "customer_address VARCHAR(200) NOT NULL", "customer_postcode VARCHAR(200) NOT NULL", "customer_telephone VARCHAR(50) NOT NULL", "customer_dob DATE NOT NULL");
	
	public function meta_box($pdq)
	{
		?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="customer_name">Name *</label></th>
						<td>
							<input type="text" id="customer_name" name="customer_name" value="<?php echo isset($pdq->customer_name) ? $pdq->customer_name : ''; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="customer_address">Address (First Line) *</label></th>
						<td>
							<input type="text" id="customer_address" name="customer_address" value="<?php echo isset($pdq->customer_address) ? $pdq->customer_address : ''; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="customer_postcode">Postcode *</label></th>
						<td>
							<input type="text" id="customer_postcode" name="customer_postcode" value="<?php echo isset($pdq->customer_postcode) ? $pdq->customer_postcode : ''; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="customer_telephone">Telephone Number *</label></th>
						<td>
							<input type="text" id="customer_telephone" name="customer_telephone" value="<?php echo isset($pdq->customer_telephone) ? $pdq->customer_telephone : ''; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="customer_dob">Date of Birth</label></th>
						<td>
							<input type="text" id="customer_dob" name="customer_dob" value="<?php echo isset($pdq->customer_dob) ? date('d-m-Y', strtotime($pdq->customer_dob)) : ''; ?>" />
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}
	
	protected function print_box($pdq)
	{
		?>
		
		<table>
			<tr>
				<td><b>Name</b></td>
				<td><?php echo $pdq->customer_name; ?></td>
			</tr>
			<tr>
				<td><b>Address</b></td>
				<td><?php echo $pdq->customer_address; ?></td>
			</tr>
			<tr>
				<td><b>Telephone Number</b></td>
				<td><?php echo $pdq->customer_telephone; ?></td>
			</tr>
			<tr>
				<td><b>Date of Birth</b></td>
				<td><?php echo date('d-m-Y', strtotime($pdq->customer_dob)); ?></td>
			</tr>
		</table>
		
		<?php
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		$customer_name = esc_html($_POST['customer_name']);
		$customer_address = esc_html($_POST['customer_address']);
		$customer_telephone = esc_html($_POST['customer_telephone']);
		
		$customer_dob = '';
		
		$customer_dob_post = isset($_POST['customer_dob']) ? esc_html($_POST['customer_dob']) : '';
		
		$customer_postcode = esc_html($_POST['customer_postcode']);
		$customer_postcode = str_replace(' ', '', $customer_postcode);
		$customer_postcode = strtoupper(wordwrap($customer_postcode, strlen($customer_postcode)-3,' ', true));
		
		if($customer_dob_post != '')
		{		
			$customer_dob = date("Y-m-d", strtotime($customer_dob_post));
		}
		else
		{
			$validation_errors['customer_dob'] = "Customer Date of Birth Is Required";
		}
		
		if(empty($customer_name))
		{
			$validation_errors['customer_name'] = "Customer Name Is Required";
		}
		
		if(empty($customer_address))
		{
			$validation_errors['customer_address'] = "Customer Address Is Required";
		}
		
		if(empty($customer_postcode))
		{
			$validation_errors['customer_postcode'] = "Customer Postcode Is Required";
		}
		else
		{
			$postcode = preg_grep("/^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))$/i", explode("\n", $customer_postcode));
			
			if(count($postcode) != 1)
			{
				$validation_errors['customer_postcode'] = "Customer Postcode Is Not Valid";
			}
		}
		
		if(empty($customer_telephone))
		{
			$validation_errors['customer_telephone'] = "Customer Telephone Number Is Required";
		}
		
		$newdata['customer_name'] = $customer_name;
		$newdata['customer_address'] = $customer_address;
		$newdata['customer_postcode'] = $customer_postcode;
		$newdata['customer_telephone'] = $customer_telephone;
		$newdata['customer_dob'] = $customer_dob;
	}
	
	public function footer_script()
	{
		echo '$("#customer_name").rules("add", {"minlength": 6, "required": true});';
		echo '$("#customer_address").rules("add", {"minlength": 6, "required": true});';
		echo '$("#customer_postcode").rules("add", {"required": true});';
		echo '$("#customer_telephone").rules("add", {"minlength": 6, "required": true, "number": true});';
		echo '$("#customer_dob").rules("add", {"required": true, "dateBR": true});';
		
		echo '$("#customer_dob").datepicker({
				dateFormat: "dd-mm-yy",
				defaultDate: "01-01-1990",
				changeYear: true,
				yearRange: "c-80:c"
		});';
	}
}