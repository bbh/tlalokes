<?php
/**
 * Tlalokes framework classes
 * Copyright (c) 2011 Basilio Briceno <bbh@tlalokes.org>
 *
 * This file is part of the Tlalokes framework.
 *
 * Tlalokes framework is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, version 3 of the License.
 *
 * Tlalokes framework is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this file. If not, see <http://www.gnu.org/licenses/lgpl.html>.
 */

/**
 * Class to connect to a database extending PDO and provides custom features
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @todo MORE CONTROL IN query() ERROR RESULT
 * @todo A SIMPLE WAY FOR TRANSACTIONAL SQL STATEMENTS
 */
class TFPDO extends PDO {

  private $conn_name;

  /**
   * Creates a PDO instance representing a connection to a database
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @param array $dsn An array with DSN information
   * @return PDO Returns a PDO object on success.
   * @todo ADD CHARSET TO DSN
   */
  public function __construct ( array &$dsn, $name )
  {
    $this->conn_name = $name;

    return parent::__construct( $dsn['type'].':host='.$dsn['host'].';'.
                                'dbname='.$dsn['name'].';',
                                $dsn['username'], $dsn['password'],
                                isset( $dsn['options'] ) ? $dsn['options'] : null );
  }

  /**
   * Executes SQL statement, returns a PDOStatement object or Array if fetched
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @param string $sql SQL statement
   * @param boolean $fetch Flag to returns result as a fetched array
   * @param boolean $one_row Flag to return only one fetched row array
   * @return mixed PDOStatement object, fetched array, or FALSE on failure
   */
  public function query ( $sql, $fetch = false, $one_row = false )
  {
    if ( request( 'debug' ) ) {

      $start_time = microtime( true );
    }

    $statament = parent::query( $sql );

    if ( request( 'debug' ) ) {

      tf_log( 'SQL ['.round( $start_time - microtime( true ), 4 ).'s] '.$sql );
      unset( $start_time );
    }

    $statament->setFetchMode( parent::FETCH_ASSOC );

    if ( $one_row ) {

      return $statament->fetch();
    }

    if ( $fetch ) {

      return $statament->fetchAll();
    }

    return $statament;
  }

  public function close ()
  {
    $GLOBALS['_REGISTRY']['db'][$this->conn_name] = null;

    unset( $GLOBALS['_REGISTRY']['db'][$this->conn_name] );
  }
}

/**
 * Class to connect to a database extending mysqli and provides custom features
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2012, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @todo MORE CONTROL IN query() ERROR RESULT
 * @todo A SIMPLE WAY FOR TRANSACTIONAL SQL STATEMENTS
 */
class TFMySQLi extends mysqli {

  /**
   * Creates a mysqli instance representing a connection to a database
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @param array $dsn An array with DSN information
   * @return mysqli Returns a mysqli object on success
   */
  public function __construct ( array &$dsn )
  {
    $conn = parent::mysqli( $dsn['host'], $dsn['username'], $dsn['password'],
                            $dsn['name'] );

    if ( mysqli_connect_errno( $conn ) ) {

      throw new Exception( "Failed to connect: " . mysqli_connect_error() );
    }

    return $conn;
  }


  /**
   * Executes SQL statement, returns a mysqli_result object or Array if fetched
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @param string $sql SQL statement
   * @param boolean $fetch Flag to returns result as a fetched array
   * @param boolean $one_row Flag to return only one fetched row array
   * @return mixed mysqli_result object, fetched array, or FALSE on failure
   */
  public function query ( $sql, $fetch = false, $one_row = false )
  {
    if ( request( 'debug' ) ) {

      $start_time = microtime( true );
    }

    $result = parent::query( $sql );

    if ( request( 'debug' ) ) {

      tf_log( 'SQL ['.round( $start_time - microtime( true ), 4 ).'s] '.$sql );

      unset( $start_time );
    }

    if ( $one_row ) {

      $rows = $result->fetch_all( MYSQLI_ASSOC );

      $result->free();

      return $rows[0];
    }

    if ( $fetch ) {

      $rows = $result->fetch_all( MYSQLI_ASSOC );

      $result->free();

      return $rows;
    }

    return $result;
  }
}
