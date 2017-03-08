<?php

class PDQ_Box
{
	public $slug = 'null';
	public $title = 'null';
	public $meta_priority = 0;
	public $print_priority = 0;
	public $setup_types = array();
	public $side = false;
	public $table_data = array();
	
	public function __construct()
	{
	}
	
	public function meta_box($pdq){}
	protected function print_box($pdq){}
	public function save_box(&$newdata, &$validation_errors){}
	public function footer_script(){}
	
	public function register_meta_box()
	{
		global $pdq_tracker;
		
		if(in_array($pdq_tracker->current_setup_type, $this->setup_types))
		{
			add_meta_box('meta-box-' . $this->slug, $this->title, array(&$this, 'meta_box'), $pdq_tracker->pdq_admin_pages['pdq-tracker'], (($this->side) ? 'side' : 'normal'), 'low');
		}
	}
	
	public function get_print_box($pdq)
	{
		ob_start();
		
		$this->print_box($pdq);
		
		return ob_get_clean();
	}
}

function add_default_pdq_boxes()
{
	global $pdq_tracker;
	
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/save-pdq-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/store-information-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/customer-details-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/customer-emails-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/software-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/recovery-media-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/data-transfer-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/showhow-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/blancco-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/colleague-notes-box.class.php');
	require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'boxes/apple-id-box.class.php');
	
	$pdq_tracker->add_pdq_box(new Save_PDQ_Box());
	$pdq_tracker->add_pdq_box(new Store_Information_Box());
	$pdq_tracker->add_pdq_box(new Customer_Details_Box());
	$pdq_tracker->add_pdq_box(new Customer_Emails_Box());
	$pdq_tracker->add_pdq_box(new Software_Box());
	$pdq_tracker->add_pdq_box(new Recovery_Media_Box());
	$pdq_tracker->add_pdq_box(new Data_Transfer_Box());
	$pdq_tracker->add_pdq_box(new ShowHow_Box());
	$pdq_tracker->add_pdq_box(new Blancco_Box());
	$pdq_tracker->add_pdq_box(new Colleague_Notes_Box());
	$pdq_tracker->add_pdq_box(new Apple_ID_Box());
}

add_action('add_pdq_boxes', 'add_default_pdq_boxes');