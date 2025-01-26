<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump\Compatibility;

use Daan\StripDbDump\CompatibilityInterface;

class EasyDigitalDownloads implements CompatibilityInterface {
	/**
	 * @inheritDoc
	 */
	public static function get_order_tables() {
		return [
			'edd_orders',
			'edd_ordermeta',
			'edd_order_transactions',
			'edd_order_items',
			'edd_order_itemmeta',
			'edd_order_adjustments',
			'edd_order_adjustmentmeta',
			'edd_order_addresses',
			'edd_subscriptions',
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function get_customer_tables() {
		return [
			'edd_customers',
			'edd_customermeta',
			'edd_customer_email_addresses',
			'edd_customer_addresses',
			'edd_logs',
			'edd_logs_api_requestmeta',
			'edd_logs_api_requests',
			'edd_logs_emailmeta',
			'edd_logs_emails',
			'edd_logs_file_downloadmeta',
			'edd_logs_file_downloads',
			'edd_notemeta',
			'edd_notes',
		];
	}
}
