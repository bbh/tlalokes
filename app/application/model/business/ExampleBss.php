<?php

class ExampleBss {

  public static function getData ()
  {
    $db = tf_db( 'default' );

    if ( $db ) {

      $result = $db->query( 'SELECT * FROM example', true, true );

      return $result;
    }

    unset( $db );
  }

  public static function read ()
  {
    $db = tf_db( 'default' );

    $sql = 'SELECT * FROM example';

    var_dump( $db->query( $sql, true ) );
  }
}
