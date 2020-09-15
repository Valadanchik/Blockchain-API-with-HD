<?php

namespace Blockchain\Create;

use Blockchain\Blockchain;
use Blockchain\Exception\ParameterError;

class Create
{
    public function __construct(Blockchain $blockchain)
    {
        $this->blockchain = $blockchain;
    }

    public function create($password, $hd=null, $email = null, $label = null)
    {
        return $this->doCreate($password, $hd. null, $email, $label);
    }

    public function createWithKey($password, $privKey, $hd=null, $email = null, $label = null)
    {
        if (!isset($privKey) || is_null($privKey)) {
            throw new ParameterError("Private Key required.");
        }

        return $this->doCreate($password, $hd, $privKey, $email, $label);
    }

    public function doCreate($password, $hd=null, $priv = null, $email = null, $label = null)
    {
        if (!isset($password) || is_null($password)) {
            throw new ParameterError("Password required.");
        }
        
        $params = array(
            'password'=>$password,
            'format'=>'json'
        );
        if (!is_null($priv)) {
            $params['priv'] = $priv;
        }
        if (!is_null($email)) {
            $params['email'] = $email;
        }
        if (!is_null($label)) {
            $params['label'] = $label;
        }
        if (!is_null($hd)){
            $params['hd'] = $hd;
        }


        return new WalletResponse($this->blockchain->post('api/v2/create', $params));
    }
}
