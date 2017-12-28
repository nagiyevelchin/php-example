<?php

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Exception('The NE CMS 2.0 requires PHP version 5.4 or higher.');
}
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'NE\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/../';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class);

    $pos = strrpos($relative_class, '\\');
    $class_name = substr($relative_class, $pos + 1);
    // if the file exists, require it
    if (file_exists($file . '.php')) {
        require_once $file . '.php';
    } else if (file_exists($file . '.admin.php')) {
        require_once $file . '.admin.php';
    } else if (file_exists($file . '.user.php')) {
        require_once $file . '.user.php';
    } else {
        print($file . ' - not exists<br />');
    }
});
