<?php
// Global path/url constants for relocated /mp application folder
if (!defined('BASE_PATH')) {
    // /.../public_html/mp (one level above this file's directory is system/includes)
    define('BASE_PATH', realpath(__DIR__ . '/../../'));
}
if (!defined('BASE_URL')) {
    // Web base URL for the mp folder
    define('BASE_URL', '/mp');
} 