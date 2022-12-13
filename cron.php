<?php
require_once __DIR__ . '/dotenv.php';
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/db_mysql.php';
require_once __DIR__ . '/shemas.php';
require_once __DIR__ . '/sheetreader.php';
require_once __DIR__ . '/shopifyreader.php';
require_once __DIR__ . '/fileworker.php';

class alsswiss{

	function __construct(){

		$this->sheetreader = new sheet_reader();
		$this->shopifyreader = new shopify_reader();
		$this->fileworker = new fileworker();
	}

	function load_receipts(){

		$receipts = $this->sheetreader->get_reciepts();

		foreach($receipts as $receipt){

			$this->receipt_process($receipt);
		}
	}

	function receipt_process($receipt){

		if(!$receipt['commit']){

			return;
		}

		unset($receipt['commit']);

		if(!$this->receipt_exists($receipt)){

			$this->receipt_add($receipt);
		}
	}

	function receipt_exists($receipt){

		global $db;

		$db->query("SELECT 1 FROM receipts WHERE receipt_number='".$receipt['receipt_number']."' AND item_sku='".$receipt['item_sku']."'");
		if($db->num_rows() == 0){

			return false;
		}

		return true;
	}

	function receipt_add($receipt){

		global $db;

		$shema_receipt = shema_receipt();
		$receipt = shema_apply_filter($receipt, $shema_receipt);

		$db->insert_row('receipts', $receipt);
	}

	function run(){

		$this->load_receipts();

		$this->shopifyreader->run();

		$this->fileworker->run();
	}
}

$dp = new alsswiss();
$dp->run();
?>