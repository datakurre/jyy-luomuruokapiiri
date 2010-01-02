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

require_once 'BaseView.php' ;

class OrderFormView extends BaseView {
  private $auth ;
  
  public function OrderFormView($auth) {
    parent::BaseView('orderform.xhtml') ;
    $this->auth =& $auth ;
  }
  protected function tidyInstructions($instructions) {
    if ($this->auth->isAuthorized()) {
      return $instructions ;
    } else {
      $in=array(
        '`((?:https?|ftp)://\S+[[:alnum:]]/?)`si',
        '`((?<!//)(www\.\S+[[:alnum:]]/?))`si'
      );
      $out=array(
        '<a href="$1" rel="nofollow" target="_blank">$1</a> ',
        '<a href="http://$1" rel="nofollow" target="_blank">$1</a>'
      );
      return explode("\n", preg_replace($in, $out, strip_tags($instructions))) ;
    }
  }
}
?>