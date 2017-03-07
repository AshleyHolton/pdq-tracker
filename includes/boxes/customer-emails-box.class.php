<?php

class Customer_Emails_Box extends PDQ_Box
{
	public $slug = 'customer-emails';
	public $title = 'Customer Emails';
	public $meta_priority = 15;
	public $print_priority = 15;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = false;
	public $table_data = array("customer_email LONGTEXT NOT NULL");
	
	public function meta_box($pdq)
	{
		?>
		
		<table class="form-table customer-email-table">
			<tbody>
				
		<?php
		
		$customer_email_addresses = array();
		$customer_passwords = array();
		$customer_creates = array();
		
		if($pdq)
		{
			$emails = json_decode($pdq->customer_email, true);
			foreach($emails as $email)
			{
				$email_array = explode(':', $email);
				
				if(count($email_array) == 3)
				{
					$checked = '';
					
					if($email_array[2] == 'on')
					{
						$checked = "checked";
					}
					
					?>
					
					<tr valign="top">
						<td>
							<input type="email" id="customer_email_address[]" name="customer_email_address[]" value="<?php echo $email_array[0]; ?>" placeholder="Email Address" />
						</td>
						<td>
							<input type="text" id="customer_email_password[]" name="customer_email_password[]" value="<?php echo $email_array[1]; ?>" placeholder="Password" />
						</td>
						<td>
							<input type="hidden" id="customer_email_create[]" name="customer_email_create[]" value="off">
							<p>Create? <input type="checkbox" id="customer_email_create[]" name="customer_email_create[]" value="on" <?php echo $checked; ?> /></p>
						</td>
						<td>
							<a href="#" class="remove_field">Remove</a>
						</td>
					</tr>
					
					<?php
				}
			}
		}
		
		?>
		
			</tbody>
		</table>
		<button class="button add_new_email">Add</button>
		
		<?php
	}
	
	protected function print_box($pdq)
	{
		?>
		
		<table class="form-table customer-email-table">
			<tr valign="top">
				<th>Email Address</th>
				<th>Password</th>
				<th>Create?</th>
				<th>Complete</th>
			</tr>
				
		<?php
		
		$customer_email_addresses = array();
		$customer_passwords = array();
		$customer_creates = array();
		
		if($pdq)
		{
			$emails = json_decode($pdq->customer_email, true);
			foreach($emails as $email)
			{
				$email_array = explode(':', $email);
				
				if(count($email_array) == 3)
				{
					$checked = '';
					
					if($email_array[2] == 'on')
					{
						$checked = "X";
					}
					
					?>
					
					<tr valign="top">
						<td><?php echo $email_array[0]; ?></td>
						<td><?php echo $email_array[1]; ?></td>
						<td><?php echo $checked; ?></td>
						<td></td>
					</tr>
					
					<?php
				}
			}
		}
		
		?>
		
		</table>
		
		<?php
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		if(isset($_POST['customer_email_address']))
		{
			$customer_emails_s = array();
			$customer_emails = array();
			
			$customer_email_addresses = array_map('sanitize_email', $_POST['customer_email_address']);
			$customer_email_passwords = array_map('esc_html', ($_POST['customer_email_password']));
			$customer_email_creates = array_map('esc_html', ($_POST['customer_email_create']));
			
			for($i = 0; $i < count($customer_email_creates); $i++)
			{
				if($customer_email_creates[$i] == "on")
				{
					$customer_email_creates[$i - 1] = "remove";
				}
			}
			
			$customer_email_creates = array_values(array_diff($customer_email_creates, array('remove')));

			for($i = 0; $i < count($customer_email_addresses); $i++)
			{
				$customer_emails[] = $customer_email_addresses[$i] . ':' . $customer_email_passwords[$i] . ':' . $customer_email_creates[$i];
			}
			
			$customer_emails_s = json_encode($customer_emails);
		
			$newdata['customer_email'] = $customer_emails_s;
		}
		else
		{
			$newdata['customer_email'] = json_encode(array());
		}
	}
}