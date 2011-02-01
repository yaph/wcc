<?php
/**
 * Loads files required for tests to run
 * 
 * Example for creating skeleton test classes
 * phpunit --bootstrap ../tests/bootstrap.php --skeleton-test RSS
 */
$path_root = dirname(dirname(__FILE__));
$path_wcc = $path_root . '/wcc';
$path_test_data = $path_root . '/tests/_data';
require $path_wcc . '/WCC.php';
require $path_wcc . '/RSS.php';
require $path_wcc . '/SPARQL.php';
require $path_wcc . '/YouTube.php';

define('PATH_TEST_DATA', $path_test_data);