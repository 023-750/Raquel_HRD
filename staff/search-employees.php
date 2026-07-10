<?php
/**
 * Redirects legacy search-employees URL to the new employees.php.
 */
header('Location: employees.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit();
