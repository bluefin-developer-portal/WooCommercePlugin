# Bluefin Payment Gateway Plugin for WooCommerce

This is the official eCommerce plugin for accepting and processing payments via the Bluefin Payment Gateway for the WooCommerce platform/stores.

These are the Card-Not-Present transaction types that this WooCommerce plugin supports:

| Sale | Authorization + Manual Capture | Refund | Partial Refund | Void (Authorization Reversal) |
| ---- | ------------------------------ | ------ | -------------- | ----------------------------- |
| ✔    | ✔                              | ✔      | ✔              | ✗                             |



One of the most common use cases of **Authorization + Manual Capture** is when an order requires shipping (shipping-related information including address, etc). Upon the order having been shipped to the customer, the merchant manually captures the authorization - completing the order.

In the case of Bluefin authorization transaction, note that the status of the WooCommerce order is `On hold`. Upon capture, it is `Completed`.

For the tutorial of authorization and capturing a WooCommerce order via the Bluefin Plugin, see below. 

Also check out [Order Statuses in WooCommerce](https://woocommerce.com/document/managing-orders/order-statuses/#order-statuses-in-woocommerce) and [WooCommerce | Authorize and capture](https://woocommerce.com/document/woopayments/settings-guide/authorize-and-capture/).



Over the course of transaction actions, the order notes are updated according to the Bluefin Plugin for WooCommerce tracking purposes. For example,

screenshot





To get these actions from the Bluefin API, use the corresponding transaction id attached to the order and get the transaction metadata.

```json
```





## Dependencies

- WooCommerce



## Getting Started

### 

### Installation and Build

-   [NPM](https://www.npmjs.com/)
-   [Composer](https://getcomposer.org/download/)
-   [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)

```
npm install
npm run build
wp-env start
```
