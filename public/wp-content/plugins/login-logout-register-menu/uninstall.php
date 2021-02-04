<?php

/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link       http://freewptp.com
 * @since      1.0.0
 *
 * @package    LLRM
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}