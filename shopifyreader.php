<?php
require_once __DIR__ . '/dotenv.php';
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/db_mysql.php';
require_once __DIR__ . '/shemas.php';

use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Auth\FileSessionStorage;
use Shopify\Rest\Admin2022_07\Metafield;
use Shopify\Utils;

Context::initialize(
    $_ENV['shopify_api_key'],
    $_ENV['shopify_api_secret_key'],
    'read_products, write_products',
    $_ENV['shopify_shop'],
    new FileSessionStorage('/tmp/php_sessions'),
    '2022-04',
    true,
    false,
);

class shopify_reader{

	function __construct(){

		$this->client = new Rest($_ENV['shopify_shop'], $_ENV['shopify_api_secret_token']);
	}

	function iterate_products(){

		$product_list = [];

		$response = $this->client->get('products');

		$body = $response->getDecodedBody();
		$pageinfo = $response->getPageInfo();
		$this->iterate_products_body($body);

		while($pageinfo->hasNextPage()){

			$response = $this->client->get('products', [], $pageinfo->getNextPageQuery());
			$body = $response->getDecodedBody();
			$pageinfo = $response->getPageInfo();

			$product_list[] = $body;
		}

		foreach($product_list as $body){

			$this->iterate_products_body($body);
		}
	}

	function iterate_products_body($body){

		echo count($body['products'])."\n";

		foreach($body['products'] as $list_body){

			$product = new shopify_product([
					'shopify_list_body' => $list_body,
				]);

			$this->iterate_product($product);
		}
	}

	function iterate_product($product){

		global $db;

		$sku = $product->get_sku();

		$db->query("SELECT 1 FROM receipts WHERE item_sku='".$sku."'");
		if($db->num_rows() > 0){

			$this->save_product($product);
		}
	}

	function save_product($product){

		global $db;

		$p = [];
		
		$p['item_sku'] = $product->get_sku();

		$title = $product->get_title();

		$p['item_description'] = mb_substr($title, 0, 30);
		$p['item_short_description'] = mb_substr($title, 0, 15);

		$db->query("SELECT 1 FROM products WHERE item_sku='".$p['item_sku']."'");
		if($db->num_rows() == 0){

			$shema_product = shema_product();
			$p = shema_apply_filter($p, $shema_product);

			$db->insert_row('products', $p);
		}
	}

	function iterate_orders(){

		$response = $this->client->get('orders',[],['status'=>'any']);

		$body = $response->getDecodedBody();
		$res = $this->iterate_orders_body($body);
	}

	function iterate_orders_body($body){

		echo count($body['orders'])."\n";

		foreach($body['orders'] as $list_body){

			$order = new shopify_order([
					'shopify_list_body' => $list_body,
				]);

			$this->iterate_order($order);
		}

		return ['count'=> count($body['orders']),'last_id' => $order->get_id()];
	}

	function iterate_order($order){

		global $db;

		if($order->get_fulfillment_status() == 'fulfilled'){

			$items = $order->get_items();

			foreach($items as $item){

				$db->query("SELECT 1 FROM receipts WHERE item_sku='".$item['sku']."'");
				if($db->num_rows() > 0){

					$this->save_order($order, $item);
				}
			}
		}
	}

	function save_order($order, $item){

		global $db;

		$ord = [];

		$shipping = $order->get_shipping();

		$ord['order_number'] = $order->get_id();
		$ord['date'] = date('Y-m-d H:i:s', strtotime($order->get_created_at()));
		$ord['customer_id'] = $order->get_customer_id();
		$ord['shipping_first_name'] = $shipping['first_name'];
		$ord['shipping_last_name'] = $shipping['last_name'];
		$ord['shipping_address_1'] = $shipping['address1'];
		$ord['shipping_postcode'] = $shipping['zip'];
		$ord['shipping_city'] = $shipping['city'];
		$ord['shipping_country'] = $shipping['country_code'];
		$ord['item_sku'] = $item['sku'];
		$ord['item_quantity'] = $item['quantity'];

		$db->query("SELECT 1 FROM orders WHERE order_number='".$ord['order_number']."' AND item_sku='".$ord['item_sku']."'");
		if($db->num_rows() == 0){

			$shema_order = shema_order();
			$ord = shema_apply_filter($ord, $shema_order);

			$db->insert_row('orders', $ord);
		}
	}

	function run(){

		$this->iterate_products();
		$this->iterate_orders();
	}
}

class shopify_product{

	var $shopify_body = null;
	var $shopify_meta = null;

	function __construct($args){

		if(isset($args['shopify_list_body'])){

			$this->init_from_shopify_list($args['shopify_list_body']);
		}
	}

	function init_from_shopify_list($body){

		$this->shopify_body = $body;
	}

	function load_shopify_meta(){

		$meta = $this->client->get('products/'.$this->get_shopify_id().'/metafields.json')->getDecodedBody();

		$this->shopify_meta = $meta;
	}

	function get_shopify_id(){

		return $this->shopify_body['id'];
	}

	function get_sku(){

		return $this->shopify_body['variants'][0]['sku'];
	}

	function get_title(){

		return $this->shopify_body['title'];
	}

	function get_vendor(){

		return $this->shopify_body['vendor'];
	}

	function get_product_type(){

		return $this->shopify_body['product_type'];
	}

	function is_visible(){

		if(!$this->shopify_body['status']=='active'){

			return false;
		}

		if(!(intval($this->shopify_body['variants'][0]['inventory_quantity'])>0)){

			return false;
		}

		return true;
	}

	function get_shopify_condition(){

		return $this->get_meta_value('my_fields', 'conditionrating');
	}

	function get_meta_value($namespace, $meta_key){

		if(is_null($this->shopify_meta)){

			$this->load_shopify_meta();
		}

		$value = null;

		foreach($this->shopify_meta['metafields'] as $meta_item){

			if(isset($meta_item['namespace']) AND $meta_item['namespace'] == $namespace AND isset($meta_item['key']) AND $meta_item['key'] == $meta_key){

				$value = $meta_item['value'];
			}
		}

		return $value;
	}

	function get_meta_property_map(){

		return [
			'Colour' => [
					'namespace' => 'mm-google-shopping',
					'key' => 'color'
				],
			'Material' => [
					'namespace' => 'mm-google-shopping',
					'key' => 'material'
				],
			'Depth' => [
					'namespace' => 'my_fields',
					'key' => 'heightexport'
				],
			'Width' => [
					'namespace' => 'my_fields',
					'key' => 'widthexport'
				],
			'Length' => [
					'namespace' => 'my_fields',
					'key' => 'lenghtexport'
				],
			];
	}

	function get_custom_property($property){

		$map = $this->get_meta_property_map();

		$vl = null;

		if(isset($map[$property])){

			$vl = $this->get_meta_value($map[$property]['namespace'], $map[$property]['key']);
		}

		return $vl;
	}

	function get_cover_image(){

		if (isset($this->shopify_body['image'])){

			return $this->shopify_body['image'];

		}else{

			return null;
		}
	}

	function get_images(){

		if (isset($this->shopify_body['images'])){

			return $this->shopify_body['images'];

		}else{

			return null;
		}
	}
}

class shopify_order{

	var $list_body = null;

	function __construct($args){

		if(isset($args['shopify_list_body'])){

			$this->init_from_shopify_list($args['shopify_list_body']);
		}
	}

	function init_from_shopify_list($body){

		$this->list_body = $body;
	}

	function get_id(){

		return $this->list_body['id'];
	}

	function get_financial_status(){

		return $this->list_body['financial_status'];
	}

	function get_fulfillment_status(){

		return $this->list_body['fulfillment_status'];
	}

	function get_created_at(){

		return $this->list_body['created_at'];
	}

	function get_order_number(){

		return $this->list_body['order_number'];
	}

	function get_customer_id(){

		return $this->list_body['customer']['id'];
	}

	function get_shipping(){

		return $this->list_body['shipping_address'];
	}

	function get_items(){

		$items = [];

		foreach($this->list_body['line_items'] as $i_row){

			$it = [
				'sku' => $i_row['sku'],
				'quantity' => $i_row['quantity']
				];

			$items[] = $it;
		}

		return $items;
	}
}
?>