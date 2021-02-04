<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCleverWoosb_Helper' ) ) {
	class WPCleverWoosb_Helper {
		public static function woosb_get_price( $product, $min_or_max = 'min' ) {
			if ( get_option( '_woosb_bundled_price_from', 'sale_price' ) === 'regular_price' ) {
				if ( $product->is_type( 'variable' ) ) {
					if ( $min_or_max === 'max' ) {
						return $product->get_variation_regular_price( 'max' );
					} else {
						return $product->get_variation_regular_price( 'min' );
					}
				} else {
					return $product->get_regular_price();
				}
			} else {
				if ( $product->is_type( 'variable' ) ) {
					if ( $min_or_max === 'max' ) {
						return $product->get_variation_price( 'max' );
					} else {
						return $product->get_variation_price( 'min' );
					}
				} else {
					return $product->get_price();
				}
			}
		}

		public static function woosb_get_price_to_display( $product, $qty = 1, $min_or_max = 'min' ) {
			return (float) wc_get_price_to_display( $product, array(
				'price' => self::woosb_get_price( $product, $min_or_max ),
				'qty'   => $qty
			) );
		}

		public static function woosb_clean_ids( $ids ) {
			$ids = preg_replace( '/[^,.\/0-9]/', '', $ids );

			return $ids;
		}
	}

	new WPCleverWoosb_Helper();
}