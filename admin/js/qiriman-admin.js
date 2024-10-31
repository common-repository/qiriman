(function ($) {
	'use strict';

	var title = $('#mainform > h2').html();

	/** Hanya jalankan di halaman setting Qiriman */
	if (title === 'Qiriman - Ekspedisi Indonesia') {

		/** Sembunyikan field hidden */
		$("tr:last").hide();
		var bearerField = $("tr:last").prev();
		bearerField.hide();
		var courierField = bearerField.prev();
		courierField.hide();
		var premiumKeyField = $('#woocommerce_qiriman_shipping_premium_key');

		// $('button[type=submit]').prop('disabled', true);

		/**
		 * Setup tampilan checkbox.
		 * Terpaksa dihandle di sini, karena API Setting WooCommerce tidak
		 * support custom style checkbox.
		 */
		var checkboxFullName = $('tr:nth-child(3)');
		var checkboxOptionalEmail = checkboxFullName.next();
		var checkboxAddress = checkboxOptionalEmail.next();
		var checkboxCompany = checkboxAddress.next();
		var checkboxPostCode = checkboxCompany.next();
		var checkboxHideEmail = checkboxPostCode.next();
		var checkboxOrderNote = checkboxHideEmail.next();
		checkboxFullName.children().addClass('idwebmobile-qiriman-no-padding-bottom');
		checkboxOptionalEmail.children().addClass('idwebmobile-qiriman-no-padding-top').addClass('idwebmobile-qiriman-no-padding-bottom');
		checkboxAddress.children().addClass('idwebmobile-qiriman-no-padding-top').addClass('idwebmobile-qiriman-no-padding-bottom');
		checkboxCompany.children().addClass('idwebmobile-qiriman-no-padding-top').addClass('idwebmobile-qiriman-no-padding-bottom');
		checkboxPostCode.children().addClass('idwebmobile-qiriman-no-padding-top').addClass('idwebmobile-qiriman-no-padding-bottom');
		checkboxHideEmail.children().addClass('idwebmobile-qiriman-no-padding-top').addClass('idwebmobile-qiriman-no-padding-bottom');
		checkboxOrderNote.children().addClass('idwebmobile-qiriman-no-padding-top');

		var key = premiumKeyField.val();

		var checkActivation = function () {
			return $.post(qiriman_ajax.endpoint, {
				action: 'qiriman_check_activation',
				nonce: qiriman_ajax.nonce,
				qiriman_premium_key: key
			});
		};

		var tryRun = function () {

			if (key) {
				var keyCheck = checkActivation();
				keyCheck.done(function (data) {
					if (data.code === 'ok') {
						$('#woocommerce_qiriman_shipping_courier').removeClass('idwebmobile-qiriman-invisible');
						courierField.show();
						premiumKeyField.next().html('Lisensi akan expired pada: <strong>' + data.expires + '</strong>.');
					} else if (data.code === 'license_not_found') {
						alert('Lisensi Qiriman Anda tidak terdaftar');
						premiumKeyField.next().html('Lisensi Anda tidak terdaftar. Dapatkan lisensi <a href="https://qiriman.idwebmobile.com" target="_blank" title="Beli Qiriman Permiun" >di sini</a>.').addClass('idwebmobile-qiriman-error-message');
					} else if (data.code === 'license_expired') {
						alert('Lisensi Qiriman Anda telah expired');
						premiumKeyField.next().html('Lisensi Anda telah expired. Dapatkan / perpanjang lisensi <a href="https://qiriman.idwebmobile.com" target="_blank" title="Beli Qiriman Permiun" >di sini</a>.').addClass('idwebmobile-qiriman-error-message');
					} else if (data.code === 'license_disabled') {
						alert('Lisensi Qiriman Anda telah dinonaktifkan');
						premiumKeyField.next().html('Lisensi Anda telah dinonaktifkan. Dapatkan lisensi baru <a href="https://qiriman.idwebmobile.com" target="_blank" title="Beli Qiriman Permiun" >di sini</a>.').addClass('idwebmobile-qiriman-error-message');
					} else {
						alert('Lisensi Qiriman Anda tidak valid');
						premiumKeyField.next().html('Lisensi Anda tidak valid.').addClass('idwebmobile-qiriman-error-message');
					}
				});
			}

			if ($().select2()) {

				var originField = $('#woocommerce_qiriman_shipping_origin');
				var savedOriginField = $('#woocommerce_qiriman_shipping_selected_origin');

				var savedOrigin = savedOriginField.val();
				if (savedOrigin) {
					savedOrigin = JSON.parse(savedOrigin);
				}

				originField.select2({
					placeholder: 'Kecamatan asal pengiriman',
					data: savedOrigin ? savedOrigin : null,
					minimumInputLength: 3,
					ajax: {
						type: 'POST',
						url: qiriman_ajax.endpoint,
						data: function (params) {
							var query = {
								search: params.term,
								action: 'qiriman_get_subdistrict',
								nonce: qiriman_ajax.nonce
							}
							return query;
						},
						processResults: function (data) {

							var subdistricts = $.map(data.results, function (n) {
								n.text = n.subdistrict_name + ' ' + n.type + ' ' + n.city;
								n.id = n.subdistrict_id;
								return n;
							});

							return {
								results: subdistricts,
							};
						}
					}
				});

				originField.change(function () {
					var selectedData = originField.select2('data');
					savedOriginField.val(JSON.stringify(selectedData));
				});

			} else {
				setTimeout(tryRun, 500);
			}
		};

		tryRun();
	}

})(jQuery);
