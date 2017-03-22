<?php

class Store_Information_Box extends PDQ_Box
{
	public $slug = 'store-information';
	public $title = 'Store Information';
	public $meta_priority = 5;
	public $print_priority = 5;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = true;
	public $table_data = array("receipt_number VARCHAR(13) NOT NULL DEFAULT '0'", "items_left LONGTEXT NOT NULL", "item_order_type VARCHAR(20) NOT NULL DEFAULT 'not_ordered'");
	
	public $item_order_types = array('not_ordered' => 'Not Ordered', 'medics' => 'Medics', 'pay_and_collect' => 'Pay & Collect');
	
	public function meta_box($pdq)
	{
		$date_collected = isset($pdq->collected_) ? $pdq->collected_on : date('Y-m-d H:i:s');

		$collected_by = isset($pdq->collected_by) ? get_user_by('id', $pdq->collected_by) : wp_get_current_user();
		
		if(isset($pdq->status) && $pdq->status == 'collected')
		{
			echo '<p><b>Date Collected:</b> ' . date('jS F Y G:i', strtotime($date_collected)) . '</p>
				<p><b>Collected From:</b> ' . $collected_by->first_name . ' ' . $collected_by->last_name . '</p>
				<hr />';
		}
		
		$date_completed = isset($pdq->completed_on) ? $pdq->completed_on : date('Y-m-d H:i:s');

		$completed_by = isset($pdq->completed_by) ? get_user_by('id', $pdq->completed_by) : wp_get_current_user();
		
		if(isset($pdq->status) && ($pdq->status == 'complete' || $pdq->status == 'collected'))
		{
			echo '<p><b>Date Completed:</b> ' . date('jS F Y G:i', strtotime($date_completed)) . '</p>
				<p><b>Completed By:</b> ' . $completed_by->first_name . ' ' . $completed_by->last_name . '</p>
				<hr />';
		}
		
		$date_created = isset($pdq->purchase_date) ? $pdq->purchase_date : date('Y-m-d H:i:s');

		$colleague = isset($pdq->colleague_id) ? get_user_by('id', $pdq->colleague_id) : wp_get_current_user();

		$receipt_number = isset($pdq->receipt_number) ? $pdq->receipt_number : '';

		$est_completion_time = isset($pdq->est_completion_time) ? $pdq->est_completion_time : get_option('pdq_estimated_time', 2);

		$item_order_type = isset($pdq->item_order_type) ? $pdq->item_order_type : '';

?>
		<p><b>Date Created:</b> <?php echo date('jS F Y G:i', strtotime($date_created)); ?></p>
		<p><b>Sales Colleague:</b> <?php echo $colleague->first_name . ' ' . $colleague->last_name; ?></p>
		<p><b>Receipt/P&C No. *</b>: <input type="text" name="receipt_number" id="receipt_number" value="<?php echo $receipt_number; ?>" /></p>

		<p><b>Estimated Completion Time:</b> <?php echo $est_completion_time; ?> days</p>
		<hr />
		<p><b>Item Ordered:</b> <select name="item_order_type" id="item_order_type">
								<?php
			foreach($this->item_order_types as $key => $value)
			{
				if($key === $item_order_type)
				{
					echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
				}
				else
				{
					echo '<option value="' . $key . '">' . $value . '</option>';
				}
			}
								?>
			</select></p>
		<p><b>Left Items:</b>
		<?php

		$items_html = '';

		if($pdq)
		{
			$items = json_decode($pdq->items_left, true);

			foreach($items as $key)
			{
				$items_html .= '
				<tr valign="top">
					<td>
						<input type="text" class="left-item" id="left_item[]" name="left_item[]" value="' . $key . '" placeholder="Item Name" />
					</td>
					<td>
						<a href="#" class="remove_field">Remove</a>
					</td>
				</tr>';
			}
		}

        ?>
		<table class="form-table left-items-table">
			<tbody>
				<?php echo $items_html; ?>
			</tbody>
		</table>
		<button class="button add_new_left_item">Add</button>
		</p>
	<?php
	}
	
	protected function print_box($pdq)
	{
		$date_created = isset($pdq->purchase_date) ? $pdq->purchase_date : date('Y-m-d H:i:s');

		$colleague = isset($pdq->colleague_id) ? get_user_by('id', $pdq->colleague_id) : wp_get_current_user();

		$receipt_number = isset($pdq->receipt_number) ? $pdq->receipt_number : '';

		$est_completion_time = isset($pdq->est_completion_time) ? $pdq->est_completion_time : get_option('pdq_estimated_time', 2);

		$item_order_type = isset($pdq->item_order_type) ? $pdq->item_order_type : '';
		?>
		
		<table>
			<tr>
				<th>Colleague Name</th>
				<th>Receipt/P&C No.</th>
				<th>Date of Purchase</th>
				<th>Estimated Completion Time</th>
			</tr>
			<tr>
				<td><?php echo $colleague->first_name . ' ' . $colleague->last_name; ?></td>
				<td><?php echo $pdq->receipt_number; ?></td>
				<td><?php echo date('d-m-Y', strtotime($pdq->purchase_date)); ?></td>
				<td><?php echo $pdq->est_completion_time; ?> days</td>
			</tr>
		</table>
		
		<table>
			<tr>
				<th>Item Ordered</th>
				<th>Left Items</th>
			</tr>
			<tr>
				<td><?php echo $this->item_order_types[$item_order_type]; ?></td>
				<td>
					<?php

					if($pdq)
					{
						$items = json_decode($pdq->items_left, true);

						foreach($items as $key)
						{
							echo $key . ' - ';
						}
					}

					?>
				</td>
			</tr>
		</table>
		
		<?php
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		$newdata['status'] = 'incomplete';
		
		//Item Order Type
		$item_order_type = isset($_POST['item_order_type']) ? esc_html($_POST['item_order_type']) : 'not_ordered';
		
		if(!array_key_exists($item_order_type, $this->item_order_types))
		{
			$item_order_type = 'not_ordered';
		}
		
		if($item_order_type != 'not_ordered')
		{
			if($_POST['action'] == 'create_pdq')
			{
				$newdata['status'] = 'ordered';
			}
			else if($_POST['action'] == 'update_pdq')
			{
				if($_POST['status'] && $_POST['status'] == 'ordered')
				{
					$newdata['status'] = 'ordered';
				}
			}
		}
		
		$newdata['item_order_type'] = $item_order_type;
		
		//Receipt Number
		$receipt_number = esc_html($_POST['receipt_number']);
		
		$error = true;
		
		if($item_order_type == 'pay_and_collect')
		{
			if(strlen($receipt_number) == 13 && count(preg_grep("/^(CUR|PCW)[0-9]{10}$/", explode("\n" ,$receipt_number))) == 1)
			{
				$error = false;
			}
		}
		else
		{
			if(strlen($receipt_number) == 6 && is_numeric($receipt_number))
			{
				$error = false;
			}
		}
		
		if($error) $validation_errors['receipt_number'] = "Invalid Receipt Number";		
		
		$newdata['receipt_number'] = $receipt_number;

		//Items Left Behind
		$items_left_behind = isset($_POST['left_item']) ? json_encode(array_map('esc_html', $_POST['left_item'])) : json_encode(array());
		
		$newdata['items_left'] = $items_left_behind;
	}
	
	public function footer_script()
	{		
		echo '$("#receipt_number").rules("add", {"receipt": function(element){
				return $("#item_order_type").find(":selected").val();
		}});';
		
		echo '$.validator.addClassRules("left-item", { required: true });';
		
		echo '
		
		//Add Items Left Behind
		var max_items_left = 20;
		var items_left_wrapper = $(".left-items-table");
		var existing_items = $(".left-items-table tr").length;
		var add_item_left_button = $(".add_new_left_item");
		var numberIncr = existing_items;

		$(add_item_left_button).click(function (e) {
			e.preventDefault();
			if ($(".left-items-table tr").length < max_items_left) {
				$(items_left_wrapper).append(\'<tr valign="top"><td><input type="text" class="left-item" style="width: 100%;" id="left_item[\' + numberIncr + \']" name="left_item[\' + numberIncr + \']" placeholder="Item Name" /></td><td><a href="#" class="remove_field" style="width: 100%;">Remove</a></td></tr>\');
				numberIncr++;
			}
		});

		$(items_left_wrapper).on("click", ".remove_field", function (e) {
			e.preventDefault();
			$(this).closest("tr").remove();
		});
	
	';
	}
}