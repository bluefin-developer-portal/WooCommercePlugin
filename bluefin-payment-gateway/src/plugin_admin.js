jQuery(function ($) {
    

    // document.addEventListener("DOMContentLoaded", function (event) {
    $(document).ready(function() {
        const capture_button = document.querySelector("#bluefin_capture_button");

        let { search } = window.location

        let add_capture = typeof (search) == 'string'
            && search.includes('?page=wc-orders&action=edit')
            && capture_button

        add_capture && console.debug('capture_button:', capture_button, window.location)

        add_capture && capture_button.addEventListener('click', async function () {


            if (!window.confirm("Confirm Capture via Bluefin Payment Gateway? Please, note that this action cannot be undone.")) {
                return
            }

            let resp = null, data = null

            const {
                capture_url,
                nonce
            } = bluefinPlugin

            try {
                // Spinning Animation
                $( '#woocommerce-order-items' ).block({
				    message: null,
				    overlayCSS: {
					    background: '#fff',
					    opacity: 0.6
				    }
			    });

                resp = await fetch(capture_url, {
                    method: 'POST',
                    // credentials: "include",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        'order_id': add_capture.dataset.orderId,
                    })
                });

                if (resp.headers.get('content-type').includes('application/json')) {
                    data = await resp.json();
                }

                console.debug('capture resp:', resp, data)

                if (!resp.ok) {
                    let err = new Error("HTTP status code: " + resp.status)
                    err.message = JSON.stringify(data);
                    err.status = resp.status
                    throw err
                }

                // Rest Body Response
                if (data.ok) {
                    alert("Successful Capture via Bluefin Payment Gateway!")
                    $( '#woocommerce-order-items' ).unblock();
                    window.location.reload()
                }

            } catch (err) {
                $( '#woocommerce-order-items' ).unblock();
                alert(err);
            }


        })

    })


})


