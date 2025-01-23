<?php
/**
 * Plugin Name: Daan - Strip DB Dump
 * Description: Adds shorthands in WP-CLI to easily create database dumps without sensitive data, i.e. customers, users and/or orders.
 * Version: 1.1.0
 * Author: Daan from Daan.dev
 * License: GPLv2 or later
 */

class DaanStripDBDump {
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
		WP_CLI::add_command( 'stripped_db', [ $this, 'dump' ] );
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
		$strip_users     = isset( $assoc_args[ 'strip-users' ] );
		$strip_customers = isset( $assoc_args[ 'strip-customers' ] );
		$strip_orders    = isset( $assoc_args[ 'strip-orders' ] );

		// Add exclude tables based on the flags provided
		$exclude_tables = [];

		global $wpdb;

		// Exclude WordPress users and metadata
		if ( $strip_users ) {
			$exclude_tables[] = $wpdb->users;
			$exclude_tables[] = $wpdb->usermeta;
		}

		// Exclude WooCommerce or EDD customer data
		if ( $strip_customers ) {
			if ( class_exists( 'WooCommerce' ) ) {
				// Exclude WooCommerce customer tables
				$exclude_tables[] = "{$wpdb->prefix}wc_customer_lookup";
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				// Exclude EDD-related customer tables
				$exclude_tables[] = "{$wpdb->prefix}edd_customers";
				$exclude_tables[] = "{$wpdb->prefix}edd_customermeta";
				$exclude_tables[] = "{$wpdb->prefix}edd_customer_email_addresses";
				$exclude_tables[] = "{$wpdb->prefix}edd_customer_addresses";
			}

			// If neither WooCommerce nor EDD is active
			if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
				WP_CLI::warning(
					'Neither WooCommerce nor Easy Digital Downloads is active, skipping customer exclusion.'
				);
			}
		}

		// Exclude WooCommerce or EDD order data
		if ( $strip_orders ) {
			if ( class_exists( 'WooCommerce' ) ) {
				// Exclude WooCommerce order tables
				$exclude_tables[] = "{$wpdb->prefix}wc_orders_meta";
				$exclude_tables[] = "{$wpdb->prefix}wc_orders";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_tax_lookup";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_stats";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_product_lookup";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_operational_data";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_coupon_lookup";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_adresses";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_items";
				$exclude_tables[] = "{$wpdb->prefix}wc_order_itemmeta";
			}

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				// Exclude EDD-related order tables
				$exclude_tables[] = "{$wpdb->prefix}edd_orders";
				$exclude_tables[] = "{$wpdb->prefix}edd_ordermeta";
				$exclude_tables[] = "{$wpdb->prefix}edd_order_transactions";
				$exclude_tables[] = "{$wpdb->prefix}edd_order_items";
				$exclude_tables[] = "{$wpdb->prefix}edd_order_itemmeta";
				$exclude_tables[] = "{$wpdb->prefix}edd_order_adjustments";
				$exclude_tables[] = "{$wpdb->prefix}edd_order_adjustmentmeta";
				$exclude_tables[] = "{$wpdb->prefix}edd_order_adresses";
			}

			// If neither WooCommerce nor EDD is active
			if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
				WP_CLI::warning(
					'Neither WooCommerce nor Easy Digital Downloads is active, skipping order exclusion.'
				);
			}
		}

		// Format exclude tables
		$exclude_option = '';

		if ( ! empty( $exclude_tables ) ) {
			$exclude_option = '--exclude_tables=' . implode( ',', $exclude_tables );
		}

		// Build the command to run
		$command = 'db dump ';

		if ( $exclude_option ) {
			$command .= $filename . ' ' . $exclude_option;
		}

		// Pass any additional arguments
		foreach ( $assoc_args as $key => $value ) {
			if ( ! in_array( $key, [ 'strip-users', 'strip-customers', 'strip-orders' ], true ) ) {
				$command .= " --{$key}=" . escapeshellarg( $value );
			}
		}

		// Execute the command
		WP_CLI::runcommand( $command );
	}
}

new DaanStripDBDump();
