## 6.0.0 - 2021-06-07

- Compatibility with Magento 2.4.x

## 5.1.2 - 2021-03-17

- Changed URLs from backend.

## 5.1.2 - 2021-03-03

- Bumped version of JS SDK.

## 5.1.1 - 2021-01-22

- Fixed and issue when Sipay was the only payment method active.

## 5.1.0 - 2021-01-07

- Added support for PSD2 transactions.

## 5.0.10 - 2020-10-22

- Fixed and issue of precision with Paypal Express Checkout that may cause and error processing a payment.

## 5.0.9 - 2020-10-19

- Fixed a bug that may cause to send more than two decimals on total discount in Paypal Express Checkout

## 5.0.8 - 2020-10-06

- Fixed a bug where Payment Wall and Magento Grand total didn't match. See: https://github.com/magento/magento2/issues/7769

## 5.0.7 - 2020-09-21

- Fixed and issue with order confirmation email sent before payment confirmation on redirect payment methods.

## 5.0.6 - 2020-09-14

- Fixed and issue with state not reseting correctly.
- Fixed and issue that added products from minicart express checkout when in product page.

## 5.0.5 - 2020-09-11

- Fixed and issue with empty streets in express checkout.

## 5.0.4 - 2020-09-07

- Updated version of PHP SDK to include is_digital param in all Express Checkout requests.

## 5.0.3 - 2020-09-03

- Fix and issue with Paypal EC whith taxes.

## 5.0.2 - 2020-08-24

- Fix and issue when minicart and product page or cart page custom position where both active.
- Added validation and tips in admin configuration.

## 5.0.1 - 2020-07-10

- Added cart info to Paypal express checkout request.
- Added logo to Amazon Express checkout overlay.

## 5.0.0 - 2020-07-09

- Added express checkout payment option to minicart, cart and product page.

## 4.1.5 - 2020-07-16

- Changed tag when all products are mixed from mixed to mixto.

## 4.1.4 - 2020-07-16

- Changed tag when all products are virtual from virtual to digital.

## 4.1.3 - 2020-07-14

- Changed the selector to hide place order button.

## 4.1.2 - 2020-07-09

- Fixed and issue where checkout shipping or billing information don't save to quote, preventing from creating order.

## 4.1.1 - 2020-07-08

- Added payment info to admin panel when payment method redirects back to store.

## 4.1.0 - 2020-07-03

- Changed checkout validation to be done once user clicks on PaymentWall.
- Methods that requires redirect to gateway will close the current cart as pending payment order.
- New page that will validate if the payment was succesfull, changing the order state to processing.
