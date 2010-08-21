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

require_once ORDERBOOK . 'models/Catalog.php' ;
require_once ORDERBOOK . 'models/ProductLimit.php' ;

require_once ORDERBOOK . 'views/LimitsView.php' ;

class LimitsController extends BaseController {
  
  public function main() {
    $catalog = new Catalog($this->db) ;
  
    $view = new LimitsView() ;
    $view->set('auth', $this->auth) ;

    $action = isset($_POST['action']) ? $_POST['action'] : null ;

    switch ($action) {
      case 'save':
        $limits = array();
        foreach($catalog->products as $product) {
          if (ProductLimit::hasLimit($product)) {
            $limits[$product->limit->getId()] = $product->limit;
          }
        }
        $this->save($catalog, $limits) ;
        $view->set('MSG_CHANGES_SAVED', true) ;
        $catalog->refresh() ;
        break ;
      case 'update':
        $limits = array();
        foreach($catalog->products as $product) {
          if (ProductLimit::hasLimit($product)) {
            ProductLimit::refreshOrdered($this->db, $product->limit, $product) ;
            $product->limit->commit() ;
          }
        }
        $view->set('MSG_LIMITS_UPDATED', true) ;
        break ;
    }
    
    $limited_products = array() ;
    $unlimited_products = array() ;
    foreach($catalog->products as $product) {
      if (ProductLimit::hasLimit($product)) {
        $limited_products[$product->getId()] = $product ;
      } else if (ProductLimit::canHaveLimit($product)) {
        $unlimited_products[$product->getId()] = $product ;
      }
    }

    $view->set('limited', $limited_products) ;
    $view->set('unlimited', $unlimited_products) ;

    die($view->render()) ;
  }

  private function save($catalog, $limits) {
    $update = array() ;
    $create = array() ;
    $delete = array() ;
    
    foreach ($_POST as $field => $value) {
      $parts = split('_', $field, 2);
      if (count($parts) == 2) {
        $id = $parts[0] ;
        $field = $parts[1] ;
        if ($id == '#' and strlen($value) > 0) {
            $create[$field] = $value ;
        } else {
          $id = strval($id) ;
          if (array_key_exists($id, $limits)) {
            if (!array_key_exists($id, $update)) {
              $update[$id] = array() ;
            }
            if ($field == 'delete') {
              $delete[] = $id ;
            } else {
              $update[$id][$field] = $value ;
            }
          }
        }
      }
    }

    foreach ($update as $id => $values) {
      $limits[$id]->fill($values) ;
      if ($limits[$id]->getAvailable() > 0) {
        $limits[$id]->commit() ;
      } else {
        $limits[$id]->destroy() ;        
      }
    }

    foreach ($delete as $id) {
      $limits[$id]->destroy() ;
    }

    if (count($create) > 1) {
      $limit = new ProductLimit($this->db, $create) ;
      if ($limit->getAvailable() > 0 and ProductLimit::canHaveLimit($catalog->products[$limit->getProductId()])) {
        ProductLimit::refreshOrdered($this->db, $limit, $catalog->products[$limit->getProductId()]) ;
        $limit->commit() ;
      }
    }
  }
}
?>