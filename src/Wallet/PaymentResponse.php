<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */


namespace Blockchain\Wallet;

class PaymentResponse
{
    /**
     * Properties
     */
    public $to;                         // string
    public $amounts;                         // string
    public $from;                         // string
    public $fee;                         // string
    public $txid;                         // string
    public $tx_hash;                    // string
    public $message;                    // string
    public $success;                    // string
    public $notice;                     // string

    /**
     * Methods
     */
    public function __construct($json)
    {
        if (array_key_exists('to', $json)) {
            $this->to = $json['to'];
        }
        if (array_key_exists('amounts', $json)) {
            $this->amounts = $json['amounts'];
        }
        if (array_key_exists('from', $json)) {
            $this->from = $json['from'];
        }
        if (array_key_exists('txid', $json)) {
            $this->txid = $json['txid'];
        }
        if (array_key_exists('message', $json)) {
            $this->message = $json['message'];
        }
        if (array_key_exists('tx_hash', $json)) {
            $this->tx_hash = $json['tx_hash'];
        }
        if (array_key_exists('warning', $json)) {
            $this->notice = $json['warning'];
        }
        if (array_key_exists('success', $json)) {
            $this->success = $json['success'];
        }
        if (array_key_exists('fee', $json)) {
            $this->fee = $json['fee']/100000000;
        }
    }
}
