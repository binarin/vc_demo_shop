<?php

// -*- coding: utf-8-unix -*-
namespace VirtualCards;
class API {
    private $url;
    private $serviceId;
    private $secret;
    public function __construct($url, $serviceId, $secret) {
        $this->url = $url;
        $this->serviceId = $serviceId;
        $this->secret = $secret;
    }

    public function payLink($orderId, $amount, $currency = "RUB", $phone = null) {
        return $this->url;
    }
}
