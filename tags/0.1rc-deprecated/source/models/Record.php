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

abstract class Record {
  private static function DB_SELECT($db, $table, $id) {
    $statement = $db->prepare("SELECT * FROM $table WHERE id = ?") ;
    $results =& $db->execute($statement, array($id)) ;
    $db->free($statement) ;
    return $results ;
  }

  private static function DB_SELECT_ID($db, $table, $fields, $values) {
    $conditions = implode(' = ? AND ', $fields) . ' = ?' ;
    $statement = $db->prepare("SELECT id FROM $table WHERE $conditions") ;
    $results =& $db->execute($statement, $values) ;
    $db->free($statement) ;
    return $results ;
  }

  private static function DB_INSERT($db, $table, $fields, $values) {
    $into = implode(', ', $fields) ;
    $valueset = str_repeat('?, ', count($fields) - 1) . '?' ;
    $statement = $db->prepare("INSERT INTO $table ($into) VALUES ($valueset)") ;
    $results =& $db->execute($statement, $values) ;
    $db->free($statement) ;
    return $results ;
  }

  private static function DB_UPDATE($db, $table, $fields, $values, $id) {
    $values[] = $id ;
    $valueset = implode(' = ?, ', $fields) . ' = ?' ;
    $statement = $db->prepare("UPDATE $table SET $valueset WHERE id = ?") ;
    $results =& $db->execute($statement, $values) ;
    $db->free($statement) ;
    return $results ;
  }

  private static function DB_DELETE($db, $table, $id) {
    $statement = $db->prepare("DELETE FROM $table WHERE id = ?") ;
    $results =& $db->execute($statement, array($id)) ;
    $db->free($statement) ;
    return $results ;
  }

  protected $DB_CHANGES = array() ;
  
  protected $DB = null ;
  
  public $id = null ;

  public abstract function getTable() ;

  public abstract function getFields() ;

  public function Record($db, $values=array()) {
    $this->DB =& $db ;

    // Initialize fields
    foreach ($this->getFields() as $field) $this->$field = '' ;

    // Prepare values from a record
    if (is_a($values, 'Record') or is_subclass_of($values, 'Record')) {
      $record = $values ; $values = array() ;
      foreach ($record->getFields() as $field) {
        $values[$field] = $record->$field ;
      }
    }

    // Initilize id when given
    if (is_array($values) and isset($values['id'])) {
      $this->id = $values['id'] ;
    }

    // Initilize fields with given values
    if (is_array($values)) {
      $this->fill($values) ;
      // If $id was set, presuppose that values remain up-to-date.
      if (isset($this->id)) {
        $this->DB_CHANGES = array() ;
      }
    }
  }

  public function get($field, $default=null) {
    if (in_array($field, $this->getFields())) {
      if (isset($this->$field)) {
        return $this->$field ;
      }
      return $default ;
    }
    return false ;
  }

  public function set($field, $value) {
    $tidy = "tidy" . str_camelize($field) ;
    if (in_array($field, $this->getFields())) {
      $value = is_string($value) ? trim($value) : $value ;
      $value = method_exists($this, $tidy) ? $this->$tidy($value) : $value ;
      if (!isset($this->$field) or $this->$field != $value) {
        if (!in_array($field, $this->DB_CHANGES)) {
          $this->DB_CHANGES[] = $field ;
        }
        return $this->$field = $value ;
      }
      return false ;
    }
    return false ;
  }
  
  public function fill($values) {
    foreach ($values as $field => $value) {
      $this->set($field, $value) ;
    }
  }

  public function refresh() {
    if (isset($this->id)) {
      $results = self::DB_SELECT($this->DB, $this->getTable(), $this->id) ;
      while ($row = $results->fetchRow()) {
        foreach ($this->getFields() as $field) {
          $this->$field = $row[$field] ;
        }
      }
      $results->free() ;
      return $this->id ;
    }
    return false ;
  }
  
  public function commit() {
    if (count($this->DB_CHANGES)) {
      $values = array() ;
      foreach ($this->getFields() as $field) {
        $values[] = $this->$field ;
      }
      if (isset($this->id)) {
        $results = self::DB_UPDATE($this->DB, $this->getTable(), $this->getFields(), $values, $this->id) ;
      } else {
        $results = self::DB_INSERT($this->DB, $this->getTable(), $this->getFields(), $values) ;
        $results = self::DB_SELECT_ID($this->DB, $this->getTable(), $this->getFields(), $values) ;
        while ($row = $results->fetchRow()) {
          $this->id = $row['id'] ;
        }
        $results->free() ;
      }
      $this->DB_CHANGES = array() ;
      return $this->id ;
    }
    return false ;
  }
  
  public function destroy() {
    if (isset($this->id)) {
      $results = self::DB_DELETE($this->DB, $this->getTable(), $this->id) ;
      $this->id = null ;
      return true ;
    }
    return false ;
  }

  public function __call($name, $arguments) {
    if ($name == 'getId') {
      return $this->id ;
    } else if (strlen($name) > 3 && substr($name, 0, 3) == 'get'
      && in_array(str_decamelize(substr($name, 3)), $this->getFields())) {
        if (count($arguments) == 0) {
          return $this->get(str_decamelize(substr($name, 3))) ;
        } else {
          return $this->get(str_decamelize(substr($name, 3)), $arguments[0]) ;
        }
    } else if (strlen($name) > 3 && substr($name, 0, 3) == 'set'
      && in_array(str_decamelize(substr($name, 3)), $this->getFields())
      && count($arguments) == 1) {
        return $this->set(str_decamelize(substr($name, 3)), $arguments[0]) ;
    } else {
      throw new Exception("unknown method $name");
    }
  }
}
?>