<?php

namespace Idwebmobile\WooPlugin;

class Qiriman_Shipping extends \WC_Shipping_Method
{
    protected $origin;

    public function __construct()
    {
        $this->id = 'qiriman_shipping';
        $this->method_title = 'Qiriman - Ekspedisi Indonesia';
        $this->method_description = 'Pengiriman lokal dengan ekspedisi Indonesia';
        $this->enabled = $this->get_option('enabled');
        $this->origin = json_decode($this->get_option('selected_origin'), true);

        $this->init();
    }

    public function init()
    {
        // Load the settings API
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => 'Enable',
                'type' => 'checkbox',
                'label' => 'Enable Qiriman - Ekspedisi Indonesia',
                'default' => 'no'
            ],

            'origin' => [
                'title' => 'Asal pengiriman',
                'type' => 'select',
                'description' => 'Masukkan kecamatan asal pengiriman',
                'default' => '',
            ],

            'full_name' => [
                'title' => 'Field checkout',
                'type' => 'checkbox',
                'label' => 'Gabung nama depan dan nama belakang menjadi nama lengkap',
                'default' => 'yes',
            ],

            'optional_email' => [
                'type' => 'checkbox',
                'label' => 'Buat email menjadi optional',
                'default' => 'no',
            ],

            'billing_only' => [
                'type' => 'checkbox',
                'label' => 'Hilangkan alamat tambahan',
                'default' => 'yes',
            ],

            'hide_company' => [
                'type' => 'checkbox',
                'label' => 'Sembunyikan perusahaan',
                'default' => 'yes',
            ],

            'hide_postcode' => [
                'type' => 'checkbox',
                'label' => 'Sembunyikan kode pos',
                'default' => 'yes',
            ],

            'hide_email' => [
                'type' => 'checkbox',
                'label' => 'Sembunyikan email',
                'default' => 'no',
            ],

            'hide_order_notes' => [
                'type' => 'checkbox',
                'label' => 'Sembunyikan catatan order',
                'default' => 'yes',
            ],

            'premium_key' => [
                'title' => 'Key premium',
                'type' => 'text',
                'description' => 'Masukkan <strong>key</strong> untuk menggunakan fitur premium. Tidak punya key ? dapatkan <a href="https://qiriman.idwebmobile.com" target="_blank" title="Beli Qiriman Premiun" >di sini</a>.',
                'default' => '',
            ],

            'courier' => [
                'title' => 'Expedisi (premium)',
                'type' => 'multiselect',
                'description' => 'Pilih ekspedisi. Hanya untuk pengguna premium.',
                'default' => 'jne',
                'options' => [
                    'jne' => 'JNE',
                    'jnt' => 'JNT',
                    'pos' => 'POS',
                    'wahana' => 'Wahana',
                    'tiki' => 'TIKI'
                ],
                'class' => 'idwebmobile-qiriman-invisible'
            ],

            'selected_origin' => [
                'type' => 'hidden',
                'default' => '',
                'class' => 'idwebmobile-qiriman-invisible'
            ],

            'bearer' => [
                'type' => 'hidden',
                'default' => '',
                'class' => 'idwebmobile-qiriman-invisible'
            ],
        ];
    }

    public function calculate_shipping($package = [])
    {
        $postData = $this->extractPostData();

        if (!isset($postData['billing_destination'])) {
            return;
        }

        $destination = [
            'subdistrict' => ($postData['billing_destination'] * 1),
            'city' => ($postData['billing_destination_city'] * 1),
        ];

        if (isset($postData['ship_to_different_address'])) {
            if (!isset($postData['shipping_destination'])) {
                return;
            }

            $destination = [
                'subdistrict' => ($postData['shipping_destination'] * 1),
                'city' => ($postData['shipping_destination_city'] * 1),
            ];
        }

        $weight = $this->getPackageWeight($package);

        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->get_option('bearer')) {
            $headers['Authorization'] = 'Bearer ' . $this->get_option('bearer');
        }

        $response = wp_remote_get(
            IDWEBMOBILE_QIRIMAN_API_BASEURL . 'costs',
            [
                'method' => 'GET',
                'body' => [
                    'weight' => $weight,
                    'destinationSubdistrict' => $destination['subdistrict'],
                    'destinationCity' => $destination['city'],
                    'originSubdistrict' => $this->origin[0]['subdistrict_id'],
                    'originCity' => $this->origin[0]['city_id'],
                    'courier' => $this->get_option('courier'),
                ],
                'headers' => $headers
            ]
        );

        $data = json_decode(wp_remote_retrieve_body($response), true);

        /**
         * Jika status tidak ok, maka berarti key bermasalah. Kosongkan bearer.
         * Jika status ok dan ada activation_code, maka bearer perlu diperbarui.
         * Operasi get_option dan update_option adalah operasi yg expensive, jadi jangan sampai
         * memperlambat response. Pastikan hanya dijalankan ketika dibutuhkan saja.
         */

        if ($data['code'] === 'ok') {
            if ($data['activation_code']) {
                $optionValue = get_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY);
                $optionValue['bearer'] = $data['activation_code'];
                update_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY, $optionValue);
            }
        } else {
            $optionValue = get_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY);
            $optionValue['bearer'] = null;
            update_option(IDWEBMOBILE_QIRIMAN_OPTION_KEY, $optionValue);
        }

        foreach ($data['rajaongkir']['results'] as $expedisi) {
            foreach ($expedisi['costs'] as $paket) {
                $rate = [
                    'id' => $this->id . '_' . $expedisi['code'] . '_' . $paket['service'],
                    'label' => strtoupper($expedisi['code']) . ' - ' . $paket['description'],
                    'cost' => $paket['cost'][0]['value'],
                    'calc_tax' => 'per_item'
                ];

                // Register the rate
                if (!empty($rate['cost'])) {
                    $this->add_rate($rate);
                }
            }
        }
    }

    private function getPackageWeight($package)
    {
        $weight = 0;

        foreach ($package['contents'] as $item_id => $values) {
            $_product = $values['data'];
            $package_weight = wc_get_weight($_product->get_weight(), 'g');

            $weight = $weight + ($package_weight * $values['quantity']);
        }

        if ($weight < 1) {
            /**
             * Jika berat tidak ada, maka ubah berat menjadi 1 kg.
             * Tidak perlu kasih option untuk berat default ini.
             * */
            $weight = 1000;
        }
        return $weight;
    }

    private function extractPostData()
    {
        $resultPostData = [];

        if (isset($_POST['post_data'])) {
            $postdata = explode('&', $_POST['post_data']);

            foreach ($postdata as $valData) {
                $ar = explode('=', $valData);
                if (count($ar) == 2) {
                    $key = htmlspecialchars($ar[0]);
                    $val = htmlspecialchars($ar[1]);
                    $resultPostData[$key] = $val;
                }
            }
        } else {
            $resultPostData['billing_destination'] = isset($_POST['billing_destination']) ? $_POST['billing_destination'] : null;
            $resultPostData['billing_destination_city'] = isset($_POST['billing_destination_city']) ? $_POST['billing_destination_city'] : null;
        }

        return $resultPostData;
    }

    public function process_admin_options()
    {
        $post_data = $this->get_post_data();

        $prefix = 'woocommerce_' . $this->id . '_';
        $post_data[$prefix . 'bearer'] = '';

        if ($post_data[$prefix . 'premium_key']) {
            $response = wp_remote_post(
                IDWEBMOBILE_QIRIMAN_API_BASEURL . 'activate-key',
                [
                    'method' => 'POST',
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                    'body' => [
                        'key' => $post_data[$prefix . 'premium_key'],
                        'domain' => $_SERVER['HTTP_HOST']
                    ]
                ]
            );

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if ($data['code'] === 'ok') {
                $post_data[$prefix . 'bearer'] = $data['activation_code'];
            } else {
                $post_data[$prefix . 'bearer'] = '';
                $this->add_error($data['message']);
                $this->display_errors();
            }
        }

        $this->set_post_data($post_data);

        /** This boilerplate code was taken from woocommerce/includes/abstracts/abstract-wc-shipping-method.php */
        /** Panggil implementasi parent untuk simpan data ke database */
        return parent::process_admin_options();
    }
}
