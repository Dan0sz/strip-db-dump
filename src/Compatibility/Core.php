<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump\Compatibility;

use Daan\StripDbDump\UsersCompatibilityInterface;

class Core implements UsersCompatibilityInterface {
	/**
	 * @inheritDoc
	 */
	public static function get_users_tables(): array {
		return [
			'users',
			'usermeta',
		];
	}
}
