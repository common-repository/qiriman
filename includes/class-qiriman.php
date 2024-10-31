<?php

namespace Idwebmobile\WooPlugin;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       qiriman.idwebmobile.com
 * @since      1.0.0
 *
 * @package    Qiriman
 * @subpackage Qiriman/includes
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
 * @package    Qiriman
 * @subpackage Qiriman/includes
 * @author     idwebmobile <devel@idwebmobile.com>
 */
class Qiriman
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Qiriman_Loader    $loader    Maintains and registers all hooks for the plugin.
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

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('IDWEBMOBILE_QIRIMAN_VERSION')) {
            $this->version = IDWEBMOBILE_QIRIMAN_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'qiriman';

        $this->load_dependencies();
        $this->set_locale();
        $this->prepare_qiriman_shipping();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Qiriman_Loader. Orchestrates the hooks of the plugin.
     * - Qiriman_i18n. Defines internationalization functionality.
     * - Qiriman_Admin. Defines all hooks for the admin area.
     * - Qiriman_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-qiriman-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-qiriman-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-qiriman-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-qiriman-public.php';

        $this->loader = new Qiriman_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Qiriman_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Qiriman_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Qiriman_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Qiriman_Public($this->get_plugin_name(), $this->get_version());

        if (isset($plugin_public->option['enabled'])) {
            if ($plugin_public->option['enabled'] === 'yes') {
                $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
                $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

                /**
                 * Hilangkan shipping address
                 */
                if ($plugin_public->billingOnly === 'yes') {
                    $this->loader->add_filter('woocommerce_cart_needs_shipping_address', $plugin_public, 'need_shipping_address');
                }

                /**
                 * Hilangkan order notes
                 */
                if ($plugin_public->hideOrderNotes === 'yes') {
                    $this->loader->add_filter('woocommerce_enable_order_notes_field', $plugin_public, 'order_notes');
                }

                /** Setup fields custom di halaman checkout  */
                $this->loader->add_filter('woocommerce_checkout_fields', $plugin_public, 'set_checkout_fields');
                $this->loader->add_filter('woocommerce_billing_fields', $plugin_public, 'set_billing_fields');
                $this->loader->add_filter('woocommerce_shipping_fields', $plugin_public, 'set_shipping_fields');

                $this->loader->add_action('woocommerce_customer_save_address', $plugin_public, 'customer_save_address', 10, 2);
            }
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Qiriman_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    private function prepare_qiriman_shipping()
    {
        $this->loader->add_filter('woocommerce_shipping_methods', $this, 'add_qiriman_shipping_method');
        $this->loader->add_action('woocommerce_shipping_init', $this, 'init_qiriman_shipping');

        $this->loader->add_action('wp_ajax_qiriman_get_subdistrict', $this, 'qiriman_get_subdistrict');
        $this->loader->add_action('wp_ajax_nopriv_qiriman_get_subdistrict', $this, 'qiriman_get_subdistrict');

        $this->loader->add_action('wp_ajax_qiriman_check_activation', $this, 'qiriman_check_activation');
        $this->loader->add_action('wp_ajax_nopriv_qiriman_check_activation', $this, 'qiriman_check_activation');
    }

    public function add_qiriman_shipping_method($methods)
    {
        $methods['qiriman_shipping'] = 'Idwebmobile\WooPlugin\Qiriman_Shipping';
        return $methods;
    }

    public function init_qiriman_shipping()
    {
        /**
         * The class responsible for shipping functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-qiriman-shipping.php';
    }

    public function qiriman_get_subdistrict()
    {
        $search = htmlspecialchars($_POST['search']);

        $response = wp_remote_get(
            IDWEBMOBILE_QIRIMAN_API_BASEURL . 'places',
            [
                'method' => 'GET',
                'headers' => [
                    'accept' => 'application/json',
                ],
                'body' => ['name' => $search]
            ]
        );

        $data = json_decode(wp_remote_retrieve_body($response), true);

        $list = [
            'results' => $data['data']
        ];

        wp_send_json($list);
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function qiriman_check_activation()
    {
        $response = wp_remote_get(
            IDWEBMOBILE_QIRIMAN_API_BASEURL . 'check-activation',
            [
                'method' => 'GET',
                'headers' => [
                    'accept' => 'application/json',
                ],
                'body' => [
                    'key' => htmlspecialchars($_POST['qiriman_premium_key']),
                    'domain' => $_SERVER['HTTP_HOST']
                ]
            ]
        );

        $data = json_decode(wp_remote_retrieve_body($response), true);

        $optionValue = get_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY);

        if ($data['code'] === 'ok') {
            /**
             * Jika ada data JWT baru, berarti ada perubahan expire registrasi.
             * Update JWT di option agar waktu expire ikut terupdate.
             */
            if ($data['activation_code']) {
                $optionValue['bearer'] = $data['activation_code'];
                update_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY, $optionValue);
            }
        } else {
            /**
             * Jika hasil tidak ok, berarti key bermasalah.
             * Hapus bearer.
             */
            $optionValue['bearer'] = null;
            update_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY, $optionValue);
        }

        wp_send_json($data);
        wp_die(); // this is required to terminate immediately and return a proper response
    }
}
