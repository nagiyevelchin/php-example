<?php

namespace NE\System {

    /**
     *
     * @author elchin
     * 9/25/15 12:27 PM
     */
    class Config {

        private static $conf = [];

        public static function load($filename) {
            if (file_exists($filename) == false) {
                trigger_error("The config file $filename is not exists", E_USER_ERROR);
            }
            if (is_readable($filename) == false) {
                trigger_error("The config file $filename is not readable", E_USER_ERROR);
            }
            self::$conf = require $filename;
        }

        public static function __callStatic($method, $args) {
            if (preg_match('/^([gs]et)(.*)$/', $method, $match)) {
                $property = $match[2];
                if (isset(self::$conf[lcfirst($property)])) {
                    $property = lcfirst($property);
                }
                if (isset(self::$conf[$property])) {
                    switch ($match[1]) {
                        case 'get':
                            return self::$conf[$property];
                            break;
                        case 'set':
                            self::$conf[$property] = $args[0];
                            break;
                    }
                } else {
                    throw new \InvalidArgumentException("Property {$property} doesn't exist.");
                }
            }
        }

    }

}
