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

require_once 'Record.php' ;

class ProductStatistic extends Record {
  private static $DB_TABLE = 'stats_products' ;

  private static $DB_FIELDS = array('order_id', 'producer', 'description',
                                    'quantity', 'unit', 'sum') ;

  private static function DB_SELECT_ALL($db, $table, $order_id, $order_by='description') {
    $statement = $db->prepare("SELECT * FROM $table WHERE order_id = ? ORDER BY $order_by") ;
    $results =& $db->execute($statement, array($order_id)) ;
    $db->free($statement) ;
    return $results ;
  }

  public static function all($db, $order_id, $order_by='description') {
    $results = self::DB_SELECT_ALL($db, self::$DB_TABLE, $order_id, $order_by) ;

    $products = array() ;
    while ($row = $results->fetchRow()) {
      $products[$row['id']] = new ProductStatistic($db, $row) ;
    }

    $results->free() ;
    return $products ;
  }

  public function getTable() {
    return self::$DB_TABLE ;
  }

  public function getFields() {
    return self::$DB_FIELDS ;
  }

  protected function tidyOrderId($order_id) {
    return intval($order_id) ;
  }

  protected function tidyQuantity($quantity) {
    return intval($quantity) ;
  }

  protected function tidyUnit($unit) {
    return in_array($unit, array('pcs', 'kg')) ? $unit : 'kg' ;
  }

  protected function tidySum($price) {
    return str_currency_fmt($price) ;
  }
}
?>