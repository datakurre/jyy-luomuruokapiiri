<?php

/*
	HTML-KIT -ohjelmassa ääkköset näkyvät ja muokkautuvat oikein,
	kun valitsee valikosta Tools -> Unicode -> Unicode Pad... ja
	muokkauksen jälkeen klikkaa painiketta "Apply".	
*/

// Testikäytössä sähköpostinlähetys on kytketty pois päältä.
$confirmation = false ;

// Ruokapiirin nimi eli otsikko, joka näytetään useilla
// sivuilla ja joka näkyy tilausvahvistusten lähettäjänä.
$title = "Esimerkillinen Ruokapiiri" ;

// Tilauskohtainen ruokapiirin ylläpitokorvaus, joka
// lisätään jokaisen tilauksen loppusummaan.
$charge = "0,50" ; // euroa

// Vastuuhenkilön sähköpostiosoite, joka näkyy
// tilausvahvistusten lähetys- ja vastausosoitteena.
$email = "asko.soukka@iki.fi" ;

// Tietokannan yksilöivä nimi (vain kirjaimia a - z),
// jota käytetään ainoastaan tilausten tallentamiseen
// palvelimella, eikä sitä näytetä lainkaan tilaajille.
$database = "esimerkki" ;

// Salasanat, joilla pääsee kirjautumaan ruokapiirin
// tuote- ja tilaushallintaan; Salasanoja voi olla
// yksi tai useampi, mutta ne ovat kaikki saman arvoisia;
// Uusia salasanoja voi lisätä tai vanhoja poistaa
// rivejä lisäämällä tai vähentämällä mallin mukaan.
$passwords = array(
  "esimerkki1" ,
//  "esimerkki2" , // tämä salasana on poissa käytöstä
) ;
// Mahdollisuutta useampaan kuin yhteen salasanaan voi
// hyödyntää esimerkiksi silloin, kun on tarvetta antaa
// jollekin tilapäinen pääsy tilausjärjestelmään ilman,
// että vakituisen ylläpitäjän salasanaa tarvitsee muuttaa.

// Tästä eteenpäin käynnistetään tilausjärjestelmää, eikä
// näitä rivejä sovi muuttaa, ellei tiedä mitä tekee...
require_once "source/config.php" ;
$orderbook = new MasterController($database, $passwords) ;
$orderbook->main() ;
?>