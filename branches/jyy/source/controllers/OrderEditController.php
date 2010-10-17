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

require_once ORDERBOOK . 'models/Order.php' ;
require_once ORDERBOOK . 'models/Catalog.php' ;
require_once ORDERBOOK . 'models/ProductLimit.php' ;

require_once ORDERBOOK . 'views/OrderEditView.php' ;

class OrderEditController extends BaseController {
  public function main() {
    $view = new OrderEditView() ;
    $view->set('auth', $this->auth) ;
    $view->set('pickup', unserialize(ORDERBOOK_PICKUP)) ;

    $id = isset($_GET['id']) ? $_GET['id'] : null ;
    if ($id) {
      $catalog = new Catalog($this->db) ;

      $order = Order::single($this->db, $id) ;

      $action = isset($_POST['action']) ? $_POST['action'] : 'view' ;

      switch ($action) {
        case 'save':
          $view->set('MSG_CHANGES_SAVED', true) ;
          $order->fill($_POST) ;
          foreach($order->getProducts() as $product) {
            if (isset($_POST[strval($product->getId())])) {
              $quantity = intval($_POST[strval($product->getId())]) ;
              /***
               * Update ProductLimit when quantities are modified */
              $hash = $product->getHash() ;
              if (array_key_exists($hash, $catalog->hashes)
                and ProductLimit::hasLimit($catalog->hashes[$hash])) {
                $delta = $quantity - $product->getQuantity();
                $catalog->hashes[$hash]->limit->addToOrdered($delta);
                $catalog->hashes[$hash]->limit->commit();        
              } 
              /***/
              $product->setQuantity($quantity) ;
            }
            $product->commit() ;
          }
          if (!array_key_exists('participate', $_POST)) {
            $order->setParticipate('') ;
          }
          if (isset($_POST['#_product_id']) and isset($_POST['#_quantity'])
            and array_key_exists(intval($_POST['#_product_id']), $catalog->products)) {
            $quantity = intval($_POST['#_quantity']) ;
            if($quantity) {
              $product_id = intval($_POST['#_product_id']) ; 
              $order->add($catalog->products[$product_id], $quantity) ;
              if (ProductLimit::hasLimit($catalog->products[$product_id])) {
                $catalog->products[$product_id]->limit->addToOrdered($quantity);
                $catalog->products[$product_id]->limit->commit();        
              } 
            }
          }
          $order->commit() ;
          /* Finally, $order->products must be reseted to enable it searching products from the DB */
          $order->products = array() ;
          break ;
      }

      $hashes = array() ;
      foreach($order->getProducts() as $product) {
        $hashes[] = $product->getHash() ;
      }
      $new_products = array() ;
      foreach($catalog->products as $key => $product) {
        if (!in_array($product->getHash(), $hashes)) {
          $new_products[$key] =& $catalog->products[$key] ;
        }
      }
      unset($hashes) ;

      $view->set('order', $order) ;
      $view->set('products', $new_products) ;
      die($view->render()) ;
    }
  }
}
?>