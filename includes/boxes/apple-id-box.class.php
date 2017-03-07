<?php

class Apple_ID_Box extends PDQ_Box
{
	public $slug = 'apple-id';
	public $title = 'Apple ID';
	public $meta_priority = 10;
	public $print_priority = 10;
	public $setup_types = array('mac', 'ipad');
	public $side = false;
	public $table_data = array("apple_id LONGTEXT NOT NULL");
	
	public function meta_box($pdq)
	{
		$apple_id_email = '';
		$apple_id_password = '';
		
		if($pdq)
		{
			$apple_id = explode(':', $pdq->apple_id);
			
			if(count($apple_id) == 2)
			{
				$apple_id_email = $apple_id[0];
				$apple_id_password = $apple_id[1];
			}
		}
		
		?>
		<a href="https://appleid.apple.com/account#!&page=create" target="_blank">Click here to create an Apple ID with the customer present</a>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<td>
						<input type="email" id="apple_id_email" name="apple_id_email" value="<?php echo $apple_id_email; ?>" placeholder="Email Address" />
					</td>
					<td>
						<input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" />
					</td>
					<!--<td>
						Create? <input type="checkbox" id="apple_id_create" name="apple_id_create" />
					</td>-->
				</tr>
			</tbody>
		</table>
		<!--<hr />
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<td>Existing eMail Address</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
				<tr valign="top">
					<td>Password</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
				<tr valign="top">
					<td>First Name</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
				<tr valign="top">
					<td>Last Name</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
				<tr valign="top">
					<td>Date of Birth</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
				<tr valign="top">
					<td>Favorite children’s book</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
				<tr valign="top">
					<td>Name of your best friend at school</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
				<tr valign="top">
					<td>First album purchased</td>
					<td><input type="text" id="apple_id_password" name="apple_id_password" value="<?php echo $apple_id_password; ?>" placeholder="Password" /></td>
				</tr>
			</tbody>
		</table>-->
		<?php
	}
	
	protected function print_box($pdq)
	{
		$apple_id = explode(':', $pdq->apple_id);
			
		if(count($apple_id) == 2)
		{
			$apple_id_email = $apple_id[0];
			$apple_id_password = $apple_id[1];
			
			?>
			<table class="form-table software-table">
				<tr valign="top">
					<th>Email Address</th>
					<th>Password</th>
					<th>Complete</th>
				</tr>
				<tr valign="top">
					<td><?php echo $apple_id_email; ?></td>
					<td><?php echo $apple_id_password; ?></td>
					<td></td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		if((isset($_POST['apple_id_email']) && $_POST['apple_id_email'] != '') && (isset($_POST['apple_id_password']) && $_POST['apple_id_password'] != ''))
		{			
			$apple_id_email = sanitize_email($_POST['apple_id_email']);
			$apple_id_password = esc_html($_POST['apple_id_password']);
			
			$apple_id = $apple_id_email . ':' . $apple_id_password;
		
			$newdata['apple_id'] = $apple_id;
		}
		else
		{
			$validation_errors['apple_id'] = "Apple ID is required";
		}
	}
}