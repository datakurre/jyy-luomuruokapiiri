<?php
/* Ruokapiirin tilausjärjestelmä.

  Copyright (C) 2006-2010 Asko Soukka <asko.soukka@iki.fi>

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

require_once 'Record.php' ;
require_once 'BreakDown.php' ;

class ProductLimit extends Record {
  private static $DB_TABLE = 'limits' ;

  private static $DB_FIELDS = array('product_id', 'available', 'ordered') ;

  private static function DB_SELECT_ALL($db, $table) {
    $statement = $db->prepare("SELECT * FROM $table") ;
    $results =& $db->execute($statement, array()) ;
    $db->free($statement) ;
    return $results ;
  }

  public static function all($db) {
    $results = self::DB_SELECT_ALL($db, self::$DB_TABLE) ;

    $limits = array() ;
    while ($row = $results->fetchRow()) {
      $limits[$row['id']] = new ProductLimit($db, $row) ;
    }

    $results->free() ;
    return $limits ;
  }

  public static function hasLimit($product) {
    return property_exists($product, 'limit') and is_a($product->limit, 'ProductLimit');
  }

  public static function canHaveLimit($product) {
    return !property_exists($product, 'limit');
  }

  public static function refreshOrdered($db, $limit, $product) {
    $limit->setOrdered(BreakDown::quantityFor($db, $product)) ;
  }

  public static function isAvailable($product) {
    if (property_exists($product, 'limit') and is_a($product->limit, 'ProductLimit')) {
      return $product->limit->available > $product->limit->ordered ? 1 : 0 ;
    } else {
      return 1 ;
    }
  }

  public function getTable() {
    return self::$DB_TABLE ;
  }

  public function getFields() {
    return self::$DB_FIELDS ;
  }

  public function addToOrdered($quantity) {
    $this->setOrdered($this->getOrdered() + $quantity) ;
  }

  public function subtractFromOrdered($quantity) {
    $this->setOrdered($this->getOrdered() - $quantity) ;
  }

  protected function tidyAvailable($available) {
    return trim($available) ? intval($available) : 0 ;
  }

  protected function tidyOrdered($ordered) {
    return trim($ordered) ? intval($ordered) : 0 ;
  }
}
?>