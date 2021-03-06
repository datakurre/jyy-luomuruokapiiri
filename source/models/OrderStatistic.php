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

require_once ORDERBOOK . "utils.php" ;

require_once 'Record.php' ;

class OrderStatistic extends Record {
  private static $DB_TABLE = 'stats_orders' ;

  private static $DB_FIELDS = array('date', 'quantity', 'sum', 'charges') ;

  private static function DB_SELECT_ALL($db, $table, $order_by='date DESC') {
    $statement = $db->prepare("SELECT * FROM $table ORDER BY $order_by") ;
    $results =& $db->execute($statement, array()) ;
    $db->free($statement) ;
    return $results ;
  }

  public static function all($db, $order_by='date DESC') {
    $results = self::DB_SELECT_ALL($db, self::$DB_TABLE, $order_by) ;

    $orders = array() ;
    while($row = $results->fetchRow()) {
      $orders[$row['id']] = new OrderStatistic($db, $row) ;
    }
    $results->free() ;

    return $orders ;
  }

  public function getTable() {
    return self::$DB_TABLE ;
  }

  public function getFields() {
    return self::$DB_FIELDS ;
  }  

  public function getProducts() {
    require_once 'ProductStatistic.php' ;
    if ($this->getId()) {
      return ProductStatistic::all($this->DB, $this->id) ;
    } else {
      return False ;
    }
  }

  public function getProducers() {
    require_once 'ProducerStatistic.php' ;
    if ($this->getId()) {
      return ProducerStatistic::all($this->DB, $this->id) ;
    } else {
      return False ;
    }
  }

  protected function tidyQuantity($quantity) {
    return intval($quantity) ;
  }

  protected function tidySum($charge) {
    return str_currency_fmt($charge) ;
  }

  protected function tidyCharge($charge) {
    return str_currency_fmt($charge) ;
  }
}
?>