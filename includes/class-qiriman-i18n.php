<?php

namespace Idwebmobile\WooPlugin;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       qiriman.idwebmobile.com
 * @since      1.0.0
 *
 * @package    Qiriman
 * @subpackage Qiriman/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Qiriman
 * @subpackage Qiriman/includes
 * @author     idwebmobile <devel@idwebmobile.com>
 */
class Qiriman_i18n
{
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            'qiriman',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
