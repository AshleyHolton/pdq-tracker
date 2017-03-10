<?php

class ShowHow_Box extends PDQ_Box
{
	public $slug = 'showhow';
	public $title = 'ShowHow';
	public $meta_priority = 40;
	public $print_priority = 40;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = true;
	public $table_data = array("showhow DATETIME NOT NULL");
	
	public function meta_box($pdq)
	{
		$showhow_date = '';
		$showhow_time = '';
		
		if(isset($pdq->showhow))
		{			
			if($pdq->showhow != '0000-00-00 00:00:00')
			{
				$showhow_date = date('d-m-Y', strtotime($pdq->showhow));
				$showhow_time = date('h:i A', strtotime($pdq->showhow));
			}
		}
		
		?>
			<p><b>Booked Date:</b><input type="text" name="showhow_date" id="showhow_date" value="<?php echo $showhow_date; ?>" /></p>
			<p><b>Booked Time:</b><input type="text" name="showhow_time" id="showhow_time" value="<?php echo $showhow_time; ?>" /></p>
		<?php
	}
	
	protected function print_box($pdq)
	{
		$showhow_date = '';
		$showhow_time = '';
		
		if(isset($pdq->showhow))
		{			
			if($pdq->showhow != '0000-00-00 00:00:00')
			{
				$showhow_date = date('d-m-Y', strtotime($pdq->showhow));
				$showhow_time = date('h:i A', strtotime($pdq->showhow));
				
				?>		
				<table>
					<tr>
						<th>ShowHow</th>
						<th>Complete</th>
					</tr>
					<tr>
						<td><b>Booked Date:</b></td>
						<td><?php echo $showhow_date; ?></td>
					</tr>
					<tr>
						<td><b>Booked Time:</b></td>
						<td><?php echo $showhow_time; ?></td>
					</tr>
				</table>
				<?php
			}
		}
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		if(isset($_POST['showhow_date']) && isset($_POST['showhow_time']))
		{
			$showhow = '';
			
			$showhow_date = esc_html($_POST['showhow_date']);
			$showhow_time = esc_html($_POST['showhow_time']);
			
			if($showhow_date != '' && $showhow_time != '')
			{		
				$showhow = date("Y-m-d H:i:s", strtotime($showhow_date . ' ' . $showhow_time));
			
				$newdata['showhow'] = $showhow;
			}
		}
	}
	
	public function footer_script()
	{
		echo '$("#showhow_date").rules("add", {"dateBR": true, "required": function(element){
				return $("#showhow_time").val().length > 0;
		}});';
		echo '$("#showhow_time").rules("add", {"required": function(element){
				return $("#showhow_date").val().length > 0;
		}});';
		
		echo '$("#showhow_date").datepicker({
				dateFormat: "dd-mm-yy",
		});';
	}
}