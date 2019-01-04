<?php

namespace Qasim\Db;

use Qasim\Log;

/**
 * Db类
 * @name Db
 * @author Qasim <15750783791@163.com>
 * @time 2019/1/2 0002 下午 3:27
 * @since 1.0
 * @package Qasim
 * @method Query name(string $name) static 指定数据表（不含前缀）
 */
class Db
{

    /**
     * 当前数据库连接对象
     */
    protected static $connection;

    /**
     * 数据库配置
     * @var array
     */
    protected static $config = [];

    /**
     * 查询次数
     * @var integer
     */
    public static $queryTimes = 0;

    /**
     * 执行次数
     * @var integer
     */
    public static $executeTimes = 0;

    /**
     * 配置
     * @access public
     * @param  mixed $config
     * @return void
     */
    public static function init($config = [])
    {

        self::$config = array_merge(self::$config, $config);

    }

    /**
     * 切换数据库连接
     * @param array $config 连接配置
     * @param bool $name    连接标识 true 强制重新连接
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/4 0004 下午 6:13
     * @version 1.0
     * @return mixed    返回查询对象实例
     */
    public static function connect($config = [], $name = false)
    {

        // 解析配置参数
        $options = self::parseConfig($config ?: self::$config);

        // 创建数据库连接对象实例
        self::$connection = Connection::instance($options, $name);

        return new Query(self::$connection);

    }


    /**
     * 数据库连接参数解析
     * @access private
     * @param  mixed $config
     * @return array
     */
    private static function parseConfig($config)
    {
        if (is_string($config) && false === strpos($config, '/')) {
            // 支持读取配置参数
            $config = isset(self::$config[$config]) ? self::$config[$config] : self::$config;
        }

        $result = is_string($config) ? self::parseDsnConfig($config) : $config;

        if (empty($result['query'])) {
            $result['query'] = self::$config['query'];
        }

        return $result;
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @access private
     * @param  string $dsnStr
     * @return array
     */
    private static function parseDsnConfig($dsnStr)
    {
        $info = parse_url($dsnStr);

        if (!$info) {
            return [];
        }

        $dsn = [
            'type'     => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'hostname' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '',
            'database' => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset'  => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];

        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dsn['params'] = [];
        }

        return $dsn;
    }

    /**
     * 得到实例化的对象.
     * 为每个数据库建立一个连接
     * 如果连接超时，将会重新建立一个连接
     * @param array $options
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/12 0012 下午 5:55
     * @version 1.0
     * @return mixed
     */
    public static function getInstance($options = [])
    {

        if (!(self::$_instance instanceof self)) {

            self::$_instance = new self($options);

        }

        return self::$_instance;

    }

    //防止克隆
    private function __clone()
    {
    }

    /**
     * 初始化数据库
     * @param $method
     * @param $params
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/4 0004 下午 3:42
     * @version 1.0
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {

        return call_user_func_array([self::connect(), $method], $params);

    }

    /**
     * 解析pdo连接的dsn信息
     * @param $config
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/3 0003 上午 10:55
     * @version 1.0
     * @return string
     */
    private static function parseDsn($config)
    {

        $dsn = 'mysql:dbname=' . $config['database'] . ';host=' . $config['hostname'];

        if (!empty($config['hostport'])) {

            $dsn .= ';port=' . $config['hostport'];

        } elseif (!empty($config['socket'])) {

            $dsn .= ';unix_socket=' . $config['socket'];

        }

        if (!empty($config['charset'])) {

            $dsn .= ';charset=' . $config['charset'];

        }

        return $dsn;

    }

    /**
     * 释放查询结果
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/3 0003 上午 11:25
     * @version 1.0
     */
    public static function free()
    {

        self::$PDOStatement = null;

    }

    /**
     * 关闭连接
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/3 0003 上午 11:48
     * @version 1.0
     */
    public static function close()
    {

        self::$db = null;

    }


    /**
     * 自动释放资源
     */
    public function __destruct()
    {

        // 释放查询
        if (!empty(self::$PDOStatement)) {

            self::free();

        }

        // 关闭连接
        self::close();

    }

}