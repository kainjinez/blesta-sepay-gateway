<?php
/**
 * en_us language for SePay non-merchant gateway.
 */

// Basics
$lang['Sepay.name'] = 'SePay';
$lang['Sepay.description'] = 'Accept VND transfers via SePay hosted checkout and QR payment page.';

// Errors
$lang['Sepay.!error.sandbox_merchant.empty'] = 'Sandbox merchant is required.';
$lang['Sepay.!error.sandbox_checkout_secret.empty'] = 'Sandbox checkout secret is required.';
$lang['Sepay.!error.sandbox_ipn_secret.empty'] = 'Sandbox IPN secret is required.';
$lang['Sepay.!error.live_merchant.empty'] = 'Live merchant is required.';
$lang['Sepay.!error.live_checkout_secret.empty'] = 'Live checkout secret is required.';
$lang['Sepay.!error.live_ipn_secret.empty'] = 'Live IPN secret is required.';
$lang['Sepay.!error.mode.valid'] = 'Mode must be sandbox or live.';
$lang['Sepay.!error.payment_method.valid'] = 'Invalid payment method.';
$lang['Sepay.!error.order_prefix.valid'] = 'Order prefix must be 1-10 letters.';
$lang['Sepay.!error.currency.valid'] = 'SePay supports VND only.';
$lang['Sepay.!error.invoices.single'] = 'SePay gateway supports single invoice checkout only.';
$lang['Sepay.!error.return_url.valid'] = 'Missing return URL.';
$lang['Sepay.!error.amount.valid'] = 'Invalid payment amount.';
$lang['Sepay.!error.credentials.active'] = 'Active mode credentials are missing.';

// Settings
$lang['Sepay.meta.mode'] = 'Mode';
$lang['Sepay.meta.mode.sandbox'] = 'Sandbox';
$lang['Sepay.meta.mode.live'] = 'Live';
$lang['Sepay.meta.section.sandbox'] = 'Sandbox Credentials';
$lang['Sepay.meta.section.live'] = 'Live Credentials';
$lang['Sepay.meta.sandbox_merchant'] = 'Sandbox Merchant';
$lang['Sepay.meta.sandbox_checkout_secret'] = 'Sandbox Checkout Secret';
$lang['Sepay.meta.sandbox_ipn_secret'] = 'Sandbox IPN Secret (X-Secret-Key)';
$lang['Sepay.meta.live_merchant'] = 'Live Merchant';
$lang['Sepay.meta.live_checkout_secret'] = 'Live Checkout Secret';
$lang['Sepay.meta.live_ipn_secret'] = 'Live IPN Secret (X-Secret-Key)';
$lang['Sepay.meta.payment_method'] = 'Payment Method (Optional)';
$lang['Sepay.meta.order_prefix'] = 'Order Prefix';
$lang['Sepay.meta.bank_account_link_text'] = 'Bank Account';
$lang['Sepay.meta.order_prefix.sync_note'] = 'Go to %1$s -> General Settings, turn on Sync Transaction and Incoming transaction sync, then make sure Synchronize transactions by keyword contains a record matching Order Prefix.';
$lang['Sepay.meta.payment_method.none'] = 'SePay default';
$lang['Sepay.meta.payment_method.bank_transfer'] = 'Bank Transfer';
$lang['Sepay.meta.payment_method.napas_bank_transfer'] = 'Napas Bank Transfer';
$lang['Sepay.meta.payment_method.card'] = 'Card';

$lang['Sepay.meta.note'] = 'Set SePay IPN URL to: /callback/gw/{company_id}/sepay/. Order Prefix uses letters only and defaults to BLS.';

// Build process
$lang['Sepay.buildprocess.submit'] = 'Pay with SePay';
