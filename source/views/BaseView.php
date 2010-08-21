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

require_once 'PHPTAL.php' ;

require_once ORDERBOOK . 'utils.php' ;

abstract class BaseView {
  private $template = null ;

  protected function BaseView($filename) {
    $this->template = new PHPTAL(ORDERBOOK_TEMPLATES . $filename) ;
    $this->set('base', 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('/(.*\/)\w*\?*.*/', '$1', $_SERVER['REQUEST_URI'])) ;
    if (isset($_GET['view'])) {
      $this->set('view', $_GET['view']) ;
    }
  }

  public function render() {
    try {
      echo $this->template->execute() ;
      if (error_reporting() != E_ERROR) {
        echo "Memory peak usage: " . memory_get_peak_usage() / 1024. / 1024. . "MB" ;
      }
    }
    catch (Exception $e){
      echo "<pre>$e</pre>" ;
    }
  }

  public function contents() {
    try {
      return $this->template->execute() ;
    }
    catch (Exception $e){
      echo "<pre>$e</pre>" ;
    }
  }

  public function set($name, $value) {
    if (isset($name) and isset($value)) {
      $tidy = "tidy" . str_camelize($name) ;
      $value = method_exists($this, $tidy) ? $this->$tidy($value) : $value ;
      $this->template->$name = $value ;
    }
  }
}
?>