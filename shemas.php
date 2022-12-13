<?php

function shema_order(){

	return [
		'order_number' => ['type' => 'text', 'csv'=>1],
		'date' => ['type' => 'text', 'csv'=>1],
		'customer_id' => ['type' => 'text', 'csv'=>1],
		'shipping_first_name' => ['type' => 'text', 'csv'=>1],
		'shipping_last_name' => ['type' => 'text', 'csv'=>1],
		'shipping_address_1' => ['type' => 'text', 'csv'=>1],
		'shipping_postcode' => ['type' => 'text', 'csv'=>1],
		'shipping_city' => ['type' => 'text', 'csv'=>1],
		'shipping_country' => ['type' => 'text', 'csv'=>1],
		'item_sku' => ['type' => 'text', 'csv'=>1],
		'item_quantity' => ['type' => 'int', 'csv'=>1],
		'committed' => ['type' => 'int'],
		];
}

function shema_receipt(){

	return [
		'receipt_number' => ['type' => 'text', 'csv'=>1],
		'date' => ['type' => 'text', 'csv'=>1],
		'supplier_id' => ['type' => 'text', 'csv'=>1],
		'supplier_name' => ['type' => 'text', 'csv'=>1],
		'supplier_address_1' => ['type' => 'text', 'csv'=>1],
		'supplier_postcode' => ['type' => 'text', 'csv'=>1],
		'supplier_city' => ['type' => 'text', 'csv'=>1],
		'supplier_country' => ['type' => 'text', 'csv'=>1],
		'item_sku' => ['type' => 'text', 'csv'=>1],
		'item_quantity' => ['type' => 'int', 'csv'=>1],
		'committed' => ['type' => 'int'],
		];
}

function shema_product(){

	return [
		'item_sku' => ['type' => 'text', 'csv'=>1],
		'item_description' => ['type' => 'text', 'csv'=>1],
		'item_short_description' => ['type' => 'text', 'csv'=>1],
		'committed' => ['type' => 'int'],
		];
}
?>