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
    $query = ["service_id" => $this->serviceId,
              "order_id" => $orderId,
              "amount" => $amount,
              "timestamp" => gmstrftime("%Y-%m-%dT%H:%M:%S", time()),
              "currency" => $currency];
    if ($phone) {
      $query["phone"] = $phone;
    }
    $signString = self::signatureData('GET', $this->url, $this->url, $query);
    $query["signature"] = base64_encode(hash_hmac("sha256", $signString, $this->secret, true));
    return $this->url . "?" . http_build_query($query);
  }

  private static function normalizeHost($host) {
    $parsed = parse_url($host);
    if (isset($parsed["port"])) {
      $portPart = ":{$parsed['port']}";
    }
    return $parsed["host"] . $portPart;
  }

  private static function normalizePath($path) {
    $parsed = parse_url($path);
    return $parsed["path"];
  }

  private static function normalizeQuery($query) {
    ksort($query);
    return http_build_query($query);
  }

  private function signatureData($method, $host, $path, $query) {
    return implode("\n", array($method,
                               self::normalizeHost($host),
                               self::normalizePath($path),
                               self::normalizeQuery($query)));
  }
}
