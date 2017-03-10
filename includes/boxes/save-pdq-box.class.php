<?php

class Save_PDQ_Box extends PDQ_Box
{
	public $slug = 'save-pdq';
	public $title = 'Save PDQ';
	public $meta_priority = 0;
	public $print_priority = 0;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = true;
	public $table_data = array();
	
	public function meta_box($pdq)
	{
		echo "<p>Don't forget to save the PDQ after editing.</p>";
		
		if($pdq)
		{
			echo '<p><a href="admin.php?page=pdq-tracker&action=print&pdq=' . $pdq->id . '" target="_blank" id="downloadPDQ">Download PDQ</a></p>';

			if(isset($pdq->status) && $pdq->status !== 'complete')
			{
				echo '<input type="submit" name="save_pdq" id="save_pdq" class="button primary" value="Save PDQ" onclick="return confirm(\'Are you sure?\')" />';
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'complete-pdq-' . $pdq->id), 'complete_pdq', $pdq->id, 'Complete');
			}
			else
			{
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'incomplete-pdq-' . $pdq->id), 'incomplete_pdq', $pdq->id, 'Edit');
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'delete-pdq-' . $pdq->id), 'delete_pdq', $pdq->id, 'Delete');
			}
		}
		else
		{
			echo '<input type="submit" name="save_pdq" id="save_pdq" class="button primary" value="Save PDQ" onclick="return confirm(\'Are you sure?\')" />';
		}
	}
	
	protected function print_box($pdq)
	{
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
	}
}