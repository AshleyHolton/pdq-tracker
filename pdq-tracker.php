<?php

/*
Plugin Name: 	PDQ Tracker
Plugin URI:		https://github.com/AshleyHolton/pdq-tracker/
Description: 	Create and track setup PDQs
Version: 		0.0.7
Author:			Ashley Holton
Author URI: 	http://www.ashleyholton.co.uk
*/

require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'includes/helpers.class.php');

define('PDQ_DEBUG', true);

$GLOBALS['pdq_tracker']  = new PDQ_Tracker();

//Statuses: ordered, incomplete, complete, collected
class PDQ_Tracker
{
	protected $pdq_database_version = '0.0.1';
	public $pdq_table_name = null;
	
	public $minify = '';
	
	public $pdq_admin_pages = array();
	
	private $pdq_boxes = array();
	
	public $pdq_types = array('windows' => 'Windows', 'mac' => 'Mac', 'ipad' => 'iPad', 'android' => 'Android');
	
	public $current_setup_type = '';
	
	public $manage_pdqs_capability = 'publish_posts'; //PDQ_MANAGE
	public $manage_settings_capability = 'publish_posts'; //PDQ_EDIT_SETTINGS
	public $approve_showhow_capability = 'publish_posts'; //PDQ_SH_Approve
	public $collect_setup_capability = 'publish_posts'; //PDQ_COLLECT_SETUP
	public $update_database_capability = 'publish_posts'; //PDQ_UPDATE_DB
	
	public function __construct()
	{
		global $wpdb;
		
		if(defined('PDQ_DEBUG_MODE') && PDQ_DEBUG_MODE)
		{
			error_reporting(-1);
			
			$this->minify = '.min';
		}

		$this->pdq_table = $wpdb->prefix . 'pdq_entries';
		
		add_action('current_screen', array(&$this, 'purge_pdqs'));
		add_action('current_screen', array(&$this, 'register_pdq_boxes'));
		add_action('current_screen', array(&$this, 'add_pdq'));
		add_action('current_screen', array(&$this, 'update_pdq'));
		add_action('current_screen', array(&$this, 'delete_pdq'));
		add_action('current_screen', array(&$this, 'complete_pdq'));
		add_action('current_screen', array(&$this, 'incomplete_pdq'));
		add_action('current_screen', array(&$this, 'collect_pdq'));
		add_action('current_screen', array(&$this, 'update_settings'));
		add_action('current_screen', array(&$this, 'register_pdq_list'));
		
		add_action('admin_menu', array(&$this, 'add_admin'));
		add_action('admin_menu', array(&$this, 'additional_plugin_setup'));
		
		add_action("admin_enqueue_scripts", array(&$this, 'admin_scripts'));
		
		add_action('admin_notices', array(&$this, 'admin_notices'));
		
		add_action('admin_print_footer_scripts', array(&$this, 'admin_validation_scripts'));
	}
	
	public function admin_validation_scripts()
	{
		echo "<script type='text/javascript'>\n";
		echo "jQuery(document).ready(function($){\n";
		
		foreach($this->pdq_boxes as $box)
		{
			if(in_array($this->current_setup_type, $box->setup_types))
			{				
				$box->footer_script();
			}
		}
		
		echo "});\n</script>";
	}
	
	public function additional_plugin_setup()
	{
		$current_page = $this->pdq_admin_pages['pdq-tracker'];
		
		if(current_user_can('update_plugins'))
		{
			require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'includes/updater.class.php');
			$updater = new Updater(__FILE__);
		}
		
		if(class_exists('User_Role_Editor'))
		{
			$this->manage_pdqs_capability = 'PDQ_MANAGE';
			$this->manage_settings_capability = 'PDQ_EDIT_SETTINGS';
			$this->approve_showhow_capability = 'PDQ_SH_Approve';
			$this->collect_setup_capability = 'PDQ_COLLECT_SETUP';
			$this->update_database_capability = 'PDQ_UPDATE_DB';
			
			$main_roles = new WP_Roles();
			$main_roles->add_cap('administrator', $this->manage_pdqs_capability);
			$main_roles->add_cap('administrator', $this->manage_settings_capability);
			$main_roles->add_cap('administrator', $this->approve_showhow_capability);
			$main_roles->add_cap('administrator', $this->collect_setup_capability);
			$main_roles->add_cap('administrator', $this->update_database_capability);
		}
	}
	
	public function admin_scripts()
	{
		wp_enqueue_script('jquery-ui-datepicker');
		wp_register_style('jquery-ui', plugins_url("/css/jquery-ui.css", __FILE__));
		wp_enqueue_style('jquery-ui');
		
		wp_enqueue_script('jquery-validate', plugins_url("/js/jquery.validate.js", __FILE__), array(), false, true);
		
		wp_enqueue_script('pdq-main', plugins_url("/js/pdq-main$this->minify.js", __FILE__), array(), false, true);
		
		wp_register_style('admin', plugins_url("/css/admin.css", __FILE__));
		wp_enqueue_style('admin');
	}
	
	public function create_pdq_database()
	{
		global $wpdb;
		
		$charset = (defined('DB_CHARSET' && '' !== DB_CHARSET)) ? DB_CHARSET : 'utf8';
		$collate = (defined('DB_COLLATE' && '' !== DB_COLLATE)) ? DB_COLLATE : 'utf8_general_ci';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$pdq_box_sql = '';
		
		foreach($this->pdq_boxes as $box)
		{			
			foreach($box->table_data as $line)
			{
				$pdq_box_sql .= ($line . ",\n");
			}
		}

		$pdq_sql = "CREATE TABLE $this->pdq_table (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				setup_type VARCHAR(20) NOT NULL DEFAULT 'windows',
				colleague_id BIGINT(20) NOT NULL DEFAULT '0',
				purchase_date DATETIME NOT NULL,
				est_completion_time INT(2) NOT NULL DEFAULT '0',
				status VARCHAR(10) NOT NULL DEFAULT 'incomplete',
				completed_on DATETIME NOT NULL,
				completed_by BIGINT(20) NOT NULL,
				collected_on DATETIME NOT NULL,
				collected_by BIGINT(20) NOT NULL,
				" . $pdq_box_sql . "PRIMARY KEY  (id)
				) DEFAULT CHARACTER SET $charset COLLATE $collate;";
				
		$hash = sha1($pdq_sql);
				
		if(get_option('pdq_database_hash') != $hash)
		{
			dbDelta($pdq_sql);
			update_option('pdq_database_hash', $hash);
		}
	}
	
	public function add_pdq()
	{
		global $wpdb;

		if(!isset($_POST['action']) || !isset($_GET['page']))
		{
			return;
		}

		if('pdq-tracker' !== $_GET['page'])
		{
			return;
		}

		if('create_pdq' !== $_POST['action'])
		{
			return;
		}

		if(!current_user_can($this->manage_pdqs_capability))
		{
    		wp_die('You do not have sufficient permissions to create a new PDQ.');
		}

		check_admin_referer('pdq_create_nonce');
		
		$newdata = array();
		$validation_errors = array();

		//Immutable Data
		$colleague_id = wp_get_current_user()->ID;
		$date_created = date('Y-m-d H:i:s');
		$est_completion_time = get_option('pdq_estimated_time', 2);
		
		$newdata['colleague_id'] = $colleague_id;
		$newdata['purchase_date'] = $date_created;
		$newdata['est_completion_time'] = $est_completion_time;
		$newdata['setup_type'] = isset($_POST['setup_type']) ? esc_html($_POST['setup_type']) : 'windows';
		
		//Recursive Box Save		
		foreach($this->pdq_boxes as $box)
		{					
			if(in_array($newdata['setup_type'], $box->setup_types))
			{
				$box->save_box($newdata, $validation_errors);
			}
		}
		
		//Check Validation
		if(!empty($validation_errors))
		{
			$text = '';
			
			foreach($validation_errors as $error)
			{
				$text .= $error . ' / ';
			}
			
			wp_die($text);
		}
		
		//wp_die(var_dump($newdata));
		
		$wpdb->query("ALTER TABLE $this->pdq_table DROP COLUMN blanco"); //REMOVE AFTER RUNNING ONCE

		//Push To Database
		if($wpdb->insert($this->pdq_table, $newdata))
		{
			$new_pdq_id = $wpdb->insert_id;

			wp_redirect('admin.php?page=pdq-tracker&action=edit&pdq=' . $new_pdq_id);
			
			exit();
		}
	}

	public function update_pdq()
	{
		global $wpdb;

		if(!isset($_POST['action']) || !isset($_GET['page']))
		{
			return;
		}

		if('pdq-tracker' !== $_GET['page'])
		{
			return;
		}

		if('update_pdq' !== $_POST['action'])
		{
			return;
		}

		if(!current_user_can($this->manage_pdqs_capability))
		{
    		wp_die('You do not have sufficient permissions to update a PDQ.');
		}
		
		check_admin_referer('pdq_update_nonce');
		
		$newdata = array();
		$validation_errors = array();

		//PDQ ID;
		$id = absint($_POST['pdq_id']);
		
		//Recursive Box Save
		$this->current_setup_type = isset($_POST['setup_type']) ? esc_html($_POST['setup_type']) : 'windows';
		
		if(!array_key_exists($this->current_setup_type, $this->pdq_types))
		{
			$this->current_setup_type = 'windows';
		}
		
		foreach($this->pdq_boxes as $box)
		{
			if(in_array($this->current_setup_type, $box->setup_types))
			{
				$box->save_box($newdata, $validation_errors);
			}
		}
		
		//Check Validation
		if(!empty($validation_errors))
		{
			$text = '';
			
			foreach($validation_errors as $error)
			{
				$text .= $error . ' / ';
			}
			wp_die($text);
		}

		//Push To Database
		if($wpdb->update($this->pdq_table, $newdata, array('id' => $id)))
		{
		}
	}

	public function delete_pdq()
	{
		global $wpdb;

		if(!isset($_GET['action']) || !isset($_GET['page']))
		{
			return;
		}

		if('pdq-tracker' !== $_GET['page'])
		{
			return;
		}

		if('delete_pdq' !== $_GET['action'])
		{
			return;
		}

		$id = absint($_GET['pdq']);

		check_admin_referer('delete-pdq-' . $id);

		$wpdb->query($wpdb->prepare("DELETE FROM $this->pdq_table WHERE id = %d", $id));
		
		wp_redirect(add_query_arg('action', 'deleted', 'admin.php?page=pdq-tracker'));
		
		exit();
	}
	
	public function complete_pdq()
	{
		global $wpdb;

		if(!isset($_GET['action']) || !isset($_GET['page']))
		{
			return;
		}

		if('pdq-tracker' !== $_GET['page'])
		{
			return;
		}

		if('complete_pdq' !== $_GET['action'])
		{
			return;
		}

		$id = absint($_GET['pdq']);

		check_admin_referer('complete-pdq-' . $id);
		
		$newdata = array();
		
		$colleague_id = wp_get_current_user()->ID;
		$completed_on = date('Y-m-d H:i:s');
		
		$newdata['completed_by'] = $colleague_id;
		$newdata['completed_on'] = $completed_on;
		$newdata['status'] = 'complete';
		
		//Push To Database
		if($wpdb->update($this->pdq_table, $newdata, array('id' => $id)))
		{
			wp_redirect('admin.php?page=pdq-tracker&action=edit&pdq=' . $id);
		
			exit();
		}
	}
	
	public function incomplete_pdq()
	{
		global $wpdb;

		if(!isset($_GET['action']) || !isset($_GET['page']))
		{
			return;
		}

		if('pdq-tracker' !== $_GET['page'])
		{
			return;
		}

		if('incomplete_pdq' !== $_GET['action'])
		{
			return;
		}

		$id = absint($_GET['pdq']);

		check_admin_referer('incomplete-pdq-' . $id);
		
		$newdata = array();
		
		$newdata['completed_by'] = '';
		$newdata['completed_on'] = '';
		$newdata['collected_by'] = '';
		$newdata['collected_on'] = '';
		$newdata['status'] = 'incomplete';
		
		//Push To Database
		if($wpdb->update($this->pdq_table, $newdata, array('id' => $id)))
		{
			wp_redirect('admin.php?page=pdq-tracker&action=edit&pdq=' . $id);
		
			exit();
		}
	}
	
	public function collect_pdq()
	{
		global $wpdb;

		if(!isset($_GET['action']) || !isset($_GET['page']))
		{
			return;
		}

		if('pdq-tracker' !== $_GET['page'])
		{
			return;
		}

		if('collect_pdq' !== $_GET['action'])
		{
			return;
		}

		$id = absint($_GET['pdq']);

		check_admin_referer('collect-pdq-' . $id);
		
		$newdata = array();
		
		$colleague_id = wp_get_current_user()->ID;
		$collected_on = date('Y-m-d H:i:s');
		
		$newdata['collected_by'] = $colleague_id;
		$newdata['collected_on'] = $collected_on;
		$newdata['status'] = 'collected';
		
		//Push To Database
		if($wpdb->update($this->pdq_table, $newdata, array('id' => $id)))
		{
			wp_redirect('admin.php?page=pdq-tracker&action=edit&pdq=' . $id);
		
			exit();
		}
	}

	public function update_settings()
	{
		if(!isset($_POST['action']) || !isset($_GET['page']))
			return;

		if('pdq-settings' !== $_GET['page'])
			return;

		if('update_settings' !== $_POST['action'])
			return;

		check_admin_referer('pdq_settings_nonce');

		$pdq_estimated_time = $_POST['pdq_estimated_time'];

		if($pdq_estimated_time < 1)
		{
			$pdq_estimated_time = 2;
		}

		update_option('pdq_estimated_time', $pdq_estimated_time);
	}
	
	public function purge_pdqs()
	{
		global $wpdb;
		
		$results = $wpdb->get_results("SELECT * FROM $this->pdq_table WHERE status = 'collected'", OBJECT);
		
		foreach($results as $pdq)
		{
			$diff = Helpers::date_difference($pdq->collected_on, date("Y-m-d H:i:s"));
			
			if($diff >= 30)
			{
				$wpdb->query($wpdb->prepare("DELETE FROM $this->pdq_table WHERE id = %d", $pdq->id));
			}
		}
	}
	
	public function register_pdq_list()
	{
		global $pdqs_list;

		require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'includes/pdq-list.class.php');
		$pdqs_list = new PDQ_Entries_List();
	}
	
	public function all_pdqs()
	{
		global $wpdb, $pdqs_list;

		$order = sanitize_sql_orderby('id ASC');

		$where = apply_filters('pdq_tracker_pre_get_pdqs', '');
		$pdqs = $wpdb->get_results("SELECT id, customer_name FROM $this->pdq_table WHERE 1=1 $where ORDER BY $order");

		if (!$pdqs) :
			echo '<p><h3 id="no-pdqs">You currently do not have any PDQs.</h3></div>';
			return;
		endif;

		echo '<form id="pdqs-filter" method="post" action="">';

		$pdqs_list->views();
		$pdqs_list->prepare_items();

    	$pdqs_list->search_box('search', 'search_id');
    	$pdqs_list->display();

		echo '</form>';
		
		echo '<br /><a href="?page=pdq-tracker&action=ordered" target="_blank" class="button">Download Order Report</a>';
?>

	<?php
	}
	
	public function register_pdq_boxes()
	{
		do_action('add_pdq_boxes');
		
		if(current_user_can($this->update_database_capability))
		{
			$this->create_pdq_database();
		}
	}
	
	public function add_pdq_box($slug, $object)
	{		
		$this->pdq_boxes[$slug] = $object;
	}
	
	public function add_admin()
	{
		$this->pdq_admin_pages['pdq-tracker'] = add_menu_page('PDQ Tracker', 'PDQ Tracker', $this->manage_pdqs_capability, 'pdq-tracker', array(&$this, 'admin_main_page'), 'dashicons-feedback');

		add_submenu_page('pdq-tracker', 'PDQ Tracker', 'All PDQs', $this->manage_pdqs_capability, 'pdq-tracker', array(&$this, 'admin_main_page'));

		foreach($this->pdq_types as $type => $title)
		{
			add_submenu_page('pdq-tracker', 'PDQ Tracker', 'New ' . $this->pdq_types[$type] . ' PDQ', $this->manage_pdqs_capability, 'pdq-tracker&action=add&type=' . $type, array(&$this, 'admin_main_page'));
		}
		
		$this->pdq_admin_pages['pdq-settings'] = add_options_page('PDQ Settings', 'PDQ Settings', $this->manage_settings_capability, 'pdq-settings', array(&$this, 'admin_settings_page'));
	}
	
	public function admin_settings_page()
	{
		?>
			<div class="wrap">
				<h2>PDQ Settings</h2>
				<form action="" method="post">
					<input name="action" type="hidden" value="update_settings" />
					<?php wp_nonce_field('pdq_settings_nonce'); ?>
					<label>Estimated Completion Time: </label><input type="number" name="pdq_estimated_time" id="pdq_estimated_time" value="<?php echo get_option('pdq_estimated_time', 2); ?>" min="1" max="365" />
					<?php submit_button('Save Settings', 'primary', 'save_settings', false); ?>
				</form>
			</div>
		<?php
	}
	
	public function admin_main_page()
	{
		global $wpdb;
		
		$action = isset($_GET['action']) ? $_GET['action'] : false;
		$edit_pdq_id = isset($_GET['pdq']) ? $_GET['pdq'] : '0';

		?>
			<div class="wrap">
				<?php
				if($action && $action == 'add')
				{
					$this->current_setup_type = isset($_GET['type']) ? $_GET['type'] : 'windows';

					if(!array_key_exists($this->current_setup_type, $this->pdq_types))
					{
						$this->current_setup_type = 'windows';
					}
					
					usort($this->pdq_boxes, array($this, "sort_by_meta_priority"));
					
					foreach($this->pdq_boxes as $box)
					{
						$box->register_meta_box();
					}
					
					include_once(trailingslashit(plugin_dir_path(__FILE__)) . 'includes/add-pdq.php');
				}
				else if((!empty($edit_pdq_id) && $edit_pdq_id !== '0') && ($action && $action == 'edit'))
				{
					$order = sanitize_sql_orderby('id DESC');
					$pdq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->pdq_table WHERE id = %d ORDER BY $order", $edit_pdq_id));

					if(!$pdq || $pdq->id !== $edit_pdq_id)
					{
						wp_die('You must select a PDQ');
					}

					$this->current_setup_type = stripslashes($pdq->setup_type);
					
					usort($this->pdq_boxes, array($this, "sort_by_meta_priority"));
					
					foreach($this->pdq_boxes as $box)
					{
						$box->register_meta_box();
					}
					
					include_once(trailingslashit(plugin_dir_path(__FILE__)) . 'includes/edit-pdq.php');
				}
				else if((!empty($edit_pdq_id) && $edit_pdq_id !== '0') && ($action && $action == 'print'))
				{
					$order = sanitize_sql_orderby('id DESC');
					$pdq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->pdq_table WHERE id = %d ORDER BY $order", $edit_pdq_id));

					if (!$pdq || $pdq->id !== $edit_pdq_id)
					{
						wp_die('You must select a PDQ');
					}

					$this->current_setup_type = stripslashes($pdq->setup_type);
					
					//var_dump($this->pdq_boxes);
					
					usort($this->pdq_boxes, array($this, "sort_by_print_priority"));
					
					//var_dump($this->pdq_boxes);
					
					//wp_die();
					
					$box_html = '';
					
					foreach($this->pdq_boxes as $box)
					{
						$box_html .= $box->get_print_box($pdq);
					}
					
					$print_html = '<title>Print PDQ</title>
									<div id="pre-setup-container">
										<div id="pre-setup" class="A4">
											<link rel="stylesheet" href="' . plugins_url("/css/paper.css", __FILE__) . '" type="text/css" />
											<section class="sheet">
												<div style="float:left;">
													<img src="' . plugins_url( '/img/knowhow.png', __FILE__ ) . '" height="48px" style="margin: 0.67em 0;" />
												</div>
												<div style="float:right;">
													<h1><b>' . ucfirst($pdq->setup_type) . ' Setup PDQ</b></h1>
												</div>
												<div style="clear:both;"></div>
												' . $box_html . '
											</section>
										</div>
									</div>';
									
					$print_html = trim(preg_replace('/\s+/', ' ', $print_html)) ;
					
					?>
					
					<script>
						jQuery(document).ready(function($)
						{
							$('html').html('<?php echo $print_html; ?>');
						});
					</script>
					
					<?php
				}
				else if($action && $action == 'ordered')
				{
					$order = sanitize_sql_orderby('id DESC');
					
					$pdqs = $wpdb->get_results("SELECT * FROM $this->pdq_table WHERE status = 'ordered' ORDER BY $order");

					if(!$pdqs) :
						echo '<p><h3 id="no-pdqs">Nothing has been ordered</h3></div>';
						return;
					endif;
					
					$box_html = '';
					
					foreach($pdqs as $pdq)
					{
						$colleague = get_user_by('id', $pdq->colleague_id);
						$colleague_name = $colleague->first_name . ' ' . $colleague->last_name;
						
						$box_html .= '<tr>';
						$box_html .= '<td>' . $pdq->customer_name . '</td>';
						$box_html .= '<td>' . $pdq->customer_postcode . '</td>';
						$box_html .= '<td>' . $colleague_name . '</td>';
						$box_html .= '<td>' . date('d/m/Y', strtotime($pdq->purchase_date)) . '</td>';
						$box_html .= '<td>' . $this->pdq_boxes['store-information']->item_order_types[$pdq->item_order_type] . '</td>';
						$box_html .= '</tr>';
					}
					
					$print_html = '<title>PDQ Order List</title>
									<div id="pre-setup-container">
										<div id="pre-setup" class="A4">
											<link rel="stylesheet" href="' . plugins_url("/css/paper.css", __FILE__) . '" type="text/css" />
											<section class="sheet">
												<div style="float:left;">
													<img src="' . plugins_url( '/img/knowhow.png', __FILE__ ) . '" height="48px" style="margin: 0.67em 0;" />
												</div>
												<div style="float:right;">
													<h1><b>PDQ Order List</b></h1>
													<p>21/03/2017</p>
												</div>
												<div style="clear:both;"></div>
												<table>
													<tr>
														<th>Customer Name</th>
														<th>Customer Postcode</th>
														<th>Colleague</th>
														<th>Purchase Date</th>
														<th>Order Type</th>
													</tr>
													' . $box_html . '
												</table>
											</section>
										</div>
									</div>';
									
					$print_html = trim(preg_replace('/\s+/', ' ', $print_html)) ;
					
					?>
					
					<script>
						jQuery(document).ready(function($)
						{
							$('html').html('<?php echo $print_html; ?>');
						});
					</script>
					
					<?php
				}
				else
				{
				?>
					<h2>All PDQs</h2>
					<div id="pdq-list">
						<div id="pdq-main" class="pdq-order-type-list">
							<?php $this->all_pdqs(); ?>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		<?php
	}
	
	function sort_by_meta_priority($a, $b)
	{
		return ($a->meta_priority > $b->meta_priority);
	}
	
	function sort_by_print_priority($a, $b)
	{
		return ($a->print_priority > $b->print_priority);
	}
	
	public function admin_notices()
	{
		if(!isset($_POST['action']) || !isset($_GET['page']))
		{
			return;
		}

		if(!in_array($_GET['page'], array('pdq-tracker', 'pdq-settings')))
		{
			return;
		}

		switch($_POST['action'])
		{
			case 'create_pdq' :
				echo '<div id="message" class="updated"><p>PDQ created</p></div>';
				break;

			case 'update_pdq' :
				echo '<div id="message" class="updated"><p>PDQ updated</p></div>';
				break;

			case 'deleted' :
				echo '<div id="message" class="updated"><p>PDQ permanently deleted</p></div>';
				break;

			case 'update_settings' :
				echo '<div id="message" class="updated"><p>PDQ Settings Updated</p></div>';
				break;
		}
	}
}

require_once(trailingslashit(plugin_dir_path(__FILE__)) . 'includes/pdq-box.class.php');