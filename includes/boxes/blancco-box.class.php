<?php

class Blancco_Box extends PDQ_Box
{
	public $slug = 'blancco';
	public $title = 'Blancco';
	public $meta_priority = 30;
	public $print_priority = 30;
	public $setup_types = array('windows', 'mac');
	public $side = true;
	public $table_data = array("blancco TINYINT(1) NOT NULL");
	
	public function meta_box($pdq)
	{
		$checked = '';
		
		if(isset($pdq->blancco))
		{
			 $checked = ($pdq->blancco == 1 ? 'checked' : '');
		}
		
		?>
			<p>Full Data Destruction? <input type="checkbox" name="blancco" id="blancco" <?php echo $checked; ?> value="1" /></p>
		<?php
	}
	
	protected function print_box($pdq)
	{
		if(isset($pdq->blancco) && $pdq->blancco == 1)
		{
		?>		
			<table>
				<tr>
					<th>Blancco?</th>
					<th>Complete</th>
				</tr>
				<tr>
					<td>Yes</td>
					<td></td>
				</tr>
			</table>
		<?php
		}
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		if(isset($_POST['blancco']))
		{
			$newdata['blancco'] = 1;
		}
		else
		{
			$newdata['blancco'] = 0;
		}
	}
}