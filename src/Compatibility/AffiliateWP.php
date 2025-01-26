<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump\Compatibility;

use Daan\StripDbDump\CustomersCompatibilityInterface;
use Daan\StripDbDump\OrdersCompatibilityInterface;

class AffiliateWP implements CustomersCompatibilityInterface, OrdersCompatibilityInterface {
	/**
	 * @inheritDoc
	 */
	public static function get_orders_tables(): array {
		return [
			'affiliate_wp_referrals',
			'affiliate_wp_referralmeta',
			'affiliate_wp_sales',
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function get_customers_tables(): array {
		return [
			'affiliate_wp_affiliates',
			'affiliate_wp_affiliatemeta',
			'affiliate_wp_customers',
			'affiliate_wp_customermeta',
			'affiliate_wp_lifetime_customers',
			'affiliate_wp_payouts',
			'affiliate_wp_visits',
		];
	}
}
