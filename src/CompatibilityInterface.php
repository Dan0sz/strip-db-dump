<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump;

interface CompatibilityInterface {
	/**
	 * Gets the list of database tables of 3rd party plugins that need to be truncated.
	 *
	 * @return array List of table names.
	 */
	public static function get_order_tables();

	/**
	 * Retrieves a list of customer related tables.
	 *
	 * @return array List of table names.
	 */
	public static function get_customer_tables();
}