<?php
/**
 * en_us language for SePay non-merchant gateway.
 */

// Basics
$lang['Sepay.name'] = 'SePay';
$lang['Sepay.description'] = 'Thanh toán chuyển khoản VND qua trang thanh toán và QR của SePay.';

// Errors
$lang['Sepay.!error.sandbox_merchant.empty'] = 'Bắt buộc nhập Sandbox Merchant.';
$lang['Sepay.!error.sandbox_checkout_secret.empty'] = 'Bắt buộc nhập Sandbox Checkout Secret.';
$lang['Sepay.!error.sandbox_ipn_secret.empty'] = 'Bắt buộc nhập Sandbox IPN Secret.';
$lang['Sepay.!error.live_merchant.empty'] = 'Bắt buộc nhập Live Merchant.';
$lang['Sepay.!error.live_checkout_secret.empty'] = 'Bắt buộc nhập Live Checkout Secret.';
$lang['Sepay.!error.live_ipn_secret.empty'] = 'Bắt buộc nhập Live IPN Secret.';
$lang['Sepay.!error.mode.valid'] = 'Chế độ phải là Sandbox hoặc Live.';
$lang['Sepay.!error.payment_method.valid'] = 'Phương thức thanh toán không hợp lệ.';
$lang['Sepay.!error.order_prefix.valid'] = 'Tiền tố đơn hàng chỉ được gồm 1-10 chữ cái.';
$lang['Sepay.!error.currency.valid'] = 'SePay chỉ hỗ trợ VND.';
$lang['Sepay.!error.invoices.single'] = 'Cổng SePay chỉ hỗ trợ thanh toán từng hóa đơn một.';
$lang['Sepay.!error.return_url.valid'] = 'Thiếu return URL.';
$lang['Sepay.!error.amount.valid'] = 'Số tiền thanh toán không hợp lệ.';
$lang['Sepay.!error.credentials.active'] = 'Thiếu thông tin xác thực của chế độ đang dùng.';

// Settings
$lang['Sepay.meta.mode'] = 'Chế độ';
$lang['Sepay.meta.mode.sandbox'] = 'Sandbox';
$lang['Sepay.meta.mode.live'] = 'Live';
$lang['Sepay.meta.section.sandbox'] = 'Thông tin Sandbox';
$lang['Sepay.meta.section.live'] = 'Thông tin Live';
$lang['Sepay.meta.sandbox_merchant'] = 'Sandbox Merchant';
$lang['Sepay.meta.sandbox_checkout_secret'] = 'Sandbox Checkout Secret';
$lang['Sepay.meta.sandbox_ipn_secret'] = 'Sandbox IPN Secret (X-Secret-Key)';
$lang['Sepay.meta.live_merchant'] = 'Live Merchant';
$lang['Sepay.meta.live_checkout_secret'] = 'Live Checkout Secret';
$lang['Sepay.meta.live_ipn_secret'] = 'Live IPN Secret (X-Secret-Key)';
$lang['Sepay.meta.payment_method'] = 'Phương thức thanh toán (Tùy chọn)';
$lang['Sepay.meta.order_prefix'] = 'Tiền tố đơn hàng';
$lang['Sepay.meta.order_prefix.sync_note'] = 'Hãy vào %1$s -> Cấu hình chung -> bật "Đồng bộ giao dịch" và "Đồng bộ giao dịch tiền vào". Đảm bảo mục "Đồng bộ giao dịch theo từ khoá" có một bản ghi khớp với Tiền tố đơn hàng.';
$lang['Sepay.meta.payment_method.none'] = 'Mặc định SePay';
$lang['Sepay.meta.payment_method.bank_transfer'] = 'Chuyển khoản ngân hàng';
$lang['Sepay.meta.payment_method.napas_bank_transfer'] = 'Chuyển khoản Napas';
$lang['Sepay.meta.payment_method.card'] = 'Thẻ';

$lang['Sepay.meta.note'] = 'Đặt SePay IPN URL là: /callback/gw/{company_id}/sepay/. Tiền tố đơn hàng chỉ dùng chữ cái và mặc định là BLS.';

// Build process
$lang['Sepay.buildprocess.submit'] = 'Thanh toán bằng SePay';
