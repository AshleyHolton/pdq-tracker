<?php

/*if(!defined('WP_UNINSTALL_PLUGIN'))
{
	die;
}

global $wpdb;

$pdq_table = $wpdb->prefix . 'pdq_entries';

$option_names = array('pdq_database_hash', 'pdq_estimated_time');

foreach($option_names as $option)
{
	delete_option($option_name);
}

$wpdb->query("DROP TABLE IF EXISTS $pdq_table");