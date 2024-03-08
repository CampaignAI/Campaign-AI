<?php

ob_start();
require_once 'Credentials.php';
require_once 'Adwords.php';


class Processor {
    protected $advertising;
    public function __construct()
    {           
        $arr = [
            'OAUTH2' => [
                            'developerToken' => Credentials::$DEVELOPER_TOKEN,
                            'clientId' => Credentials::$CLIENT_ID,
                            'clientSecret' => Credentials::$CLIENT_SECRET,
                            'refreshToken' => Credentials::$REFRESH_TOKEN,
                        ]
            ];
            $this->advertising = new Adwords($arr,  Credentials::$MASTER_ID);
    }
   
    public function get_customer_account()
    {
       return $this->advertising->GetAccountInfo($this->advertising->createClient(Credentials::$MASTER_ID), Credentials::$ACCOUNT_ID);
    }

}
