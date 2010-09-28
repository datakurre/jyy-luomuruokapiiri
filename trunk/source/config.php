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
  
require_once 'utils.php' ;

// Ruokapiirin otsikko
if (!isset($title)) $title = "Esimerkillinen ruokapiiri" ;

// Tilauskohtainen ruokapiirin ylläpitokorvaus
if (!isset($charge)) $charge = "0,50" ; // euroa

// Vaihtoehtoiset noutopaikat tilauksille
if (!isset($pickup)) $pickup = array () ;

// Vastuuhenkilön sähköpostiosoite
if (!isset($email)) $email = "asko.soukka@iki.fi" ;

// Lähetetäänkö tilausvahvistus sähköpostilla
if (!isset($confirmation)) $confirmation = true ;

// Tietokannan yksilöivä nimi (vain kirjaimia a - z)
if (!isset($database)) $database = 'esimerkki' ;

// Käyttäjätunnukset ja salasanat
if (!isset($passwords)) $passwords = array(
  'salasana1' , 'salasana2' ,
) ;

// Ruokapiirin otsikko
define('ORDERBOOK_TITLE', $title) ;

// Tilauskohtainen ruokapiirin ylläpitokorvaus
define('ORDERBOOK_CHARGE', str_currency_fmt($charge)) ;

// Tilauskohtainen ruokapiirin ylläpitokorvaus
define('ORDERBOOK_PICKUP', serialize($pickup) );

// Vastuuhenkilön nimi ja sähköpostiosoite
define('ORDERBOOK_ADMIN', "$title <$email>") ;

// Vahvistussähköpostin lähetys
define('ORDERBOOK_CONFIRMATION', $confirmation) ;

// Lähdekoodin hakemisto palvelimella, päättyen merkkiin '/'
define('ORDERBOOK', getcwd() . '/source/') ;

// Sivupohjien hakemisto palvelimella, päättyen merkkiin '/''
define('ORDERBOOK_TEMPLATES',  getcwd() . '/templates/') ;

// Tietokannan hakemisto palvelimella, päättyen merkkiin '/'
define('DB_DIR', getcwd() . '/database/') ;
// 'txt-db-api' edellyttää nimen 'DB_DIR' käyttöä

// Maa-asetukset
setlocale(LC_ALL, 'fi_FI') ;
date_default_timezone_set('Europe/Helsinki') ;

// Virheidenkäsittely
error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED); // Tuotannoss po. E_ERROR 
// error_reporting(E_ERROR) ; // Tuotannoss po. E_ERROR 
// ini_set('display_errors', 1) ; // Tuotannossa po. 0
ini_set('display_errors', 0) ; // Tuotannossa po. 0

// PEAR-asennuksen polku palvelimella, päättyen merkkiin '/'
ini_set('include_path', ini_get('include_path') . ':' . getcwd() . '/PEAR/') ;
// Vaadittavat PEAR-komponentit: Auth, DB, PHPTAL, txt-db-api, Validate

// Tietokantaohjelmiston hakemisto palvelimella, päättyen merkkiin '/'
define('API_HOME_DIR', getcwd() . '/PEAR/txt-db-api/') ;
// 'txt-db-api' edellyttää nimen 'API_HOME_DIR' käyttöä

// Ladataan Ruokapiiri
require_once ORDERBOOK . 'controllers/MasterController.php' ;
?>