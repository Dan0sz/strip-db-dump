<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump\Compatibility;

use Daan\StripDbDump\CompatibilityInterface;

class Core implements CompatibilityInterface {
	/**
	 * Returns the User related tables for this compatibility fix.
	 *
	 * @return string[]
	 */
	public static function get_user_tables() {
		return [
			'users',
			'usermeta',
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function get_order_tables() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public static function get_customer_tables() {
		return [];
	}
}
