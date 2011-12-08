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
class TlalokesDBConnection extends PDO {

  /**
   * Creates a PDO instance representing a connection to a database
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @param array $dsn An array with DSN information
   * @return PDO Returns a PDO object on success.
   * @todo ADD CHARSET TO DSN
   */
  public function __construct( array &$dsn ) {

    return parent::__construct( $dsn['type'].':'.
                                'host='.$dsn['host'].';'.
                                'dbname='.$dsn['name'].';',
                                $dsn['username'], $dsn['password'],
                                isset( $dsn['options'] ) ?
                                $dsn['options'] : null );
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
    if ( tf_request( 'debug' ) ) {

      $start_time = microtime( true );
    }

    $statament = parent::query( $sql );

    if ( tf_request( 'debug' ) ) {

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
}
