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
		global $pdq_tracker;
		
		echo "<p>Don't forget to save the PDQ after editing.</p>";
		
		if($pdq)
		{
			echo '<p><a href="admin.php?page=pdq-tracker&action=print&pdq=' . $pdq->id . '" target="_blank" id="downloadPDQ">Download PDQ</a></p>';

			if(isset($pdq->status) && $pdq->status == 'incomplete')
			{
				echo '<input type="submit" name="save_pdq" id="save_pdq" class="button primary" value="Save PDQ" onclick="return confirm(\'Are you sure?\')" />';
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'complete-pdq-' . $pdq->id), 'complete_pdq', $pdq->id, 'Complete');
			}
			else if(isset($pdq->status) && $pdq->status == 'complete')
			{
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'incomplete-pdq-' . $pdq->id), 'incomplete_pdq', $pdq->id, 'Reopen');
				if(current_user_can($pdq_tracker->collect_setup_capability)) echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'collect-pdq-' . $pdq->id), 'collect_pdq', $pdq->id, 'Collect');
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'delete-pdq-' . $pdq->id), 'delete_pdq', $pdq->id, 'Delete');
			}
			else if(isset($pdq->status) && $pdq->status == 'ordered')
			{
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'incomplete-pdq-' . $pdq->id), 'incomplete_pdq', $pdq->id, 'Arrived');
			}
			else
			{
				echo sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="button" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'incomplete-pdq-' . $pdq->id), 'incomplete_pdq', $pdq->id, 'Reopen');
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