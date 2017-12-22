<?php
/**
 * Table installation procedure.
 */

class WC_Custom_Order_Table_Install {

	/**
	 * The database table schema version.
	 *
	 * @var int
	 */
	protected $table_version = 1;


	/**
	 * Actions to perform on plugin activation.
	 */
	public function activate() {
		$this->maybe_install_tables();
	}

	/**
	 * Retrieve the latest table schema version.
	 *
	 * @return int The latest schema version.
	 */
	public function get_latest_table_version() {
		return absint( $this->table_version );
	}

	/**
	 * Retrieve the current table version from the options table.
	 *
	 * @return int The current schema version.
	 */
	public function get_installed_table_version() {
		return absint( get_option( 'wc_orders_table_version' ) );
	}

	/**
	 * Install or update the tables if the site is not using the current schema.
	 */
	protected function maybe_install_tables() {
		if ( $this->get_installed_table_version() < $this->get_latest_table_version() ) {
			$this->install_tables();
		}
	}

	/**
	 * Perform the database delta to create the table.
	 *
	 * @global $wpdb
	 */
	protected function install_tables() {
		global $wpdb;

		// Load wp-admin/includes/upgrade.php, which defines dbDelta().
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$table  = wc_custom_order_table()->get_table_name();
		$tables = "
			CREATE TABLE {$table} (
				order_id BIGINT UNSIGNED NOT NULL,
				order_key varchar(100) NOT NULL,
				customer_id BIGINT UNSIGNED NOT NULL,
				billing_first_name varchar(100) NOT NULL,
				billing_last_name varchar(100) NOT NULL,
				billing_company varchar(100) NOT NULL,
				billing_address_1 varchar(200) NOT NULL,
				billing_address_2 varchar(200) NOT NULL,
				billing_city varchar(100) NOT NULL,
				billing_state varchar(100) NOT NULL,
				billing_postcode varchar(100) NOT NULL,
				billing_country varchar(100) NOT NULL,
				billing_email varchar(200) NOT NULL,
				billing_phone varchar(200) NOT NULL,
				shipping_first_name varchar(100) NOT NULL,
				shipping_last_name varchar(100) NOT NULL,
				shipping_company varchar(100) NOT NULL,
				shipping_address_1 varchar(200) NOT NULL,
				shipping_address_2 varchar(200) NOT NULL,
				shipping_city varchar(100) NOT NULL,
				shipping_state varchar(100) NOT NULL,
				shipping_postcode varchar(100) NOT NULL,
				shipping_country varchar(100) NOT NULL,
				payment_method varchar(100) NOT NULL,
				payment_method_title varchar(100) NOT NULL,
				discount_total float NOT NULL DEFAULT 0,
				discount_tax float NOT NULL DEFAULT 0,
				shipping_total float NOT NULL DEFAULT 0,
				shipping_tax float NOT NULL DEFAULT 0,
				cart_tax float NOT NULL DEFAULT 0,
				total float NOT NULL DEFAULT 0,
				version varchar(16) NOT NULL,
				currency varchar(3) NOT NULL,
				prices_include_tax tinyint(1) NOT NULL,
				transaction_id varchar(200) NOT NULL,
				customer_ip_address varchar(40) NOT NULL,
				customer_user_agent varchar(200) NOT NULL,
				created_via varchar(200) NOT NULL,
				date_completed datetime DEFAULT NULL,
				date_paid datetime DEFAULT NULL,
				cart_hash varchar(32) NOT NULL,
			PRIMARY KEY  (order_id)
			) $collate;
		";

		// Apply the database migration.
		dbDelta( $tables );

		// Store the table version in the options table.
		update_option( 'wc_orders_table_version', $this->get_latest_table_version() );
	}
}
