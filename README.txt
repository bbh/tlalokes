                           Tlalokes Framework
                           Version 2.0 (alpha)

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

  PHP 5.3.x (do not uses namespaces, requires testing on 5.2.x)
  If you need database connections: use PDO binary extension installed (native driver recommended)
  If you require CLI execution: enable PHP CLI mode
  Tested with Apache 2.x and Nginx

DOCUMENTATION

 - Documentation for version 2.0 is in progress.
 - You can test it from the CLI by locating yourself in the htdocs directory and executing 'php index.php'
 - Documentation for version 1.0 is in http://wiki.tlalokes.org

 STRUCTURE

 When you check into the Tlalokes, you will see this structure

 + framework. Contains the frameworks required files.
 + example. Contains an example of the structure required to use the framework.
  \ htdocs. Contains the files to be exposed on the Web and the index file to load the framework
   |- css
   |- img
   |- js
   \ application. This directory contains the structure of your application.
    | controller. Contains the classes used as controllers.
    \ model. Contains the files used as models.
     |- business. Contains the files where the business logic must be allocated. 
     |- vendor.
    \ view. Contains the views files and structure.
     |- layout. Contains the layouts files used to display a view
     |- block. Contains the blocks for the layouts.
    \ _misc.
     |- locale.
     |- lib.
     |- sql.
     |- tmp.

IF SOMETHING GOES WRONG:

  Contact the author at bbh@tlalokes.org or the twitter accout @tlalokes

WARNING

  2.0 alpha is a development version, right now is not for production.

CREDITS

  Thanks to Simon Martinez-Arriaga <simonm64@gmail.com> for the testing & fixes
