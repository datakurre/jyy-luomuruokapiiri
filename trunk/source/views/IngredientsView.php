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

class IngredientsView extends BaseView {
  public function IngredientsView() {
    parent::BaseView('ingredients.xhtml') ;
  }
  protected function tidyOther($other) {
    return $this->tidyProducts($other) ;
  }
  protected function tidyProducers($producers) {
    foreach (array_keys($producers) as $producer) {
      $producers[$producer]['products'] = $this->tidyProducts($producers[$producer]['products']) ;
    }
    return $producers ;
  }
  protected function tidyProducts($products) {
    foreach ($products as $product) {
      $product->ingredients = explode("\n", $product->getIngredients()) ;
    }
    return $products ;
  }
}
?>