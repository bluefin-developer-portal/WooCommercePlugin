# Bluefin Payment Gateway Plugin for WooCommerce

This is the official eCommerce plugin for accepting and processing payments via the Bluefin Payment Gateway for the WooCommerce platform/stores.

These are the Card-Not-Present transaction types this WooCommerce payment gateway plugin supports:

| Sale | Authorization + Manual Capture | Refund | Partial Refund | Void (Authorization Reversal) |
| ---- | ------------------------------ | ------ | -------------- | ----------------------------- |
| ✔    | ✔                              | ✔      | ✔              | ✗                             |



> 📘 Authorization + Manual Capture
>
> One of the most common use cases of **Authorization + Manual Capture** is when an order requires shipping (shipping-related information including address, phone number, etc). Upon the order having been shipped to the customer, the merchant manually captures the authorization - completing the order.
>
> In the case of Bluefin authorization transaction, note that the status of the WooCommerce order is `On hold`. Upon capture, it is `Completed`.
>
> For the tutorial of authorizing and capturing a WooCommerce order via the Bluefin Plugin, see below. 
>
> Also check out [Order Statuses in WooCommerce](https://woocommerce.com/document/managing-orders/order-statuses/#order-statuses-in-woocommerce) and [WooCommerce | Authorize and capture](https://woocommerce.com/document/woopayments/settings-guide/authorize-and-capture/).



## Dependencies

- WooCommerce



## Checkout Blocks Support

This plugin uses the latest WooCommerce Checkout Blocks UI, built with the React library, to improve the checkout flow and overall user experience.

For more information, see [WooCommerce block-based checkout](https://woocommerce.com/checkout-blocks/).



## Overview

This plugin implements and combines the Bluefin Checkout Component and Bluefin REST API, constituting the complete ready-to-use Bluefin payment method for WooCommerce platform.

The checkout component supports Card Payment, Google Pay, Mastercard Click to Pay, providing an all-in comprehensive eCommerce payment solution.

The plugin requires the merchant integration with the Bluefin Gateway where the integration team sets up your configuration according to your needs. The merchant is free to customize their Iframe configuration and configure their payment method options on their own as they have gained enough experience while certifying their Bluefin integration.

The plugin is built upon the Bluefin® PayConex™ REST API that connects to various PayConex™ services, thus serving as an HTTPS communication bridge to the PayConex™ Gateway.



> 📘 Note
>
> The merchant using this plugin is *not* required to understand much of what's happening behind the scenes and how the Bluefin APIs are used.
>
> If you are interested in all the ins and outs, check out our [Comprehensive Documentation and Reference Materials](https://developers.bluefin.com/payconex/v4/reference/payconex-introduction) and [the plugin source code](https://github.com/bluefin-developer-portal/WooCommercePlugin).



Here are some of the key components that the Bluefin payment plugin offers to the merchant.

### **Bluefin Hosted Checkout Components**

- **Easy Integration:** Use our secure, pre-built Checkout Component UI via our SDK, designed for seamless integration into your existing systems.
- **Security:** These components are hosted on Bluefin's servers and handle all payment data input through an HTML iframe, ensuring that no sensitive credit card data reaches your servers.
- **Flexible Management and Configuration**: With a set of API endpoints, you can easily configure and create iframe payment instances, and effectively overwrite the configuration for a specific instance per customer. For more, see [Creating an Instance](https://developers.bluefin.com/payconex/v4/reference/creating-an-instance).
- **Tokenization:** Once the form is completed, it securely tokenizes the information for CNP transactions by communicating with the ShieldConex® tokenization service and utilizes a payment authentication service based on the type of payment method, e.g. 3DS (Credit or Debit Card), Google Authentication Methods (Google Pay), ACH (Bank Information), Mastercard Click to Pay. After tokenization, a transaction is supposed to be processed during the PayConex™ token life-span (within 10 minutes).
- **Saved Cards**: The Checkout Component enables the customer to securely save their card data by checking the `Save payment method`. During the initialization of the iframe instance, the merchant supplies the saved token references, which facilitates faster checkout.

### **Versatile Transaction Processing**

- **Security:** Bluefin ShieldConex® ensures that no sensitive card information is ever stored on your servers, significantly reducing the PCI scope.
- **Card Not Present Transactions:** Before processing, CNP transactions primarily rely on ShieldConex® for security. **ShieldConex®** does not store any sensitive cardholder data. Instead, it uses tokenization/detokenization on its vaultless tokens for online PII, PHI, payments and ACH account data. These tokens can be securely utilized or stored on the merchant's server, significantly reducing the vendor's or merchant's PCI footprint. However, if the merchant loses these tokens, they *cannot* be recovered. For more information, check out [PayConex™ and ShieldConex®](https://developers.bluefin.com/payconex/v4/reference/payconex-and-shieldconex).
- **Transaction Types**: Our gateway supports a variety of the most common transaction types used on a day-to-day basis such as sale, authorization, store, capture, refund and credit.

### **3DS Support**

- **Security Backbone:** Besides the vaultless tokenization solution by ShieldConex®, Bluefin provides one of the security backbones for processing online CNP transactions, with iframe configurations that can fully integrate 3DS as a feature of PayConex™.
- **Fraud Prevention:** Implement 3DS to enhance fraud prevention and secure customer authentication.
- **User Experience:** Ensure a smooth user experience while maintaining high security standards.
- **3DS MPI Simulation**: Bluefin 3DS Solution can be simulated in the certification environment for testing purposes.



## Installing the Plugin

First, download the latest release of our Bluefin Payment Gateway Plugin from [GitHub](https://github.com/bluefin-developer-portal/WooCommercePlugin/releases).

After downloading the zip of the plugin's build (`bluefin-payment-gateway.zip`), go to `Admin -> Plugins -> Add Plugin -> Upload Plugin` and upload the zip. Then, you are set to activate and configure your plugin instance.

Please check out the [Start with WooCommerce in 5 Steps | Extend WooCommerce](https://woocommerce.com/document/start-with-woocommerce-in-5-steps/#extend-woocommerce) Guide for the purpose and installation of third-party plugins.

> 📘 Building is not required
>
> By downloading `bluefin-payment-gateway.zip`, you do not need to build the plugin - only install it as mentioned above.
>
> If you are a developer testing, you may as well download the source code and build our solution.



## Comprehensive Reference

For the comprehensive documentation on this WooCommerce plugin, please go to [our readme.io page](https://developers.bluefin.com/payconex/v4/reference/woocommerce-plugin-for-bluefin).

