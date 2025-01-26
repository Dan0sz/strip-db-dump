<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump;

interface OrdersCompatibilityInterface {
	/**
	 * Gets the list of Orders related database tables that need to be truncated for this 3rd party plugin.
	 *
	 * @return array List of table names.
	 */
	public static function get_orders_tables(): array;
}