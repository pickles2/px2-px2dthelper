<?php
$_SERVER['PHP_SELF'] = __FILE__;
$_SERVER['SCRIPT_NAME'] = __FILE__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
chdir(__DIR__);

@require_once( './../../../vendor/autoload.php' );
@require_once( './px-files/themes/pickles/php/theme.php' );
new picklesFramework2\pickles('./px-files/');
