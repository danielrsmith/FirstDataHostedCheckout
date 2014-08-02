<?php

class FirstDataHostedCheckout
{
    private $_config = array();
    private $_amount;
    private $_recurrence_type;
    private $_start_date;
    private $_end_date;
    private $_test;
    private $_item;
    private $_item_description;

    public function __construct($config_array, $test_mode = false)
    {
        $this->_config = self::validateConfigOptions($config_array);
        $this->_test = $test_mode;
    }

    public function createHostedCheckout($amount, $item, $item_description, $recurrence_type = 'none', $start_date = null, $end_date = null)
    {
        $this->_amount = $amount;
        $this->_recurrence_type = $this->validateRecurrenceType($recurrence_type);
        $this->_start_date = $start_date;
        $this->_end_date = $end_date;
        $this->_item = $item;
        $this->_item_description = $item_description;

        return $this;
    }

    public function generateHostedCheckout()
    {
        srand(time());
        $sequence = rand(1000, 100000) + 13579;
        $timestamp = time();
        $hash = $this->checkoutHash($sequence, $timestamp);

        if($this->_recurrence_type != 'none')
        {
            $this->_item_description = 'First Payment: ' . $this->_item_description;
        }

        $data = array(
            'x_login' => urlencode($this->_config['login']),
            'x_amount' => urlencode($this->_amount),
            'x_fp_sequence' => urlencode($sequence),
            'x_fp_timestamp' => urlencode($timestamp),
            'x_fp_hash' => urlencode($hash),
            'x_currency_code' => urlencode($this->_config['currency_code']),
            'x_show_form' => 'PAYMENT_FORM',
            'x_line_item' => sprintf("Item ID<|>%s<|>%s<|>1<|>%s<|><|><|><|><|><|><|><|><|><|>%s", $this->_item,$this->_item_description, $this->_amount, $this->_amount)
        );

        if($this->_test)
        {
            $data['x_test_request'] = 'TRUE';
        }

        if($this->_recurrence_type != 'none')
        {
            //We are in a recurring billing and need to append values
            $data['x_recurring_billing'] = 'TRUE';
            $data['x_recurring_billing_id'] = $this->_config['recurrence_type'][$this->_recurrence_type];
            $data['x_recurring_billing_amount'] = $this->_amount;
            $data['x_recurring_billing_start_date'] = $this->_start_date;
            $data['x_recurring_billing_end_date'] = $this->_end_date;
        }

        $response = sprintf("<form action=\"%s\" method=\"POST\" id='paymentForm'>", $this->_config['url']);

        foreach($data as $field => $value)
        {
            $response .= sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />", $field, $value);
        }

        $response .= "</form>";

        return $response;
    }

    private function checkoutHash($sequence, $timestamp)
    {
        $data_string = sprintf("%s^%s^%s^%s^%s", $this->_config['login'],
            $sequence, $timestamp, $this->_amount, $this->_config['currency_code']);
        return hash_hmac('MD5', $data_string, $this->_config['transaction_key']);
    }

    private function validateRecurrenceType($type)
    {
        if($type == 'none' || !isset($type))
        {
            return 'none';
        }

        if(!array_key_exists($type, $this->_config['recurrence_type']))
        {
            throw new Exception('Recurrence type does not exist in configuration.');
        }

        return $type;
    }

    private static function validateConfigOptions($config)
    {
        $required_keys = array('login', 'currency_code', 'recurrence_type', 'transaction_key', 'url');

        if(count(array_intersect_key(array_flip($required_keys), $config)) != count($required_keys))
        {
            throw new Exception('Configuration is not complete.');
        }

        return $config;
    }
}
