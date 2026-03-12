# Blesta SePay Gateway

![SePay logo](views/default/images/logo.png)

SePay non-merchant hosted checkout gateway for Blesta.

This package lets Blesta clients pay a single invoice in `VND` through SePay hosted checkout and QR payment flow, with IPN-based transaction confirmation and configurable order prefixes.

## Features

- Hosted checkout flow via SePay
- `VND` only
- Single-invoice checkout
- IPN callback validation
- Configurable order prefix for SePay order IDs
- English and Vietnamese language packs
- Blesta transaction matching using the generated order reference

## Requirements

- Blesta `5.13.x` or a compatible custom build
- PHP environment supported by your Blesta installation
- A SePay merchant account
- HTTPS for production callback and return URLs

## Project Structure

```text
sepay/
├── config.json
├── composer.json
├── language/
│   ├── en_us/sepay.php
│   └── vi_vn/sepay.php
├── sepay.php
└── views/default/
```

## Installation

Manual install only.

1. Copy this `sepay` folder into:
   - `components/gateways/nonmerchant/sepay/`
2. In Blesta admin, go to:
   - `Settings -> Payment Gateways`
3. Install `SePay`.
4. Configure the gateway settings.

This project is not maintained as a Composer registry package.

## Gateway Setup In Blesta

After installation, configure these fields:

- `Mode`: `Sandbox` or `Live`
- `Sandbox Merchant`
- `Sandbox Checkout Secret`
- `Sandbox IPN Secret`
- `Live Merchant`
- `Live Checkout Secret`
- `Live IPN Secret`
- `Payment Method` (optional)
- `Order Prefix` (optional, default: `BLS`)

## Required Setup In SePay

In SePay, configure:

- IPN URL:
  - `https://{your-domain}/callback/gw/{company_id}/sepay/`
- Secret header:
  - must match the configured `IPN Secret`

For transaction synchronization by order prefix:

1. Go to [Bank Account](https://my.sepay.vn/bankaccount).
2. Open `General Settings`.
3. Turn on transaction synchronization.
4. Turn on incoming transaction synchronization.
5. In keyword-based synchronization, make sure there is a record matching `Order Prefix`.

## Order ID Format

Generated SePay order IDs follow this structure:

```text
{PREFIX}{company_id}_{client_id}_{invoice_id}_{unique_suffix}
```

Example:

```text
BLS1_25_1024_17123456789012
```

Each payment attempt creates a unique SePay order reference while still mapping back to the same Blesta invoice.

## Local Development

Useful checks before release:

```bash
php -l sepay.php
php -l language/en_us/sepay.php
php -l language/vi_vn/sepay.php
```

Package this gateway for deployment:

```bash
zip -r blesta-sepay-gateway.zip .
```

## Publishing

Initialize Git:

```bash
git init -b main
git add .
git commit -m "feat: initial sepay gateway release"
```

Push to your remote:

```bash
git remote add origin <your-repo-url>
git push -u origin main
```

## Release Checklist

- Verify Sandbox checkout
- Verify Live checkout with a low-value payment
- Verify IPN callback is received
- Verify approved payment is applied to the correct invoice
- Verify order prefix matches SePay synchronization keyword rules
- Verify both `en_us` and `vi_vn` language packs render correctly

## Support

This gateway is intended for Vietnam market usage with `VND` payments through SePay.
