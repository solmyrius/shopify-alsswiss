<?php
require_once __DIR__ . '/dotenv.php';

require_once __DIR__ . '/db_mysql.php';
require_once __DIR__ . '/shemas.php';

class fileworker{

	function __construct(){

		$this->conn = ssh2_connect($_ENV['sftp_host'], 22);
		ssh2_auth_password($this->conn, $_ENV['sftp_user'], $_ENV['sftp_pass']);
	}

	function run(){

		global $db;

		$shema_order = shema_order();
		$shema_receipt = shema_receipt();
		$shema_product = shema_product();

		$files = [];

		$orders = [];
		$receipts = [];
		$products = [];

		$db->query("SELECT * FROM orders WHERE committed=0");
		while($row = $db->row()){

			$orders[$row['order_number']][] = $row;
		}

		foreach($orders as $order_number => $order){

			$name = 'files/'.$this->generate_name('order');

			$f = fopen($name, 'w');

			fwrite($f, implode(",", $this->csv_head_row($shema_order))."\n");

			foreach($order as $order_row){
	
				fwrite($f, implode(",", $this->csv_data_row($order_row, $shema_order))."\n");
			}

			fclose($f);

			$db->query("UPDATE orders SET committed=1 WHERE order_number='".$order_number."'");

			$files[] = $name;
		}

		$db->query("SELECT * FROM receipts WHERE committed=0");
		while($row = $db->row()){

			$receipts[$row['receipt_number']][] = $row;
		}

		foreach($receipts as $receipt_number => $receipt){

			$name = 'files/'.$this->generate_name('receipt');

			$f = fopen($name, 'w');

			fwrite($f, implode(",", $this->csv_head_row($shema_receipt))."\n");

			foreach($receipt as $receipt_row){
	
				fwrite($f, implode(",", $this->csv_data_row($receipt_row, $shema_receipt))."\n");
			}

			fclose($f);

			$db->query("UPDATE receipts SET committed=1 WHERE receipt_number='".$receipt_number."'");

			$files[] = $name;
		}

		$db->query("SELECT * FROM products WHERE committed=0");
		while($row = $db->row()){

			$products[] = $row;
		}

		if(count($products) > 0){

			$name = 'files/'.$this->generate_name('product');

			$f = fopen($name, 'w');

			fwrite($f, implode(",", $this->csv_head_row($shema_product))."\n");

			foreach($products as $product){

				fwrite($f, implode(",", $this->csv_data_row($product, $shema_product))."\n");
				$db->query("UPDATE products SET committed=1 WHERE item_sku='".$product['item_sku']."'");
			}

			fclose($f);

			$files[] = $name;
		}

		$this->upload_files($files);
	}

	function upload_files($files){

		foreach($files as $filename){

			$dst = 'IN/'.basename($filename);
			ssh2_scp_send($this->conn, $filename, $dst);
			print($dst."\n");
		}
	}

	function generate_name($type){

		global $db;

		switch($type){

			case 'order':
				$name = $_ENV['alsswiss_order'];
				break;

			case 'receipt':
				$name = $_ENV['alsswiss_receipt'];
				break;
			
			case 'product':
				$name = $_ENV['alsswiss_product'];
				break;
		}

		$ts = date('Y-m-d H:i:s');

		$file = [
			'type' => $type,
			'name' => '',
			'ts' => $ts,
			];

		$db->insert_row('files', $file);
		$id = $db->insert_id();

		$name .= '_'.$id.'.csv';

		$db->query("UPDATE files SET name='".$name."' WHERE id='".$id."'");

		return $name;
	}

	function csv_head_row($shema){

		$rows = [];

		foreach($shema as $key => $s){

			if(!empty($s['csv'])){

				$rows[] = $key;
			}
		}

		return $rows;
	}

	function csv_data_row($data, $shema){

		$rows = [];

		foreach($shema as $key => $s){

			if(!empty($s['csv'])){

				if(isset($data[$key])){

					$rows[] = $data[$key];

				}else{

					$rows[] = '';
				}
			}
		}

		return $rows;
	}
}
?>