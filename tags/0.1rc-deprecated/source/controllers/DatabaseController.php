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

// This is the only file where the API of txt-db-api is used directly.
// Elsewhere database is accessed through PEAR::DB.

require_once API_HOME_DIR . 'txt-db-api.php' ;

require_once 'txt-db-api.php' ;
require_once 'DB.php' ;

class DatabaseController {
  private $db = null ;

  public function DatabaseController($id) {
    // Luodaan tietokanta.
    if (!file_exists(DB_DIR . $id)) {
      $root = new Database(ROOT_DATABASE) ;
      $root->executeQuery('CREATE DATABASE ' . $id) ;
    }

    // Avataan tietokanta.
    $db = new Database($id);

    // Alustetaan tietokanta:
    // 1) Asetukset
    if (!file_exists(DB_DIR . $id . '/settings.txt')) {
      $db->executeQuery('CREATE TABLE settings ( id inc , name str , value str )') ;
    }

    // 2) Tuotteet
    if (!file_exists(DB_DIR . $id . '/catalog.txt')) {
      $db->executeQuery('CREATE TABLE catalog ( id inc , description str , ' .
                        'notes str , ingredients str , price str , unit str , ' .
                        'producer str , orderable int , position int )') ;
    }
    // 3) Tilaukset
    if (!file_exists(DB_DIR . $id . '/orders.txt')) {
      $db->executeQuery('CREATE TABLE orders ( id inc , date str , charge str , ' .
                        'name str , phone str , email str , pickup str , ' .
                        'participate int , notes str , state int )') ;
    }
    // 4) Tilauserittelyt
    if (!file_exists(DB_DIR . $id . '/breakdowns.txt')) {
      $db->executeQuery('CREATE TABLE breakdowns ( id inc , order_id int , ' .
                        'description str , producer str , price str , ' .
                        'unit str , quantity int )') ;
    }
    // 5) Tilastot
    if (!file_exists(DB_DIR . $id . '/stats_orders.txt')) {
      $db->executeQuery('CREATE TABLE stats_orders ( id inc , ' .
                        'date str , quantity int , sum str , charges str )') ;
    }
    if (!file_exists(DB_DIR . $id . '/stats_products.txt')) {
      $db->executeQuery('CREATE TABLE stats_products ( id inc , ' .
                        'order_id int , producer str , description str , ' .
                        'quantity int , unit str , sum str )') ;
    }
    if (!file_exists(DB_DIR . $id . '/stats_producers.txt')) {
      $db->executeQuery('CREATE TABLE stats_producers ( id inc ,' .
                        'order_id int , producer str , sum str )') ;
    }
    $this->db =& DB::connect('txtdbapi://localhost/' . $id) ;
  }
  
  public function prepare($query) {
    return $this->db->prepare($query) ;
  }

  public function execute($statement, $data) {
    return $this->db->execute($statement, $data) ;
  }

  public function free($statement) {
    return $this->db->freePrepared($statement) ;
  }
}
?>