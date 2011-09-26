<?php

/**
 * Example class to ilustrate how Tlalokes 2 works
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @Controller( output='CLI' )
 */
class ExampleCtl {

  /**
   * Hello world example method
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @Action( layout='example', block='content:hello' )
   */
  public function helloWorld ()
  {
    print_r( tf_conf_get( 'action_annotation' ) );
    tf_response_set( 'hello_world', 'Hola Mundo' );
  }

  /**
   * Another example method
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @Action( layout='example', block='content:hello' )
   */
  public function sumThis ()
  {
    tf_response_set( 'result', tf_request('val1') + tf_request('val2') );
  }

  /**
   * DB Connection example method
   *
   * @author Basilio Briceno <bbh@tlalokes.org>
   * @Action( layout='example', zone='content:data,hello;foo:da,de' )
   */
  public function getDataFromDB ( )
  {
    require 'ExampleBss.php';

    tf_response_set( 'example', ExampleBss::getData() );
  }
}
