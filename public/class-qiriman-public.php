<?php

namespace Idwebmobile\WooPlugin;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       qiriman.idwebmobile.com
 * @since      1.0.0
 *
 * @package    Qiriman
 * @subpackage Qiriman/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Qiriman
 * @subpackage Qiriman/public
 * @author     idwebmobile <devel@idwebmobile.com>
 */
class Qiriman_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The plugin option
     *
     * @var array
     */
    public $option;

    /**
     * Name option
     *
     * @var string
     */
    private $isFullName;
    private $optionalEmail;
    private $hideCompany;
    private $hidePostCode;
    private $hideEmail;

    public $billingOnly;
    public $hideOrderNotes;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->option = get_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY, []);

        /** Beri default value */
        $this->isFullName = $this->option['full_name'] ?? 'yes';
        $this->optionalEmail = $this->option['optional_email'] ?? 'no';
        $this->billingOnly = $this->option['billing_only'] ?? 'yes';
        $this->hideCompany = $this->option['hide_company'] ?? 'yes';
        $this->hidePostCode = $this->option['hide_postcode'] ?? 'yes';
        $this->hideEmail = $this->option['hide_email'] ?? 'no';
        $this->hideOrderNotes = $this->option['hide_order_notes'] ?? 'yes';
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Qiriman_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Qiriman_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/qiriman-public.css', [], $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Qiriman_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Qiriman_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/qiriman-public.js', ['jquery', 'select2'], $this->version, true);
        wp_localize_script($this->plugin_name, 'qiriman_ajax', [
            'endpoint' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-request')
        ]);
    }

    public function set_checkout_fields($fields)
    {
        /* Hilangkan field - field yang tidak dipakai */

        if ($this->isFullName === 'yes') {
            unset($fields['billing']['billing_last_name'], $fields['shipping']['shipping_last_name']);

            /** Ubah label dan style field2 bawaan */
            $fields['billing']['billing_first_name']['label'] = 'Nama Lengkap';
            $fields['billing']['billing_first_name']['placeholder'] = 'Masukkan nama lengkap';
            $fields['billing']['billing_first_name']['class'] = ['form-row-wide'];

            /** Ubah label dan style field2 bawaan */
            $fields['shipping']['shipping_first_name']['label'] = 'Nama Lengkap';
            $fields['shipping']['shipping_first_name']['placeholder'] = 'Masukkan nama lengkap';
            $fields['shipping']['shipping_first_name']['class'] = ['form-row-wide'];
        } else {
            $fields['billing']['billing_first_name']['label'] = 'Nama Depan';
            $fields['billing']['billing_last_name']['label'] = 'Nama Belakang';

            $fields['shipping']['shipping_first_name']['label'] = 'Nama Depan';
            $fields['shipping']['shipping_last_name']['label'] = 'Nama Belakang';
        }

        if ($this->hideCompany === 'yes') {
            unset($fields['billing']['billing_company'], $fields['shipping']['shipping_company']);
        } else {
            $fields['billing']['billing_company']['label'] = 'Perusahaan';
            $fields['shipping']['shipping_company']['label'] = 'Perusahaan';
        }

        if ($this->hidePostCode === 'yes') {
            unset($fields['billing']['billing_postcode'], $fields['shipping']['shipping_postcode']);
        } else {
            $fields['billing']['billing_postcode']['label'] = 'Kode POS';
            $fields['shipping']['shipping_postcode']['label'] = 'Kode POS';
        }

        $fields['billing']['billing_address_1']['label'] = 'Alamat';
        $fields['billing']['billing_address_1']['placeholder'] = 'Masukkan alamat lengkap';
        $fields['billing']['billing_address_1']['class'] = ['form-row-wide'];

        $fields['billing']['billing_phone']['label'] = 'No Telp/WA';
        $fields['billing']['billing_phone']['placeholder'] = 'Masukkan nomor telepon/WA';
        $fields['billing']['billing_phone']['class'] = ['form-row-wide'];

        $fields['billing']['billing_email']['label'] = 'Email';
        $fields['billing']['billing_email']['placeholder'] = 'Masukkan alamat email';

        if ($this->optionalEmail === 'yes') {
            $fields['billing']['billing_email']['required'] = false;
        }

        if ($this->hideEmail === 'yes') {
            unset($fields['billing']['billing_email']);
        }

        $fields['shipping']['shipping_address_1']['label'] = 'Alamat';
        $fields['shipping']['shipping_address_1']['placeholder'] = 'Masukkan alamat lengkap';
        $fields['shipping']['shipping_address_1']['class'] = ['form-row-wide'];

        /**
         * Siapkan field custom yang akan kita perlukan. Ada 2:
         * billing_destination untuk UI select2
         * destination_data untuk simpan data JSON dari billing_destination
         */
        $fields['billing']['billing_destination']['label'] = 'Kecamatan';
        $fields['billing']['billing_destination']['type'] = 'select';
        $fields['billing']['billing_destination']['placeholder'] = 'Masukkan kecamatan';
        $fields['billing']['billing_destination']['required'] = true;
        $fields['billing']['billing_destination']['options'] = ['a'];
        $fields['billing']['billing_destination']['priority'] = 60;

        $fields['billing']['billing_destination_city']['type'] = 'text';
        $fields['billing']['billing_destination_city']['priority'] = 64;

        $fields['billing']['billing_destination_data']['type'] = 'text';
        $fields['billing']['billing_destination_data']['priority'] = 65;

        /**
         * Siapkan field custom yang akan kita perlukan. Ada 2:
         * shipping_destination untuk UI select2
         * destination_data untuk simpan data JSON dari shipping_destination
         */
        $fields['shipping']['shipping_destination']['label'] = 'Kecamatan';
        $fields['shipping']['shipping_destination']['type'] = 'select';
        $fields['shipping']['shipping_destination']['placeholder'] = 'Masukkan kecamatan';
        $fields['shipping']['shipping_destination']['required'] = true;
        $fields['shipping']['shipping_destination']['options'] = ['a'];
        $fields['shipping']['shipping_destination']['priority'] = 60;

        $fields['shipping']['shipping_destination_city']['type'] = 'text';
        $fields['shipping']['shipping_destination_city']['priority'] = 64;

        $fields['shipping']['shipping_destination_data']['type'] = 'text';
        $fields['shipping']['shipping_destination_data']['priority'] = 65;

        /* Beri nilai default untuk custom field jika user login */
        /* Jika user login, maka ambil data user sebagai nilai default */
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_meta = get_user_meta($user_id);

            $fields['billing']['billing_destination']['default'] = isset($user_meta['billing_destination'][0]) ? $user_meta['billing_destination'][0] : '';
            $fields['billing']['billing_destination_city']['default'] = isset($user_meta['billing_destination_city'][0]) ? $user_meta['billing_destination_city'][0] : '';
            $fields['billing']['billing_destination_data']['default'] = isset($user_meta['billing_destination_data'][0]) ? $user_meta['billing_destination_data'][0] : '';

            $fields['shipping']['shipping_destination']['default'] = isset($user_meta['shipping_destination'][0]) ? $user_meta['shipping_destination'][0] : '';
            $fields['shipping']['shipping_destination_city']['default'] = isset($user_meta['shipping_destination_city'][0]) ? $user_meta['shipping_destination_city'][0] : '';
            $fields['shipping']['shipping_destination_data']['default'] = isset($user_meta['shipping_destination_data'][0]) ? $user_meta['shipping_destination_data'][0] : '';
        }

        return $fields;
    }

    public function set_billing_fields($fields)
    {
        /* Hilangkan field - field yang tidak dipakai */

        if ($this->isFullName === 'yes') {
            unset($fields['billing_last_name']);
            /** Ubah label dan style field2 bawaan */
            $fields['billing_first_name']['label'] = 'Nama Lengkap';
            $fields['billing_first_name']['placeholder'] = 'Masukkan nama lengkap';
            $fields['billing_first_name']['class'] = ['form-row-wide'];
        } else {
            $fields['billing_first_name']['label'] = 'Nama Depan';
            $fields['billing_last_name']['label'] = 'Nama Belakang';
        }

        if ($this->hideCompany === 'yes') {
            unset($fields['billing_company']);
        } else {
            $fields['billing_company']['label'] = 'Perusahaan';
        }

        if ($this->hidePostCode === 'yes') {
            unset($fields['billing_postcode']);
        } else {
            $fields['billing_postcode']['label'] = 'Kode POS';
        }

        $fields['billing_address_1']['label'] = 'Alamat';
        $fields['billing_address_1']['placeholder'] = 'Masukkan alamat lengkap';
        $fields['billing_address_1']['class'] = ['form-row-wide'];

        $fields['billing_phone']['label'] = 'No Telp/WA';
        $fields['billing_phone']['placeholder'] = 'Masukkan nomor telepon/WA';
        $fields['billing_phone']['class'] = ['form-row-wide'];

        $fields['billing_email']['label'] = 'Email';
        $fields['billing_email']['placeholder'] = 'Masukkan alamat email';

        if ($this->optionalEmail === 'yes') {
            $fields['billing_email']['required'] = false;
        }

        if ($this->hideEmail === 'yes') {
            unset($fields['billing_email']);
        }

        /**
         * Siapkan field custom yang akan kita perlukan. Ada 2:
         * billing_destination untuk UI select2
         * destination_data untuk simpan data JSON dari billing_destination
         */
        $fields['billing_destination']['label'] = 'Kecamatan';
        $fields['billing_destination']['type'] = 'select';
        $fields['billing_destination']['placeholder'] = 'Masukkan kecamatan';
        $fields['billing_destination']['required'] = true;
        $fields['billing_destination']['options'] = ['a'];
        $fields['billing_destination']['priority'] = 60;

        $fields['billing_destination_city']['type'] = 'text';
        $fields['billing_destination_city']['priority'] = 64;

        $fields['billing_destination_data']['type'] = 'text';
        $fields['billing_destination_data']['priority'] = 65;

        return $fields;
    }

    public function set_shipping_fields($fields)
    {
        /* Hilangkan field - field yang tidak dipakai */

        if ($this->isFullName === 'yes') {
            unset($fields['shipping_last_name']);
            /** Ubah label dan style field2 bawaan */
            $fields['shipping_first_name']['label'] = 'Nama Lengkap';
            $fields['shipping_first_name']['placeholder'] = 'Masukkan nama lengkap';
            $fields['shipping_first_name']['class'] = ['form-row-wide'];
        } else {
            $fields['shipping_first_name']['label'] = 'Nama Depan';
            $fields['shipping_last_name']['label'] = 'Nama Belakang';
        }

        if ($this->hideCompany === 'yes') {
            unset($fields['shipping_company']);
        } else {
            $fields['shipping_company']['label'] = 'Perusahaan';
        }

        if ($this->hidePostCode === 'yes') {
            unset($fields['shipping_postcode']);
        } else {
            $fields['shipping_postcode']['label'] = 'Kode POS';
        }

        $fields['shipping_address_1']['label'] = 'Alamat';
        $fields['shipping_address_1']['placeholder'] = 'Masukkan alamat lengkap';
        $fields['shipping_address_1']['class'] = ['form-row-wide'];

        /**
         * Siapkan field custom yang akan kita perlukan. Ada 2:
         * shipping_destination untuk UI select2
         * destination_data untuk simpan data JSON dari shipping_destination
         */
        $fields['shipping_destination']['label'] = 'Kecamatan';
        $fields['shipping_destination']['type'] = 'select';
        $fields['shipping_destination']['placeholder'] = 'Masukkan kecamatan';
        $fields['shipping_destination']['required'] = true;
        $fields['shipping_destination']['options'] = ['a'];
        $fields['shipping_destination']['priority'] = 60;

        $fields['shipping_destination_city']['type'] = 'text';
        $fields['shipping_destination_city']['priority'] = 64;

        $fields['shipping_destination_data']['type'] = 'text';
        $fields['shipping_destination_data']['priority'] = 65;

        return $fields;
    }

    public function customer_save_address($user_id, $load_address)
    {
        /**
         * Pastikan field yang tidak dipakai, isinya kosong.
         */
        if ($this->isFullName === 'yes') {
            update_user_meta($user_id, 'billing_last_name', '');
            update_user_meta($user_id, 'shipping_last_name', '');
        }

        if ($this->hideCompany === 'yes') {
            update_user_meta($user_id, 'billing_company', '');
            update_user_meta($user_id, 'shipping_company', '');
        }

        if ($this->hidePostCode === 'yes') {
            update_user_meta($user_id, 'billing_postcode', '');
            update_user_meta($user_id, 'shipping_postcode', '');
        }

        if ($this->hideEmail === 'yes') {
            update_user_meta($user_id, 'billing_email', '');
        }

        $destination = $load_address . '_destination';
        $destinationCity = $load_address . '_destination_city';
        $destinationData = $load_address . '_destination_data';

        if (isset($_POST[$destination])) {
            update_user_meta($user_id, $destination, htmlentities($_POST[$destination]));
        }

        if (isset($_POST[$destinationCity])) {
            update_user_meta($user_id, $destinationCity, htmlentities($_POST[$destinationCity]));
        }

        if (isset($_POST[$destinationData])) {
            update_user_meta($user_id, $destinationData, htmlentities($_POST[$destinationData]));
        }
    }

    public function need_shipping_address()
    {
        return false;
    }

    public function order_notes()
    {
        return false;
    }
}
