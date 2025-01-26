<?php
/**
 * Plugin Name: Daan - Strip DB Dump
 * Description: Adds shorthands in WP-CLI to easily create database dumps without sensitive data, i.e. customers, users and/or orders.
 * Version: 1.1.1
 * Author: Daan from Daan.dev
 * GitHub Plugin URI: Dan0sz/strip-db-dump
 * License: GPLv2 or later
 */

require_once __DIR__ . '/vendor/autoload.php';

// Check if WP-CLI is loaded, and if so, let's go!
if ( class_exists( 'WP_CLI' ) ) {
	$daan_strip_db_dump = new \Daan\StripDbDump\Main();
}
