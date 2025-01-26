<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump\Compatibility;

use Daan\StripDbDump\CompatibilityInterface;

class WPForms implements CompatibilityInterface {
	/**
	 * @inheritDoc
	 */
	public static function get_order_tables() {
		return [
			'wpforms_payments',
			'wpforms_payment_meta',
		];
	}

	public static function get_customer_tables() {
		return [
			'wpforms_entries',
			'wpforms_entry_fields',
			'wpforms_entry_meta',
		];
	}
}
