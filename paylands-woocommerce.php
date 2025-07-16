<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://paylands.com/contacto/
 * @since             1.0.0
 * @package           Paylands_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name: Paylands WooCommerce
 * Plugin URI: http://paylands.com/
 * Description: Start to sell in your store using the Paylands Gateway as your secure payment method.
 * Version: 3.5.2
 * Author: Paylands
 * Author URI: http://paylands.com/contacto/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 3.5.0
 * WC tested up to: 3.9.2
 * Text Domain: paylands-woocommerce
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define( 'PAYLANDS_WOOCOMMERCE_VERSION', '3.5.2' );
define( 'PAYLANDS_ROOT_PATH', plugin_dir_path( __FILE__ ) );

/**
 * WooCommerce fallback notice.
 *
 * @since 4.1.2
 * @return string
 */
function woocommerce_paylands_missing_wc_notice() {
	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Paylands requires WooCommerce to be installed and active. You can download %s here.', 'paylands-woocommerce' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-paylands-woocommerce-activator.php
 */
function activate_paylands_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-paylands-woocommerce-activator.php';
	Paylands_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-paylands-woocommerce-deactivator.php
 */
function deactivate_paylands_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-paylands-woocommerce-deactivator.php';
	Paylands_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_paylands_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_paylands_woocommerce' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action( 'plugins_loaded', 'run_paylands_woocommerce' );

function run_paylands_woocommerce() {
	/**
	 * Doesn't anything if WooCommerce aren't activated.
	 */
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woocommerce_paylands_missing_wc_notice' );
		return;
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-paylands-woocommerce.php';

	$plugin = new Paylands_Woocommerce();
	$plugin->run();
}
