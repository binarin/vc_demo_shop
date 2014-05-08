<?php

// -*- coding: utf-8-unix -*-
class Page {
  public static function dispatch() {
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "";
    if (preg_match('/^\/pay\/(?P<orderId>\d+)/', $_SERVER['PATH_INFO'], $matches)) {
      $page = new PayPage($matches["orderId"]);
    } elseif ($path_info === '/create') {
      $page = new CreatePage();
    } elseif ($path_info === '/result') {
      $page = new StatusPage();
    } elseif ($path_info === '/callback') {
      $page = new CallbackPage();
    } else {
      $page = new IndexPage();
    }
    return $page->run();
  }
}

class StatusPage {
  public function run() {
    $config = new Config();
    $api = new VirtualCards\API($config->vcUrl, $config->vcServiceId, $config->vcSecret);

    if (
      $api->validateSignature(
        $_GET["signature"],
        $_SERVER["REQUEST_METHOD"],
        $config->publicUrl,
        (isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/"),
        $_SERVER["QUERY_STRING"]
      )
    ) {
      $order = Order::load($_GET["order_id"]);
      return ["title" => "Заказ {$order->id}",
              "content" => "<h1>Спасибо за покупку нашего слона!</h1>"];
    } else {
      return ["title" => "Неправильная подпись",
              "content" => "<h1>Нет прав на просмотр данной записи</h1>"];
    }
  }
}

class CallbackPage {
  public function run() {
    header("Content-type: text/plain");

    $config = new Config();
    $api = new VirtualCards\API($config->vcUrl, $config->vcServiceId, $config->vcSecret);

    if (
      $api->validateSignature(
        $_GET["signature"],
        $_SERVER["REQUEST_METHOD"],
        $config->publicUrl,
        (isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/"),
        $_SERVER["QUERY_STRING"]
      )
    ) {
      $order = Order::load($_GET["order_id"]);
      if ($api->isStatusFinal($_GET["status"])) {
        $order->changeStatus($_GET["status"]);
      }
      $order->addNotification($_GET);
      echo "OK";
    } else {
      http_response_code(403);
    }
  }
}

class CreatePage {
  public function run() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      header("Location: /");
      return;
    }
    $order = Order::create(10.0);
    header("Location: /pay/{$order->id}");
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
    header("refresh: 5; url=/pay/{$this->order->id}");
    $config = new Config();
    $api = new VirtualCards\API($config->vcUrl, $config->vcServiceId, $config->vcSecret);
    $payLink = $api->payLink($this->order->id, 10.0);

    $title = 'Выбор метода оплаты';
    $content = "
      <h1>$title</h1>
      <h2>Заказ #{$this->order->id}</h2>
      <p>Стоимость {$this->order->amount} р.</p>
      <a target='_blank' href='$payLink' type='button' class='btn btn-primary btn-lg'>Купить виртуальную карту для оплаты</a>
      <button type='button' class='btn btn-primary btn-lg'>Оплатить с помощью VISA/MasterCard</button>
    " . $this->renderOrder($this->order);
    return array("title" => $title, "content" => $content);
  }

  private function renderOrder($order) {
    ob_start(); ?>

  <h1>Отладочная информация</h1>
  <p>Статус: <?php echo $order->status ?></p>
  <h2>Записи в логах</h2>
  <ul>
    <?php foreach ($order->notifications["log"] as $logRecord) : ?>
      <li><?php echo $logRecord ?></li>
    <?php endforeach; ?>
  </ul>
  <h2>Полученные нотификации</h2>
  <table class="table">
    <tr>
      <th>Дата</td>
      <th>Идентификатор</td>
      <th>Статус</td>
    </tr>
    <?php foreach ($order->notifications["notifications"] as $id => $data) : ?>
      <tr>
        <td><?php echo $data["timestamp"] ?></td>
        <td><?php echo $id ?></td>
        <td><?php echo $data["status"] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
    <?php
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
  }

}
