/**
 * External dependencies
 */
import { registerPaymentMethod } from '@woocommerce/blocks-registry';

// import BlocksRegistry from '@woocommerce/blocks-registry';

import { __ } from '@wordpress/i18n';
import { getPaymentMethodData } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
// import { sanitizeHTML } from '@woocommerce/utils';

// import { RawHTML } from '@wordpress/element';

import { dispatch, select } from '@wordpress/data';

import {
	paymentStore,
	CHECKOUT_STORE_KEY,
	checkoutStore,
	cartStore,
	CART_STORE_KEY,
} from '@woocommerce/block-data';

import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
// import { PAYMENT_METHOD_NAME } from './constants';

const PAYMENT_METHOD_NAME = 'bluefin_gateway';

const settings = getPaymentMethodData( PAYMENT_METHOD_NAME, {} );
const defaultLabel = __( 'Bluefin Payment Gateway', 'woocommerce' );
const label = decodeEntities( settings?.title || '' ) || defaultLabel;

window.bluefin_component = window.bluefin_component || {};

window.bluefin_component.request = window.bluefin_component.request || {};

function isDigit( c ) {
	return c >= '0' && c <= '9';
}

class Input {
	static parseIntPhoneNumber( phone_string ) {
		let s = '';
		for ( const i in phone_string ) {
			const c = phone_string[ i ];

			if ( isDigit( c ) ) s += c;
		}
		return s;
	}
}

const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components;

	return (
		<PaymentMethodLabel
			icon={
				<img
					className="wc-block-components-payment-method-icon"
					src={ settings.icon }
					width="300"
					height="300"
				/>
			}
			text={ 'Payment Gateway' }
		/>
	);

	/*return (
		<div>
		  <img src="https://www.bluefin.com/wp-content/uploads/2022/03/Bluefin-sq.png" width="400" height="300" />
		  <p> {label} </p> 
		</div>
		);*/
};

const canMakePayment = ( props ) => {
	return true;
};

function transformCustomerData(customerData) {
	/*
	Bluefin API Schema:
	"customer": {
		"name": "Jane Smith",
		"email": "jsmith@example.com",
		"phone": "+14441234321",
		"billingAddress": {
		"address1": "123 Plain St",
		"address2": "West Side",
		"city": "Atlanta",
		"state": "GA",
		"zip": "90210",
		"country": "USA",
		"company": "Acme Inc."
		}
	},
	"shippingAddress": {
		"address1": "123 Plain St",
		"address2": "West Side",
		"city": "Atlanta",
		"state": "GA",
		"zip": "90210",
		"country": "USA",
		"company": "Acme Inc.",
		"recipient": "John Williams",
		"recipientPhone": "123456789012"
	},
	*/

	const shippingAddressData = {};
	const billingAddressData = {};
	const bfcustomerData = {};

	const shippingSchema = [
		{
			wc: 'address_1',
			bf: 'address1',
		},
		{
			wc: 'address_2',
			bf: 'address2',
		},
		{
			wc: 'city',
		},
		{
			wc: 'company',
		},
		{
			wc: 'country',
		},
		{
			wc: 'phone',
			bf: 'recipientPhone',
		},
		{
			wc: 'postcode',
			bf: 'zip',
		},
		{
			wc: 'state',
		},
	];

	const billingSchema = [
		{
			wc: 'address_1',
			bf: 'address1',
		},
		{
			wc: 'address_2',
			bf: 'address2',
		},
		{
			wc: 'city',
		},
		{
			wc: 'company',
		},
		{
			wc: 'country',
		},
		{
			wc: 'postcode',
			bf: 'zip',
		},
		{
			wc: 'state',
		},
	];

	if (
		customerData.shippingAddress.first_name &&
		customerData.shippingAddress.last_name
	) {
		shippingAddressData.recipient =
			customerData.shippingAddress.first_name +
			' ' +
			customerData.shippingAddress.last_name;
	}

	if (
		customerData.billingAddress.first_name &&
		customerData.billingAddress.last_name
	) {
		bfcustomerData.name =
			customerData.billingAddress.first_name +
			' ' +
			customerData.billingAddress.last_name;
	}

	if ( customerData.billingAddress.email ) {
		bfcustomerData.email =
			customerData.billingAddress.email;
	}

	if ( customerData.billingAddress.phone ) {
		bfcustomerData.phone =
			'+' +
			Input.parseIntPhoneNumber(
				customerData.billingAddress.phone
			);
	}

	for ( const field of shippingSchema ) {
		if ( !! customerData.shippingAddress[ field.wc ] ) {
			if ( field.bf == null ) {
				shippingAddressData[ field.wc ] =
					customerData.shippingAddress[ field.wc ];
			} else {
				shippingAddressData[ field.bf ] =
					customerData.shippingAddress[ field.wc ];
			}
		}
	}

	for ( const field of billingSchema ) {
		if ( !! customerData.billingAddress[ field.wc ] ) {
			if ( field.bf == null ) {
				billingAddressData[ field.wc ] =
					customerData.billingAddress[ field.wc ];
			} else {
				billingAddressData[ field.bf ] =
					customerData.billingAddress[ field.wc ];
			}
		}
	}
	
	return {
		shippingAddressData,
		billingAddressData,
		bfcustomerData
	}
}

function get_total(total_price, currency_minor_unit) {
	let total = parseInt(total_price)
	
	let minor_unit = parseInt(currency_minor_unit)
	
	if(minor_unit != NaN) {
		total = total / Math.pow(10, minor_unit)
	}
	
	return total.toString()
}

const Content = ( props ) => {
	const customerData = select( CART_STORE_KEY ).getCustomerData();
	const cartData = select( CART_STORE_KEY ).getCartData();
	const cardTotals = select( CART_STORE_KEY ).getCartTotals();

	const store = select( cartStore );
	
	const checkout_store = select( checkoutStore )
	
	// console.debug('isCustomerDataUpdating:', store.isCustomerDataUpdating())

	// NOTE: total_price including total_fees, total_tax, etc.
	const {
		currency_code,
		total_price,
		currency_minor_unit
	} = cardTotals;

	const { eventRegistration, emitResponse, onSubmit } = props;

	const { onPaymentSetup, onCheckoutValidation } = eventRegistration;
	
	
	// console.debug('getEditingBillingAddress:', checkout_store.getEditingBillingAddress(), dispatch( checkoutStore ).setEditingBillingAddress)
	
	// console.debug('getEditingShippingAddress:', checkout_store.getEditingShippingAddress(), dispatch( checkoutStore ).setEditingShippingAddress)
	
	
	useEffect( () => {
		const unsubscribe = onPaymentSetup( async () => {
			// Here we can do any processing we need, and then emit a response.
			const IsBfTokenReferenceValid =
				!! bluefin_component.request.bftokenreference;

			if ( IsBfTokenReferenceValid ) {
				return {
					type: emitResponse.responseTypes.SUCCESS,
					meta: {
						paymentMethodData: {
							...bluefin_component.request,
						},
					},
				};
			}

			return {
				type: emitResponse.responseTypes.ERROR,
				message: 'Please, complete the Bluefin checkout step.',
			};
		} );
		// Unsubscribes when this component is unmounted.
		return () => {
			unsubscribe();
		};
	}, [
		emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentSetup,
	] );

	/*
	useEffect( () => {
		const unsubscribe = onCheckoutValidation( () => {
			// console.debug('onCheckoutValidation')
			return {
				type: emitResponse.responseTypes.ERROR,
				message: 'Please, complete the Bluefin checkout step.',
			}
		} );
		return unsubscribe;
	}, [ onCheckoutValidation ] );
	*/

	// console.debug('componets: ', props.components.ValidationInputError('AAA') )

	console.debug( 'Content props:', props, cardTotals );

	const iframeConfig = {
		parentDivId: 'bluefin-payment-gateway-iframe-container',
		width: '700px',
		height: '500px',
	};

	console.debug( 'paymentStatus:', props.paymentStatus );

	console.debug( 'emitResponse:', emitResponse );

	const callbacks = {
		iframeLoaded( ...args ) {
			/*
			console.debug('customerData', customerData, store)

			console.debug('cartData: ', cartData)


			// select(CART_STORE_KEY).getNeedsShipping()
			console.debug('needsShipping: ', cartData.needsShipping)

			// NOTE: Gets order grand total including taxes, shipping cost, fees, and coupon discounts. Used in gateways.
			console.debug('cardTotals: ', cardTotals, currency_code, total_price)

			console.debug('Iframe loaded', args)

			console.debug('getOrderNotes:', store.getOrderNotes())

			console.debug('props:', props, )
			*/

			bluefin_component.request.total_price = get_total(total_price, currency_minor_unit);
			bluefin_component.request.currency = currency_code;
			// props.shouldSavePayment = true;
		},
		checkoutComplete( data ) {
			console.debug( 'Checkout complete:', data );

			bluefin_component.request.bftokenreference = data.bfTokenReference;
			// Trigger Place Order Button
			onSubmit();

			// data.data.meta.savePaymentOption;
		},
		error( data ) {
			console.error( 'Error:', data );
		},
		timeout( data ) {
			console.debug( 'Timeout:', data );
		},
	}

	if ( ! bluefin_component.loaded ) {
		bluefin_component.loaded = true;

		const init_iframe_id = setInterval( async () => {
			if (
				document.getElementById(
					'bluefin-payment-gateway-iframe-container'
				) != null &&
				window.bluefinPlugin
			) {
				const { cc_endpoint, generate_bearer_token_url, nonce } =
					window.bluefinPlugin;

				clearInterval( init_iframe_id );

				let resp = null,
					data = null;

				// '/index.php?rest_route=/wc_bluefin/v1/generate_bearer_token'
				try {
					const bearer_body = {};
					
					const {
						shippingAddressData,
						billingAddressData,
						bfcustomerData
					} = transformCustomerData(customerData)

					if (
						cartData.needsShipping &&
						customerData.shippingAddress
					) {
						bearer_body.shippingaddress = shippingAddressData;
					}

					if ( customerData.billingAddress ) {
						bfcustomerData.billingAddress = billingAddressData;
						bearer_body.customer = bfcustomerData;
					}

					bearer_body.total_price = get_total(total_price, currency_minor_unit);
					bearer_body.currency = currency_code;

					console.debug( 'bearer_body:', bearer_body );
					
					
					document.getElementById(
					'bluefin-payment-gateway-iframe-container').addEventListener('click', function()
						{
							dispatch( checkoutStore ).setEditingBillingAddress(false);console.log("EditingBillingAddress");
						})
					
					// Request Bearer Token
					resp = await fetch( generate_bearer_token_url, {
						method: 'POST',
						// credentials: "include",
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce': nonce,
						},
						body: JSON.stringify( bearer_body ),
					} );

					if (
						resp.headers
							.get( 'content-type' )
							.includes( 'application/json' )
					) {
						data = await resp.json();
					}

					console.debug(
						`res: ${ generate_bearer_token_url }`,
						resp
					);

					if ( ! resp.ok ) {
						const err = new Error(
							'HTTP status code: ' + resp.status
						);
						err.message = JSON.stringify( data );
						err.status = resp.status;
						throw err;
					}
				} catch ( err ) {
					alert( err );
				}

				const bearerToken = data.iframe_instance_resp.bearerToken;
				
				
				const transactionId = data.iframe_instance_resp.transactionId;

				bluefin_component.bearerToken = bearerToken;

				bluefin_component.request.transactionid = transactionId;

				window.IframeV2.init(
					iframeConfig,
					bearerToken,
					callbacks,
					null,
					cc_endpoint
				);
			}
		}, 1111 );
	} else {
		const iframe_container = document.getElementById(
			'bluefin-payment-gateway-iframe-container'
		);

		console.debug( 'else:', JSON.stringify( bluefin_component ) ); // prevent mutation for logging with JSON.stringify

		// NOTE: Prevent injecting the same iframe twice or more and start clean.
		iframe_container && ( iframe_container.innerHTML = '' );

		// Still problems with same messages doubling up.
		// TODO: Should be fixed by the 06.2025 release. Check in then
		// getEventListeners

		const bearerToken = bluefin_component.bearerToken;

		bearerToken &&
			window.bluefinPlugin &&
			window.IframeV2.init(
				iframeConfig,
				bearerToken,
				callbacks,
				null,
				window.bluefinPlugin.cc_endpoint
			);
	}

	// settings.description
	return <div id="bluefin-payment-gateway-iframe-container"></div>;
};



( async function () {
	const BluefinPaymentMethod = {
		name: PAYMENT_METHOD_NAME,
		label: <Label />,
		content: <Content onClick = {function() { dispatch( checkoutStore ).setEditingBillingAddress(false);console.log("EditingBillingAddress"); }} />,
		edit: <Content />,
		canMakePayment,
		ariaLabel: label,
		// placeOrderButtonLabel: '',
		supports: {
			features: settings?.supports ?? [],

			// NOT NEEDED since Bluefin SDK has these built-in.
			// showSaveOption: true,
			// showSavedCards: true,

			// features: [{ showSaveOption: true }]
		},
	};

	// console.debug(wc.wcBlocksRegistry, settings?.supports );

	registerPaymentMethod( BluefinPaymentMethod );
} )();
