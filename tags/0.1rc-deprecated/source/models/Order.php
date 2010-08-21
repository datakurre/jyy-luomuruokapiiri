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

require_once 'Validate.php' ;

require_once ORDERBOOK . "utils.php" ;

require_once 'Record.php' ;
require_once 'BreakDown.php' ;

class Order extends Record {
  private static $DB_TABLE = 'orders' ;

  private static $DB_FIELDS = array('date', 'charge', 'name', 'phone',
                                    'email', 'pickup', 'participate',
                                    'notes', 'state') ;

  private static function DB_SELECT_ALL($db, $table, $state=1, $order_by='date DESC') {
    $statement = $db->prepare("SELECT * FROM $table WHERE state = ? ORDER BY $order_by") ;
    $results =& $db->execute($statement, array($state)) ;
    $db->free($statement) ;
    return $results ;
  }

  public static function all($db, $state=1, $order_by='date DESC') {
    $results = self::DB_SELECT_ALL($db, self::$DB_TABLE, $state, $order_by) ;

    $orders = array() ;
    while($row = $results->fetchRow()) {
      $orders[$row['id']] = new Order($db, $row) ;
    }
    $results->free() ;

    return $orders ;
  }

  public static function single($db, $id) {
    $order = new Order($db) ;
    $order->id = $id ;
    $order->refresh() ;
    return $order ;
  }

  static public function couldParticipate($order) {
    return $order->getParticipate() ;
  }

  public static function validate($order, &$errors) {
    $is_valid = true ;
    if (!$order->getName()) {
      if (!$order->forename) $errors['forename'] = true ;
      if (!$order->surname) $errors['surname'] = true ;
      $is_valid = false ;
    }
    if (!$order->getPhone()) {
      $errors['phone'] = true ;
      $is_valid = false ;
    }
    if (($email = $order->getEmail())) {
      if (!Validate::email($email)) {
        $errors['email'] = true ;
        $is_valid = false ;
      }
    }  
    return $is_valid ;
  }

  public $forename = '' ;
  public $surname = '' ;

  public $products = array() ;

  public function Order($db, $values=array(), $charge="0.00", $products_to_array=false) {
    parent::Record($db, $values) ;
    
    if (in_array('forename', array_keys($values))) {
      $this->forename = trim($values['forename']) ;
    }
    if (in_array('surname', array_keys($values))) {
      $this->surname = trim($values['surname']) ;
    }
    if ($this->surname && $this->forename) {
      $this->setName($this->surname . ' ' . $this->forename) ;
    }
    
    if (!$this->getDate()) {
      $this->setDate(time()) ;
    }
    if (!$this->getCharge()) {
      $this->setCharge($charge) ;
    }
    if (!$this->getState()) {
      $this->setState(1) ;
    }
  }

  public function add($product, $quantity=1) {
    $this->products[$product->id] = new BreakDown($this->DB, $product) ;
    $this->products[$product->id]->setQuantity($quantity) ;
  }

  public function sum() {
    $sum = floatval($this->getCharge()) ;
    if ($this->getId() and !count($this->products)) {
      foreach (BreakDown::all($this->DB, $this->id) as $product) {
        $sum += floatval($product->getQuantity()) * floatval($product->getPrice()) ;
      }
    } else {
      foreach ($this->products as $product) {
        $sum += floatval($product->getQuantity()) * floatval($product->getPrice()) ;
      }
    }
    return str_currency_fmt($sum) ;
  }
  
  public function commit() {
    parent::commit() ;
    foreach ($this->products as $product) {
      if(!$product->getOrderId()) {
        $product->setOrderId($this->id) ;
      }
      $product->commit() ;
    }
  }

  public function getTable() {
    return self::$DB_TABLE ;
  }

  public function getFields() {
    return self::$DB_FIELDS ;
  }  

  public function getProducts() {
    if ($this->getId() and !count($this->products)) {
      return BreakDown::all($this->DB, $this->id) ;
    } else {
      return $this->products ;
    }
  }

  public function destroy() {
    if ($this->getId()) {
      foreach (BreakDown::all($this->DB, $this->id) as $product) {
        $product->destroy() ;
      }
    }
    return parent::destroy() ;
  }
  
  protected function tidyCharge($charge) {
    return str_currency_fmt($charge) ;
  }

  protected function tidyParticipate($participate) {
    return $participate ? 1 : 0 ;
  }

  protected function tidyNotes($notes) {
    return str_trim($notes) ;
  }

  protected function tidyState($state) {
    return intval($state) ;
  }
}
?>