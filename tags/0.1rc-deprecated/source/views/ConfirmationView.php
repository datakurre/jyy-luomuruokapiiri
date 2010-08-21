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

require_once ORDERBOOK . 'utils.php' ;

require_once 'BaseView.php' ;

require_once ORDERBOOK . 'models/Order.php' ;

class ConfirmationView extends BaseView {
  public function ConfirmationView() {
    parent::BaseView('confirmation.xhtml') ;
  }
  
  public function render($order) {
    $this->set('order', $order) ;

    $confirmation = false ;
    $message = trim($this->contents()) ;
    if ($order->getEmail() and ORDERBOOK_CONFIRMATION) {
      $confirmation = UTF8_mail(ORDERBOOK_ADMIN,
                                $order->getName() . ' <' . $order->getEmail() . '>',
                                'Tilausvahvistus: ' . $order->getName() . ', ' . $order->getPhone(),
                                $message, '', ORDERBOOK_ADMIN) ;
    } else if (ORDERBOOK_CONFIRMATION) {
       UTF8_mail(ORDERBOOK_ADMIN, ORDERBOOK_ADMIN,
                 'Tilausvahvistus: ' . $order->getName() . ', ' . $order->getPhone(),
                 $message) ;
    }

    $email = $order->getEmail() ;
    parent::BaseView('thankyou.xhtml') ;
    $this->set('confirmation', $confirmation ? $email : false) ;
    $this->set('order', $message) ;
    parent::render() ;
  }
  
  protected function tidyMessage($message) {
    return str_linefy($message) ;
  }

  protected function tidyOrder($order) {
    if (is_a($order, 'Order')) {
      $order->notes = str_linefy($order->notes) ;
      foreach (array_keys($order->products) as $product) {
        $order->products[$product]->ingredients = str_linefy($order->products[$product]->ingredients, $width=76, $indent=4) ;
      }
    }
    return $order ;
  }
}
?>