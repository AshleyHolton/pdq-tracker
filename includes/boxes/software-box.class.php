<?php

class Software_Box extends PDQ_Box
{	
	public $slug = 'software';
	public $title = 'Software';
	public $meta_priority = 20;
	public $print_priority = 20;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = false;
	public $table_data = array("software LONGTEXT NOT NULL");
	
	public $software_default_windows = array("McAfee", "Office", "Cloud", "Adobe Reader", "Skype", "iTunes", "Google Chrome", "Mozilla Firefox");
	public $software_default_mac = array("McAfee", "Office", "Cloud", "Skype", "Google Chrome", "Mozilla Firefox");
	public $software_default_ipad = array("McAfee", "Office", "Cloud");
	public $software_default_android = array("McAfee", "Office", "Cloud");
	
	public function meta_box($pdq)
	{
		global $pdq_tracker;
		
		$software_html = '';

		if($pdq)
		{
			$softwares = json_decode($pdq->software, true);
			
			$i = 0;

			foreach($softwares as $key => $value)
			{
				$software_html .= '
				<tr valign="top">
					<td>
						<input type="text" class="software-name" id="software[' . $i . ']" name="software[' . $i . ']" value="' . $key . '" placeholder="Email Address" />
					</td>
					<td>
						<input type="number" class="software-quantity" id="software_quantity[' . $i . ']" name="software_quantity[' . $i . ']" min="1" max="10" value="' . $value . '" />
					</td>
					<td>
						<a href="#" class="remove_field">Remove</a>
					</td>
				</tr>';
				
				$i++;
			}
		}
		else
		{
			$meta_defaults = $this->{'software_default_' . $pdq_tracker->current_setup_type};
			
			$i = 0;

			foreach($meta_defaults as $key)
			{
				$software_html .= '
				<tr valign="top">
					<td>
						<input type="text" class="software-name "id="software[' . $i . ']" name="software[' . $i . ']" value="' . $key . '" placeholder="Software Name" />
					</td>
					<td>
						<input type="number" class="software-quantity "id="software_quantity[' . $i . ']" name="software_quantity[' . $i . ']" min="1" max="10" value="1" />
					</td>
					<td>
						<a href="#" class="remove_field">Remove</a>
					</td>
				</tr>';
				
				$i++;
			}
		}
		?>
			<table class="form-table software-table">
				<tbody>
					<?php echo $software_html; ?>
				</tbody>
			</table>
			<button class="button add_new_software">Add</button>
		<?php
	}
	
	protected function print_box($pdq)
	{
		$software_html = '';

		if($pdq)
		{
			$softwares = json_decode($pdq->software, true);
			
			if(count($softwares) > 0)
			{
				foreach($softwares as $key => $value)
				{
					$software_html .= '
					<tr>
						<td>' . $key . '</td>
						<td>' . $value . '</td>
						<td></td>
					</tr>';
				}
			
				?>
				<table class="form-table software-table">
					<tr valign="top">
						<th>Software</th>
						<th>Quantity</th>
						<th>Complete</th>
						</tr>
						<?php echo $software_html; ?>
				</table>
				<?php
			}
		}
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		if(isset($_POST['software']))
		{
			$software_s = array();
			$software = array();
		
			$software_names = array_map('esc_html', $_POST['software']);
			$software_quantities = array_map('esc_html', ($_POST['software_quantity']));

			for($i = 0; $i < count($software_names); $i++)
			{
				$software[$software_names[$i]] = $software_quantities[$i];
			}
			
			$software_s = json_encode($software);
		
			$newdata['software'] = $software_s;
		}
		else
		{
			$newdata['software'] = json_encode(array());
		}
	}
	
	public function footer_script()
	{
		echo '
			$.validator.addClassRules("software-name", { required: true });
			$.validator.addClassRules("software-quantity", { required: true, number: true });
			
			//Add Software
			var max_software = 20;
			var software_wrapper = $(".software-table");
			var existing_software = $(".software-table tr").length;
			var add_software_button = $(".add_new_software");
			var numberIncr = existing_software;

			$(add_software_button).click(function (e) {
				e.preventDefault();
				if ($(".software-table tr").length < max_software) {
					$(software_wrapper).append(\'<tr valign="top"><td><input type="text" class="software-name" style="width: 100%;" id="software[\' + numberIncr + \']" name="software[\' + numberIncr + \']" placeholder="Software Name" /></td><td><input type="number" class="software-quantity" style="width: 100%;" id="software_quantity[\' + numberIncr + \']" name="software_quantity[\' + numberIncr + \']" min="1" max="10" value="1" /></td><td><a href="#" class="remove_field" style="width: 100%;">Remove</a></td></tr>\');
					numberIncr++;
				}
			});

			$(software_wrapper).on("click", ".remove_field", function (e) {
				e.preventDefault();
				$(this).closest("tr").remove();
			});	
		';
	}
}