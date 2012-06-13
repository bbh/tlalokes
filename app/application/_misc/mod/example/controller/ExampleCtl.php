<?php
/**
 * Example to ilustrate how Tlalokes 2 works
 *
 * @Controller( default='helloWorld' )
 */
class ExampleCtl {

  /**
   * Action displays Hello World in a Template based View
   *
   * @Action( file='example_hello' )
   */
  public function helloWorld ()
  {
    tf_response_set( 'hello_world', 'Hello World' );
  }

  /**
   * Actions displays data from an example database in a Layout based View
   *
   * @Action( layout='example', zone='content:data' )
   */
  public function getDataFromDB ( )
  {
    require 'ExampleBss.php';

    tf_response_set( 'example', ExampleBss::getData() );
  }

  /**
   * A simple sum without View layer
   */
  public function sumThis ()
  {
    echo tf_request('val1') + tf_request('val2');
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

        tf_response_set( 'flag', true );
      }
    }
  }
}
