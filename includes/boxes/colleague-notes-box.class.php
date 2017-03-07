<?php

class Colleague_Notes_Box extends PDQ_Box
{
	public $slug = 'colleague-notes';
	public $title = 'Colleague Notes';
	public $meta_priority = 45;
	public $print_priority = 45;
	public $setup_types = array('windows', 'mac', 'ipad', 'android');
	public $side = false;
	public $table_data = array("colleague_notes LONGTEXT NOT NULL");
	
	public function meta_box($pdq)
	{
		?>
			<textarea class="large-text" rows="10" name="colleague_notes" id="colleague_notes"><?php if(isset($pdq->colleague_notes)) echo $pdq->colleague_notes; ?></textarea>
		<?php
	}
	
	protected function print_box($pdq)
	{
		if(isset($pdq->colleague_notes)) echo "<b>Notes:</b> <br /><br />" . $pdq->colleague_notes;
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		$colleague_notes = isset($_POST['colleague_notes']) ? esc_html($_POST['colleague_notes']) : '';
		
		$newdata['colleague_notes'] = $colleague_notes;
	}
}