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
  
require_once 'BaseController.php' ;

require_once ORDERBOOK . 'models/Product.php' ;
require_once ORDERBOOK . 'models/Catalog.php' ;

require_once ORDERBOOK . 'views/IngredientsView.php' ;

class IngredientsController extends BaseController {
  public function main() {
    $view = new IngredientsView() ;
    $view->set('auth', $this->auth) ;

    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'product' ;

    $view->set('mode', $mode) ;
    switch ($mode) {
      case 'product':
        $catalog = new Catalog($this->db, $order_by='description') ;
        $products = array_filter($catalog->products, "Product::isOrderable") ;
        $view->set('products', $products) ;
        break ;

      case 'producer':
        $catalog = new Catalog($this->db, $order_by='producer,description') ;
        $products = array_filter($catalog->products, "Product::isOrderable") ;
        $producers = array() ;
        $other = array() ;
        foreach ($products as $product) {
          $producer = $product->getProducer() ;
          if ($producer and $product->getDescription()) {
            if (!array_key_exists($producer, $producers)) {
              $producers[$producer] = array(
                'name' => $producer,
                'products' => array()
              ) ;
            }
            $producers[$producer]['products'][] = $product ;
          } else if ($product->getDescription()) {
            $other[] = $product ;
          }
        }
        $view->set('producers', $producers) ;
        $view->set('other', $other) ;
        break ;
    }

    die($view->render()) ;
  }
}
?>