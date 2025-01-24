<?php
/**
 * Plugin Name: Daan - Strip DB Dump
 * Description: Adds shorthands in WP-CLI to easily create database dumps without sensitive data, i.e. customers, users and/or orders.
 * Version: 1.1.0
 * Author: Daan from Daan.dev
 * License: GPLv2 or later
 */

class DaanStripDBDump {
	const AVAILABLE_ASSOC_ARGS = [ 'users', 'customers', 'orders' ];

	/**
	 * Initializes the class by hooking into the 'cli_init' action to register the CLI command.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'cli_init', [ $this, 'register_cli_command' ] );
	}

	/**
	 * Registers a custom CLI command with WP-CLI.
	 *
	 * @return void
	 */
	public function register_cli_command() {
		WP_CLI::add_command( 'strip-db', [ $this, 'dump' ] );
	}

	/**
	 * Registers and executes the custom `wp db dump` command with additional arguments.
	 *
	 * @param string[] $args       Positional arguments.
	 * @param array    $assoc_args Associative arguments.
	 */
	public function dump( $args, $assoc_args ) {
		// Check and process additional parameters
		$filename        = $args[ 1 ] ?? '';
		$strip_users     = isset( $assoc_args[ self::AVAILABLE_ASSOC_ARGS[ 0 ] ] );
		$strip_customers = isset( $assoc_args[ self::AVAILABLE_ASSOC_ARGS[ 1 ] ] );
		$strip_orders    = isset( $assoc_args[ self::AVAILABLE_ASSOC_ARGS[ 2 ] ] );

		// Add exclude tables based on the flags provided
		$exclude_tables = [];

		global $wpdb;

		// Prepare conditional `--where` clauses for specific tables
		$tables_to_truncate = [];

		if ( $strip_users ) {
			$tables_to_truncate[ $wpdb->users ]    = '1 = 0'; // Excludes all rows but retains the structure
			$tables_to_truncate[ $wpdb->usermeta ] = '1 = 0';
		}

		if ( $strip_customers ) {
			if ( class_exists( 'WooCommerce' ) ) {
				$tables_to_truncate[ "{$wpdb->prefix}wc_customer_lookup" ] = '1 = 0'; // WooCommerce customers
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				$tables_to_truncate[ "{$wpdb->prefix}edd_customers" ]                = '1 = 0'; // EDD customers
				$tables_to_truncate[ "{$wpdb->prefix}edd_customermeta" ]             = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_customer_email_addresses" ] = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_customer_addresses" ]       = '1 = 0';
			}
		}

		// Exclude WooCommerce or EDD order data
		if ( $strip_orders ) {
			if ( class_exists( 'WooCommerce' ) ) {
				// Exclude WooCommerce order tables
				$tables_to_truncate[ "{$wpdb->prefix}wc_orders_meta" ]            = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_orders" ]                 = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_tax_lookup" ]       = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_stats" ]            = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_product_lookup" ]   = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_operational_data" ] = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_coupon_lookup" ]    = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_adresses" ]         = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_items" ]            = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}wc_order_itemmeta" ]         = '1 = 0';
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				// Exclude EDD-related order tables
				$tables_to_truncate[ "{$wpdb->prefix}edd_orders" ]               = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_ordermeta" ]            = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_order_transactions" ]   = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_order_items" ]          = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_order_itemmeta" ]       = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_order_adjustments" ]    = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_order_adjustmentmeta" ] = '1 = 0';
				$tables_to_truncate[ "{$wpdb->prefix}edd_order_addresses" ]      = '1 = 0';
			}

			if ( empty( $tables_to_truncate ) ) {
				WP_CLI::error(
					'No tables specified for stripping data. Use --users, --customers, and/or --orders, otherwise just use wp db dump to make a full database export.'
				);

				return;
			}

			// Build `--table` and `--where` options for wp-cli dump
			$where_clauses = [];

			foreach ( $tables_to_truncate as $table => $where ) {
				$where_clauses[] = ' --tables=' . $table . ' --where=' . escapeshellarg( $where );
			}

			// Build the database dump command
			$dump_command = 'db export ';

			if ( $filename ) {
				$dump_command .= $filename . ' ';
			}

			// Append WHERE conditions for reducing data in specified tables
			$dump_command .= implode( ' ', $where_clauses );

			// Pass any additional arguments
			foreach ( $assoc_args as $key => $value ) {
				if ( ! in_array( $key, self::AVAILABLE_ASSOC_ARGS, true ) ) {
					$dump_command .= " --{$key}=" . escapeshellarg( $value );
				}
			}

			// Execute the command
			WP_CLI::runcommand( $dump_command );
			WP_CLI::success( 'Database dump completed with selected data stripped.' );
		}
	}
}

new DaanStripDBDump();
