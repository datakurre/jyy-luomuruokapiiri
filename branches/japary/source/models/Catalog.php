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

require_once 'Product.php' ;

class Catalog {
  protected $db = null ;
  protected $order_by = 'position' ;

  public $products = array() ;

  public function Catalog($db, $order_by='position') {
    $this->db =& $db ;
    $this->refresh() ;
  }
  
  public function refresh($order_by='position') {
    unset($this->products) ;
    $this->products = Product::all($this->db, $order_by) ;
    if ($order_by == 'position') {
      $counter = 1;
      foreach($this->products as $product) {
        $product->setPosition($counter++) ;
      }
    }
  }
}
?>