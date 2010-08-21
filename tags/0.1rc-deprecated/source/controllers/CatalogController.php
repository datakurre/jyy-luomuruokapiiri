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

require_once ORDERBOOK . 'views/CatalogView.php' ;

class CatalogController extends BaseController {
  public function main() {
    $catalog = new Catalog($this->db) ;

    $view = new CatalogView() ;
    $view->set('auth', $this->auth) ;

    $action = isset($_POST['action']) ? $_POST['action'] : null ;

    switch ($action) {
      case 'save':
        $this->save($catalog) ;
        $view->set('MSG_CHANGES_SAVED', true) ;
        $catalog->refresh() ;
        break ;
    }

    $view->set('products', $catalog->products) ;
    die($view->render()) ;
  }
  
  private function save($catalog) {
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
          if (array_key_exists($id, $catalog->products)) {
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
      $catalog->products[$id]->fill($values) ;
      if (!array_key_exists('orderable', $values)) {
        $catalog->products[$id]->setOrderable('') ;
      }
      $catalog->products[$id]->commit() ;
    }

    foreach ($delete as $id) {
      $catalog->products[$id]->destroy() ;
    }

    if (count($create) > 1) {
      $product = new Product($this->db, $create) ;
      $product->setPosition(count($catalog->products) + 1) ;
      $product->commit() ;
    }
  }
}