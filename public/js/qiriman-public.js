(function ($) {
	'use strict';

	/**
	 * Siapkan semua variabel yg berhubungan dg DOM
	 */
	var body = $(document.body);
	var useShipping = $('#ship-to-different-address-checkbox');

	var address = {
		billing: {
			destination: $('#billing_destination'),
			destinationSubdistrict: $('#billing_address_2'),
			destinationCity: $('#billing_destination_city'),
			destinationData: $('#billing_destination_data'),
			city: $('#billing_city'),
			province: $('#billing_state')
		},
		shipping: {
			destination: $('#shipping_destination'),
			destinationSubdistrict: $('#shipping_address_2'),
			destinationCity: $('#shipping_destination_city'),
			destinationData: $('#shipping_destination_data'),
			city: $('#shipping_city'),
			province: $('#shipping_state')
		}
	};

	/** Data pemetaaan kode provinsi di Rajaongkir dengan kode di Woocommerce */
	var provinceCode = [
		{
			"id": "1",
			"text": "Bali",
			"kode": "BA"
		},
		{
			"id": "2",
			"text": "Bangka Belitung",
			"kode": "BB"
		},
		{
			"id": "3",
			"text": "Banten",
			"kode": "BT"
		},
		{
			"id": "4",
			"text": "Bengkulu",
			"kode": "BE"
		},
		{
			"id": "5",
			"text": "DI Yogyakarta",
			"kode": "YO"
		},
		{
			"id": "6",
			"text": "DKI Jakarta",
			"kode": "JK"
		},
		{
			"id": "7",
			"text": "Gorontalo",
			"kode": "GO"
		},
		{
			"id": "8",
			"text": "Jambi",
			"kode": "JA"
		},
		{
			"id": "9",
			"text": "Jawa Barat",
			"kode": "JB"
		},
		{
			"id": "10",
			"text": "Jawa Tengah",
			"kode": "JT"
		},
		{
			"id": "11",
			"text": "Jawa Timur",
			"kode": "JI"
		},
		{
			"id": "12",
			"text": "Kalimantan Barat",
			"kode": "KB"
		},
		{
			"id": "13",
			"text": "Kalimantan Selatan",
			"kode": "KS"
		},
		{
			"id": "14",
			"text": "Kalimantan Tengah",
			"kode": "KT"
		},
		{
			"id": "15",
			"text": "Kalimantan Timur",
			"kode": "KI"
		},
		{
			"id": "16",
			"text": "Kalimantan Utara",
			"kode": "KU"
		},
		{
			"id": "17",
			"text": "Kepulauan Riau",
			"kode": "KR"
		},
		{
			"id": "18",
			"text": "Lampung",
			"kode": "LA"
		},
		{
			"id": "19",
			"text": "Maluku",
			"kode": "MA"
		},
		{
			"id": "20",
			"text": "Maluku Utara",
			"kode": "MU"
		},
		{
			"id": "21",
			"text": "Nanggroe Aceh Darussalam (NAD)",
			"kode": "AC"
		},
		{
			"id": "22",
			"text": "Nusa Tenggara Barat (NTB)",
			"kode": "NB"
		},
		{
			"id": "23",
			"text": "Nusa Tenggara Timur (NTT)",
			"kode": "NT"
		},
		{
			"id": "24",
			"text": "Papua",
			"kode": "PA"
		},
		{
			"id": "25",
			"text": "Papua Barat",
			"kode": "PB"
		},
		{
			"id": "26",
			"text": "Riau",
			"kode": "RI"
		},
		{
			"id": "27",
			"text": "Sulawesi Barat",
			"kode": "SR"
		},
		{
			"id": "28",
			"text": "Sulawesi Selatan",
			"kode": "SN"
		},
		{
			"id": "29",
			"text": "Sulawesi Tengah",
			"kode": "ST"
		},
		{
			"id": "30",
			"text": "Sulawesi Tenggara",
			"kode": "SG"
		},
		{
			"id": "31",
			"text": "Sulawesi Utara",
			"kode": "SA"
		},
		{
			"id": "32",
			"text": "Sumatera Barat",
			"kode": "SB"
		},
		{
			"id": "33",
			"text": "Sumatera Selatan",
			"kode": "SS"
		},
		{
			"id": "34",
			"text": "Sumatera Utara",
			"kode": "SU"
		}
	];

	/**
	 * Field2 ini disembunyikan di client side, karena kalau disembunyikan di
	 * server side, maka data tidak akan dikirim. Sementara itu, Woocommerece
	 * menggunakan hash kota dan provinsi untuk mentriger kalkulasi ulang ogkir.
	 * Karena itu, data kota ttp harus diubah dan dikirim agar ongkir dihitung
	 * ulang. Tapi disembunyikan dari tampilan agar tidak mengganggu user.
	 **/
	var prepareFields = function (segment) {
		$('#' + segment + '_destination_data_field').hide().css({opacity: 0, height: 0, width: 0});
		$('#' + segment + '_destination_city_field').hide().css({opacity: 0, height: 0, width: 0});
		$('#' + segment + '_address_2_field').hide().css({opacity: 0, height: 0, width: 0});
		$('#' + segment + '_city_field').hide().css({opacity: 0, height: 0, width: 0});
		$('#' + segment + '_state_field').hide().css({opacity: 0, height: 0, width: 0});
		$('#' + segment + '_country_field').hide().css({opacity: 0, height: 0, width: 0});
		$('#' + segment + '_destination>option').remove();
	}

	var updateCheckoutData = function () {

		var selectedData;
		var segment = 'billing';

		if (useShipping.prop('checked')) {
			segment = 'shipping';
		}

		selectedData = address[segment].destination.select2('data');

		if (selectedData) {
			address[segment].destinationSubdistrict.val(selectedData[0].subdistrict_name);
			address[segment].destinationCity.val(selectedData[0].city_id);

			/** Simpan data tsb ke field destination_data */
			address[segment].destinationData.val(JSON.stringify(selectedData));

			/** Ubah juga nilai kota dan provinsi */
			address[segment].city.val(selectedData[0].city);
			address[segment].province.val(provinceCode.find(function (n) {
				return n.id == selectedData[0].province_id;
			}).kode);

			/** Trigger hitung ulang ongkir */
			body.trigger("update_checkout");
		}

	};

	/**
	 * Inisialisasi select2  address untuk field yg diinginkan.
	 */
	var prepareSelect = function (segment) {

		var savedAddress = address[segment].destinationData.val();

		if (savedAddress) {
			savedAddress = JSON.parse(savedAddress);
		} else {
			/**
			 * Jika destination kosong, maka pastikan juga city kosong.
			 * Jika tidak, maka Woo akan menggunakan cache untuk tampilan
			 * ongkirnya. Dengan mengosongkan city, akan mentrigger Woo
			 * untuk melakukan kalkulasi ulang
			 */
			address[segment].city.val(null);
		}

		address[segment].destination.select2({
			placeholder: 'Kecamatan tujuan',
			data: savedAddress ? savedAddress : null,
			minimumInputLength: 3,
			ajax: {
				type: 'POST',
				url: qiriman_ajax.endpoint,
				data: function (params) {
					var query = {
						search: params.term,
						action: 'qiriman_get_subdistrict',
						nonce: qiriman_ajax.nonce,
						timestamp: Date.now()
					}
					return query;
				},
				processResults: function (data) {

					var subdistricts = $.map(data.results, function (n) {
						n.text = n.subdistrict_name + ', ' + n.type + ' ' + n.city + ', ' + n.province;
						n.id = n.subdistrict_id;
						return n;
					});

					return {
						results: subdistricts,
					};
				}
			}
		});

	};

	var init = function () {
		prepareFields('billing');
		prepareFields('shipping');

		if ($().select2()) {
			prepareSelect('billing');
			prepareSelect('shipping');
		} else {
			setTimeout(tryRun, 500);
		}

		address.billing.destination.change(updateCheckoutData);
		address.shipping.destination.change(updateCheckoutData);
	}

	init();

})(jQuery);
