<?php
// -*- coding: utf-8-unix -*-
class Page {
  public static function dispatch() {
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "";
    if (preg_match('/^\/pay\/(?P<orderId>\d+)/', $_SERVER['PATH_INFO'], $matches)) {
      $page = new PayPage($matches["orderId"]);
    } elseif ($_SERVER['PATH_INFO'] === '/create') {
      $page = new CreatePage();
    } else {
      $page = new IndexPage();
    }

    return $page->run();
  }
}

class CreatePage {
  public function run() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      header("Location: /");
      return;
    }
    $order = Order::create(10.0);
    header("Location: /pay/{$order['id']}");
  }
}

class IndexPage {
  public function run() {
    $title = 'Покупка слона';
    $content = "
      <h1>Дядь, купи слона!</h1>
      <img src='slon.jpg'/>
      <p>Стоимость 10р.</p>
      <form method='post' action='create'>
        <button type='submit' class='btn btn-primary btn-lg'>Купить слона</button>
      </form>
    ";
    return array("title" => $title, "content" => $content);
  }
}

class PayPage {
  private $order;
  public function __construct($orderId) {
    $this->order = Order::load($orderId);
  }

  public function run() {
    $config = new Config();
    $api = new VirtualCards\API($config->vcUrl, $config->vcServiceId, $config->vcSecret);
    $payLink = $api->payLink($this->order["id"], 10.0);

    $title = 'Выбор метода оплаты';
    $content = "
      <h1>$title</h1>
      <h2>Заказ #{$this->order['id']}</h2>
      <p>Стоимость {$this->order['amount']} р.</p>
      <a href='$payLink' type='button' class='btn btn-primary btn-lg'>Купить виртуальную карту для оплаты</a>
      <button type='button' class='btn btn-primary btn-lg'>Оплатить VISA/MasterCard</button>
    ";
    return array("title" => $title, "content" => $content);
  }
}
