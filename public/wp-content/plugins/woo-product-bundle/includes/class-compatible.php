<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCleverWoosb_Compatible' ) ) {
	class WPCleverWoosb_Compatible {
		function __construct() {
			/*
			 * WooCommerce PDF Invoices & Packing Slips
			 * https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
			 */

			if ( get_option( '_woosb_compatible_wcpdf_hide_bundles', 'no' ) === 'yes' ) {
				add_filter( 'wpo_wcpdf_order_items_data', array( $this, 'woosb_wcpdf_hide_bundles' ), 99, 1 );
			}

			if ( get_option( '_woosb_compatible_wcpdf_hide_bundled', 'no' ) === 'yes' ) {
				add_filter( 'wpo_wcpdf_order_items_data', array( $this, 'woosb_wcpdf_hide_bundled' ), 99, 1 );
			}

			/*
			 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels
			 * https://en-gb.wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/
			 */

			add_filter( 'wf_pklist_modify_meta_data', array( $this, 'woosb_pklist_hide_meta' ), 99, 1 );

			if ( get_option( '_woosb_compatible_pklist_hide_bundles', 'no' ) === 'yes' ) {
				add_filter( 'wf_pklist_alter_order_items', array( $this, 'woosb_pklist_order_hide_bundles' ), 99, 1 );
				add_filter( 'wf_pklist_alter_package_order_items', array(
					$this,
					'woosb_pklist_package_hide_bundles'
				), 99, 1 );
			}

			if ( get_option( '_woosb_compatible_pklist_hide_bundled', 'no' ) === 'yes' ) {
				add_filter( 'wf_pklist_alter_order_items', array( $this, 'woosb_pklist_order_hide_bundled' ), 99, 1 );
				add_filter( 'wf_pklist_alter_package_order_items', array(
					$this,
					'woosb_pklist_package_hide_bundled'
				), 99, 1 );
			}
		}

		/*
		 * WooCommerce PDF Invoices & Packing Slips
		 * https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
		 */

		function woosb_wcpdf_hide_bundles( $data_list ) {
			foreach ( $data_list as $key => $data ) {
				$bundles = wc_get_order_item_meta( $data['item_id'], '_woosb_ids', true );

				if ( ! empty( $bundles ) ) {
					// hide bundles
					unset( $data_list[ $key ] );
				}
			}

			return $data_list;
		}

		function woosb_wcpdf_hide_bundled( $data_list ) {
			foreach ( $data_list as $key => $data ) {
				$bundled = wc_get_order_item_meta( $data['item_id'], '_woosb_parent_id', true );

				if ( ! empty( $bundled ) ) {
					// hide bundled
					unset( $data_list[ $key ] );
				}
			}

			return $data_list;
		}

		/*
		 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels
		 * https://en-gb.wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/
		 */

		// meta data

		function woosb_pklist_hide_meta( $meta_data ) {
			if ( array_key_exists( '_woosb_ids', $meta_data ) || array_key_exists( '_woosb_parent_id', $meta_data ) ) {
				$meta_data = array();
			}

			return $meta_data;
		}

		// invoice

		function woosb_pklist_order_hide_bundles( $order_items ) {
			foreach ( $order_items as $order_item_id => $order_item ) {
				if ( $order_item->meta_exists( '_woosb_ids' ) ) {
					unset( $order_items[ $order_item_id ] );
				}
			}

			return $order_items;
		}

		function woosb_pklist_order_hide_bundled( $order_items ) {
			foreach ( $order_items as $order_item_id => $order_item ) {
				if ( $order_item->meta_exists( '_woosb_parent_id' ) ) {
					unset( $order_items[ $order_item_id ] );
				}
			}

			return $order_items;
		}

		// package

		function woosb_pklist_package_hide_bundles( $order_package ) {
			foreach ( $order_package as $order_package_key => $order_package_item ) {
				if ( isset( $order_package_item['extra_meta_details'], $order_package_item['extra_meta_details']['_woosb_ids'] ) ) {
					unset( $order_package[ $order_package_key ] );
				}
			}

			return $order_package;
		}

		function woosb_pklist_package_hide_bundled( $order_package ) {
			foreach ( $order_package as $order_package_key => $order_package_item ) {
				if ( isset( $order_package_item['extra_meta_details'], $order_package_item['extra_meta_details']['_woosb_parent_id'] ) ) {
					unset( $order_package[ $order_package_key ] );
				}
			}

			return $order_package;
		}
	}

	new WPCleverWoosb_Compatible();
}