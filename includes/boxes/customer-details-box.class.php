<?php

class Customer_Details_Box extends PDQ_Box
{
	public $slug = 'customer-details';
	public $title = 'Customer Details';
	public $meta_priority = 10;
	public $print_priority = 5;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = false;
	public $table_data = array("customer_name VARCHAR(200) NOT NULL", "customer_address VARCHAR(200) NOT NULL", "customer_telephone VARCHAR(50) NOT NULL", "customer_dob DATE NOT NULL");
	
	public function meta_box($pdq)
	{
		?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="customer_name">Customer Name *</label></th>
						<td>
							<input type="text" id="customer_name" name="customer_name" minlength="2" value="<?php echo isset($pdq->customer_name) ? $pdq->customer_name : ''; ?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="customer_address">Customer Address *</label></th>
						<td>
							<input type="text" id="customer_address" name="customer_address" minlength="5" value="<?php echo isset($pdq->customer_address) ? $pdq->customer_address : ''; ?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="customer_telephone">Customer Telephone Number *</label></th>
						<td>
							<input type="text" id="customer_telephone" name="customer_telephone" minlength="10" value="<?php echo isset($pdq->customer_telephone) ? $pdq->customer_telephone : ''; ?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="customer_dob">Customer Date of Birth *</label></th>
						<td>
							<input type="text" id="customer_dob" name="customer_dob" value="<?php echo isset($pdq->customer_dob) ? date('d-m-Y', strtotime($pdq->customer_dob)) : ''; ?>" required />
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
		
		if(empty($customer_telephone))
		{
			$validation_errors['customer_telephone'] = "Customer Telephone Number Is Required";
		}
		
		$newdata['customer_name'] = $customer_name;
		$newdata['customer_address'] = $customer_address;
		$newdata['customer_telephone'] = $customer_telephone;
		$newdata['customer_dob'] = $customer_dob;
	}
	
	public function footer_script()
	{
		echo '$("#customer_name").rules("add", {"minlength": 6, "required": true});';
		echo '$("#customer_address").rules("add", {"minlength": 6, "required": true});';
		echo '$("#customer_telephone").rules("add", {"minlength": 6, "required": true, "number": true});';
		echo '$("#customer_dob").rules("add", {"required": true, "date": true});';
		
		echo '$("#customer_dob").datepicker({
				dateFormat: "dd-mm-yy",
				defaultDate: "01-01-1990",
				changeYear: true,
				yearRange: "c-80:c"
		});';
	}
}