<?php
/**
 * Example to ilustrate how TF2 works
 *
 * @Controller( default='hello_world' )
 */
class ExampleCtl {

  /**
   * Action displays Hello World in a Template based View
   *
   * @Action( file='example_hello' )
   */
  public function hello_world ()
  {
    response( 'hello_world', 'Hello World' );
  }

  /**
   * Actions displays data from an example database in a Layout based View
   *
   * @Action( layout='example', zone='content:data' )
   */
  public function get_data_from_db ( )
  {
    require 'ExampleBss.php';

    response( 'example', ExampleBss::getData() );
  }

  /**
   * A simple sum without View layer
   */
  public function sum ()
  {
    echo request( 'val1' ) + request( 'val2' );
  }

  /**
   * A single upload
   *
   * @Action( layout='example', zone='content:upload' )
   */
  public function uploadFile ()
  {
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

      if ( tf_fileup_save( 'myfile', array( 'type' => 'jpeg,png',
                                            'size' => 160383 ) ) ) {

        response( 'flag', true );
      }
    }
  }
}
