document.addEventListener("DOMContentLoaded", function (event) {

    const capture_button = document.querySelector("#bluefin_capture_button");

    // TODO: window.location, ?page=wc-orders&action=edit
   

    let { search } = window.location

    let add_capture = typeof(search) == 'string' 
        && search.includes('?page=wc-orders&action=edit') 
        && capture_button

    add_capture && console.debug('capture_button:', capture_button,  window.location);


    add_capture && capture_button.addEventListener('click', async function () {

        let resp = null, data = null

        try {
            // TODO: bluefinPlugin.rest_url
            resp = await fetch('/index.php?rest_route=/wc_bluefin/v1/capture_transaction', {
                method: 'POST',
                // credentials: "include",
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': bluefinPlugin.nonce
                },
                body: JSON.stringify({})
            });

            if (resp.headers.get('content-type').includes('application/json')) {
                data = await resp.json();
            }

            console.debug('capture resp:', resp)

            if (!resp.ok) {
                let err = new Error("HTTP status code: " + resp.status)
                err.message = JSON.stringify(data);
                err.status = resp.status
                throw err
            }

        } catch (err) {
            alert(err);
        }


    })


});
