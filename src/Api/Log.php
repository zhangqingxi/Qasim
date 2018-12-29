<?php
/**
 * 日志类
 * Class Log
 * @auther Qasim <1575073791@163.com>
 * @since 1.0 2018-04-25
 */
defined('QASIM') or exit('Access Denied');
class Log
{

    private static $handle = null;

    public static function Init($filepath = null,$level = 15)
    {
        $logDir = dirname($filepath);
        if (!is_dir($logDir))
        {
            mkdir($logDir, 0777, true);
        }
        self::$handle = fopen($filepath, 'a');
    }

     public static function DEBUG($msg)
    {
        self::write($msg ,1);
    }

    public static function WARN($msg)
    {
        self::write($msg ,4);
    }

    public static function ERROR($msg)
    {
        $debugInfo = debug_backtrace();
        $stack = "[";
        foreach($debugInfo as $key => $val){
            if(array_key_exists("file", $val)){
                $stack .= ",file:" . $val["file"];
            }
            if(array_key_exists("line", $val)){
                $stack .= ",line:" . $val["line"];
            }
            if(array_key_exists("function", $val)){
                $stack .= ",function:" . $val["function"];
            }
        }
        $stack .= "]";
        self::write($stack . $msg ,8);
    }

    public static function INFO($msg)
    {
        self::write($msg ,2);
    }

    private static function getLevelStr($level)
    {
        switch ($level)
        {
            case 1:
                return 'debug';
                break;
            case 2:
                return 'info';
                break;
            case 4:
                return 'warn';
                break;
            case 8:
                return 'error';
                break;
            default:

        }
    }

    private function write($msg , $level = 1)
    {
        $msg = '['.date('Y-m-d H:i:s').']['.self::getLevelStr($level).'] '.$msg."\n";
        fwrite(self::$handle , $msg);
        //手动销毁
        self::destruct();
    }

    private static function destruct()
    {
        fclose(self::$handle);
    }

}