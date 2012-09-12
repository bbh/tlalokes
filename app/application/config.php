<?php

$c = array();

// Default
$c['default']['controller'] = 'example';
$c['default']['theme'] = 'default';
$c['default']['locale'] = 'eng';
$c['default']['charset'] = 'utf8';
$c['default']['timezone'] = 'America/Monterrey';
$c['default']['uploads'] = 'uploads/';

if ( !isset( $_ENV['application_environment'] ) ) {

  tf_error( '[Configuration] Environment for application required', true );

} else {

  if ( $_ENV['application_environment'] == "development" ) {

    // DSN
    $c['dsn']['default']['type'] = 'mysql';
    $c['dsn']['default']['driver'] = 'mysqli';
    $c['dsn']['default']['host'] = 'localhost';
    $c['dsn']['default']['name'] = 'tf_example';
    $c['dsn']['default']['username'] = 'root';
    $c['dsn']['default']['password'] = '';
    $c['dsn']['default']['options'] = null;

    // module example
    $c['module']['example'] = true;

  } else {

    tf_error( '[Configuration] Enviroment for application not found', true );
  }
}
