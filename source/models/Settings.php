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

class Settings {
  private static function DB_SELECT($db, $table, $name) {
    $statement = $db->prepare("SELECT value FROM $table WHERE name = ?") ;
    $results =& $db->execute($statement, array($name)) ;
    $db->free($statement) ;
    return $results ;
  }

  private static function DB_INSERT($db, $table, $name, $value) {
    $statement = $db->prepare("INSERT INTO $table (name, value) VALUES (?, ?)") ;
    $results =& $db->execute($statement, array($name, $value)) ;
    $db->free($statement) ;
    return $results ;
  }

  private static function DB_UPDATE($db, $table, $name, $value) {
    $statement = $db->prepare("UPDATE $table SET value = ? WHERE name = ?") ;
    $results =& $db->execute($statement, array($value, $name)) ;
    $db->free($statement) ;
    return $results ;
  }

  private static function DB_DELETE($db, $table, $name) {
    $statement = $db->prepare("DELETE FROM $table WHERE name = ?") ;
    $results =& $db->execute($statement, array($id)) ;
    $db->free($statement) ;
    return $results ;
  }

  private static $DB_TABLE = 'settings' ;

  private $DB ;

  function Settings($db) {
    $this->DB =& $db ;
  }
  
  public function set($name, $value=null) {
    $value = strval($value) ;
    // If $value is null then delete $name from setttings
    if (isset($name) and !isset($value)) {
      $results =& self::DB_DELETE($this->DB, self::$DB_TABLE, $name) ;
    // If $value is set then set $name from settings to $value
    } else if (isset($name) and isset($value)) {
      if ($this->get($name) === false) {
        $results =& self::DB_INSERT($this->DB, self::$DB_TABLE, $name, $value) ;
      } else {
        $results =& self::DB_UPDATE($this->DB, self::$DB_TABLE, $name, $value) ;
      }
      return $this->get($name) ;
    }
  }

  public function get($name, $default=null) {
    // If $name is set then return $value for $name from settings
    if (isset($name)) {
      $results =& self::DB_SELECT($this->DB, self::$DB_TABLE, $name) ;
      $row = $results->fetchRow() ;
      $results->free() ;
      if (isset($row)) return $row['value'] ;

      // If $name was not found from $settings and $default was set then return $default
      else if (isset($default)) return $default;

      // Returns false when setting was not found and no default was set
      else return false ;
    }
  }
}
?>