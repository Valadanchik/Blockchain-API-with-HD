<?php

namespace Blockchain\Wallet;

use Blockchain\Blockchain;
use Blockchain\Conversion\Conversion;
use Blockchain\Exception\CredentialsError;
use Blockchain\Exception\ParameterError;

class Wallet
{
    private $identifier = null;
    private $main_password = null;
    private $second_password = null;

    public function __construct(Blockchain $blockchain)
    {
        $this->blockchain = $blockchain;
    }

    public function credentials($id, $pw1, $pw2 = null)
    {
        $this->identifier = $id;
        $this->main_password = $pw1;
        if (!is_null($pw2)) {
            $this->second_password = $pw2;
        }
    }

    private function _checkCredentials()
    {
        if (is_null($this->identifier) || is_null($this->main_password)) {
            throw new CredentialsError('Please enter wallet credentials.');
        }
    }

    private function reqParams($extras = array())
    {
        $ret = array('password'=>$this->main_password);
        if (!is_null($this->second_password)) {
            $ret['second_password'] = $this->second_password;
        }

        return array_merge($ret, $extras);
    }

    private function url($resource)
    {
        return "merchant/" . $this->identifier . "/" . $resource;
    }

    private function call($resource, $params = array())
    {
        $this->_checkCredentials();
        return $this->blockchain->post($this->url($resource), $this->reqParams($params));
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getBalance()
    {
        $json = $this->call('balance');
        return Conversion::btcInt2Str($json['balance']);
    }

    public function getAddressBalance($address)
    {
        return new WalletAddress($this->call('address_balance', array('address'=>$address)));
    }

    public function getAddresses()
    {
        $json = $this->call('list');
        $addresses = array();
        foreach ($json['addresses'] as $address) {
            $addresses[] = new WalletAddress($address);
        }
        return $addresses;
    }

    public function getNewAddress($label = null)
    {
        $params = array();
        if (!is_null($label)) {
            $params['label'] = $label;
        }
        return new WalletAddress($this->call('new_address', $params));
    }

    public function archiveAddress($address)
    {
        $json = $this->call('archive_address', array('address'=>$address));
        if (array_key_exists('archived', $json)) {
            if ($json['archived'] == $address) {
                return true;
            }
        }
        return false;
    }

    public function unarchiveAddress($address)
    {
        $json = $this->call('unarchive_address', array('address'=>$address));
        if (array_key_exists('active', $json)) {
            if ($json['active'] == $address) {
                return true;
            }
        }
        return false;
    }

    public function send($to_address, $amount, $from_address = null, $fee = null)
    {
        if (!isset($amount)) {
            throw new ParameterError("Amount required.");
        }

        $params = array(
            'to' => $to_address,
            'amount' => Conversion::btcFloat2Int($amount)
        );
        if (!is_null($from_address)) {
            $params['from'] = $from_address;
        }else{
            $params['from'] = 0;
        }
        if (!is_null($fee)) {
            $params['fee'] = Conversion::btcFloat2Int($fee);
        }

        return new PaymentResponse($this->call('payment', $params));
    }

    public function sendMany($recipients, $from_address = null, $fee = null)
    {
        $R = array();
        // Construct JSON by hand, preserving the full value of amounts
        foreach ($recipients as $address => $amount) {
            $R[] = '"' . $address . '":' . Conversion::btcFloat2Int($amount);
        }
        $json = '{' . implode(',', $R) . '}';

        $params = array(
            'recipients'=>$json
        );
        if (!is_null($from_address)) {
            $params['from'] = $from_address;
        }
        if (!is_null($fee)) {
            $params['fee'] = Conversion::btcFloat2Int($fee);
        }

        return new PaymentResponse($this->call('sendmany', $params));
    }

    public function listXpubsHD()
    {
        return $this->call('accounts/xpubs');
    }

    public function enableHD()
    {
        return $this->call('enableHD');
    }

    public function receivingAddressHD($xpub = NULL){
        //use xpub of the default account if xpub is not supplied
        $xpub = (is_null($xpub)) ? $this->listXpubsHD()[0] : $xpub;
        if(empty($xpub)){
            throw new ParameterError('Invalid xpub. Please enter correct wallet xpub.');
        }else{
            return $this->call('accounts/'.$xpub.'/receiveAddress');
        }
    }

    public function getBalanceHD($xpub =  NULL)
    {
        $xpub = (is_null($xpub)) ? $this->listXpubsHD()[0] : $xpub;
        if(empty($xpub)){
            throw new ParameterError('Invalid xpub. Please enter correct wallet xpub.');
        }else{
            $json = $this->call('accounts/'.$xpub.'/balance');
            return Conversion::btcInt2Str($json['balance']);
        }
    }
}
