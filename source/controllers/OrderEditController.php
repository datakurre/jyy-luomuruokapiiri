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

require_once ORDERBOOK . 'views/OrderEditView.php' ;

class OrderEditController extends BaseController {
  public function main() {
    $view = new OrderEditView() ;
    $view->set('auth', $this->auth) ;

    $id = isset($_GET['id']) ? $_GET['id'] : null ;
    if ($id) {
      $order = Order::single($this->db, $id) ;

      $action = isset($_POST['action']) ? $_POST['action'] : 'view' ;

      switch ($action) {
        case 'save':
          $view->set('MSG_CHANGES_SAVED', true) ;
          $order->fill($_POST) ;
          foreach($order->getProducts() as $product) {
            if (isset($_POST[strval($product->getId())])) {
              $quantity = intval($_POST[strval($product->getId())]) ;
              $product->setQuantity($quantity) ;
            }
            $product->commit() ;
          }
          if (!array_key_exists('participate', $_POST)) {
            $order->setParticipate('') ;
          }
          $order->commit() ;
          break ;
      }

      $view->set('order', $order) ;
      die($view->render()) ;
    }
  }
}
?>