<?php

class ExampleBss {

  public static function getData ()
  {
    $db = tf_db( 'default' );

    if ( $db ) {

      $result = $db->query( 'SELECT * FROM example', true, true );

      $db->close();

      return $result;
    }

    unset( $db );
  }
}
