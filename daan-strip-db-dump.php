<?php
/**
 * Plugin Name: Daan - Strip DB Dump
 * Description: Adds shorthands in WP-CLI to easily create database dumps without sensitive data, i.e. customers, users and/or orders.
 * Version: 1.0.0
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

		// Strip the file extension, because we need to append numbering to the created files.
		if ( str_contains( $filename, '.sql' ) ) {
			$filename = substr( $filename, 0, - 4 );
		}

		global $wpdb;

		// Prepare conditional `--where` clauses for specific tables
		$tables_to_truncate = [];

		if ( $strip_users ) {
			$tables_to_truncate[] = $wpdb->users;
			$tables_to_truncate[] = $wpdb->usermeta;
		}

		if ( $strip_customers ) {
			if ( class_exists( 'Affiliate_WP' ) ) {
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_affiliates";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_affiliatemeta";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_customers";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_customermeta";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_lifetime_customers";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_payouts";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_visits";
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				$tables_to_truncate[] = "{$wpdb->prefix}edd_customers";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_customermeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_customer_email_addresses";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_customer_addresses";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_logs";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_logs_api_requestmeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_logs_api_requests";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_logs_emailmeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_logs_emails";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_logs_file_downloadmeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_logs_file_downloads";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_notemeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_notes";
			}

			if ( class_exists( 'WooCommerce' ) ) {
				$tables_to_truncate[] = "{$wpdb->prefix}wc_customer_lookup";
			}

			if ( class_exists( 'WPForms' ) ) {
				$tables_to_truncate[] = "{$wpdb->prefix}wpforms_entries";
				$tables_to_truncate[] = "{$wpdb->prefix}wpforms_entry_fields";
				$tables_to_truncate[] = "{$wpdb->prefix}wpforms_entry_meta";
			}
		}

		// Exclude WooCommerce or EDD order data
		if ( $strip_orders ) {
			if ( class_exists( 'Affiliate_WP' ) ) {
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_referrals";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_referralmeta";
				$tables_to_truncate[] = "{$wpdb->prefix}affiliate_wp_sales";
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				// Exclude EDD-related order tables
				$tables_to_truncate[] = "{$wpdb->prefix}edd_orders";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_ordermeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_order_transactions";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_order_items";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_order_itemmeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_order_adjustments";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_order_adjustmentmeta";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_order_addresses";
				$tables_to_truncate[] = "{$wpdb->prefix}edd_subscriptions";
			}

			if ( class_exists( 'WooCommerce' ) ) {
				// Exclude WooCommerce order tables
				$tables_to_truncate[] = "{$wpdb->prefix}wc_orders_meta";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_orders";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_tax_lookup";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_stats";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_product_lookup";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_operational_data";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_coupon_lookup";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_adresses";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_items";
				$tables_to_truncate[] = "{$wpdb->prefix}wc_order_itemmeta";
			}

			if ( class_exists( 'WPForms' ) ) {
				$tables_to_truncate[] = "{$wpdb->prefix}wpforms_payments";
				$tables_to_truncate[] = "{$wpdb->prefix}wpforms_payment_meta";
			}

			if ( empty( $tables_to_truncate ) ) {
				WP_CLI::error(
					'No tables specified for stripping data. Use --users, --customers, and/or --orders, otherwise just use wp db export to make a full database export.'
				);

				return;
			}

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
			WP_CLI::success( sprintf( 'First file created: %s', $filename . '-1.sql' ) );

			// Now build the 2nd export, containing the tables that should be truncated, but maintain their structure.
			$tables_clause = '--tables=' . implode( ',', $tables_to_truncate );
			$where_clause  = '--where="1=0"';

			WP_CLI::runcommand( "db export $filename-2.sql $tables_clause $where_clause $additional_args" );
			WP_CLI::success( sprintf( 'Second file created: %s', $filename . '-2.sql' ) );
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
	}
}

new DaanStripDBDump();
