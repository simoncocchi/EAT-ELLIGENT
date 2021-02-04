<?php
/*
Plugin Name:       Login Logout Register Menu
Plugin URI:        http://freewptp.com/plugins/login-logout-register-menu/
Description:       The plugin allows you to add login, logout, register and profile menus in the navigation bar which can be configured from the admin area.
Version:           1.0
Author:            Vinod Dalvi
Author URI:        http://freewptp.com
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Domain Path:       /languages
Text Domain:       login-logout-register-menu

Login Logout Register Menu plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Login Logout Register Menu plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Login Logout Register Menu plugin. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

/**
 * Changelog :
 * 1.0 - Intial release.
 */

/**
 * The file responsible for starting the Login Logout Register Menu plugin
 *
 * The Login Logout Register Menu is a plugin that can be used
 * to display search menu in the navigation bar. This particular file is responsible for
 * including the necessary dependencies and starting the plugin.
 *
 * @package LLRM
 */


/**
 * If this file is called directly, then abort execution.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-login-logout-register-menu-activator.php
 */
function activate_llrm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-login-logout-register-menu-activator.php';
	Login_Logout_Register_Menu_Activator::activate();
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-login-logout-register-menu-deactivator.php
 */
function deactivate_llrm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-login-logout-register-menu-deactivator.php';
	Login_Logout_Register_Menu_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_llrm' );
register_deactivation_hook( __FILE__, 'deactivate_llrm' );


/**
 * Include the core class responsible for loading all necessary components of the plugin.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-login-logout-register-menu.php';

/**
 * Instantiates the Login Logout Register Menu class and then
 * calls its run method officially starting up the plugin.
 */
function run_llrm() {
	$ewpd = new Login_Logout_Register_Menu();
	$ewpd->run();
}

/**
 * Call the above function to begin execution of the plugin.
 */
run_llrm();