<?php

class PDQ_Entries_List extends WP_List_Table
{
	public $pdq_table;
	public $errors;

	function __construct()
	{
		global $status, $page, $wpdb;

		// Setup global database table names
		$this->pdq_table    = $wpdb->prefix . 'pdq_entries';

		// Set parent defaults
		parent::__construct(array(
			'singular'  => 'PDQ',
			'plural'    => 'PDQs',
			'ajax'      => false
		));

		// Handle our bulk actions
		$this->process_bulk_action();
	}

	function column_default($item, $column_name)
	{
		switch ($column_name) {
			case 'id':
				return $item[ $column_name ];
		}
	}

	function column_customer_name($item)
	{
		$actions = array();

		$customer_name = sprintf('<strong><a href="?page=%s&pdq=%s&action=edit" id="%3$s" class="edit-pdq">%s</a></strong>', $_GET['page'], $item['id'], $item['customer_name']);
		$actions['print'] = sprintf('<a target="_blank" href="?page=%s&action=%s&pdq=%s" id="%3$s" class="view-pdq">%s</a>', $_GET['page'], 'print', $item['id'], __('Print', 'pdq-tracker'));
		$actions['edit'] = sprintf('<a href="?page=%s&action=%s&pdq=%s" id="%3$s" class="view-pdq">%s</a>', $_GET['page'], 'edit', $item['id'], __('Edit', 'pdq-tracker'));
		$actions['clone'] = sprintf('<a href="?page=%s&action=%s&pdq=%s" id="%3$s" class="view-pdq">%s</a>', $_GET['page'], 'clone', $item['id'], __('Clone', 'pdq-tracker'));
		$actions['delete'] = sprintf('<a href="%s&action=%s&pdq=%s" id="%3$s" class="view-pdq" onclick="return confirm(\'Are you sure?\')">%s</a>', wp_nonce_url(admin_url('admin.php?page=pdq-tracker'), 'delete-pdq-' . $item['id']), 'delete_pdq', $item['id'], __('Delete', 'pdq-tracker'));

		return sprintf('%1$s %2$s', $customer_name, $this->row_actions($actions));
	}
	
	function column_customer_postcode($item)
	{
		return $item['customer_postcode'];
	}
	
	function column_urgency($item)
	{
		switch($item['urgency'])
		{
			case 0: $color = '#ff0000'; break; //Red Overdue
			case 1: $color = '#ffcc00'; break; //Amber Due Today
			case 2: $color = '#00ff00'; break; //Green Not Due
			case 3: $color = '#cccccc'; break; //Grey Complete
			case 4: $color = '#007fff'; break; //Blue Collected
			case 5: $color = '#ff00ff'; break; //Pink Ordered
		}
		
		return '<div style="background: ' . $color . '; width: 40px; height: 40px;"></div>';
	}

	function column_cb($item)
	{
		return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id']);
	}

	function column_setup_type($item)
	{

		return ucfirst($item['setup_type']);
	}

	function column_colleague_name($item)
	{
		$colleague = get_user_by('id', $item['colleague_id']);
		$colleague_name = $colleague->first_name . ' ' . $colleague->last_name;

		return $colleague_name;
	}

	function column_purchase_date($item)
	{
		return date(get_option('date_format'), strtotime($item['purchase_date']));
	}

	function column_est_completion_time($item)
	{
		return $item['est_completion_time'] . ' days';
	}
	
	function get_date_diff()
	{
		$est_time = $pdq->est_completion_time;
		$duration = new DateInterval("P" . $est_time . "D");
		$purchase_date = new DateTime(date('Y-m-d', strtotime($pdq->purchase_date)));
		$now = new DateTime(date('Y-m-d'));
			
		$expected_completion_date = $purchase_date->add($duration);
			
		if($expected_completion_date > $now)
		{
			$urgency = '3';
		}
		else if($expected_completion_date < $now)
		{
			$urgency = '1';
		}
		else
		{
			$urgency = '2';
		}
		
		return $urgency;
	}

	function get_columns()
	{
		$columns = array(
			'cb' 					=> '<input type="checkbox" />',
			'customer_name' 		=> 'Customer Name',
			'customer_postcode' 	=> 'Customer Postcode',
			'urgency' 				=> 'Urgency',
			'setup_type'			=> 'Type',
			'colleague_name'		=> 'Colleague',
			'purchase_date'			=> 'Purchase Date',
			'est_completion_time'	=> 'Estimated Completion Time',
		);

		return $columns;
	}
	
	function cmp($a, $b)
	{
		return strcmp($a->urgency, $b->urgency);
	}

	function get_pdqs($orderby = 'id', $order = 'ASC', $per_page, $offset = 0, $search = ''){
		global $wpdb;
		
		$urgency = false;
		
		if($orderby == 'urgency')
		{
			$orderby = 'id';
			$urgency = true;
		}

		// Set OFFSET for pagination
		$offset = ($offset > 0) ? "OFFSET $offset" : '';

		$where = apply_filters('pdq_tracker_pre_get_pdqs', '');

		// If the pdq filter dropdown is used
		if ($this->current_filter_action())
			$where .= ' AND pdqs.id = ' . $this->current_filter_action();

		$sql_order = sanitize_sql_orderby("$orderby $order");
		$cols = $wpdb->get_results("SELECT * FROM $this->pdq_table AS pdqs WHERE 1=1 $where $search ORDER BY $sql_order LIMIT $per_page $offset");
		
		foreach($cols as $pdq)
		{
			$est_time = $pdq->est_completion_time;
			$duration = new DateInterval("P" . $est_time . "D");
			$purchase_date = new DateTime(date('Y-m-d', strtotime($pdq->purchase_date)));
			$now = new DateTime(date('Y-m-d'));
				
			$expected_completion_date = $purchase_date->add($duration);
			
			if($pdq->status == 'incomplete')
			{
				if($expected_completion_date > $now)
				{
					$pdq->urgency = '2';
				}
				else if($expected_completion_date < $now)
				{
					$pdq->urgency = '0';
				}
				else
				{
					$pdq->urgency = '1';
				}
			}
			else if($pdq->status == 'complete')
			{
				$pdq->urgency = '3';
			}
			else if($pdq->status == 'collected')
			{
				$pdq->urgency = '4';
			}
			else if($pdq->status == 'ordered')
			{
				$pdq->urgency = '5';
			}
		}
		
		if($urgency)
		{
			usort($cols, array(&$this, "cmp"));
			
			if(strcasecmp($order, 'ASC'))
			{
				$cols = array_reverse($cols);
			}
		}

		return $cols;
	}

	function get_views() {
		$status_links = array();
		$num_pdqs = $this->get_pdqs_count();
		$class = '';
		$link = '?page=pdq-tracker';

		$stati = array(
			'all'    => _n_noop('All <span class="count">(<span class="pending-count">%s</span>)</span>', 'All <span class="count">(<span class="pending-count">%s</span>)</span>'),
		);

		$total_entries = (int) $num_pdqs->all;
		$entry_status = isset($_GET['status']) ? $_GET['status'] : 'all';

		foreach ($stati as $status => $label) {
			$class = ($status == $entry_status) ? ' class="current"' : '';

			if (!isset($num_pdqs->$status))
				$num_pdqs->$status = 10;

			$link = add_query_arg('status', $status, $link);

			$status_links[ $status ] = "<li class='$status'><a href='$link'$class>" . sprintf(
				translate_nooped_plural($label, $num_pdqs->$status),
				number_format_i18n($num_pdqs->$status)
			) . '</a>';
		}

		return $status_links;
	}

	function get_pdqs_count() {
		global $wpdb;

		$stats = array();

		$count = $wpdb->get_var("SELECT COUNT(*) FROM $this->pdq_table");

		$stats['all'] = $count;

		$stats = (object) $stats;

		return $stats;
	}

	function get_sortable_columns()
	{
		$sortable_columns = array(
			'urgency' 			=> array('urgency', false),
			'id' 				=> array('id', true),
			'customer_name'		=> array('customer_name', true),
			'customer_postcode'	=> array('customer_postcode', true),
			'setup_type'		=> array('setup_type', true),
			'purchase_date'		=> array('purchase_date', true)
		);

		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array();

		// Build the row actions
		$actions['delete'] = 'Delete Permanently';

		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;

		$pdq_id = '';

		// Set the Entry ID array
		if (isset($_POST['pdq'])) {
			if (is_array($_POST['pdq']))
				$pdq_id = $_POST['pdq'];
			else
				$pdq_id = (array) $_POST['pdq'];
		}

		switch($this->current_action()) {
			case 'trash' :
				foreach ($pdq_id as $id) {
					$id = absint($id);
					$wpdb->update($this->pdq_table, array('pdq_approved' => 'trash'), array('id' => $id));
				}
			break;

			case 'delete' :
				foreach ($pdq_id as $id) {
					$id = absint($id);
					$wpdb->query($wpdb->prepare("DELETE FROM $this->pdq_table WHERE id = %d", $id));
				}
			break;

		}
	}

	function current_filter_action() {
		if (isset($_POST['pdq-filter']) && -1 != $_POST['pdq-filter'])
			return absint($_POST['pdq-filter']);

		return false;
	}

	function search_box($text, $input_id) {
	    parent::search_box($text, $input_id);
	}

	function prepare_items() {
		global $wpdb;

		// get the current user ID
		$user = get_current_user_id();

		// get the current admin screen
		$screen = get_current_screen();

		$per_page = 25;

		// Get the date/time format that is saved in the options table
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');

		// What page are we looking at?
		$current_page = $this->get_pagenum();

		// Use offset for pagination
		$offset = ($current_page - 1) * $per_page;

		// Get column headers
		$columns  = $this->get_columns();
		$hidden   = get_hidden_columns($this->screen);

		// Get sortable columns
		$sortable = $this->get_sortable_columns();

		// Build the column headers
		$this->_column_headers = array($columns, $hidden, $sortable);

		// Get entries search terms
		$search_terms = (!empty($_POST['s'])) ? explode(' ', $_POST['s']) : array();

		$searchand = $search = '';
		// Loop through search terms and build query
		foreach($search_terms as $term)
		{
			$term = esc_sql($wpdb->esc_like($term));

			$search .= "{$searchand}((pdqs.customer_name LIKE '%{$term}%') OR (pdqs.setup_type LIKE '%{$term}%') OR (pdqs.customer_postcode LIKE '%{$term}%'))";
			$searchand = ' AND ';
		}

		$search = (!empty($search)) ? " AND ({$search}) " : '';

		// Set our ORDER BY and ASC/DESC to sort the entries
		$orderby  = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'urgency';
		$order    = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

		// Get the sorted entries
		$pdqs = $this->get_pdqs($orderby, $order, $per_page, $offset, $search);

		$data = array();

		// Loop trough the entries and setup the data to be displayed for each row
		foreach ($pdqs as $pdq) :

			$data[] = array(
				'id' 					=> $pdq->id,
				'urgency' 				=> $pdq->urgency,
				'customer_name' 		=> stripslashes($pdq->customer_name),
				'customer_postcode' 	=> stripslashes($pdq->customer_postcode),
				'setup_type' 			=> stripslashes($pdq->setup_type),
				'colleague_id' 			=> stripslashes($pdq->colleague_id),
				'purchase_date'			=> stripslashes($pdq->purchase_date),
				'est_completion_time'	=> stripslashes($pdq->est_completion_time)
			);

		endforeach;

		// How many pdqs do we have?
		$total_items = $this->get_pdqs_count();

		// Add sorted data to the items property
		$this->items = $data;

		// Register our pagination
		$this->set_pagination_args(array(
			'total_items'	=> $total_items->all,
			'per_page'		=> $per_page,
			'total_pages'	=> ceil($total_items->all / $per_page)
		));
	}

	function pagination($which)
	{
		if (empty($this->_pagination_args))
			return;

		extract($this->_pagination_args, EXTR_SKIP);

		$output = '<span class="displaying-num">' . sprintf(_n('1 PDQ', '%s PDQs', $total_items), number_format_i18n($total_items)) . '</span>';

		$current = $this->get_pagenum();

		$current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg(array('hotkeys_highlight_last', 'hotkeys_highlight_first'), $current_url);

		$page_links = array();

		// Added to pick up the months dropdown
		$m = isset($_POST['m']) ? (int) $_POST['m'] : 0;

		$disable_first = $disable_last = '';
		if ($current == 1)
			$disable_first = ' disabled';
		if ($current == $total_pages)
			$disable_last = ' disabled';

		$page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__('Go to the first page'),
			esc_url(remove_query_arg('paged', $current_url)),
			'&laquo;'
		);

		// Modified the add_query_args to include my custom dropdowns
		$page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__('Go to the previous page'),
			esc_url(add_query_arg(array('paged' => max(1, $current-1), 'm' => $m, 'pdq-filter' => $this->current_filter_action()), $current_url)),
			'&lsaquo;'
		);

		if ('bottom' == $which)
			$html_current_page = $current;
		else
			$html_current_page = sprintf("<input class='current-page' title='%s' type='text' name='paged' value='%s' size='%d' />",
				esc_attr__('Current page'),
				$current,
				strlen($total_pages)
			);

		$html_total_pages = sprintf("<span class='total-pages'>%s</span>", number_format_i18n($total_pages));
		$page_links[] = '<span class="paging-input">' . sprintf(_x('%1$s of %2$s', 'paging'), $html_current_page, $html_total_pages) . '</span>';

		$page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__('Go to the next page'),
			esc_url(add_query_arg(array('paged' => min($total_pages, $current+1), 'm' => $m, 'pdq-filter' => $this->current_filter_action()), $current_url)),
			'&rsaquo;'
		);

		// Modified the add_query_args to include my custom dropdowns
		$page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__('Go to the last page'),
			esc_url(add_query_arg(array('paged' => $total_pages, 'm' => $m, 'pdq-filter' => $this->current_filter_action()), $current_url)),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if (! empty($infinite_scroll))
			$pagination_links_class = ' hide-if-js';
		$output .= "\n<span class='$pagination_links_class'>" . join("\n", $page_links) . '</span>';

		if ($total_pages)
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}
}