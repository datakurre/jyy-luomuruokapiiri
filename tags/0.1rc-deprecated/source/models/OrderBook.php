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

require_once 'Order.php' ;

class OrderBook {
  public static function reduceToEmail($initial, $current) {
    if (strlen($initial)) {
      if (strlen($current->getEmail())) {
        $current = $current->getEmail() ;
        return stristr($initial, $current) ? $initial : $initial . ', ' . $current ;
      } else {
        return $initial ;
      }
    } else {
      return $current->getEmail() ;
    }
  }

  protected $db = null ;
  
  public $orders = array() ;

  public function OrderBook($db, $state=1) {
    $this->db =& $db ;
    $this->refresh() ;
  }
  
  public function refresh($state=1, $order_by='date DESC') {
    unset($this->orders) ;
    $this->orders = Order::all($this->db, $state, $order_by) ;
  }

  public function sum() {
    $sum = 0 ;
    foreach($this->orders as $order) {
      $sum += floatval($order->sum()) ;
    }
    return str_currency_fmt($sum) ;
  }

  public function charges() {
    $charges = 0 ;
    foreach($this->orders as $order) {
      $charges += floatval($order->getCharge()) ;
    }
    return str_currency_fmt($charges) ;
  }

  public function participants() {
    return array_filter($this->orders, "Order::couldParticipate") ;
  }
}
?>