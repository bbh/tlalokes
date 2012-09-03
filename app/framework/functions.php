<?php
/**
 * Tlalokes framework functions
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
 * Loads Tlalokes framework
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $application Application's path
 */
function tf_init ( $application = false )
{
  // set short open tags <? as default
  ini_set( 'short_open_tag', '1' );

  // init session
  session_start();

  // set registry global
  $GLOBALS['_REGISTRY'] = array();

  if ( !$application ) {

    $application = realpath( 'app/application' );
  }

  if ( !file_exists( $application ) ) {

    tf_error( "[Framework] Application directory ($application) not found", true );
  }

  // if application is CLI based parse request vars from argv
  if ( PHP_SAPI == 'cli' ) {

    // parse arguments from CLI
    tf_parse_argv();

  // application is web based
  } else {

    // load request
    $GLOBALS['_REGISTRY']['request'] =& $_REQUEST;
  }

  tf_log( "Request: variables loaded" );

  // load configuration file
  if ( !file_exists( $application . '/config.php' ) ) {

    tf_error( "[Framework] Application configuration file not found", true );
  }

  require $application . '/config.php';

  // set default timezone
  date_default_timezone_set( $c['default']['timezone'] );

  // load theme name
  $theme = tf_request( 'theme' ) ?
           $application . '/view/theme/' . tf_request( 'theme' ) :
           $application . '/view/theme/' . $c['default']['theme'];

  // load modules
  if ( isset( $c['module'] ) && is_array( $c['module'] ) ) {

    // iterate each module in the module's array
    foreach ( $c['module'] as $name => $active ) {

      // check if module must be active
      if ( $active === true ) {

        // check module existance
        if ( !file_exists( $application . '/_misc/mod/'. $name ) ) {

          tf_error( "[Framework] Module '$name' not found." );

        } else {

          $modules[$name] = $name;

          $mod_path = $application . '/_misc/mod/'. $name;

          $mods_inc = !isset( $mods_inc ) ? '' : $mods_inc;

          $mods_inc .= PATH_SEPARATOR . $mod_path . '/controller' .
                       PATH_SEPARATOR . $mod_path . '/model' .
                       PATH_SEPARATOR . $mod_path . '/view';

          unset( $mod_path );

          tf_log( "Module $name is ready to load" );
        }
      }
    }

    // set modules enabled to configuration, to avoid recheck
    if ( isset( $modules ) && is_array( $modules ) && $modules ) {

      $GLOBALS['_REGISTRY']['modules_enabled'] = $modules;

      unset( $modules );
    }
  }

  // set configuration in global registry
  $GLOBALS['_REGISTRY']['conf'] = $c;

  // set include_path into environment
  ini_set( 'include_path', PATH_SEPARATOR . $application . '/controller' .
                           PATH_SEPARATOR . $application . '/model' .
                           PATH_SEPARATOR . $application . '/model/business' .
                           PATH_SEPARATOR . $application . '/view' .
                           PATH_SEPARATOR . $theme .
                           PATH_SEPARATOR . $application . '/_misc/locale' .
                           PATH_SEPARATOR . $application . '/_misc/lib' .
                           isset( $mods_inc ) ? $mods_inc : $mods_inc );

  // set theme in configuration
  $GLOBALS['_REGISTRY']['conf']['path']['application'] = $application;
  $GLOBALS['_REGISTRY']['conf']['path']['theme'] = $theme;
  unset( $c, $application, $theme );

  // set start time
  if ( isset( $GLOBALS['_REGISTRY']['request']['debug'] ) ) {

    tf_conf_set( 'start_time', microtime( true ) );
  }

  // load controller
  if ( !$controller = tf_request( 'controller' ) ) {

    // load default controller
    if ( !$controller = tf_conf_get( 'default', 'controller' ) ) {

      tf_error( "[Framework] Controller name required", true );
    }
  }

  tf_conf_set( 'controller', $controller );
  unset( $controller );

  $controller = tf_controller_load();

  // load view
  tf_view_load();

  if ( isset( $GLOBALS['_REGISTRY']['request']['debug'] ) ) {

    tf_log( 'Time: '. round( microtime( true ) - tf_conf_get('start_time'), 4 ).
            's, Memory: ' . memory_get_usage( true ) / 1024 . 'K' );
  }

  tf_log_print();

  unset( $GLOBALS['_REGISTRY'] );

  exit;
}

/**
 * Parses arguments from CLI from a "foo=1&bar=2" format and sets it as request
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 */
function tf_parse_argv ()
{
  // check if argv is On in php.ini
  if ( ini_get('register_argc_argv') != 1 ) {

    tf_error( "[Framework] Set 'register_argc_argv=On' in php.ini", true );
  }

  // check if there is an argument to load
  if ( !isset( $_SERVER['argv'][1] ) || !$_SERVER['argv'][1] ) {

    tf_error( "[Framework] Provide arguments" );
  }

  if ( isset( $_SERVER['argv'][1] ) ) {

    // parse arguments
    $arguments = explode( '&', $_SERVER['argv'][1] );

    if ( count( $arguments ) > 1 ) {

      foreach ( $arguments as $value ) {

        $var = explode( '=', $value );

        $GLOBALS['_REGISTRY']['request'][$var[0]] = $var[1];

        unset( $var );
      }

    } else {

      $var = explode( '=', $arguments[0] );

      if ( !isset( $var[1] ) || !$var[1] ) {

        tf_error( "[Framework] Provide a value in your argument", true );
      }

      $GLOBALS['_REGISTRY']['request'][$var[0]] = $var[1];

      unset( $var );
    }

    unset( $arguments );
  }
}

/**
 * Transforms string to html entities and returns it as the required charset
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $string
 * @param string $to charset name (UTF-8, ISO-8859-1, etc.) default UTF-8
 * @return string
 */
function tf_charset ( $string, $to = 'UTF-8' )
{
  if ( is_string( $string ) ) {

    // encode special chars as HTML entities
    $string = htmlentities( $string, ENT_NOQUOTES, $to );

    // decode HTML entities and return it as the specified charset
    $string = html_entity_decode( $string, ENT_NOQUOTES, $to );
  }

  return $string;
}

/**
 * Transforms an array into a string
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param mixed $element
 * @param mixed $parent;
 */
function tf_array_to_string ( $element, $parent = false )
{
  $response = '';

  if ( is_array( $element ) ) {

    foreach ( $element as $k => $v ) {

      $parent = preg_replace( '/(\[\')*(.*)(\'\])*/', '$2', $parent );

      $key = ( $parent ? "['$parent']" : '' ) . "['$k']";

      $response .= tf_array_to_string( $v, $key );
    }

  } else {

    $response .= str_replace( "']']", "']", $parent )." = '$element';\n";
  }

  return $response;
}

/**
 * Removes characters cosidered part of an injection attack
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $string
 * @return string
 */
function tf_sanitize ( $string )
{
  // remove double backslash
  $string = str_replace( '\\', '', $string );

  // remove \t & \n from ^ and $
  $string = trim( $string );

  //$string = quotemeta( trim( $string ) );

  // add slash to quotes
  return addslashes( $string );
}

/**
 * Removes a directory in a recursive way
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $path
 */
function tf_rmdir ( $path )
{
  if ( !file_exists( $path ) ) {
    tlalokes_error_msg( 'Directory provided not existant' );
  }

  if ( !is_writable( $path ) ) {
    tlalokes_error_msg( "Directory or file isn't writeable" );
  }

  // check if $path is directory or file
  if ( is_dir( $path ) ) {

    // iterate $path directory
    foreach ( glob( $path . '/*' ) as $item ) {

      // recursive call content in $item
      tf_rmdir( $item );
    }

    // remove directory $path
    rmdir( $path );

  } else {

    // remove file $path
    unlink( $path );
  }
}

/**
 * Crypts in a 'one way' mode a string provided using SHA512 with 5000 rounds
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $string String to crypt
 * @param string $code Application's code for salt
 */
function tf_crypt ( $string, $code = false )
{
  return crypt( $string,
                '$6$rounds=5000$'.($code ? $code : '35332e6b3031343174').'$' );
}

/**
 * Loads a zone's block
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $block_name Name of block to load
 */
function tf_view_block ( $block_name )
{
  $path = tf_conf_get( 'path', 'theme' ) . '/block/';

  // check if part of a module
  if ( tf_is_a_module() ) {

    $path = tf_conf_get( 'module_conf', 'theme' ) . '/block/';
  }

  $file = strtolower( tf_conf_get('controller') ).'_'.$block_name.'_block.php';

  if ( !file_exists( $path.$file ) ) {

    $file = $path.$block_name.'_block.tpl';

    if ( !file_exists( $path.$file ) ) {

      tf_error( '[Framework][Block] File not found ('. $file .')' );

      return;
    }
  }

  tf_log( 'Block: Loading '.$block_name.' ('.$file.')' );

  require $path . $file;
}

/**
 * Load a zone and its blocks
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $zone_name Name of zone to load
 */
function tf_view_zone ( $zone_name )
{
  $annotation = tf_conf_get( 'action_annotation' );

  if ( isset( $annotation['Action']['zone'] ) ) {

    foreach ( $annotation['Action']['zone'] as $zone => $value ) {

      if ( $zone == $zone_name ) {

        if ( strstr( $value, ',' ) ) {

          foreach ( explode( ',', $value ) as $block ) {

            tf_view_block( $block );
          }
        } else {

          tf_view_block( $value );
        }
      }
    }
  }
  unset( $annotation );
}

/**
 * Loads view
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @todo SET URI TO RESPONSE
 */
function tf_view_load ()
{
  // set short open tags <? as default
  ini_set( 'short_open_tag', '1' );

  // get action's annotations
  $annotation = tf_conf_get( 'action_annotation' );

  if ( !isset( $annotation['Action']['file'] ) &&
       !isset( $annotation['Action']['layout'] ) ) {

    return;
  }

  // load template
  if ( isset( $annotation['Action']['file'] ) &&
       !isset( $annotation['Action']['layout'] ) ) {

    tf_log( 'Template: Loading '. $annotation['Action']['file'] );

    // if loading a module, load view from that module
    if ( tf_is_a_module() ) {

      foreach( tf_conf_get( 'module' ) as $name => $active ) {

        if ( $active === true ) {

          tf_log( 'Template: Module view ' . $name );

          $path = tf_conf_get( 'module_conf', 'theme' ) . '/';
        }
      }

    // if not a module load from application's view
    } else {

      $path = tf_conf_get( 'path', 'theme' ) . '/';
    }

    $file = tf_conf_get('controller').'_'.$annotation['Action']['file'].'.php';

    if ( !file_exists( $path.$file ) ) {

      $file = tf_conf_get('controller').'_'.$annotation['Action']['file'].'.tpl';

      if ( !file_exists( $path.$file ) ) {

        $file = $annotation['Action']['file'].'.php';

        if ( !file_exists( $path . $file ) ) {

          $file = $annotation['Action']['file'].'.tpl';

          if ( !file_exists( $path . $file ) ) {

            tf_error( '[Framework][Template] File not found ('.
                      $path . $annotation['Action']['file'] .')', true );
          }
        }
      }
    }

    tf_log( 'Template: Loading '.$annotation['Action']['file'].' ('.$file.')' );
  }

  // load layout
  if ( isset( $annotation['Action']['layout'] ) ) {

    $path = tf_conf_get( 'path', 'theme' ) . '/layout/';

    if ( tf_is_a_module() ) {

      $path = tf_conf_get( 'module_conf', 'theme') . '/layout/';
    }

    // set the layout file name
    $file = tf_conf_get('controller').'_'.$annotation['Action']['layout'].
            '_layout.php';

    // try to find the layout file
    if ( !file_exists( $path.$file ) ) {

      $file = tf_conf_get('controller').'_'.$annotation['Action']['layout'].
              '_layout.tpl';

      if ( !file_exists( $path.$file ) ) {

        $file = $annotation['Action']['layout'].'_layout.php';

        if ( !file_exists( $path.$file ) ) {

          $file = $annotation['Action']['layout'].'_layout.tpl';

          if ( !file_exists( $path.$file ) ) {

            $file = $annotation['Action']['layout'].'.php';

            if ( !file_exists( $path.$file ) ) {

              $file = $annotation['Action']['layout'].'.tpl';

              if ( !file_exists( $path.$file ) ) {

                tf_error( '[Framework][Layout] File not found ('.
                          $annotation['Action']['layout'] .')', true );
              }
            }
          }
        }
      }
    }

    tf_log( 'Layout: Loading '.$annotation['Action']['layout'].' ('.$file.')' );
  }

  unset( $annotation );

  // set URI

  require $path.$file;

  unset( $path );
  unset( $file );
}

/**
 * Confirms if the controller it's been loaded from a module
 *
 * @author Basilio Briceno <basilio@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @return boolean
 */
function tf_is_a_module ()
{
  // check if module has been already checked
  if ( !tf_conf_get( 'module_conf' ) ) {

    // if controller is part of a mudule load theme from that module
    if ( $name = tf_conf_get( 'controller_module' ) ) {

      $mod['path'] = tf_conf_get( 'path', 'application' ) .'/_misc/mod/'. $name;

      require_once $mod['path'] . '/config.php';

      $mod['theme'] = $mod['path'] .'/view/theme/'. $c['default']['theme'];

      $mod['conf'] = $c;

      unset( $c );

      tf_conf_set( 'module_conf', $mod );

      unset( $mod );

      return true;
    }

  // if module has been already checked
  } else {

     return true;
  }

  return false;
}

/**
 * Connects to a database and returns that connection object
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $dsn_name DSN name
 * @return TlalokesDBConnection A extendend object of PDO
 */
function tf_db ( $dsn_name = 'default' )
{
  if ( tf_is_a_module() ) {

    $dsn = tf_conf_get( 'module_conf', 'dsn' );

  } else {

    $dsn = tf_conf_get( 'dsn', $dsn_name );
  }

  if ( !$dsn ) {

    tf_error( '[Framework][DB] Provide a valid DSN name' );

    return false;
  }

  if ( !isset( $GLOBALS['_REGISTRY']['conf']['db'][$dsn_name] ) ) {

    try {

      require_once 'classes.php';

      if ( !isset( $GLOBALS['_REGISTRY']['conf']['db'] ) ) {

        $GLOBALS['_REGISTRY']['db'] = array();
      }

      // if driver is defined
      if ( isset( $dsn['driver'] ) ) {

        // driver is mysqli
        if ( $dsn['driver'] == 'mysqli' ) {

          $GLOBALS['_REGISTRY']['db'][$dsn_name] = new TFMySQLi( $dsn );
        }

      // driver is PDO
      } else {

        $GLOBALS['_REGISTRY']['db'][$dsn_name] = new TFPDO( $dsn, $dsn_name );
      }

    } catch ( Exception $e ) {

      tf_error( '[Framework][DB] '.$e->getMessage() );

      return false;
    }
  }

  return $GLOBALS['_REGISTRY']['db'][$dsn_name];
}

/**
 * Returns a value from the response registry
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $name Name of the variable
 * @return mixed The value from response registry
 */
function tf_response ( $name )
{
  if ( !isset( $GLOBALS['_REGISTRY']['response'][$name] ) ) {

    return false;
  }

  return $GLOBALS['_REGISTRY']['response'][$name];
}

/**
 * Sets a value into response registry
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $name Name of variable
 * @param mixed $value Value of variable
 */
function tf_response_set ( $name, $value )
{
  if ( !isset( $GLOBALS['_REGISTRY']['response'] ) ) {

    $GLOBALS['_REGISTRY']['response'] = array();
  }

  $GLOBALS['_REGISTRY']['response'][$name] = $value;
}

/**
 * Load a controller
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @return mixed The controller's object
 */
function tf_controller_load ()
{
  // transform controller name from this_example to ThisExample
  $name = tf_strlow_to_camel( tf_conf_get( 'controller' ) ) . 'Ctl';

  // set absolute path to check file
  $app = tf_conf_get( 'path', 'application');

  // validate controller file existance
  if ( !file_exists( $app . '/controller/' . $name . '.php' ) ) {

    // validate if controller file exists in a enabled module
    $modules = $GLOBALS['_REGISTRY' ]['modules_enabled'];

    if ( is_array( $modules ) ) {

      foreach ( $modules as $module ) {

        $ctlr = $app .'/_misc/mod/'. $module . '/controller/'. $name .'.php';

        if ( file_exists( $ctlr ) ) {

          $GLOBALS['_REGISTRY']['conf']['controller_module'] = $module;

          $path = $ctlr;

          unset( $ctlr );

          break;
        }

        unset( $ctlr );
      }
    }
  }

  // validate controller existance
  if ( !isset( $path ) ) {

    tf_error( "[Framework] Controller ($name) not found", true );
  }

  // load controller
  require $path;

  unset( $path );

  tf_log( "Controller: ($name) loaded" );

  // reflect class to get annotations
  $reflection = new ReflectionClass( $name );

  // validate docComment block existance
  if ( !$doc = $reflection->getDocComment() ) {

    tf_error( "[Framework] No DocComment block found in controller ($name)" );

  // parse docComment block
  } else {

    if ( !$annotation = tf_annotation_parser( $doc ) ) {

      tf_error( "[Framework] There aren't annotations in controller ($name)" );
    }
  }

  // set annotation to configuration
  if ( isset( $annotation ) && is_array( $annotation ) ) {

    tf_conf_set( 'controller_annotation', $annotation );

    unset( $doc );

    tf_log( "Controller: Annotations loaded" );
  }

  // load action
  if ( !$action = tf_request( 'action' ) ) {

    // check default action
    if ( !isset( $annotation['Controller']['default'] ) ) {

      tf_error( "[Framework] Action name required", true );
    }

    // set default action
    $action = $annotation['Controller']['default'];
  }

  unset( $annotation );

  // validate action existance
  if ( !$reflection->hasMethod( $action ) ) {

    $action = lcfirst( tf_strlow_to_camel( $action ) );

    if ( !$reflection->hasMethod( $action ) ) {

      tf_error( "[Framework] Action ($action) not found", true );
    }
  }

  tf_log( "Action: Existance validated" );

  if ( !$doc = $reflection->getMethod( $action )->getDocComment() ) {

    tf_error( "[Framework] No DocComment block found in action ($action)" );

  // parse docComment block
  } else {

    if ( !$annotation = tf_annotation_parser( $doc ) ) {

      tf_error( "[Framework] There aren't annotations in action ($action)" );
    }
  }

  if ( isset( $annotation ) && is_array( $annotation ) ) {

    tf_conf_set( 'action_annotation', $annotation );

    tf_log( "Action: Annotations loaded" );
  }

  // set action in configuration
  tf_conf_set( 'action', $action );

  tf_log( "Action: Loading $action" );

  $controller = new $name();
  $controller->$action();
  unset( $action );
  unset( $controller );
}

/**
 * Transforms an string like my_string to MyString
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $string Example: my_original_string
 * @return string Example: MyCamelString
 */
function tf_strlow_to_camel ( $string )
{
  if ( stristr( $string, '_' ) ) {

    $response = '';

    foreach ( explode( '_', $string ) as $words ) {

      $response .= ucfirst( $words );
    }
  }

  return isset( $response ) ? $response : ucfirst( $string );
}

/**
 * Prints the registry array or a single element
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $name Name of the registry's node to print
 * @return array
 */
function tf_registry_print( $name = '' )
{
  echo ( PHP_SAPI != 'cli' ? "<pre>\n" : '' ),
       var_export( $name && isset( $GLOBALS['_REGISTRY'][$name] ) ?
                   $GLOBALS['_REGISTRY'][$name] : $GLOBALS['_REGISTRY'], true ),
       ( PHP_SAPI != 'cli' ? "</pre>\n" : '' ), "\n";
}

/**
 * Sets a variable into the registry
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $name Name of variable
 * @param mixed $value Value of variable
 */
function tf_conf_set ( $name, $value )
{
  $GLOBALS['_REGISTRY']['conf'][$name] = $value;
}

/**
 * Returns the value of a registry node
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $name Name of the node
 * @param string $subnode Subnode name to return
 */
function tf_conf_get ( $name, $subnode = false )
{
  if ( !isset( $GLOBALS['_REGISTRY']['conf'][$name] ) ) {

    tf_error( "[Framework] Variable '$name' doesn't exists in configuration" );

    return false;
  }

  if ( $subnode ) {

    if ( !isset( $GLOBALS['_REGISTRY']['conf'][$name][$subnode] ) ) {

      tf_error( "[Framework] Variable '$name'.'$subnode' doesn't exists" );

      return false;
    }

    return $GLOBALS['_REGISTRY']['conf'][$name][$subnode];
  }

  return $GLOBALS['_REGISTRY']['conf'][$name];
}

/**
 * Set an error message into execution log and it's capable of stop execution
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $error_message Error message
 * @param boolean $force_die Flag to indicate if application must stop
 */
function tf_error ( $error_message, $force_die = false )
{
  tf_log( $error_message, true );

  if ( $force_die ) {

    tf_log_print( true );

    die;
  }
}

function tf_app_error_set ( $error_message_id, $error_message )
{
  tf_log( '['.$error_message_id.'] '.$error_message );

  $GLOBALS['_REGISTRY']['app_error'][$error_message_id] = $error_message;
}

function tf_app_error ( $error_message_id )
{
  return isset( $GLOBALS['_REGISTRY']['app_error'][$error_message_id] ) ?
         $GLOBALS['_REGISTRY']['app_error'][$error_message_id] : false;
}

/**
 * Prints log registry if debug mode in on
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param boolean $force Flag to force print of log
 */
function tf_log_print ( $force = false )
{
  if ( ( isset( $GLOBALS['_REGISTRY']['request']['debug'] ) &&
         $GLOBALS['_REGISTRY']['request']['debug'] ) || $force ) {

    tf_registry_print( 'log' );
  }
}

/**
 * Returns a value from request registry or false if not found
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $var_name Nameof variable
 * @param boolean Flag to sanitize value, default is false
 * @todo SANITIZE VALUE
 */
function tf_request ( $var_name, $satinize = false )
{
  if ( isset( $GLOBALS['_REGISTRY']['request'][$var_name] ) &&
       $GLOBALS['_REGISTRY']['request'][$var_name] ) {

    return tf_cast_type( $GLOBALS['_REGISTRY']['request'][$var_name] );
  }

  return false;
}

/**
 * Set an message in log registry
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $log_message
 * @param boolean $force Flag to force to set message in log registry
 */
function tf_log( $log_message, $force = false )
{
  if ( ( isset( $GLOBALS['_REGISTRY']['request']['debug'] ) &&
         $GLOBALS['_REGISTRY']['request']['debug'] ) || $force ) {

    if ( !isset( $GLOBALS['_REGISTRY']['log'] ) ) {

      $GLOBALS['_REGISTRY']['log'] = array();
    }

    $GLOBALS['_REGISTRY']['log'][] = $log_message;
  }
}

/**
 * Returns the type of the provided $value
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param mixed $value
 * @return mixed
 */
function tf_cast_type ( $value )
{
  if ( is_numeric( $value ) ) {

    // float
    if ( preg_match( '/[0-9]*[\.][0-9]*/', $value ) ) {

      $value = (float) $value;

    // integer
    } else {

      // Integer overflow on a 32-bit system
      if ( $value < 2147483647 ) {

        $value = (int) $value;
      }
    }
  }
  if ( $value ) {

    // if array be recursive
    if ( is_array( $value ) ) {

      foreach ( $value as $k => $v ) {

        $value[$k] = tlalokes_core_get_type( $v );
      }

    // boolean
    } else {

      if ( is_bool( $value ) ) {

        $value = $value;

      } elseif (  $value == 'false' || $value == 'FALSE' ) {

        $value = false;

      } elseif ( $value == 'true' || $value == 'TRUE' ) {

        $value = true;
      }
    }
  }

  return $value;
}

/**
 * Parses a DocComment string
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @copyright Copyright (c) 2011, Basilio Briceno
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $doc docComment block string
 * @return array Annotation array
 */
function tf_annotation_parser ( $doc )
{
  // parse doc comment to get annotation's content
  preg_match_all( '/@([a-zA-Z0-9_\-]*)\s*\(\s*(.*)\s*\)/', $doc, $annotations );

  // iterate annotations
  foreach ( $annotations[2] as $key => $annotation ) {

    // check if annotation's arguments have not assigned values
    if ( !strstr( $annotation, '=' ) ) {

      // split properties simply by ,
      $arguments = explode( ',', $annotation );

    // if annotation's arguments have assigned values split them by regex
    } else {

      // split arguments by , usign regex
      $arguments = preg_split( '/[\"|\'],\s?/', $annotation );
    }

    // iterate annotation's arguments
    foreach( $arguments as $argument ) {

      // set name anf value of argument removing quotes and whitespaces
      list( $name, $value ) = explode( '=', str_replace( array( "'", '"', " " ),
                                                         '', $argument ) );

      // if argument's value have more than one subvalue split them by ;
      if ( count( $subvalues = explode( ';', $value ) ) > 1 ) {

        // iterate subvalues
        foreach ( $subvalues as $subvalue ) {

          // split subvalue by :
          list( $subvalue_name, $subvalue_value ) = explode( ':', $subvalue );

          // set subvalue array
          if ( $subvalue_value ) {

            $subvalue_array[$subvalue_name] = $subvalue_value;
          }
        }

        // set subvalue array as value
        $value = $subvalue_array;
        unset( $subvalue_array );

      // if argument's value have just one subvalue
      } else {

        // iterate subvalues
        foreach ( $subvalues as $subvalue ) {

          // if subvalue have :
          if ( strstr( $subvalue, ':' ) ) {

            // split subvalue by :
            list( $subvalue_name, $subvalue_value ) = explode( ':', $subvalue );

            // set subvalue array
            if ( $subvalue_value ) {

              $subvalue_array[$subvalue_name] = $subvalue_value;
            }
          }
        }

        // set subvalue array as value
        if ( isset( $subvalue_array  ) ) {

          $value = $subvalue_array;
          unset( $subvalue_array );
        }
      }

      // set value of arguments in annotations response array
      $response[$annotations[1][$key]][$name] = $value;
      unset( $value );
    }
  }

  return isset( $response ) ? $response : false;
}

/**
 * Saves upload files from their temporal path to the configured one
 *
 * @author Basilio Briceno <bbh@tlalokes.org>
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $input_name If not set it will try to save everything in _FILES
 * @param array $filter_rule Example: type => pdf, size => 1024
 * @return boolean Returns TRUE if file saved, FALSE if error
 */
function tf_fileup_save ( $input_name = 'all', $filter_rule = false )
{
  $path = realpath( '.' ) .'/'. tf_conf_get( 'default', 'uploads' );

  // check if _FILES contains an element
  if ( count( $_FILES ) < 1 ) {

    tf_error( '[Upload] No files to save' );

    return false;

  } else {

    // save everything in _FILES
    if ( $input_name == 'all' ) {

      foreach ( $_FILES as $key => $file ) {

        // check file size
        if ( $file['size'] > 1 ) {

          $save_flag = true;

          if ( $filter_rule ) {

            $save_flag = tf_fileup_filter( $key, $filter_rule );
          }

          if ( $save_flag ) {

            // try to copy file to destination
            if ( !@copy( $file['tmp_name'], $path.$file['name'] ) ) {

              tf_error( '[Upload] Cannot write ('.$input_name.') into '.$path );
            }

            tf_log( 'Upload: File ('. $file['name'] .') written into '. $path );
          }

          unset( $save_flag );
        }
      }
    }

    // check file existance
    if ( !isset( $_FILES[$input_name] ) ) {

      tf_error( '[Upload] Required input ('.$input_name.') not found' );

      return false;

    } else {

      // check file size
      if ( $_FILES[$input_name]['size'] > 1 ) {

        $save_flag = true;

        if ( $filter_rule ) {

          $save_flag = tf_fileup_filter( $input_name, $filter_rule );
        }

        if ( $save_flag ) {

          // try to copy file to destination
          if ( !@copy( $_FILES[$input_name]['tmp_name'],
                       $path.$_FILES[$input_name]['name'] ) ) {

            tf_error( '[Upload] Cannot write ('. $input_name .') into '.$path );

            return false;
          }

          tf_log( 'Upload: File ('. $_FILES[$input_name]['name'] .
                  ') written into '. $path );

          return true;

        } else {

          return false;
        }

        unset( $save_flag );
      }
    }
  }
}

/**
 * Filters properties of a file upload
 *
 * @author Basilio Briceño <bbh@tlalokes.org>
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $input_name
 * @param array $filter_rule Example: type => pdf, size => 1024
 * @return boolean Return TRUE if file pass filter, FALSE if error
 */
function tf_fileup_filter ( $input_name, Array $filter_rule )
{
  // check type
  if ( isset( $filter_rule['type'] ) ) {

    // iterate types to filter
    foreach ( explode( ',', $filter_rule['type'] ) as $filter_type ) {

      list( $type, $format ) = explode( '/', $_FILES[$input_name]['type'] );

      if ( $format != $filter_type ) {

        $type_flag = false;

        tf_app_error_set( 'Upload filter type', "Invalid type ($filter_type)" );

        unset( $format );

      } else {

        $type_flag = true;
        break;
      }
      unset( $type );
    }

    if ( !$type_flag ) {
      unset( $type_flag );
      return false;
    }
  }

  // check size
  if ( isset( $filter_rule['size'] ) ) {

    if ( $_FILES[$input_name]['size'] > $filter_rule['size'] ) {

      tf_app_error_set( 'Upload filter size', 'File size ('.
                        round( $_FILES[$input_name]['size'] / 1024, 2 ) .
                        'K) exceeds requirement' );

      return false;
    }
  }

  return true;
}

/**
 * Returns filename from input by name
 *
 * @author Basilio Briceño <bbh@tlalokes.org>
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @param string $input_name
 * @return mixed Filename string or FALSE if input doesn't exists
 */
function tf_fileup_getname ( $input_name )
{
  return isset( $_FILES[$input_name] ) ? $_FILES[$input_name]['name'] : false;
}

/**
 * Prints the debug var for URIs if debug was set previously
 *
 * @author Basilio Briceño <bbh@tlalokes.org>
 * @license http://www.gnu.org/licenses/lgpl.html GNU LGPL
 */
function tf_debug_var ()
{
  if ( isset( $GLOBALS['_REGISTRY']['request']['debug'] ) &&
       $GLOBALS['_REGISTRY']['request']['debug'] ) {

    echo '&debug=1';
  }
}
