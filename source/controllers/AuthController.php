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

require_once 'Auth.php' ;

require_once ORDERBOOK . 'views/LoginView.php' ;
require_once ORDERBOOK . 'views/LogoutView.php' ;

class AuthController {
  private $auth = null ;

  public static function render($username = null, $status = null, &$auth = null) {
    $view = new LoginView($username, $status) ;
    die($view->render()) ;
  }

  public function AuthController($users) {
    $this->auth = new Auth('Array', array('users' => $users),
                           'AuthController::render') ;
  }
  
  public function isAuthorized() {
    $username = array("username" => $this->auth->getUsername()) ;
    if (in_array($username, $this->auth->listUsers())) {
      return True;
    }
    return False ;
  }

  public function login() {
    $this->auth->start() ;
    if ($this->auth->checkAuth()) {
      if (!$this->isAuthorized()) {
        $this->auth->logout() ;
        AuthController::render() ;
      }
      return true ;
    }
    return false ;
  }
    
  public function logout() {
    $this->auth->start() ;
    $this->auth->logout() ;
  }
}
?>