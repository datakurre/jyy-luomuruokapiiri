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
require_once ORDERBOOK . 'models/ProductLimit.php' ;

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
    $view->set('pickup', unserialize(ORDERBOOK_PICKUP)) ;
    
    foreach(array('clear_action', 'producers_action', 'pickup_action', 'orders_action') as $action) {
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
          /* Clear All ProductLimits */
          $limits = ProductLimit::all($this->db); 
          foreach ($limits as $limit) {
            $limit->setOrdered(0);
            $limit->commit();
          } unset($limits);
          /* Proceed archiving statistics */
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

      case $_POST['pickup_action']:
        $this->summarize($orderbook, true) ;

        require_once ORDERBOOK . 'views/PickupView.php' ;
        $view = new PickupView() ;
        $view->set('auth', $this->auth) ;
        $view->set('producers', $this->producers) ;
        $view->set('producers_sum', $this->producers_sum) ;
        $view->set('other', $this->other) ;
        $view->set('other_sum', $this->other_sum) ;

        $pickup_sum = array() ;
        foreach(unserialize(ORDERBOOK_PICKUP) as $pickup) {
          $pickup_sum[$pickup] = $orderbook->sum($pickup) ;
        }

        $pickup_charges = array() ;
        foreach(unserialize(ORDERBOOK_PICKUP) as $pickup) {
          $pickup_charges[$pickup] = $orderbook->charges($pickup) ;
        }

        $view->set('pickup_sum', $pickup_sum) ;
        $view->set('pickup_charges', $pickup_charges) ;

        $view->set('sum', $orderbook->sum()) ;
        $view->set('charges', $orderbook->charges()) ;

        die($view->render()) ;
        break;

      case $_POST['producers_action']:
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
        $orderbook->refresh($state=1, $order_by='pickup ASC, name ASC') ;

        require_once ORDERBOOK . 'views/OrdersView.php' ;
        $view = new OrdersView() ;
        $view->set('auth', $this->auth) ;
        $view->set('orders', $orderbook->orders) ;

        $pickup_count = array() ;
        foreach(unserialize(ORDERBOOK_PICKUP) as $pickup) {
          $pickup_count[$pickup] = $orderbook->count($pickup) ;
        }
        $view->set('pickup_count', $pickup_count) ;

        die($view->render()) ;
        break;        
    }

    $view->set('orders', $orderbook->orders) ;
    $view->set('sum', $orderbook->sum()) ;
    $view->set('charges', $orderbook->charges()) ;
    $view->set('participants', array_reduce($orderbook->participants(), array("OrderBook", "reduceToEmail"))) ;
    $view->set('all', array_reduce($orderbook->orders, array("OrderBook", "reduceToEmail"))) ;
    
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

    /* Update product limits before destroying order */
    require_once ORDERBOOK . 'models/Catalog.php' ;
    $catalog = new Catalog($this->db) ;
    foreach ($delete as $id) {
      foreach ($orderbook->orders[$id]->getProducts() as $product) {
        $hash = $product->getHash() ;
        if (array_key_exists($hash, $catalog->hashes)
          and ProductLimit::hasLimit($catalog->hashes[$hash])) {
          $catalog->hashes[$hash]->limit->subtractFromOrdered($product->getQuantity());
          $catalog->hashes[$hash]->limit->commit();        
        }
      }
      $orderbook->orders[$id]->destroy() ;
    } unset($catalog);
  }
  
  private function summarize($orderbook, $pickup=false) {
    foreach($orderbook->orders as $order) {
      if ($pickup && $order->getPickup()) {
        if (!array_key_exists($order->getPickup(), $this->producers)) {
          $this->producers[$order->getPickup()] = array() ;
          $this->producers_sum[$order->getPickup()] = array() ;
        }
        $producers = &$this->producers[$order->getPickup()] ;
        $producers_sum = &$this->producers_sum[$order->getPickup()] ;
        $other = &$this->other[$order->getPickup()] ;
        $other_sum = &$this->other_sum[$order->getPickup()] ;
      } else if (!$pickup) {
        $producers = &$this->producers ;
        $producers_sum = &$this->producers_sum ;
        $other = &$this->other ;
        $other_sum = &$this->other_sum ;
      } else {
        continue ;
      }
      foreach($order->getProducts() as $product) {
        $unit_price = $product->getPrice() . ' / ' . $product->getUnit() ;
        if ($product->getProducer()) {
          if (!array_key_exists($product->getProducer(), $producers)) {
            $producers[$product->getProducer()] = array() ;
            $producers_sum[$product->getProducer()] = 0 ;
          }
          if (!array_key_exists($product->getDescription(), $producers[$product->getProducer()])) {
            $producers[$product->getProducer()][$product->getDescription()] = array() ;
          }
          if (!array_key_exists($unit_price,
                                $producers[$product->getProducer()][$product->getDescription()])) {
            $producers[$product->getProducer()][$product->getDescription()][$unit_price] =
              array('unit' => $product->getUnit(),
                    'price' => $product->getPrice(),
                    'quantity' => 0,
                    'sum' => 0) ;
          }
          $current = &$producers[$product->getProducer()][$product->getDescription()][$unit_price] ;

          $current['quantity'] += $product->getQuantity() ;
          $sum = $product->getQuantity() * floatval($product->getPrice()) ;
          $current['sum'] = str_currency_fmt(floatval($current['sum']) + $sum) ;
          $producers_sum[$product->getProducer()] =
            str_currency_fmt(floatval($producers_sum[$product->getProducer()]) + $sum) ;
        } else {
          if (!array_key_exists($product->getDescription(), $other)) {
            $other[$product->getDescription()] = array() ;
          }
          if (!array_key_exists($unit_price, $other[$product->getDescription()])) {
            $other[$product->getDescription()][$unit_price] =
              array('unit' => $product->getUnit(),
                    'price' => $product->getPrice(),
                    'quantity' => 0,
                    'sum' => 0) ;
          }
          $current = &$other[$product->getDescription()][$unit_price] ;

          $current['quantity'] += $product->getQuantity() ;
          $sum = $product->getQuantity() * floatval($product->getPrice()) ;
          $current['sum'] = str_currency_fmt(floatval($current['sum']) + $sum) ;
          $other_sum = str_currency_fmt(floatval($other_sum) + $sum) ;
        }
      }
    }
  }
}
?>