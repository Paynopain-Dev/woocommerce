<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 * @author     Paylands <soporte@paylands.com>
 */
class Paylands_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Paylands_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	public $locales;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PAYLANDS_WOOCOMMERCE_VERSION' ) ) {
			$this->version = PAYLANDS_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'paylands-woocommerce';
		$this->locales = PAYLANDS_ROOT_PATH . 'languages';
		$this->set_locale();

		$this->define_constants();
		$this->load_dependencies();
		// $this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_woocommerce_hooks();

		// add_action( 'plugin_loaded', array( $this, 'set_locale' ) );
	}

	/**
	 * Define the Plugin constants
	 *
	 * - PLUGIN NAME
	 * - Path of the plugin.
	 * - Path of includes
	 * - Path of admin files
	 * - Path of the pubic files
	 *
	 * @since  	1.0.0
	 * @access 	private
	 */
	private function define_constants() {
		define( 'PAYLANDS_WOOCOMMERCE_PLUGIN_NAME', $this->plugin_name );
        define( 'PAYLANDS_WOOCOMMERCE_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
        define( 'PAYLANDS_WOOCOMMERCE_INCLUDES', PAYLANDS_WOOCOMMERCE_PATH . 'includes' );
        define( 'PAYLANDS_WOOCOMMERCE_ADMIN', PAYLANDS_WOOCOMMERCE_PATH . 'admin' );
        define( 'PAYLANDS_WOOCOMMERCE_PUBLIC', PAYLANDS_WOOCOMMERCE_PATH . 'public' );
        define( 'PAYLANDS_WOOCOMMERCE_ASSETS', plugin_dir_url( __FILE__ ) . 'assets' );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Paylands_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Paylands_Woocommerce_i18n. Defines internationalization functionality.
	 * - Paylands_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Paylands_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once PAYLANDS_WOOCOMMERCE_ADMIN . '/class-paylands-woocommerce-admin.php';

		/**
		 * The class responsible to load the Paylands Logger
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-logger.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once PAYLANDS_WOOCOMMERCE_PUBLIC . '/class-paylands-woocommerce-public.php';

		/**
		 * The Trait responsible to define some helpers used on ajax and controller.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/traits/trait-paylands-helpers.php';

		/**
		 * The class responsible to load the Paylands Gateway for WooCommerce.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-gateway.php';

		/**
		 * The class responsible to load the Paylands Gateway Loader.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-gateway-loader.php';

		/**
		 * The class responsible to load the Paylands Orders Model.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-orders.php';

		/**
		 * The class responsible to load the Paylands Cards Model.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-cards.php';

		/**
		 * The class responsible to load the Paylands Controller.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-controller.php';

		/**
		 * The class responsible to load the Paylands Gateway Loader.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/api/class-paylands-api-client.php';

		/**
		 * The class responsible to load the Paylands Gateway Ajax hooks.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/class-paylands-woocommerce-ajax.php';

		/**
		 * The class responsible to load the Paylands Router Processor Class.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/http/class-processor.php';

		/**
		 * The class responsible to load the Paylands Route Class.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/http/class-route.php';

		/**
		 * The class responsible to load the Paylands Router Class.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/http/class-router.php';

		/**
		 * The class responsible to load the Paylands Gateway Ajax hooks.
		 */
		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/http/routes.php';

		require_once PAYLANDS_WOOCOMMERCE_INCLUDES . '/actions.php';

		$this->loader = new Paylands_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Paylands_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function set_locale() {
		
		// load_plugin_textdomain(
		// 	'paylands-woocommerce',
		// 	false,
		// 	$this->locales
		// );

		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			// @todo Remove when start supporting WP 5.0 or later.
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'paylands-woocommerce' );

		unload_textdomain( 'paylands-woocommerce' );
		load_textdomain( 'paylands-woocommerce', $this->locales . '/paylands-woocommerce-' . $locale . '.mo' );
		load_plugin_textdomain( 'paylands-woocommerce', false, $this->locales );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Paylands_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Paylands_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register the woocommerce hooks to can see the payment gateway
	 * in the setting payment options.
	 *
	 * @since 	1.0.0
	 * @access 	private
	 */
	private function define_woocommerce_hooks() {

		$pnp_loader = new Paylands_Gateway_Loader();

		$this->loader->add_action( 'plugins_loaded', $pnp_loader, 'paylands_payment_gateway_init' );
		$this->loader->add_filter( 'woocommerce_payment_gateways', $pnp_loader, 'add_pnp_paylands_gateway');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Paylands_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
