<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump\Compatibility;

use Daan\StripDbDump\CustomersCompatibilityInterface;
use Daan\StripDbDump\OrdersCompatibilityInterface;

class WPForms implements CustomersCompatibilityInterface, OrdersCompatibilityInterface {
	/**
	 * @inheritDoc
	 */
	public static function get_orders_tables(): array {
		return [
			'wpforms_payments',
			'wpforms_payment_meta',
		];
	}

	public static function get_customers_tables(): array {
		return [
			'wpforms_entries',
			'wpforms_entry_fields',
			'wpforms_entry_meta',
		];
	}
}
