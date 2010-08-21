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

require_once ORDERBOOK . 'utils.php' ;

require_once ORDERBOOK . 'models/OrderBook.php' ;

require_once ORDERBOOK . 'views/OrderBookView.php' ;

class OrderBookController extends BaseController {

  private $producers = array() ;
  private $producers_sum = array() ;

  private $other = array() ;
  private $other_sum = 0 ;

  public function main() {
    $orderbook = new OrderBook($this->db) ;
    $view = new OrderBookView() ;
    $view->set('auth', $this->auth) ;
    
    foreach(array('clear_action', 'products_action', 'orders_action') as $action) {
      if (!isset($_POST[$action])) {
        $_POST[$action] = null ;
      }
    }

    $action = isset($_POST['action']) ? $_POST['action'] : 'view' ;
    
    switch ($action) {
      case 'delete':
        $this->delete($orderbook) ;
        $view->set('MSG_ORDERS_DELETED', true) ;
        $orderbook->refresh() ;
        break ;

      case $_POST['clear_action']:
        if (isset($_POST['confirm'])) {
          if (!count($orderbook->orders)) {
            $view->set('MSG_NO_ORDERS_TO_CLEAR', true) ;
          } else {
            require_once ORDERBOOK . 'models/OrderStatistic.php' ;
            require_once ORDERBOOK . 'models/ProductStatistic.php' ;
            require_once ORDERBOOK . 'models/ProducerStatistic.php' ;
            $this->summarize($orderbook) ;
            $orders = array_keys($orderbook->orders) ;
            $order = new OrderStatistic($this->db,
                                        array('date' => $orderbook->orders[$orders[0]]->getDate(),
                                              'quantity' => count($orderbook->orders),
                                              'sum' => $orderbook->sum(),
                                              'charges' => $orderbook->charges())) ;
            $order_id = $order->commit() ; unset($order) ;

            foreach(array_keys($this->producers) as $producer) {
              foreach(array_keys($this->producers[$producer]) as $description) {
                foreach($this->producers[$producer][$description] as $product) {
                  $product = new ProductStatistic($this->db,
                                                  array('order_id' => $order_id,
                                                        'producer' => $producer,
                                                        'description' => $description,
                                                        'quantity' => $product['quantity'],
                                                        'unit' => $product['unit'],
                                                        'sum' => $product['sum'])) ;
                  $product->commit() ;
                  unset($product) ;
                }
              }
              $producer = new ProducerStatistic($this->db,
                                                array('order_id' => $order_id,
                                                      'producer' => $producer,
                                                      'sum' => $this->producers_sum[$producer])) ;
              $producer->commit() ;
              unset($producer) ;
            }
            if ($this->other_sum) {
              foreach(array_keys($this->other) as $description) {
                foreach($this->other[$description] as $product) {
                  $product = new ProductStatistic($this->db,
                                                  array('order_id' => $order_id,
                                                        'producer' => '',
                                                        'description' => $description,
                                                        'quantity' => $product['quantity'],
                                                        'unit' => $product['unit'],
                                                        'sum' => $product['sum'])) ;
                  $product->commit() ;
                  unset($product) ;
                }
              }
              $producer = new ProducerStatistic($this->db,
                                                array('order_id' => $order_id,
                                                      'producer' => '',
                                                      'sum' => $this->other_sum)) ;
              $producer->commit() ;
            }
            foreach($orderbook->orders as $order) {
              $order->destroy() ;
            }
            $orderbook->refresh() ;
            $view->set('MSG_ORDERS_CLEARED', true) ;
          }
        } else {
          $view->set('MSG_ORDERS_NOT_CLEARED', true) ;
        }
        break;        

      case $_POST['products_action']:
        $this->summarize($orderbook) ;

        require_once ORDERBOOK . 'views/ProducersView.php' ;
        $view = new ProducersView() ;
        $view->set('auth', $this->auth) ;
        $view->set('producers', $this->producers) ;
        $view->set('producers_sum', $this->producers_sum) ;
        $view->set('other', $this->other) ;
        $view->set('other_sum', $this->other_sum) ;

        die($view->render()) ;
        break;

      case $_POST['orders_action']:
        $orderbook->refresh($state=1, $order_by='name ASC') ;

        require_once ORDERBOOK . 'views/OrdersView.php' ;
        $view = new OrdersView() ;
        $view->set('auth', $this->auth) ;
        $view->set('orders', $orderbook->orders) ;

        die($view->render()) ;
        break;        
    }

    $view->set('orders', $orderbook->orders) ;
    $view->set('sum', $orderbook->sum()) ;
    $view->set('charges', $orderbook->charges()) ;
    $view->set('participants', array_reduce($orderbook->participants(), "OrderBook::reduceToEmail")) ;
    $view->set('all', array_reduce($orderbook->orders, "OrderBook::reduceToEmail")) ;
    
    die($view->render()) ;
  }

  private function delete($orderbook) {
    $delete = array() ;
    
    foreach ($_POST as $field => $value) {
      $parts = split('_', $field, 2);
      if (count($parts) == 2) {
        $id = $parts[0] ;
        $field = $parts[1] ;

        $id = strval($id) ;
        if (array_key_exists($id, $orderbook->orders)) {
          if ($field == 'delete') {
            $delete[] = $id ;
          }
        }
      }
    }

    foreach ($delete as $id) {
      $orderbook->orders[$id]->destroy() ;
    }
  }
  
  private function summarize($orderbook) {
    foreach($orderbook->orders as $order) {
      foreach($order->getProducts() as $product) {
        $unit_price = $product->getPrice() . ' / ' . $product->getUnit() ;
        if ($product->getProducer()) {
          if (!array_key_exists($product->getProducer(), $this->producers)) {
            $this->producers[$product->getProducer()] = array() ;
            $this->producers_sum[$product->getProducer()] = 0 ;
          }
          if (!array_key_exists($product->getDescription(), $this->producers[$product->getProducer()])) {
            $this->producers[$product->getProducer()][$product->getDescription()] = array() ;
          }
          if (!array_key_exists($unit_price, $this->producers[$product->getProducer()][$product->getDescription()])) {
            $this->producers[$product->getProducer()][$product->getDescription()][$unit_price] =
              array('unit' => $product->getUnit(),
                    'price' => $product->getPrice(),
                    'quantity' => 0,
                    'sum' => 0) ;
          }
          $this->producers[$product->getProducer()][$product->getDescription()][$unit_price]['quantity'] += $product->getQuantity() ;

          $sum = $product->getQuantity() * floatval($product->getPrice()) ;

          $this->producers[$product->getProducer()][$product->getDescription()][$unit_price]['sum'] =
            str_currency_fmt(floatval($this->producers[$product->getProducer()][$product->getDescription()][$unit_price]['sum']) + $sum) ;

          $this->producers_sum[$product->getProducer()] = str_currency_fmt(floatval($this->producers_sum[$product->getProducer()]) + $sum) ;
        } else {
          if (!array_key_exists($product->getDescription(), $this->other)) {
            $this->other[$product->getDescription()] = array() ;
          }
          if (!array_key_exists($unit_price, $this->other[$product->getDescription()])) {
            $this->other[$product->getDescription()][$unit_price] =
              array('unit' => $product->getUnit(),
                    'price' => $product->getPrice(),
                    'quantity' => 0,
                    'sum' => 0) ;
          }
          $this->other[$product->getDescription()][$unit_price]['quantity'] += $product->getQuantity() ;
          $sum = $product->getQuantity() * floatval($product->getPrice()) ;
          $this->other[$product->getDescription()][$unit_price]['sum'] =
            str_currency_fmt(floatval($this->other[$product->getDescription()][$unit_price]['sum']) + $sum) ;
          $this->other_sum = str_currency_fmt(floatval($this->other_sum) + $sum) ;
        }
      }
    }
  }
}