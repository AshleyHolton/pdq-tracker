<?php

class Recovery_Media_Box extends PDQ_Box
{
	public $slug = 'recovery-media';
	public $title = 'Recovery Media';
	public $meta_priority = 25;
	public $print_priority = 25;
	public $setup_types = array('windows');
	public $side = true;
	public $table_data = array("recovery_media TINYINT(1) NOT NULL");
	
	public function meta_box($pdq)
	{
		$checked = '';
		
		if(isset($pdq->recovery_media))
		{
			 $checked = ($pdq->recovery_media == 1 ? 'checked' : '');
		}
		
		?>
			<p>Backup OS? <input type="checkbox" name="recovery_media" id="recovery_media" <?php echo $checked; ?> value="1" /></p>
		<?php
	}
	
	protected function print_box($pdq)
	{
		if(isset($pdq->recovery_media) && $pdq->recovery_media == 1)
		{
		?>		
			<table>
				<tr>
					<th>Create Recovery Media?</th>
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
		if(isset($_POST['recovery_media']))
		{
			$newdata['recovery_media'] = 1;
		}
		else
		{
			$newdata['recovery_media'] = 0;
		}
	}
}