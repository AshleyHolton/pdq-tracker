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
		
		echo '<table style="display:none;" id="software-entry-template">'. $this->new_software_entry('', 1, '#id') .'</table>';
		
		$software_html = '';

		if($pdq)
		{
			$softwares = json_decode($pdq->software, true);
			
			$i = 0;

			foreach($softwares as $key => $value)
			{
				$software_html .= $this->new_software_entry($key, $value, $i);
				
				$i++;
			}
		}
		else
		{
			$meta_defaults = $this->{'software_default_' . $pdq_tracker->current_setup_type};
			
			$i = 0;

			foreach($meta_defaults as $key)
			{
				$software_html .= $this->new_software_entry($key, 1, $i);
				
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
	
	private function new_software_entry($name, $value, $id)
	{
		$html = '
		<tr valign="top">
			<td>
				<input type="text" class="software-name" id="software[' . $id . ']" name="software[' . $id . ']" value="' . $name . '" placeholder="Software Name" />
			</td>
			<td>
				<input type="number" class="software-quantity" id="software_quantity[' . $id . ']" name="software_quantity[' . $id . ']" min="1" max="10" value="' . $value . '" />
			</td>
			<td>
				<a href="#" class="remove_field">Remove</a>
			</td>
		</tr>';
		
		return $html;
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
			
			unset($software_names['#id']);
			unset($software_quantities['#id']);

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
			var numberIncrSoftware = existing_software;

			$(add_software_button).click(function(e)
			{
				e.preventDefault();
				if($(".software-table tr").length < max_software)
				{
					var new_entry = $("#software-entry-template tr").clone();
					
					new_entry.html(new_entry.html().replace(/#id/g, numberIncrSoftware));
					
					$(software_wrapper).append(new_entry);
					
					numberIncrSoftware++;
				}
			});

			$(software_wrapper).on("click", ".remove_field", function(e)
			{
				e.preventDefault();
				$(this).closest("tr").remove();
			});	
		';
	}
}