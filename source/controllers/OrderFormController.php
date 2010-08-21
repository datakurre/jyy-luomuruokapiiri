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

require_once 'BaseController.php' ;

require_once ORDERBOOK . 'models/Settings.php' ;
require_once ORDERBOOK . 'models/Catalog.php' ;
require_once ORDERBOOK . 'models/Order.php' ;
require_once ORDERBOOK . 'models/BreakDown.php' ;
require_once ORDERBOOK . 'models/ProductLimit.php' ;

require_once ORDERBOOK . 'views/OrderFormView.php' ;
require_once ORDERBOOK . 'views/ConfirmationView.php' ;

class OrderFormController extends BaseController{
  public function main() {
    $catalog = new Catalog($this->db) ;
    $settings = new Settings($this->db) ;

    $view = new OrderFormView($this->auth) ;
    $view->set('auth', $this->auth) ;

    $order = new Order($this->db, $_POST, ORDERBOOK_CHARGE) ;
    
    $products = array_filter($catalog->products, "Product::isOrderable") ;
    foreach($products as $product) {
      if (isset($_POST[strval($product->getId())])) {
        $quantity = intval($_POST[strval($product->getId())]) ;
        if($quantity) $order->add($product, $quantity) ;
      }
    }

    $errors = array() ;
    foreach(array('sum_action', 'order_action') as $action) {
      if (!isset($_POST[$action])) {
        $_POST[$action] = null ;
      }
    }

    $action = isset($_POST['action']) ? $_POST['action'] : 'view' ;
    switch($action) {
      case 'save':
        if ($this->auth->login()) {
          $this->save($settings) ;
          $view->set('MSG_CHANGES_SAVED', true) ;
        }
        break;
      case $_POST['sum_action']:
        $view->set('MSG_SUM_UPDATED', true) ;
        break;
      case $_POST['order_action']:
        if (!Order::validate($order, $errors)) {
          $view->set('MSG_CORRECT_ERRORS', true) ;
        } else {
          /* Update ProductLimits */
          $changes = array() ;
          foreach($order->products as $product_id => $breakdown) {
            if (ProductLimit::hasLimit($catalog->products[$product_id])) {
              $available = $catalog->products[$product_id]->limit->getAvailable()
                           - $catalog->products[$product_id]->limit->getOrdered() ;
              $quantity = max(0, min($breakdown->getQuantity(), $available)) ;
              $catalog->products[$product_id]->limit->addToOrdered($quantity) ;
              $catalog->products[$product_id]->limit->commit() ;
              if ($quantity != $breakdown->getQuantity()) {
                $changes[] = array(
                    'description' => $breakdown->getDescription(),
                    'from' => $breakdown->getQuantity(),
                    'to' => $quantity,
                    'unit' => $breakdown->getUnit()
                ) ;
                $order->products[$product_id]->setQuantity($quantity) ;
              }
            }
          }
          $order->commit() ;
          
          $confirmation = new ConfirmationView() ;
          if (count($changes)) { $confirmation->set('MSG_ORDER_CHANGED', $changes) ; }
          $confirmation->set('message', $settings->get('confirmation.message', '')) ;
          die($confirmation->render($order)) ;
        }
        break;
    }

    $view->set('instructions', $settings->get('form.instructions', '')) ;
    $view->set('enabled', $settings->get('form.enabled', '')) ;
    $view->set('message', $settings->get('confirmation.message', '')) ;
    $view->set('products', $products) ;
    $view->set('order', $order) ;
    $view->set('errors', $errors) ;
    die($view->render()) ;
  }

  protected function save($settings) {
    if (isset($_POST['instructions'])) {
      $settings->set('form.instructions', str_trim($_POST['instructions'], $strip_tags=true)) ;
    }
    if (isset($_POST['message'])) {
      $settings->set('confirmation.message', str_trim($_POST['message'])) ;
    }
    if (isset($_POST['enabled'])) {
      $settings->set('form.enabled', "true") ;
    } else {
      $settings->set('form.enabled', null) ;
    }
  }
}
?>