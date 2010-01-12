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
  
require_once 'DatabaseController.php' ;
require_once 'AuthController.php' ;

class MasterController {
  private $db = null ;
  private $auth = null ;

  public static function prepareUsers($passwords) {
    $users = array() ;
    foreach ($passwords as $password) {
      $users[$password] = 'only-username-required' ;
    }
    return $users ;
  }

  public function MasterController($database, $passwords = Array()) {
    $this->db = new DatabaseController($database) ;
    $this->auth = new AuthController(MasterController::prepareUsers($passwords)) ;
  }

  public function main() {
    $view = isset($_GET['view']) ? $_GET['view'] : 'orderform' ;
    $action = isset($_POST['action']) ? $_POST['action'] : null ;

    switch ($action) {
      case 'logout':
        $this->auth->logout() ;
        break ;
    }

    switch ($view) {
      case 'catalog':
        if ($this->auth->login()) {
          require_once 'CatalogController.php' ;
          $catalog = new CatalogController($this->db, $this->auth) ;
          $catalog->main() ;
        }
        break ;
      case 'ingredients':
        require_once 'IngredientsController.php' ;
        $ingredients = new IngredientsController($this->db, $this->auth) ;
        $ingredients->main() ;
        break ;
      case 'orderform':
        require_once 'OrderFormController.php' ;
        $orderform = new OrderFormController($this->db, $this->auth) ;
        $orderform->main() ;
        break ;
      case 'orderbook':
        if ($this->auth->login()) {
          require_once 'OrderBookController.php' ;
          $orderbook = new OrderBookController($this->db, $this->auth) ;
          $orderbook->main() ;
        }
        break ;
      case 'statistics':
        if ($this->auth->login()) {
          require_once 'StatisticsController.php' ;
          $statistics = new StatisticsController($this->db, $this->auth) ;
          $statistics->main() ;
        }
        break ;
      case 'order':
        if ($this->auth->login()) {
          require_once 'OrderEditController.php' ;
          $orderedit = new OrderEditController($this->db, $this->auth) ;
          $orderedit->main() ;
        }
        break ;
    }
  }
}