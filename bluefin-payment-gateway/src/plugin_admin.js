const jQuery = window.jQuery;

jQuery( function ( $ ) {
	// document.addEventListener("DOMContentLoaded", function (event) {
	$( document ).ready( function () {
		const capture_button = document.querySelector(
			'#bluefin_capture_button'
		);

		const { search } = window.location;

		const add_capture =
			typeof search === 'string' &&
			search.includes( '?page=wc-orders&action=edit' ) &&
			capture_button;

		const settings_page_open =
			search.includes( 'page=wc-settings' ) &&
			search.includes( 'section=bluefin_gateway' );

		if ( settings_page_open ) {
			const required = [
				// TODO: { regex: '', ...}
				{ name: 'account_id', validate: () => true },
				{ name: 'merchant_api_key_id', validate: () => true },
				{ name: 'merchant_api_key_secret', validate: () => true },
				{ name: 'iframe_config_id', validate: () => true },
				{ name: 'iframe_timeout', validate: () => true },
			];

			const settings = {};

			function getOptionDom( setting, required = false ) {
				const dom = document.querySelector(
					'[name="woocommerce_bluefin_gateway_' + setting + '"]'
				);

				dom && ( settings[ setting ] = dom );

				required && ( dom.required = true );

				return dom;
			}

			const settings_iframe_responsive =
				getOptionDom( 'iframe_responsive' );
			const setting_iframe_width = getOptionDom( 'iframe_width' );
			const setting_iframe_height = getOptionDom( 'iframe_height' );

			required.push( {
				name: 'iframe_width',
				validate: () => ! settings_iframe_responsive.checked,
			} );
			required.push( {
				name: 'iframe_height',
				validate: () => ! settings_iframe_responsive.checked,
			} );

			for ( const option of required ) {
				getOptionDom( option.name, true );
			}

			console.debug( 'settings:', settings );

			const save_button = document.querySelector( '[name="save"]' );

			function enableSave() {
				save_button.disabled = false;
			}

			function disableSave() {
				save_button.disabled = true;
			}

			function validateOption( option_name, event ) {
				console.debug( `${ option_name } input:`, event, [
					event.target.value,
				] );

				// event.target.setCustomValidity(`${option_name} is required!`);

				const target = event.target;

				if ( target.value == '' ) {
					target.style.border = 'solid red 1px';

					setTimeout( () => disableSave(), 111 );
					// event.target.reportValidity();
				} else {
					target.style.border = null;
					for ( const field of required ) {
						if (
							field.validate() &&
							settings[ field.name ].value == ''
						) {
							disableSave();
							return;
						}
					}
					enableSave();
				}
			}

			console.debug( 'save_button:', save_button );

			if ( settings_iframe_responsive.checked ) {
				setting_iframe_width &&
					( setting_iframe_width.disabled = true );
				setting_iframe_height &&
					( setting_iframe_height.disabled = true );
			}

			function height_input( event ) {
				validateOption( 'iframe_height', event );
			}

			function width_input( event ) {
				validateOption( 'iframe_width', event );
			}

			settings_iframe_responsive.addEventListener(
				'change',
				function ( event ) {
					const target = event.target;

					if ( target.checked ) {
						// reset
						setting_iframe_width.disabled = true;
						setting_iframe_height.disabled = true;
						setting_iframe_width.value = '';
						setting_iframe_height.value = '';

						enableSave();
					} else {
						setting_iframe_width.disabled = false;
						setting_iframe_height.disabled = false;
						disableSave();

						setting_iframe_width.addEventListener(
							'input',
							width_input
						);

						setting_iframe_height.addEventListener(
							'input',
							height_input
						);
					}
				}
			);

			/*
			settings.account_id.addEventListener('invalid', function(event) {
				console.debug('account_id invalid:')
				return false;
			})
			*/

			settings.account_id.addEventListener( 'input', function ( event ) {
				validateOption( 'account_id', event );
			} );

			settings.merchant_api_key_id.addEventListener(
				'input',
				function ( event ) {
					validateOption( 'merchant_api_key_id', event );
				}
			);

			settings.merchant_api_key_secret.addEventListener(
				'input',
				function ( event ) {
					validateOption( 'merchant_api_key_secret', event );
				}
			);

			settings.iframe_config_id.addEventListener(
				'input',
				function ( event ) {
					validateOption( 'iframe_config_id', event );
				}
			);

			settings.iframe_timeout.addEventListener(
				'input',
				function ( event ) {
					validateOption( 'iframe_timeout', event );
				}
			);
		}

		if ( add_capture ) {
			console.debug( 'capture_button:', capture_button, window.location );

			const refund_button = document.querySelector(
				'.button.refund-items'
			);

			refund_button && refund_button.remove();

			capture_button.addEventListener( 'click', async function () {
				if (
					! window.confirm(
						'Confirm Capture via Bluefin Payment Gateway? Please, note that this action cannot be undone.'
					)
				) {
					return;
				}

				let resp = null,
					data = null;

				const { capture_url, nonce } = bluefinPlugin;

				try {
					// Spinning Animation
					$( '#woocommerce-order-items' ).block( {
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6,
						},
					} );

					resp = await fetch( capture_url, {
						method: 'POST',
						// credentials: "include",
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce': nonce,
						},
						body: JSON.stringify( {
							order_id: add_capture.dataset.orderId,
						} ),
					} );

					if (
						resp.headers
							.get( 'content-type' )
							.includes( 'application/json' )
					) {
						data = await resp.json();
					}

					console.debug( 'capture resp:', resp, data );

					if ( ! resp.ok ) {
						const err = new Error(
							'HTTP status code: ' + resp.status
						);
						err.message = JSON.stringify( data );
						err.status = resp.status;
						throw err;
					}

					// Rest Body Response
					if ( data.ok ) {
						alert(
							'Successful Capture via Bluefin Payment Gateway!'
						);
						$( '#woocommerce-order-items' ).unblock();
						window.location.reload();
					}
				} catch ( err ) {
					$( '#woocommerce-order-items' ).unblock();
					alert( err );
				}
			} );
		}
	} );
} );
