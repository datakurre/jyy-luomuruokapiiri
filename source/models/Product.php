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

class Product extends Record {
  private static $DB_TABLE = 'catalog' ;

  private static $DB_FIELDS = array('description', 'notes', 'ingredients', 'price',
                                    'unit', 'producer', 'orderable', 'position') ;
                                   
  private static function DB_SELECT_ALL($db, $table, $order_by='id') {
    $statement = $db->prepare("SELECT * FROM $table ORDER BY $order_by") ;
    $results =& $db->execute($statement, array()) ;
    $db->free($statement) ;
    return $results ;
  }

  public static function all($db, $order_by='id') {
    $results = self::DB_SELECT_ALL($db, self::$DB_TABLE, $order_by) ;

    $products = array() ;
    while ($row = $results->fetchRow()) {
      $products[$row['id']] = new Product($db, $row) ;
    }

    $results->free() ;
    return $products ;
  }

  public static function isOrderable($product) {
    return $product->getOrderable() ;
  }

  public function getTable() {
    return self::$DB_TABLE ;
  }

  public function getFields() {
    return self::$DB_FIELDS ;
  }

  public function getHash() {
    return hash('md5', $this->getDescription() . $this->getProducer() . $this->getPrice() . $this->getUnit()) ;
  }

  protected function tidyIngredients($ingredients) {
    return str_trim($ingredients) ;
  }

  protected function tidyPrice($price) {
    return str_currency_fmt($price) ;
  }

  protected function tidyUnit($unit) {
    return in_array($unit, array('pcs', 'kg')) ? $unit : 'kg' ;
  }

  protected function tidyOrderable($orderable) {
    return $orderable ? 1 : 0 ;
  }

  protected function tidyPosition($position) {
    return $position ? intval($position) : 1 ;
  }
}
?>