<?php

function shema_order(){

	return [
		'order_number' => ['type' => 'text', 'csv'=>1, 'len'=>20],
		'date' => ['type' => 'text', 'csv'=>1],
		'customer_id' => ['type' => 'text', 'csv'=>1, 'len'=>13],
		'shipping_first_name' => ['type' => 'text', 'csv'=>1, 'len'=>14],
		'shipping_last_name' => ['type' => 'text', 'csv'=>1, 'len'=>15],
		'shipping_address_1' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'shipping_postcode' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'shipping_city' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'shipping_country' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'item_sku' => ['type' => 'text', 'csv'=>1, 'len'=>16],
		'item_quantity' => ['type' => 'int', 'csv'=>1],
		'committed' => ['type' => 'int'],
		];
}

function shema_receipt(){

	return [
		'receipt_number' => ['type' => 'text', 'csv'=>1, 'len'=>20],
		'date' => ['type' => 'text', 'csv'=>1],
		'supplier_id' => ['type' => 'text', 'csv'=>1, 'len'=>13],
		'supplier_name' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'supplier_address_1' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'supplier_postcode' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'supplier_city' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'supplier_country' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'item_sku' => ['type' => 'text', 'csv'=>1, 'len'=>16],
		'item_quantity' => ['type' => 'int', 'csv'=>1],
		'committed' => ['type' => 'int'],
		];
}

function shema_product(){

	return [
		'item_sku' => ['type' => 'text', 'csv'=>1, 'len'=>16],
		'item_description' => ['type' => 'text', 'csv'=>1, 'len'=>30],
		'item_short_description' => ['type' => 'text', 'csv'=>1, 'len'=>15],
		'committed' => ['type' => 'int'],
		];
}

function shema_apply_filter($data, $shema){

	foreach($data as $key => $vl){

		if(isset($shema[$key])){

			$s = $shema[$key];

			if(isset($s['len'])){

				$data[$key] = mb_substr($vl, 0, $s['len'], 'UTF-8');
			}
		}
	}

	return $data;
}
?>