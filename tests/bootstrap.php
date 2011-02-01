<?php
/**
 * Define constants and load files for tests.
 * 
 * Example for creating skeleton test classes
 * phpunit --bootstrap ../tests/bootstrap.php --skeleton-test RSS
 */
$path_root = dirname(dirname(__FILE__));
define('PATH_TEST_DATA', $path_root . '/tests/_data');
require $path_root . '/wcc/WCC.php';