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

import { dispatch, select, useSelect, useDispatch } from '@wordpress/data';

import {
	paymentStore,
	CHECKOUT_STORE_KEY,
	checkoutStore,
	cartStore,
	CART_STORE_KEY,
} from '@woocommerce/block-data';

import {
	useRef,
	useEffect,
	memo,
	useCallback
} from '@wordpress/element';

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
	
	const divRef = useRef(null);
	
	
	useEffect(() => {
		if(divRef.current) {
			const id = setInterval(() => {
				if(divRef.current && divRef.current.parentNode) {
					clearInterval(id)
					
					console.log(divRef.current.parentNode.parentNode.parentNode.parentNode)
					
					divRef.current.parentNode.parentNode.parentNode.addEventListener('click', function() {
						bluefin_component.closeEditing()
					})
				}
			}, 555)
		}
	}, [])

	return (
		<div ref = { divRef }>
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
		</div>
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

async function createAndInjectBluefinIframe(context) {
	const {
		cartData,
		customerData,
		total_price,
		currency_code,
		currency_minor_unit,
		iframeConfig,
		callbacks,
	} = context

	const { cc_endpoint, generate_bearer_token_url, nonce } =
		window.bluefinPlugin;
	

	let resp = null,
		data = null;
		
	
	const iframe_container = document.querySelector(
		'#bluefin-payment-gateway-iframe-container'
	);
	
	// Still problems with same messages doubling up.
	// TODO: Should be fixed by the 06.2025 release. Check in then
	// getEventListeners
	// NOTE: Prevent injecting the same iframe twice or more and start clean.
	// NOTE: Transaction ID has already been used
	iframe_container && ( iframe_container.innerHTML = '' );
	
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

		console.debug( 'fetching bearer_body:', bearer_body );
		
		
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
	
	
	bluefin_component.customerData = JSON.stringify(customerData)

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

/*
if(!window._domloaded) {
document.addEventListener("DOMContentLoaded", function() {
	console.log("DOMContentLoadedd", JSON.stringify(window.getCustomerData()));
})
window._domloaded = true
}
*/

const BluefinCheckout = ( props ) => {
	const customerData = useSelect( ( select ) =>
		select( CART_STORE_KEY ).getCustomerData()
	);
	
	// const customerData = select( CART_STORE_KEY ).getCustomerData();
	const cartData = select( CART_STORE_KEY ).getCartData();
	const cardTotals = select( CART_STORE_KEY ).getCartTotals();

	const store = select( cartStore );
	
	const checkout_store = select( checkoutStore )
	
	const { isEditingShippingAddress, isEditingBillingAddress } = useSelect(
		( select ) => {
			const store = select( CHECKOUT_STORE_KEY );
			return {
				// Default to true if the getter function doesn't exist
				isEditingShippingAddress: store.getEditingShippingAddress
					? store.getEditingShippingAddress()
					: true,
				isEditingBillingAddress: store.getEditingBillingAddress
					? store.getEditingBillingAddress()
					: true,
			};
		},
		[]
	);
	
	// console.debug('isCustomerDataUpdating:', store.isCustomerDataUpdating())

	// NOTE: total_price including total_fees, total_tax, etc.
	const {
		currency_code,
		total_price,
		currency_minor_unit
	} = cardTotals;

	const { eventRegistration, emitResponse, onSubmit } = props;

	const { onPaymentSetup, onCheckoutValidation } = eventRegistration;
	
	const isEditing = isEditingShippingAddress
		|| isEditingBillingAddress
	
	
	const customerDataAsString = () => JSON.stringify(customerData)
	
	const isScriptLoaded = () => window.bluefinPlugin != null
	
	const sameCustomerData = () => { 
		// console.debug(customerDataAsString(), bluefin_component.customerData);
		return bluefin_component.customerData != null && customerDataAsString() == bluefin_component.customerData
	}
	
	// for dev
	window.getCustomerData = () => customerData
	
	
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

	console.debug( 'Content props:', props, cardTotals, store );
	
	console.debug('customerDataAsString:', customerDataAsString())
	
	// console.debug('window.bluefinPlugin:', window.bluefinPlugin)

	const iframeConfig = {
		parentDivId: 'bluefin-payment-gateway-iframe-container',
		width: '700px',
		height: '500px',
	};

	console.debug( 'paymentStatus:', props.paymentStatus );

	console.debug( 'emitResponse:', emitResponse );
	
	/*
	useEffect(() => {
		setTimeout(()=>{
			console.log('useEffect:', JSON.stringify(window.getCustomerData()))
		}, 5555)
	}, [])
	*/
	
	if(isEditing || !isScriptLoaded() ) {
		const iframe_container = document.querySelector(
			'#bluefin-payment-gateway-iframe-container'
		);
		
		iframe_container && ( iframe_container.innerHTML = '' );
		
		return <div id="bluefin-payment-gateway-iframe-container"></div>;
	}

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
		
		bluefin_component.customerData = JSON.stringify(customerData)

		const init_iframe_id = setInterval( async () => {
			if (
				document.querySelector(
					'#bluefin-payment-gateway-iframe-container'
				) != null
			) {
				clearInterval( init_iframe_id );
				
				await createAndInjectBluefinIframe({
					cartData,
					customerData,
					total_price,
					currency_minor_unit,
					currency_code,
					iframeConfig,
					callbacks,
				})
				
			}
		}, 1111 );
		
	} else {
		const iframe_container = document.querySelector(
			'#bluefin-payment-gateway-iframe-container'
		);

		console.debug( 'else:', JSON.stringify( bluefin_component ) ); // prevent mutation for logging with JSON.stringify
		
		// Still problems with same messages doubling up.
		// TODO: Should be fixed by the 06.2025 release. Check in then
		// getEventListeners
		// NOTE: Prevent injecting the same iframe twice or more and start clean.
		// NOTE: Transaction ID has already been used
		iframe_container && ( iframe_container.innerHTML = '' );

		const same_customer_data = sameCustomerData()
		
		console.debug([
			!isEditing && same_customer_data,
			!isEditing && !same_customer_data,
		])
		
		if(!isEditing && same_customer_data) {
			const bearerToken = bluefin_component.bearerToken;

			bearerToken &&
				window.IframeV2.init(
					iframeConfig,
					bearerToken,
					callbacks,
					null,
					window.bluefinPlugin.cc_endpoint
				);
		} else if(!isEditing && !same_customer_data) {
		
			;(async function() {
				await createAndInjectBluefinIframe({
					cartData,
					customerData,
					total_price,
					currency_minor_unit,
					currency_code,
					iframeConfig,
					callbacks,
				})
				

	
	
			})();
			
			// If shipping same as billing address
			/*
			const {
				setShippingAddress: setShippingAddressDispatch,
				setBillingAddress: setBillingAddressDispatch,
			} = useDispatch( 'wc/store/cart' );
			
			let _billingAddress = { ... customerData.billingAddress }
			delete _billingAddress['email']
			
			bluefin_component.customerData = JSON.stringify({...customerData, shippingAddress: _billingAddress})
					setShippingAddressDispatch(_billingAddress)
			*/
					
					
		}
		// NOTE: fall through otherwise, which returns the payment label only given that we do (iframe_container.innerHTML = '')
		/* else if(isEditing) {
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
		} */


	}

	// settings.description
	return <div id="bluefin-payment-gateway-iframe-container"></div>;
};


/*
const BluefinIframe = memo( function BluefinIframe (props) {
	const customerData = useSelect( ( select ) =>
		select( CART_STORE_KEY ).getCustomerData()
	);
	const { isEditingShippingAddress, isEditingBillingAddress } = useSelect(
		( select ) => {
			const store = select( CHECKOUT_STORE_KEY );
			return {
				// Default to true if the getter function doesn't exist
				isEditingShippingAddress: store.getEditingShippingAddress
					? store.getEditingShippingAddress()
					: true,
				isEditingBillingAddress: store.getEditingBillingAddress
					? store.getEditingBillingAddress()
					: true,
			};
		},
		[]
	);
	

	useEffect(() => {
		console.debug('props:', props)
		console.debug('BluefinIframe useEffect',
			window.bluefinPlugin,
			JSON.stringify(customerData),
		)
	}, [])
	
	console.debug('BluefinIframe', props, JSON.stringify(customerData))
	
	// dispatch( checkoutStore ).setEditingBillingAddress(true);
	
	
	return <div id="bluefin-payment-gateway-iframe-container"></div>
} )

const BluefinCheckout = (props) => {
	const checkout_store = select( checkoutStore )
	
	console.debug('BluefinCheckout')
	
	
	let _editing = checkout_store.getEditingBillingAddress()
	
	

	
	// Get dispatch functions to update address editing states
	const { setEditingShippingAddress, setEditingBillingAddress } =
		useDispatch( CHECKOUT_STORE_KEY );

	// Memoized function to update shipping address editing state
	const setShippingAddressEditing = useCallback(
		( isEditing ) => {
			if ( typeof setEditingShippingAddress === 'function' ) {
				setEditingShippingAddress( isEditing );
			}
		},
		[ setEditingShippingAddress ]
	);

	// Memoized function to update billing address editing state
	const setBillingAddressEditing = useCallback(
		( isEditing ) => {
			if ( typeof setEditingBillingAddress === 'function' ) {
				setEditingBillingAddress( isEditing );
			}
		},
		[ setEditingBillingAddress ]
	);
	

	return <BluefinIframe
		props = {
			props
		}
		/>
}
*/


// For development
bluefin_component.closeEditing = function() { 
/*
	const { setEditingShippingAddress, setEditingBillingAddress } =
		useDispatch( CHECKOUT_STORE_KEY );

	// Memoized function to update shipping address editing state
	const setShippingAddressEditing = useCallback(
		( isEditing ) => {
			if ( typeof setEditingShippingAddress === 'function' ) {
				setEditingShippingAddress( isEditing );
			}
		},
		[ setEditingShippingAddress ]
	);

	// Memoized function to update billing address editing state
	const setBillingAddressEditing = useCallback(
		( isEditing ) => {
			if ( typeof setEditingBillingAddress === 'function' ) {
				setEditingBillingAddress( isEditing );
			}
		},
		[ setEditingBillingAddress ]
	);
	
	setBillingAddressEditing(false);
	*/
	
	
	dispatch( checkoutStore ).setEditingBillingAddress(false);

}




;( async function () {
	const BluefinPaymentMethod = {
		name: PAYMENT_METHOD_NAME,
		label: <Label />,
		content: <BluefinCheckout />,
		edit: <BluefinCheckout />,
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
