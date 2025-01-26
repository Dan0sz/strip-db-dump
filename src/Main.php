<?php
/**
 * @package       Daan/Strip DB Dump
 * @author        Daan van den Bergh
 * @license       GPLv2 or Later
 */

namespace Daan\StripDbDump;

use WP_CLI;
use Daan\StripDbDump\Compatibility\AffiliateWP;
use Daan\StripDbDump\Compatibility\Core;
use Daan\StripDbDump\Compatibility\EasyDigitalDownloads;
use Daan\StripDbDump\Compatibility\WooCommerce;
use Daan\StripDbDump\Compatibility\WPForms;

class Main {
	/**
	 * Available arguments for this tool.
	 */
	const AVAILABLE_ASSOC_ARGS = [ 'users', 'customers', 'orders', 'all' ];

	/**
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initializes the necessary actions for the CLI command registration.
	 *
	 * @return void
	 */
	private function init(): void {
		add_action( 'cli_init', [ $this, 'register_cli_command' ] );
	}

	/**
	 * Registers a custom CLI command with WP-CLI.
	 *
	 * @return void
	 */
	public function register_cli_command(): void {
		WP_CLI::add_command( 'strip-db', [ $this, 'dump' ] );
	}

	/**
	 * Registers and executes the custom `wp db dump` command with additional arguments.
	 *
	 * @param string[] $args       Positional arguments.
	 * @param array    $assoc_args Associative arguments.
	 */
	public function dump( array $args, array $assoc_args ): void {
		// Check and process additional parameters
		$filename = $args[ 1 ] ?? '';

		if ( isset( $assoc_args[ self::AVAILABLE_ASSOC_ARGS[ 3 ] ] ) ) {
			$strip_users = $strip_customers = $strip_orders = true;
		} else {
			$strip_users     = isset( $assoc_args[ self::AVAILABLE_ASSOC_ARGS[ 0 ] ] );
			$strip_customers = isset( $assoc_args[ self::AVAILABLE_ASSOC_ARGS[ 1 ] ] );
			$strip_orders    = isset( $assoc_args[ self::AVAILABLE_ASSOC_ARGS[ 2 ] ] );
		}

		// Strip the file extension, because we need to append numbering to the created files.
		if ( str_contains( $filename, '.sql' ) ) {
			$filename = substr( $filename, 0, - 4 );
		}

		if ( ! $filename ) {
			$filename = bin2hex( random_bytes( 3 ) );
		}

		// Prepare conditional `--where` clauses for specific tables
		$tables_to_truncate = [];

		if ( $strip_users ) {
			foreach ( $this->get_users_compatibility_handlers() as $handler ) {
				/** @var $handler UsersCompatibilityInterface */
				$tables_to_truncate = array_merge( $tables_to_truncate, $handler::get_users_tables() );
			}
		}

		if ( $strip_customers ) {
			foreach ( $this->get_customers_compatibility_handlers() as $handler => $plugin_class ) {
				/** @var $handler CustomersCompatibilityInterface */
				if ( class_exists( $plugin_class ) ) {
					$tables_to_truncate = array_merge( $tables_to_truncate, $handler::get_customers_tables() );
				}
			}
		}

		if ( $strip_orders ) {
			foreach ( $this->get_orders_compatibility_handlers() as $handler => $plugin_class ) {
				/** @var $handler OrdersCompatibilityInterface */
				if ( class_exists( $plugin_class ) ) {
					$tables_to_truncate = array_merge( $tables_to_truncate, $handler::get_orders_tables() );
				}
			}
		}

		if ( empty( $tables_to_truncate ) ) {
			WP_CLI::error(
				'No tables specified for stripping data. Use --users, --customers, and/or --orders, otherwise just use wp db export to make a full database export.'
			);

			return;
		}

		global $wpdb;

		// Prefix all define tables.
		$tables_to_truncate = preg_filter( '/^/', $wpdb->prefix, $tables_to_truncate );

		// Build the database dump command
		$additional_args = '';

		// Pass any additional arguments
		foreach ( $assoc_args as $key => $value ) {
			if ( ! in_array( $key, self::AVAILABLE_ASSOC_ARGS, true ) ) {
				$additional_args .= " --{$key}=" . escapeshellarg( $value );
			}
		}

		// Build first export, containing just the table that should maintain their data.
		$all_tables         = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
		$all_tables         = array_map( fn( $table_row ) => $table_row[ 0 ], $all_tables );
		$tables_to_maintain = array_diff( $all_tables, $tables_to_truncate );
		$tables_clause      = '--tables=' . implode( ',', $tables_to_maintain );

		WP_CLI::runcommand( "db export $filename-1.sql $tables_clause $additional_args" );

		// Now build the 2nd export, containing the tables that should be truncated, but maintain their structure.
		$tables_clause = '--tables=' . implode( ',', $tables_to_truncate );
		$where_clause  = '--where="1=0"';

		WP_CLI::runcommand( "db export $filename-2.sql $tables_clause $where_clause $additional_args" );
		WP_CLI::success(
			sprintf(
				'Database exports were successfully created without the selected data. First import %s, followed by %s.',
				$filename . '-1.sql',
				$filename . '-2.sql'
			)
		);

		if ( $strip_users ) {
			WP_CLI::warning(
				sprintf(
					__(
						'All users were stripped from the database, because the --users argument was used. Make sure you run %s after importing.'
					),
					'wp user create <username> <user-email> --role=administrator'
				)
			);
		}
	}

	/**
	 * Returns a list of classes that implement get_user_tables().
	 *
	 * @return array
	 */
	private function get_users_compatibility_handlers(): array {
		return [
			Core::class,
		];
	}

	/**
	 * Returns a list of classes that implement get_customer_tables().
	 *
	 * @return array [ Name of Class that implements the CompatibilityInterface => Class to determine whether the plugin is active ]
	 */
	private function get_customers_compatibility_handlers(): array {
		return [
			AffiliateWP::class          => 'Affiliate_WP',
			EasyDigitalDownloads::class => 'Easy_Digital_Downloads',
			WooCommerce::class          => 'WooCommerce',
			WPForms::class              => 'WPForms',
		];
	}

	/**
	 * Returns a list of classes that implement get_order_tables().
	 *
	 * @return array [ Name of Class that implements the CompatibilityInterface => Class to determine whether the plugin is active ]
	 */
	private function get_orders_compatibility_handlers(): array {
		return [
			AffiliateWP::class          => 'Affiliate_WP',
			EasyDigitalDownloads::class => 'Easy_Digital_Downloads',
			WooCommerce::class          => 'WooCommerce',
			WPForms::class              => 'WPForms',
		];
	}
}
