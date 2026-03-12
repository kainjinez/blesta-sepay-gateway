<?php
/**
 * SePay hosted checkout non-merchant gateway.
 *
 * Hosted checkout only, VND only, single-invoice flow.
 */
class Sepay extends NonmerchantGateway
{
    /**
     * @var array Gateway meta settings
     */
    private $meta = [];

    /**
     * Gateway constructor.
     */
    public function __construct()
    {
        $this->loadConfig(dirname(__FILE__) . DS . 'config.json');
        Loader::loadComponents($this, ['Input']);
        Language::loadLang('sepay', null, dirname(__FILE__) . DS . 'language' . DS);
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettings(array $meta = null)
    {
        $meta = $this->normalizeSettingsMeta($meta);

        $this->view = $this->makeView('settings', 'default', str_replace(ROOTWEBDIR, '', dirname(__FILE__) . DS));
        Loader::loadHelpers($this, ['Form', 'Html']);

        $this->view->set('meta', $meta);
        $this->view->set('modes', [
            'sandbox' => Language::_('Sepay.meta.mode.sandbox', true),
            'live' => Language::_('Sepay.meta.mode.live', true)
        ]);
        $this->view->set('payment_methods', [
            '' => Language::_('Sepay.meta.payment_method.none', true),
            'BANK_TRANSFER' => Language::_('Sepay.meta.payment_method.bank_transfer', true),
            'NAPAS_BANK_TRANSFER' => Language::_('Sepay.meta.payment_method.napas_bank_transfer', true),
            'CARD' => Language::_('Sepay.meta.payment_method.card', true)
        ]);

        return $this->view->fetch();
    }

    /**
     * {@inheritDoc}
     */
    public function editSettings(array $meta)
    {
        $meta = $this->normalizeSettingsMeta($meta);
        $meta['payment_method'] = (isset($meta['payment_method']) ? $meta['payment_method'] : '');
        $meta['order_prefix'] = $this->normalizeOrderPrefix($meta['order_prefix'] ?? null);

        $rules = [
            'sandbox_merchant' => [
                'empty' => [
                    'rule' => 'isEmpty',
                    'negate' => true,
                    'message' => Language::_('Sepay.!error.sandbox_merchant.empty', true)
                ]
            ],
            'sandbox_checkout_secret' => [
                'empty' => [
                    'rule' => 'isEmpty',
                    'negate' => true,
                    'message' => Language::_('Sepay.!error.sandbox_checkout_secret.empty', true)
                ]
            ],
            'sandbox_ipn_secret' => [
                'empty' => [
                    'rule' => 'isEmpty',
                    'negate' => true,
                    'message' => Language::_('Sepay.!error.sandbox_ipn_secret.empty', true)
                ]
            ],
            'live_merchant' => [
                'empty' => [
                    'rule' => 'isEmpty',
                    'negate' => true,
                    'message' => Language::_('Sepay.!error.live_merchant.empty', true)
                ]
            ],
            'live_checkout_secret' => [
                'empty' => [
                    'rule' => 'isEmpty',
                    'negate' => true,
                    'message' => Language::_('Sepay.!error.live_checkout_secret.empty', true)
                ]
            ],
            'live_ipn_secret' => [
                'empty' => [
                    'rule' => 'isEmpty',
                    'negate' => true,
                    'message' => Language::_('Sepay.!error.live_ipn_secret.empty', true)
                ]
            ],
            'mode' => [
                'valid' => [
                    'rule' => ['in_array', ['sandbox', 'live']],
                    'message' => Language::_('Sepay.!error.mode.valid', true)
                ]
            ],
            'payment_method' => [
                'valid' => [
                    'rule' => ['in_array', ['', 'BANK_TRANSFER', 'NAPAS_BANK_TRANSFER', 'CARD']],
                    'message' => Language::_('Sepay.!error.payment_method.valid', true)
                ]
            ],
            'order_prefix' => [
                'valid' => [
                    'rule' => [[$this, 'isValidOrderPrefix']],
                    'message' => Language::_('Sepay.!error.order_prefix.valid', true)
                ]
            ]
        ];

        $this->Input->setRules($rules);
        $this->Input->validates($meta);

        return $meta;
    }

    /**
     * {@inheritDoc}
     */
    public function encryptableFields()
    {
        return [
            'checkout_secret',
            'ipn_secret',
            'sandbox_checkout_secret',
            'sandbox_ipn_secret',
            'live_checkout_secret',
            'live_ipn_secret'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setMeta(array $meta = null)
    {
        $this->meta = (array)$meta;
    }

    /**
     * {@inheritDoc}
     */
    public function buildProcess(array $contact_info, $amount, array $invoice_amounts = null, array $options = null)
    {
        if (strtoupper((string)$this->currency) !== 'VND') {
            $this->Input->setErrors(['currency' => ['valid' => Language::_('Sepay.!error.currency.valid', true)]]);
            return;
        }

        if (empty($invoice_amounts) || count($invoice_amounts) !== 1) {
            $this->Input->setErrors(['invoices' => ['valid' => Language::_('Sepay.!error.invoices.single', true)]]);
            return;
        }

        if (empty($options['return_url'])) {
            $this->Input->setErrors(['return_url' => ['valid' => Language::_('Sepay.!error.return_url.valid', true)]]);
            return;
        }

        $invoice_id = (int)($invoice_amounts[0]['id'] ?? 0);
        $order_amount = (int)round((float)($invoice_amounts[0]['amount'] ?? 0), 0);
        if ($invoice_id <= 0 || $order_amount <= 0) {
            $this->Input->setErrors(['amount' => ['valid' => Language::_('Sepay.!error.amount.valid', true)]]);
            return;
        }

        $client_id = (int)($contact_info['client_id'] ?? 0);
        $company_id = (int)Configure::get('Blesta.company_id');
        $order_reference = $this->buildOrderReference($company_id, $client_id, $invoice_id);
        $description = trim((string)($options['description'] ?? 'Invoice #' . $invoice_id));
        $description = substr(preg_replace('/\s+/', ' ', $description), 0, 255);

        $return_params = [
            'sepay_order' => $order_reference,
            'sepay_invoice' => $invoice_id
        ];
        $success_url = $this->appendQuery($options['return_url'], array_merge($return_params, ['sepay_result' => 'success']));
        $error_url = $this->appendQuery($options['return_url'], array_merge($return_params, ['sepay_result' => 'error']));
        $cancel_url = $this->appendQuery($options['return_url'], array_merge($return_params, ['sepay_result' => 'cancel']));

        $merchant = $this->getActiveCredential('merchant');
        $checkout_secret = $this->getActiveCredential('checkout_secret');
        if ($merchant === '' || $checkout_secret === '') {
            $this->Input->setErrors(['credentials' => ['valid' => Language::_('Sepay.!error.credentials.active', true)]]);
            return;
        }

        $fields = ['merchant' => $merchant, 'operation' => 'PURCHASE'];
        if (!empty($this->meta['payment_method'])) {
            $fields['payment_method'] = $this->meta['payment_method'];
        }
        $fields['order_amount'] = (string)$order_amount;
        $fields['currency'] = 'VND';
        $fields['order_invoice_number'] = $order_reference;
        $fields['order_description'] = $description;
        $fields['customer_id'] = (string)$client_id;
        $fields['success_url'] = $success_url;
        $fields['error_url'] = $error_url;
        $fields['cancel_url'] = $cancel_url;

        $fields['signature'] = $this->buildSignature($fields, $checkout_secret);

        $log_fields = $fields;
        $log_fields['signature'] = '[redacted]';
        $this->log(($_SERVER['REQUEST_URI'] ?? null), json_encode($log_fields), 'input', true);

        $this->view = $this->makeView('process', 'default', str_replace(ROOTWEBDIR, '', dirname(__FILE__) . DS));
        Loader::loadHelpers($this, ['Form', 'Html']);

        $this->view->set('post_to', $this->getCheckoutUrl());
        $this->view->set('fields', $fields);

        return $this->view->fetch();
    }

    /**
     * {@inheritDoc}
     */
    public function validate(array $get, array $post)
    {
        $secret = $this->getActiveCredential('ipn_secret');
        $header_secret = (string)($_SERVER['HTTP_X_SECRET_KEY'] ?? '');
        $payload_raw = file_get_contents('php://input');
        $payload = json_decode((string)$payload_raw, true);

        if (empty($payload_raw) || !is_array($payload) || $secret === '' || !hash_equals($secret, $header_secret)) {
            $this->log(($_SERVER['REQUEST_URI'] ?? null), (string)$payload_raw, 'output', false);
            return;
        }

        $order = (isset($payload['order']) && is_array($payload['order']) ? $payload['order'] : []);
        $transaction = (isset($payload['transaction']) && is_array($payload['transaction']) ? $payload['transaction'] : []);
        $order_reference = (string)($order['order_invoice_number'] ?? '');
        $order_currency = strtoupper((string)($order['order_currency'] ?? ''));
        $order_amount = (float)($order['order_amount'] ?? 0);
        $notification_type = strtoupper((string)($payload['notification_type'] ?? ''));
        $order_status = strtoupper((string)($order['order_status'] ?? ''));
        $transaction_status = strtoupper((string)($transaction['transaction_status'] ?? ''));

        $reference_data = $this->parseOrderReference($order_reference);
        if (empty($reference_data) || $reference_data['company_id'] != (int)Configure::get('Blesta.company_id')) {
            $this->log(($_SERVER['REQUEST_URI'] ?? null), json_encode($payload), 'output', false);
            return;
        }

        Loader::loadModels($this, ['Invoices']);
        $invoice = $this->Invoices->get($reference_data['invoice_id']);
        if (!$invoice || (int)$invoice->client_id !== $reference_data['client_id']) {
            $this->log(($_SERVER['REQUEST_URI'] ?? null), json_encode($payload), 'output', false);
            return;
        }

        if ($order_currency !== 'VND' || $order_amount <= 0) {
            $this->log(($_SERVER['REQUEST_URI'] ?? null), json_encode($payload), 'output', false);
            return;
        }

        $status = 'pending';
        if ($notification_type === 'ORDER_PAID' && $order_status === 'CAPTURED' && $transaction_status === 'APPROVED') {
            $status = 'approved';
        } elseif ($notification_type === 'TRANSACTION_VOID' || $order_status === 'CANCELLED' || $transaction_status === 'VOID') {
            $status = 'void';
        } elseif (in_array($order_status, ['FAILED', 'DECLINED', 'DENIED', 'REJECTED'])) {
            $status = 'declined';
        }

        $invoice_due = max(0, (float)$invoice->total - (float)$invoice->paid);
        $apply_amount = min($order_amount, $invoice_due);
        $invoices = [];
        if ($apply_amount > 0) {
            $invoices[] = ['id' => (int)$invoice->id, 'amount' => $apply_amount];
        }

        $response = [
            'client_id' => (int)$invoice->client_id,
            'amount' => $order_amount,
            'currency' => 'VND',
            'invoices' => $invoices,
            'status' => $status,
            'reference_id' => (string)($transaction['transaction_id'] ?? ($order['order_id'] ?? '')),
            'transaction_id' => $order_reference,
            'parent_transaction_id' => null
        ];

        $this->log(($_SERVER['REQUEST_URI'] ?? null), json_encode($response), 'output', true);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function success(array $get, array $post)
    {
        $client_id = (int)($get['client_id'] ?? 0);
        $invoice_id = (int)($get['sepay_invoice'] ?? 0);
        $order_reference = (string)($get['sepay_order'] ?? '');
        $result_hint = strtolower((string)($get['sepay_result'] ?? ''));

        Loader::loadModels($this, ['Transactions', 'Invoices']);

        $transaction = null;
        if ($order_reference !== '') {
            $transaction = $this->Transactions->getByTransactionId($order_reference, $client_id);
        }

        $invoice = null;
        if ($invoice_id > 0) {
            $invoice = $this->Invoices->get($invoice_id);
            if (!$invoice || (int)$invoice->client_id !== $client_id) {
                $invoice = null;
            }
        }

        if ($transaction) {
            return [
                'client_id' => $client_id,
                'amount' => (float)$transaction->amount,
                'currency' => (string)$transaction->currency,
                'invoices' => $invoice ? [['id' => $invoice->id, 'amount' => (float)$transaction->amount]] : null,
                'status' => (string)($transaction->status ?? 'pending'),
                'transaction_id' => (string)$transaction->transaction_id,
                'parent_transaction_id' => null,
                'gateway' => 'sepay'
            ];
        }

        $amount = null;
        $currency = 'VND';
        $status = 'pending';
        $invoices = null;

        if ($result_hint === 'cancel') {
            $status = 'void';
        } elseif ($result_hint === 'error') {
            $status = 'declined';
        }

        if ($invoice) {
            $amount = max(0, (float)$invoice->total - (float)$invoice->paid);
            $currency = (string)$invoice->currency;
            if ((float)$invoice->paid >= (float)$invoice->total) {
                $status = 'approved';
            } elseif (!in_array($status, ['void', 'declined'])) {
                $status = 'pending';
            }
            $invoices = [['id' => $invoice->id, 'amount' => ($amount > 0 ? $amount : (float)$invoice->total)]];
        }

        return [
            'client_id' => $client_id,
            'amount' => $amount,
            'currency' => $currency,
            'invoices' => $invoices,
            'status' => $status,
            'transaction_id' => ($order_reference !== '' ? $order_reference : null),
            'parent_transaction_id' => null,
            'gateway' => 'sepay'
        ];
    }

    /**
     * Build SePay signature for hosted checkout fields.
     */
    private function buildSignature(array $fields, $secret)
    {
        $signed_fields = [
            'merchant',
            'operation',
            'payment_method',
            'order_amount',
            'currency',
            'order_invoice_number',
            'order_description',
            'customer_id',
            'success_url',
            'error_url',
            'cancel_url'
        ];

        $parts = [];
        foreach ($signed_fields as $name) {
            if (array_key_exists($name, $fields)) {
                $parts[] = $name . '=' . ($fields[$name] ?? '');
            }
        }

        return base64_encode(hash_hmac('sha256', implode(',', $parts), $secret, true));
    }

    /**
     * Build deterministic order reference used for Blesta transaction matching.
     */
    private function buildOrderReference($company_id, $client_id, $invoice_id)
    {
        $suffix = (string)time() . (string)mt_rand(1000, 9999);
        return $this->getOrderPrefix() . (int)$company_id . '_' . (int)$client_id . '_' . (int)$invoice_id . '_' . $suffix;
    }

    /**
     * Parse order reference back to company/client/invoice.
     */
    private function parseOrderReference($reference)
    {
        $matches = [];
        // Accept any valid alphabetic prefix so pending callbacks still work after a prefix change.
        if (preg_match('/^([A-Z]{1,10})(\d+)_(\d+)_(\d+)_(\d+)$/', (string)$reference, $matches) !== 1) {
            return [];
        }

        return [
            'company_id' => (int)$matches[2],
            'client_id' => (int)$matches[3],
            'invoice_id' => (int)$matches[4]
        ];
    }

    /**
     * Append query string to URL.
     */
    private function appendQuery($url, array $params)
    {
        $separator = (strpos($url, '?') === false ? '?' : '&');
        return $url . $separator . http_build_query($params);
    }

    /**
     * Resolve checkout URL by environment.
     */
    private function getCheckoutUrl()
    {
        if ($this->getMode() === 'sandbox') {
            return 'https://pay-sandbox.sepay.vn/v1/checkout/init';
        }

        return 'https://pay.sepay.vn/v1/checkout/init';
    }

    /**
     * Normalize settings array for view/save compatibility.
     */
    private function normalizeSettingsMeta(array $meta = null)
    {
        $meta = (array)$meta;
        $mode = (isset($meta['mode']) ? $meta['mode'] : null);
        if (!in_array($mode, ['sandbox', 'live'])) {
            $mode = (($meta['sandbox'] ?? 'false') === 'true' ? 'sandbox' : 'live');
        }
        $meta['mode'] = $mode;

        $pairs = [
            'merchant' => ($meta['merchant'] ?? ''),
            'checkout_secret' => ($meta['checkout_secret'] ?? ''),
            'ipn_secret' => ($meta['ipn_secret'] ?? '')
        ];

        foreach (['sandbox', 'live'] as $env) {
            foreach ($pairs as $name => $legacy) {
                $key = $env . '_' . $name;
                if (!isset($meta[$key]) || $meta[$key] === '') {
                    $meta[$key] = $legacy;
                }
            }
        }

        $meta['order_prefix'] = $this->normalizeOrderPrefix($meta['order_prefix'] ?? null);

        return $meta;
    }

    /**
     * Get the configured SePay order prefix.
     */
    private function getOrderPrefix()
    {
        return $this->normalizeOrderPrefix($this->meta['order_prefix'] ?? null);
    }

    /**
     * Normalize the SePay order prefix.
     */
    private function normalizeOrderPrefix($prefix)
    {
        $prefix = strtoupper(trim((string)$prefix));
        if ($prefix === '') {
            return 'BLS';
        }

        return $prefix;
    }

    /**
     * Validate the SePay order prefix format.
     */
    private function isValidOrderPrefix($prefix)
    {
        return (bool)preg_match('/^[A-Z]{1,10}$/', $this->normalizeOrderPrefix($prefix));
    }

    /**
     * Get active mode from settings.
     */
    private function getMode()
    {
        $mode = (string)($this->meta['mode'] ?? '');
        if ($mode === 'sandbox' || $mode === 'live') {
            return $mode;
        }

        return (($this->meta['sandbox'] ?? 'false') === 'true' ? 'sandbox' : 'live');
    }

    /**
     * Get active credential by mode with legacy fallback.
     */
    private function getActiveCredential($name)
    {
        $env_key = $this->getMode() . '_' . $name;
        if (!empty($this->meta[$env_key])) {
            return (string)$this->meta[$env_key];
        }

        return (string)($this->meta[$name] ?? '');
    }
}
