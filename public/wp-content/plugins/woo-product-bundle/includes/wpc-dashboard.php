<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCleverDashboard' ) ) {
	class WPCleverDashboard {
		function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		function enqueue_scripts() {
			wp_enqueue_style( 'wpc-dashboard', WPC_URI . 'assets/css/dashboard.css' );
		}
	}

	new WPCleverDashboard();
}