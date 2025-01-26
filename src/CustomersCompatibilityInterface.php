<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump;

interface CustomersCompatibilityInterface {
	/**
	 * Defines a list of Customers related that need to be truncated for the 3rd party plugin.
	 *
	 * @return array List of table names.
	 */
	public static function get_customers_tables(): array;
}