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

function str_linefy($str, $width=80, $indent=0) {
  $result = '' ;
  $limit = $width ;
  foreach (explode("\n", $str) as $line) {
    $line = trim(preg_replace('/\s\s+/', ' ', $line));
    foreach (explode(' ', $line) as $word) {
      if ($limit == $width) {
        $add = str_repeat(' ', $indent) . $word;
        $result .= $add ; $limit -= strlen($add) ;
      } else {
        $add = ' ' . $word;
        if (($limit - strlen($add)) >= 0) {
          $result .= $add ; $limit -= strlen($add) ;
        } else {
          $add = str_repeat(' ', $indent) . $word;
          $result .= "\n" . $add ; $limit = $width - strlen($add) ;
        }
      }
    }
    $result .= "\n" ; $limit = $width ;
  }
  return rtrim($result) ;
}

function str_trim($str, $strip_tags=false) {
  $str = trim($str) ;
  $str = str_replace("\r\n", "\n", $str) ;
  if ($strip_tags) {
    $str = strip_tags($str) ;
  }
  return $str ;
}
  
function str_currency_fmt($str) {
  $str = str_replace(',', '.', $str) ;
  $str = sprintf(sprintf("%01.2f", floatval($str))) ;
  /* Finnish locale, for example, uses commas instead of
    dots and we want dots back. */
  $str = str_replace(',', '.', $str) ;
  return $str ;
}

function str_camelize($str) {
  $camelized = '' ;
  $parts = explode('_', $str) ;
  foreach($parts as $part) {
    $camelized .= ucfirst($part) ;
  }
  return $camelized ;
}

function str_decamelize($str) {
  $decamelized = '' ;
  for ($i = 0; $i < strlen($str); $i++) {
    if ($str[$i] == strtoupper($str[$i])) {
      if ($i > 0) {
        $decamelized .= '_' ;
      }
      $decamelized .= strtolower($str[$i]) ;
    } else {
      $decamelized .= $str[$i] ;
    }
  }
  return $decamelized ;
}

/* http://geoland.org/2007/12/utf8-ready-php-mail-function/ */
function UTF8_mail($from, $to, $subject, $message, $cc="", $bcc="") {
  $from = explode("<", $from) ;

  $headers  =  "From: =?UTF-8?B?" . base64_encode($from[0]) . "?= <" . $from[1] . "\r\n" ;

  $to = explode("<", $to) ;
  $to = "=?UTF-8?B?" . base64_encode($to[0]) . "?= <" . $to[1] ;

  $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=\n" ;

  if ($cc != "") {
    $cc = explode("<", $cc) ;
    $headers .= "Cc: =?UTF-8?B?" . base64_encode($cc[0]) . "?= <" . $cc[1] . "\r\n" ;
  }

  if($bcc != "") {
    $bcc = explode("<", $bcc) ;
    $headers .= "Bcc: =?UTF-8?B?" . base64_encode($bcc[0]) . "?= <" . $bcc[1] . "\r\n" ;
  }

  $headers .=
      "Content-Type: text/plain;" . "charset=UTF-8; format=flowed\n"
    . "MIME-Version: 1.0\n"
    . "Content-Transfer-Encoding: 8bit\n"
    . "X-Mailer: PHP\n" ;

  return mail($to, $subject, $message, $headers) ;
}
?>