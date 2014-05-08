<?php
// -*- coding: utf-8-unix -*-
class Order {
  public $id;
  public $amount;
  public $status;
  public $notifications;

  public function __construct($data) {
    $this->setData($data);
  }

  private function setData($data) {
    $this->id = $data["id"];
    $this->amount = $data["amount"];
    $this->status = $data["status"];
    $this->notifications = ["notifications" => [], "log" => []];
    if ($data["notifications"]) {
      $this->notifications = json_decode($data["notifications"], true);
      uasort(
        $this->notifications["notifications"],
        function ($a, $b) {
          $a = $a["timestamp"]; $b = $b["timestamp"];
          if ($a == $b) {
            return 0;
          }
          return ($a < $b) ? -1 : 1;
        }
      );
    }
  }

  public static function create($amount) {
    $dbh = Config::dbh();
    $sth = $dbh->prepare("insert into orders (amount) values(?) returning *");
    $sth->execute(array($amount));
    return new Order($sth->fetch(PDO::FETCH_ASSOC));
  }

  public static function load($orderId) {
    $dbh = Config::dbh();
    $sth = $dbh->prepare("select * from orders where id = ?");
    $sth->execute(array($orderId));
    return new Order($sth->fetch(PDO::FETCH_ASSOC));
  }

  public function addNotification($notification) {
    $ts = gmstrftime("%Y-%m-%d %H:%M:%S");
    $notificationId = $notification['notification_id'];
    if (isset($this->notifications["notifications"][$notificationId])) {
      $this->notifications["log"] []= "[$ts] Повторная нотификация {$notification['notification_id']}";
    } else {
      $this->notifications["notifications"][$notificationId] = $notification;
      $this->notifications["log"] []= "[$ts] Получена нотификация {$notification['notification_id']}";
    }
    $this->save();
  }

  public function changeStatus($status) {
    $ts = gmstrftime("%Y-%m-%d %H:%M:%S");
    $this->notifications["log"] []= "[$ts] Смена статуса с '{$this->status}' на '$status'";
    $this->status = $status;
    $this->save();
  }

  public function save() {
    $dbh = Config::dbh();
    $sth = $dbh->prepare("update orders set status = ?, notifications = ? where id = ? returning *");
    $sth->execute([$this->status, json_encode($this->notifications), $this->id]);
    $this->setData($sth->fetch(PDO::FETCH_ASSOC));
  }

  public function addLog($record) {
    $ts = gmstrftime("%Y-%m-%d %H:%M:%S");
    $this->notifications["log"] []= "[$ts] {$record}";
    $this->save();
  }
}
