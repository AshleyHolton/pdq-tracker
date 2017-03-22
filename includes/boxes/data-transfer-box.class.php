<?php

class Data_Transfer_Box extends PDQ_Box
{
	public $slug = 'data-transfer';
	public $title = 'Data Transfer';
	public $meta_priority = 35;
	public $print_priority = 35;
	public $setup_types = array('windows', 'mac');
	public $side = true;
	public $table_data = array("data_transfer VARCHAR(100) NOT NULL");
	
	public function meta_box($pdq)
	{
		$dt_password = '';
		
		if(isset($pdq->data_transfer))
		{			
			if($pdq->data_transfer != '')
			{
				$dt_password = $pdq->data_transfer;
			}
		}
		?>
			<p>063323 - £35</p>
			<p><b>Password:</b><input type="text" name="data_transfer" id="data_transfer" value="<?php echo $dt_password; ?>" /></p>
		<?php
	}
	
	protected function print_box($pdq)
	{
		$dt_password = '';
		
		if(isset($pdq->data_transfer))
		{			
			if($pdq->data_transfer != '')
			{
				$dt_password = $pdq->data_transfer;
			
				?>		
				<table>
					<tr>
						<th>Data Transfer</th>
						<th>Complete</th>
					</tr>
					<tr>
						<td><b>Password: </b><?php echo $dt_password; ?></td>
						<td></td>
					</tr>
				</table>
				<?php
			}
		}
	}
	
	public function save_box(&$newdata, &$validation_errors)
	{
		$data_transfer = isset($_POST['data_transfer']) ? esc_html($_POST['data_transfer']) : '';
			
		$newdata['data_transfer'] = $data_transfer;
	}
}