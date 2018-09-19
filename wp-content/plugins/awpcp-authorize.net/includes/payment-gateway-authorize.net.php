<?php

require_once( AWPCP_DIR . '/includes/payment-gateway.php' );

class AWPCP_AuthorizeNETPaymentGateway extends AWPCP_PaymentGateway {

    public function __construct() {
        $description = _x('Authorize.Net Adavanced Integration Method', 'authorize.net payment gateway', 'awpcp-authorize.net' );
        $icon = AWPCP_URL . '/resources/images/payments-credit-cards.png';
        parent::__construct('authorize.net', 'Authorize.Net', $description, $icon);
    }

    private function is_authorize_net($transaction) {
        return $transaction->get('payment-method') == 'authorize.net';
    }

    public function get_integration_type() {
        return self::INTEGRATION_CUSTOM_FORM;
    }

    protected function render_checkout_form($transaction, $data=array(), $errors=array()) {
        wp_enqueue_style('awpcp-authorize.net');

        if (get_awpcp_option('paylivetestmode')) {
            $message = _x('Payment Gateway is currently in TEST MODE.', 'payments', 'awpcp-authorize.net' );
            awpcp_flash($message, 'error');
        }

        $hidden = array(
            'transaction_id' => $transaction->id,
            'authorize-net-step' => 'authorize-and-capture',
            'step' => awpcp_array_data('step', awpcp_post_param('step'), $data),
        );

        return $this->render_billing_form($transaction, $data, $hidden, $errors);
    }

    protected function sanitize_billing_information($data) {
        $data = parent::sanitize_billing_information($data);

        if (strlen($data['exp_year']) === 4) {
            $data['exp_year'] = substr($data['exp_year'], 2);
        }

        return $data;
    }

    protected function get_posted_billing_information() {
        $data = parent::get_posted_billing_information();
        $data['authorize-net-step'] = awpcp_post_param('authorize-net-step');

        return $this->sanitize_billing_information($data);
    }

    protected function validate_posted_billing_information($data, &$errors=array()) {
        return parent::validate_posted_billing_information($data, $errors);
    }

    protected function do_authorize_and_capture_request($transaction, $data, $errors) {
        $current_user = wp_get_current_user();

        if ( ! class_exists( 'AuthorizeNetAIM' ) ) {
            require_once( AWPCP_AUTHORIZE_NET_MODULE_DIR . '/vendors/authorize.net/AuthorizeNet.php' );
        }

        $login_id = get_awpcp_option('authorize.net-login-id');
        $transaction_key = get_awpcp_option('authorize.net-transaction-key');
        // $sandbox = get_awpcp_option('authorize.net-sandbox-mode');

        $aim = new AuthorizeNetAIM($login_id, $transaction_key);
        $aim->setFields(array(
                // testing specific response codes
                // 'test_request' => true,
                // 'amount' => 252,
                // 'card_num' => '4222222222222',

                // merchant information
                'amount' => $transaction->get_total_amount(),
                'card_num' => $data['credit_card_number'],
                'exp_date' => "{$data['exp_month']}/{$data['exp_year']}",
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'address' => $data['address_1'],
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => $data['country'],
                'zip' => $data['postal_code'],
                'card_code' => $data['csc'],

                // order information
                'description' => $transaction->get_item(0)->name,
                'invoice_num' => $transaction->id
            )
        );

        if (get_awpcp_option('paylivetestmode')) {
            $aim->setField('test_request', true);
        }

        // Sandbox mode should be always disabled (explictely set to false) in production.
        // The default value is true.
        $aim->setSandbox( false );

        return $aim->authorizeAndCapture();
    }

    private function cancel_authorize_and_capture($transaction) {
        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_CANCELED;
        return awpcp_payments_api()->process_payment_completed($transaction);
    }

    public function authorize_and_capture($transaction) {
        $data = $this->get_posted_billing_information();
        $errors = array();

        if ( $this->validate_posted_billing_information( $data, $errors ) ) {
            // TODO: $errors is unused in do_authorize_and_capture_request(), remove parameter
            $response = $this->do_authorize_and_capture_request( $transaction, $data, $errors );

            return $this->handle_authorize_and_capture_response( $response, $transaction, $data );
        } else {
            return $this->render_checkout_form($transaction, $data, $errors);
        }
    }

    private function handle_authorize_and_capture_response( $response, $transaction, $data ) {
        // this transaction is supposed to be processed only once.
        // clean up previous errors generated during development.
        unset($transaction->errors['validation']);

        $this->store_transaction_information( $transaction, $response, $data );

        if ($response->approved) {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED;

        } else if ($response->held) {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED;
            $message = _x('Your payment has been held for review by the payment gateway. The following reason was given: %s<br/><br/>Please contact us to clarify the problem.', 'authorize.net', 'awpcp-authorize.net' );
            $message = sprintf($message, "({$response->response_reason_code}) {$response->response_reason_text}");
            $transaction->errors['validation'] = $message;

            awpcp_flash($message, 'error');

            return $this->render_checkout_form( $transaction, $data );

        } else {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED;

            $message = _x('There was an error processing your transaction:<br/>%s', 'authorize.net', 'awpcp-authorize.net' );

            if ( $response->response_reason_text ) {
                $message = sprintf( $message, "({$response->response_reason_code}) {$response->response_reason_text}" );
            } else if ( $response->error_message ) {
                $message = sprintf( $message, $response->error_message );
            }

            $transaction->errors['validation'] = $message;

            awpcp_flash($message, 'error');

            return $this->render_checkout_form( $transaction, $data );
        }

        return awpcp_payments_api()->process_payment_completed($transaction);
    }

    private function store_transaction_information( $transaction, $response, $data ) {
        $transaction->set( 'txn-id', $response->transaction_id );
        $transaction->set( 'authorize.net-response', json_decode( json_encode( $response, false ) ) );
        $transaction->payer_email = $data['email'];
        $transaction->payment_gateway = $this->slug;
        $transaction->save();
    }

    public function process_payment($transaction) {
        $step = awpcp_post_param('authorize-net-step', 'checkout-form');

        if ($step == 'checkout-form') {
            return $this->render_checkout_form($transaction);
        } else if (isset($_POST['cancel']) && $step == 'authorize-and-capture') {
            return $this->cancel_authorize_and_capture($transaction);
        } else if ($step == 'authorize-and-capture') {
            return $this->authorize_and_capture($transaction);
        }
    }

    /**
     * No notification support right now.
     */
    public function process_payment_notification($transaction) {
        return false;
    }

    /**
     * This method is not required because the payment is thoroughly
     * processed in the authorize_and_capture method.
     */
    public function process_payment_completed($transaction) {
        return true;
    }

    /**
     * This method is not required because the payment is thoroughly
     * processed in the authorize_and_capture method.
     */
    public function process_payment_canceled($transaction) {
        return true;
    }
}
