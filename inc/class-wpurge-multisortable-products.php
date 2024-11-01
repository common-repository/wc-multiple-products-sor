<?php
/**
 * This class handles all the multisortable products actions
 *
 * @since 1.0.0
 * @package wpurge_multisort_products
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// if class WPurge_MultiSort_Products is defined, abort.
if ( ! class_exists( 'WPurge_MultiSortable_Products' ) ) {

	/**
	 * Clas to handle multisortable products.
	 */
	class WPurge_MultiSortable_Products {

		/**
		 * $db to define the database.
		 *
		 * @var wpdb
		 */
		private $db;

		/**
		 * Constructor.
		 */
		public function __construct() {

			global $wpdb;
			$this->db = $wpdb;

			// load the plugin text domain for translation.
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// load all assets.
			add_action( 'admin_enqueue_scripts', array( $this, 'wpurge_multisort_products_load_assets' ) );

			add_action( 'wp_ajax_woocommerce_custom_product_sorting', array( $this, 'wpurge_multisort_products_sorting' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_custom_product_sorting', array( $this, 'wpurge_multisort_products_sorting' ) );

			// define the woocommerce_custom_product_sorting callback.
			add_action( 'wp_print_scripts', array( $this, 'wpurge_multisort_products_sorting_dequeue_default_sort' ), 100 );
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			load_plugin_textdomain(
				'wpurge-multisort-products',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);

		}

		/**
		 * Load all the required assets.
		 *
		 * @return void
		 */
		public function wpurge_multisort_products_load_assets() {

			global $wp_query;
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$min       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			if ( current_user_can( 'edit_others_pages' ) && 'edit-product' === $screen_id && isset( $wp_query->query['orderby'] ) && 'menu_order title' === $wp_query->query['orderby'] ) {
				// load css.
				wp_enqueue_style( 'wpurge-multisort-products-css', WPURGE_MULTISORT_PRODUCTS_URL . 'assets/css/wpurge-multisort-products' . $min . '.css', array(), WPURGE_MULTISORT_PRODUCTS_VERSION, 'all' );

				// load multisortable js.
				wp_enqueue_script( 'wpurge-multisort-js', WPURGE_MULTISORT_PRODUCTS_URL . 'assets/js/jquery.multisortable' . $min . '.js', array( 'jquery' ), WPURGE_MULTISORT_PRODUCTS_VERSION, true );

				// load js.
				wp_enqueue_script( 'wpurge-multisort-products-js', WPURGE_MULTISORT_PRODUCTS_URL . 'assets/js/wpurge-multisort-products' . $min . '.js', array( 'jquery' ), WPURGE_MULTISORT_PRODUCTS_VERSION, true );
			}
		}

		/**
		 * Function to dequeue woocommerce_product_ordering script.
		 *
		 * @return void
		 */
		public function wpurge_multisort_products_sorting_dequeue_default_sort() {
			wp_dequeue_script( 'woocommerce_product_ordering' );
		}

		/**
		 * Function to update the product order.
		 *
		 * @return void
		 */
		public function wpurge_multisort_products_sorting() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( ! current_user_can( 'edit_products' ) || empty( $_POST['productIds'] ) ) {
				wp_die( -1 );
			}

			$products    = array_map( 'absint', $_POST['productIds'] );
			$previous_id = absint( $_POST['previd'] ?? 0 );
			$menu_orders = wp_list_pluck( $this->db->get_results( "SELECT ID, menu_order FROM {$this->db->posts} WHERE post_type = 'product' ORDER BY menu_order ASC, post_title ASC" ), 'menu_order', 'ID' );

			$new_sort = array();
			$i        = 1;
			foreach ( $menu_orders as $product_id => $menu_order ) {
				if ( in_array( $product_id, $products, true ) ) {
					continue;
				}

				$this->wpurge_multisort_products_update_product_order( $product_id, $i );

				if ( $previous_id === $product_id ) {
					foreach ( $products as $product ) {
						$i++;
						$this->wpurge_multisort_products_update_product_order( $product, $i );
						$new_sort[ $product_id ] = $i;
					}
				}

				$new_sort[ $product_id ] = $i;
				$i++;
			}

			WC_Post_Data::delete_product_query_transients();
			wp_send_json( $new_sort );

		}

		/**
		 * Function to update the product order. it saves the order in the database.
		 *
		 * @param integer $product_id Product ID.
		 * @param integer $order Order ID.
		 * @return void
		 */
		public function wpurge_multisort_products_update_product_order( int $product_id, int $order ) {
			$this->db->update( $this->db->posts, array( 'menu_order' => $order ), array( 'ID' => $product_id ) );
		}
	}

}
