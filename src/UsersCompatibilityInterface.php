<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump;

interface UsersCompatibilityInterface {
	/**
	 * Defines the list of Users related tables that need to be truncated for the 3rd party plugin.
	 *
	 * @return array List of table names.
	 */
	public static function get_users_tables(): array;
}