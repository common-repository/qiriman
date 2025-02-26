<?php

// namespace Idwebmobile\WooPlugin;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://qiriman.idwebmobile.com/
 * @since             1.0.0
 * @package           Qiriman
 *
 * @wordpress-plugin
 * Plugin Name:       Qiriman
 * Plugin URI:        https://qiriman.idwebmobile.com/
 * Description:       Qiriman is a WooCommerce plugin that provide shipping method from various expedition in Indonesia.
 * Version:           1.0.4
 * Author:            idwebmobile
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       qiriman
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('IDWEBMOBILE_QIRIMAN_VERSION', '1.0.4');

/**
 * Qiriman server
 */
define('IDWEBMOBILE_QIRIMAN_API_BASEURL', 'https://qirimanapp.idwebmobile.com/api/');
define('IDWEBMOBILE_QIRIMAN_OPTION_KEY', 'woocommerce_qiriman_shipping_settings');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-qiriman-activator.php
 */
function activate_idwebmobile_qiriman()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-qiriman-activator.php';
    \Idwebmobile\WooPlugin\Qiriman_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-qiriman-deactivator.php
 */
function deactivate_idwebmobile_qiriman()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-qiriman-deactivator.php';
    \Idwebmobile\WooPlugin\Qiriman_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_idwebmobile_qiriman');
register_deactivation_hook(__FILE__, 'deactivate_idwebmobile_qiriman');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-qiriman.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_idwebmobile_qiriman()
{
    $plugin = new \Idwebmobile\WooPlugin\Qiriman();
    $plugin->run();
}
run_idwebmobile_qiriman();
