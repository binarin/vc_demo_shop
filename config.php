<?php
// -*- coding: utf-8-unix -*-

class Config {
  public $vcUrl;
  public $vcServiceId;
  public $vcSecret;

  public function __construct()
  {
    $this->vcUrl = getenv("VC_URL");
    $this->vcServiceId = getenv("VC_SERVICE_ID");
    $this->vcSecret = getenv("VC_SECRET");
  }

  private static $dbh;
  public static function dbh() {
    if (!self::$dbh) {
      $db = parse_url(getenv("DATABASE_URL"));
      $path = ltrim($db["path"], "/");
      $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$path};user={$db['user']};password={$db['pass']}";
      self::$dbh = new PDO($dsn);
      self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return self::$dbh;
  }
}
