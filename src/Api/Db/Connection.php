<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Qasim\Db;


use Qasim\Log;

class Connection
{

    const PARAM_FLOAT          = 21;

    protected static $instance = [];

    //PDOStatement PDO操作实例
    protected $PDOStatement;

    //当前SQL指令
    protected $queryStr = '';

    // 返回或者影响记录数
    protected $numRows = 0;

    // 事务指令数
    protected $transTimes = 0;

    // 错误信息
    protected $error = '';

    //数据库连接ID 支持多个连接
    protected $links = [];

    //当前连接ID
    protected $linkID;
    protected $linkRead;
    protected $linkWrite;

    // 查询结果类型
    protected $fetchType = \PDO::FETCH_ASSOC;

    // 字段属性大小写
    protected $attrCase = \PDO::CASE_LOWER;

    // 监听回调
    protected static $event = [];

    // 数据表信息
    protected static $info = [];

    // 数据库连接参数配置
    protected $config = [
        // 数据库类型
        'type'            => '',
        // 服务器地址
        'hostname'        => '',
        // 数据库名
        'database'        => '',
        // 用户名
        'username'        => '',
        // 密码
        'password'        => '',
        // 端口
        'hostport'        => '',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => '',
        // 数据库调试模式
        'debug'           => false,
        // 是否需要进行SQL性能分析
        'sql_explain'     => false,
        // Builder类
        'builder'         => '',
        // Query类
        'query'           => '\\Qasim\\Db\\Query',
        // 是否需要断线重连
        'break_reconnect' => false,
        // 断线标识字符串
        'break_match_str' => [],
    ];

    //PDO连接参数
    protected $params = [
        // PDO::CASE_NATURAL          - 保留数据库驱动返回的列名
        // PDO::CASE_LOWER            - 强制列名小写
        // PDO::CASE_UPPER            - 强制列名大写
        \PDO::ATTR_CASE              => \PDO::CASE_NATURAL,    //强制列名为指定的大小写
        //PDO::ERRMODE_SILENT         - 仅设置错误代码。
        //PDO::ERRMODE_WARNING        -引发 E_WARNING 错误
        //PDO::ERRMODE_EXCEPTION      - 抛出 exceptions 异常。
        \PDO::ATTR_ERRMODE           => \PDO::ERRMODE_EXCEPTION,//错误报告   异常(需捕获)
        //PDO::NULL_NATURAL           - 不转换
        //PDO::NULL_EMPTY_STRING      - 将空字符串转换成 NULL
        //PDO::NULL_TO_STRING         - 将 NULL 转换成空字符串
        \PDO::ATTR_ORACLE_NULLS      => \PDO::NULL_NATURAL,//转换 NULL 和空字符串
        \PDO::ATTR_STRINGIFY_FETCHES => false,//提取的时候将数值转换为字符串 默认false
        \PDO::ATTR_EMULATE_PREPARES  => false,//启用或禁用预处理语句的模拟   默认false
    ];

    // 服务器断线标识字符
    protected $breakMatchStr = [
        'server has gone away',
        'no connection to the server',
        'Lost connection',
        'is dead or not enabled',
        'Error while sending',
        'decryption failed or bad record mac',
        'server closed the connection unexpectedly',
        'SSL connection has been closed unexpectedly',
        'Error writing data to the connection',
        'Resource deadlock avoided',
        'failed with errno',
    ];

    // 绑定参数
    protected $bind = [];


    private function __construct($config = [])
    {

        //判断php是否支持redis扩展
        if (extension_loaded('pdo')) {

            $this->config = array_merge($this->config, $config);

            if (empty($this->config['dsn'])) {

                $this->config['dsn'] = self::parseDsn($this->config);

            }

            return new \PDO($this->config['dsn'], $this->config['username'], $this->config['password'], $this->params);

        } else {

            throw new \PDOException('not support: pdo');

        }


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
     * 取得数据库连接类实例
     * @access public
     * @param  mixed         $config 连接配置
     * @param  bool|string   $name 连接标识 true 强制重新连接
     * @return Connection
     * @throws \InvalidArgumentException
     */
    public static function instance($config = [], $name = false)
    {

        if (false === $name) {

            $name = md5(serialize($config));

        }

        if (true === $name || !isset(self::$instance[$name])) {

            if (empty($config['type'])) {

                throw new \InvalidArgumentException('Undefined db type');

            }

            // 记录初始化信息
            Log::sql('[ DB ] INIT ' . $config['type'], 'log');

            if (true === $name) {

                $name = md5(serialize($config));

            }

            self::$instance[$name] = new self($config);

        }

        return self::$instance[$name];

    }


    /**
     * 获取数据库的配置参数
     * @access public
     * @param  string $config 配置名称
     * @return mixed
     */
    public function getConfig($config = '')
    {

        return $config ? $this->config[$config] : $this->config;

    }



}
