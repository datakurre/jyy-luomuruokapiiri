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

require_once ORDERBOOK . 'models/Statistics.php' ;

require_once ORDERBOOK . 'views/StatisticsView.php' ;

class StatisticsController extends BaseController {

  public function main() {
    $view = new StatisticsView() ;
    $statistics = new Statistics($this->db) ;

    $action = isset($_POST['action']) ? $_POST['action'] : 'view' ;
    switch($action) {
      case 'table':
        $statistics->refreshProducts() ;
        die($view->table($statistics->orders, $statistics->products)) ;
        break;
    }
    
    $view->set('auth', $this->auth) ;
    $view->set('orders', $statistics->orders) ;
    $view->set('producers', $statistics->producers) ;
    die($view->render()) ;
  }
}
?>