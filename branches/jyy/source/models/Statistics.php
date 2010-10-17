<?php
/* Ruokapiirin tilausjärjestelmä.

  Copyright (C) 2006-2009 Asko Soukka <asko.soukka@iki.fi>

  This file is part of Ruokapiiri.

  Ruokapiiri is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Ruokapiiri is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Ruokapiiri.  If not, see <http://www.gnu.org/licenses/>. */

require_once ORDERBOOK . 'utils.php' ;

require_once 'OrderStatistic.php' ;

class Statistics {
  protected $db = null ;
  
  public $orders = array() ;
  public $producers = array() ;
  public $products = array() ;

  public function Statistics($db) {
    $this->db =& $db ;
    $this->refresh() ;
  }
  
  public function refresh($order_by='date DESC') {
    unset($this->orders) ; unset($this->producers); $this->producers = array() ;

    $this->orders = OrderStatistic::all($this->db, $order_by) ;
    foreach($this->orders as $order) {
      foreach($order->getProducers() as $producer) {
        if (!array_key_exists($producer->getProducer(), $this->producers)) {
          $this->producers[$producer->getProducer()] = array() ;
        }
        $this->producers[$producer->getProducer()][$order->getDate()] = $producer->getSum() ;
      }
    }
  }

  public function refreshProducts() {
    if (is_array($this->orders) and is_array($this->producers)) {
      unset($this->products) ; $this->products = array() ;
      foreach($this->orders as $order) {
        foreach($order->getProducts() as $product) {
          if (!array_key_exists($product->getProducer(), $this->products)) {
            $this->products[$product->getProducer()] = array() ;
          }
          if (!array_key_exists($product->getDescription(), $this->products[$product->getProducer()])) {
            $this->products[$product->getProducer()][$product->getDescription()] = array() ;
          }
          if (!array_key_exists($order->getDate(), $this->products[$product->getProducer()][$product->getDescription()])) {
            $this->products[$product->getProducer()][$product->getDescription()][$order->getDate()] = 0 ;
          }
          $this->products[$product->getProducer()][$product->getDescription()][$order->getDate()]
            = str_currency_fmt(floatval($this->products[$product->getProducer()][$product->getDescription()][$order->getDate()])
              + floatval($product->getSum())) ;
        }
      }
    }
  }
}
?>