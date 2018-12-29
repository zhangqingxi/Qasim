<?php
/**
 * 自动加载类
 * Class Loader
 * @author Qasim <15750783791@163.com>
 * @since 1.0 2018-04-24
 */
defined('QASIM') or exit('Access Denied');
class Loader
{

    /**
     * 引入类
     * Autoload constructor.
     * @param $class
     */
    public static function autoload($class)
    {

        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $file = __DIR__ .DS. $path . EXT;

        if (file_exists($file))
        {

            include_once $file;

        }

    }

    /**
     * 注册自动加载
     * @param string $autoload
     */
    public static function register($autoload = "")
    {
        spl_autoload_register($autoload ? :  'self::autoload' , true , true);
    }

}