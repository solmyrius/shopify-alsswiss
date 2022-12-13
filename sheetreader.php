<?php
require_once __DIR__ . '/dotenv.php';
require_once __DIR__ . '/vendor/autoload.php';

class sheet_reader{

	function __construct(){

		$client = new \Google_Client();
		$client->setApplicationName('Sheet Reader');
		$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
		$client->setAccessType('offline');
		$client->setAuthConfig($_ENV['google_cred_path']);

		$this->service = new Google_Service_Sheets($client);

		$this->sheet_id = $_ENV['google_sheet_id'];
	}

	function get_reciepts(){

		$r = [];

		$range = $this->service->spreadsheets_values->get($this->sheet_id, 'A:A')->getValues();
		$started = false;

		foreach($range as $i => $row){

			if(isset($row[0])){

				$cell = $row[0];

				if($started){

					$rp = [
						'receipt_number' => trim($cell)
					];

					$rc = $this->get_receipt_row($i+1);
					$r[] = $rc;
				}

				if(trim($cell) == 'receipt_number'){

					$started = true;
				}
			}
		}

		return $r;
	}

	function get_receipt_row($i){

		$range = $this->service->spreadsheets_values->get($this->sheet_id, 'A'.$i.':K'.$i)->getValues();
		$row = $range[0];

		$receipt = [];
		$receipt['receipt_number'] = trim($row[0]);
		$receipt['date'] = trim($row[1]);
		$receipt['supplier_id'] = trim($row[2]);
		$receipt['supplier_name'] = trim($row[3]);
		$receipt['supplier_address_1'] = trim($row[4]);
		$receipt['supplier_postcode'] = trim($row[5]);
		$receipt['supplier_city'] = trim($row[6]);
		$receipt['supplier_country'] = trim($row[7]);
		$receipt['item_sku'] = trim($row[8]);
		$receipt['item_quantity'] = trim($row[9]);
		$receipt['commit'] = intval($row[10]);

		$datetime = DateTime::createFromFormat('d/m/Y H.i.s', $receipt['date']);
		$receipt['date'] = $datetime->format('Y-m-d H:i:s');

		$receipt['item_quantity'] = intval($receipt['item_quantity']);

		return $receipt;
	}
}

?>