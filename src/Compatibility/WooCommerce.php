<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump\Compatibility;

use Daan\StripDbDump\CustomersCompatibilityInterface;
use Daan\StripDbDump\OrdersCompatibilityInterface;

class WooCommerce implements CustomersCompatibilityInterface, OrdersCompatibilityInterface {
	/**
	 * @inheritDoc
	 */
	public static function get_orders_tables(): array {
		return [
			'wc_orders_meta',
			'wc_orders',
			'wc_order_tax_lookup',
			'wc_order_stats',
			'wc_order_product_lookup',
			'wc_order_operational_data',
			'wc_order_coupon_lookup',
			'wc_order_addresses',
			'wc_order_items',
			'wc_order_itemmeta',
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function get_customers_tables(): array {
		return [
			'wc_customer_lookup',
		];
	}
}
