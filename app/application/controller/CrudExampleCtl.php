<?php
/**
 * Example to ilustrate how Tlalokes 2 works
 *
 * @Controller( default='read' )
 */
class CrudExampleCtl {

  /**
   * Action to create a resource
   *
   * @Action( layout='example' zone='content:create' )
   */
  public function create ()
  {
    tf_response_set( 'hello_world', 'Hello World' );
  }

  /**
   * Actions to read a resource
   *
   * @Action( layout='example', zone='content:read' )
   */
  public function read ( )
  {
    require 'ExampleBss.php';

    tf_response_set( 'example', ExampleBss::getData() );
  }

  /**
   * Action to update a resource
   *
   * @Action( layout='example', zone='content:update' )
   */
  public function update ()
  {
  }

  /**
   * Action to delete a resource
   *
   * @Action( layout='example', zone='content:delete' )
   */
  public function delete ()
  {
  }
}
