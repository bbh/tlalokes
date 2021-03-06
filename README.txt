                           Tlalokes Framework
                              Version 2.0s
                           http://tlalokes.org

Copyright (c) 2011 Basilio Briceno <bbh@tlalokes.org>

These are the release notes for Tlalokes Framework version 2.0 (alpha).
Read them carefully, as they tell you what this is all about, and what to do
if something goes wrong.

WHAT IS TLALOKES?

  Tlalokes Framework 2.0 is a very well structured and fast PHP framework
  that helps you to write professional web applications that requires velocity.

  It is distributed under the GNU Lesser General Public License - see the
  accompanying LICENSE file for more details.

ON WHAT IT RUNS?

  You can run this on any hardware that runs PHP 5.2.x or higher.

REQUIREMENTS

  If you need database connections: use PDO binary extension installed (native driver recommended)
  If you require CLI execution: enable PHP CLI mode
  Tested on Apache 2.x and Nginx

DOCUMENTATION

 - Documentation for version 2.0 is in progress.
 - You can test it from the CLI by locating yourself in the htdocs directory and executing 'php index.php'
 - Documentation for version 1.0 is in http://wiki.tlalokes.org

 STRUCTURE

 When you check into Tlalokes 2.0, you will see this structure

 |- css. Put your cascade style sheets in this directory.
 |- img. Your application's images
 |- js. For your javascripts
 |- uploads. If using a UNIX-like OS, set write permissions to your web-server's user for this directory
 \ app. IMPORTANT. We recommend to move your app directory to other NON-PUBLIC level in your filesystem
  | framework. Contains the frameworks required files.
  \ application. This directory contains the structure of your application.
   | controller. Contains the classes used as controllers.
   \ model. Contains the files used as models.
    |- business. Contains the files where the business logic must be allocated.
    |- vendor. Contains the business logic produced by vendors.
   \ view. Contains the views files and structure.
    \ theme. Here yon can set your themes.
     \ default. Default theme directory.
      |- layout. Layouts files used in your theme to display a view
      |- block. Blocks used in your theme's layouts.
   \ _misc. Miscelaneous files
    |- locale.
    |- lib. Third party libraries.
    |- sql. Your SQL files (remove this for production).
    |- tmp. Any temporal file or log.

IF SOMETHING GOES WRONG:

  Contact the author at bbh@tlalokes.org or the twitter accout @tlalokes

WARNING

  2.0 alpha is a development version, test it and report :)

CREDITS

  Thanks to Simon Martinez-Arriaga <simonm64@gmail.com> for the testing & fixes
