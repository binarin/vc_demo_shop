<?php // -*- coding: utf-8-unix -*-
class Order {
    public static function create($amount) {
        $dbh = Config::dbh();
        $sth = $dbh->prepare("insert into orders (amount) values(?) returning *");
        $sth->execute(array($amount));
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
    public static function load($orderId) {
        $dbh = Config::dbh();
        $sth = $dbh->prepare("select * from orders where id = ?");
        $sth->execute(array($orderId));
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
}
