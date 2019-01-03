<?php
namespace Qasim;

/**
 * Db类
 * @name Db
 * @author Qasim <15750783791@163.com>
 * @time 2019/1/2 0002 下午 3:27
 * @since 1.0
 * @package Qasim
 */
class Db
{

    //PDO操作实例
    private static $PDOStatement;

    //pdo类
    private static $db;

    //实例化的对象,单例模式.
    private static $_instance = [];

    //当前SQL指令
    private static $sqlStr = '';

    // 数据库连接参数配置
    private static $config = [
        // 数据库类型
        'type'           => '',
        // 服务器地址
        'hostname'       => '',
        // 数据库名
        'database'       => '',
        // 用户名
        'username'       => '',
        // 密码
        'password'       => '',
        // 端口
        'hostport'       => '',
        // 连接dsn
        'dsn'            => '',
        // 数据库连接参数
        'params'         => [],
        // 数据库编码默认采用utf8
        'charset'        => 'utf8',
        // 数据库表前缀
        'prefix'         => ''
    ];

    //PDO连接参数
    private static $params = [
        \PDO::ATTR_PERSISTENT        => false,//是否数据库长连接  默认false
        // PDO::CASE_NATURAL          - 保留数据库驱动返回的列名
        // PDO::CASE_LOWER            - 强制列名小写
        // PDO::CASE_UPPER            - 强制列名大写
        \PDO::ATTR_CASE              => \PDO::CASE_NATURAL,	//强制列名为指定的大小写
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

    private function __construct($options = [])
    {

        //判断php是否支持redis扩展
        if (extension_loaded('pdo')) {

            self::$config = array_merge(self::$config, $options);

            if (empty(self::$config['dsn'])) {

                self::$config['dsn'] = self::parseDsn(self::$config);

            }

            self::$db = new \PDO(self::$config['dsn'], self::$config['username'], self::$config['password'], self::$params);

        } else {

            throw new \PDOException('not support: pdo');

        }

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
     * 执行 SQL 语句，返回PDOStatement对象
     * @param string $sql sql语句
     * @param array $bind 绑定参数
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/3 0003 上午 11:04
     * @version 1.0
     */
    public static function query($sql = '' , $bind = [])
    {

        try {

            self::$sqlStr = self::getRealSql($sql,  $bind);

            echo self::$sqlStr;exit;

            //释放前次的查询结果
            if (!empty(self::$PDOStatement)) {

                self::free();

            }

            // 预处理
            self::$PDOStatement = self::$db->prepare($sql);

            // 参数绑定
            self::bindValue($bind);

            // 执行查询
            $result = self::$PDOStatement->execute();
//            // 调试结束
//            $this->debug(false);
//            $procedure = in_array(strtolower(substr(trim($sql), 0, 4)), ['call', 'exec']);
//            return $this->getResult($class, $procedure);
        } catch (\PDOException $e) {

            throw new \PDOException($e, self::$config, self::$sqlStr);

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
     * 根据参数绑定组装最终的SQL语句 便于调试
     * @access public
     * @param string    $sql 带参数绑定的sql语句
     * @param array     $bind 参数绑定列表
     * @return string
     */
    private static function getRealSql($sql, array $bind = [])
    {

        if ($bind) {

            foreach ($bind as $key => $val) {

                $value = is_array($val) ? $val[0] : $val;

                $type  = is_array($val) ? $val[1] : \PDO::PARAM_STR;//表示 SQL 中的 CHAR、VARCHAR 或其他字符串类型。

                if (\PDO::PARAM_STR == $type) {

                    $value = self::quote($value);

                } elseif (\PDO::PARAM_INT == $type && '' === $value) {

                    $value = 0;

                }
                // 判断占位符
                $sql = is_numeric($key) ?
                    substr_replace($sql, $value, strpos($sql, '?'), 1) :
                    str_replace(
                        [':' . $key . ')', ':' . $key . ',', ':' . $key . ' '],
                        [$value . ')', $value . ',', $value . ' '],
                        $sql . ' ');
            }
        }
        return rtrim($sql);
    }

    /**
     * 参数绑定
     * 支持 ['name'=>'value','id'=>123] 对应命名占位符
     * 或者 ['value',123] 对应问号占位符
     * @access public
     * @param array $bind 要绑定的参数列表
     * @return void
     * @throws \think\Exception
     */
    private static function bindValue(array $bind = [])
    {
//        foreach ($bind as $key => $val) {
//            // 占位符
//            $param = is_numeric($key) ? $key + 1 : ':' . $key;
//            if (is_array($val)) {
//                if (PDO::PARAM_INT == $val[1] && '' === $val[0]) {
//                    $val[0] = 0;
//                }
//                $result = $this->PDOStatement->bindValue($param, $val[0], $val[1]);
//            } else {
//                $result = $this->PDOStatement->bindValue($param, $val);
//            }
//            if (!$result) {
//                throw new BindParamException(
//                    "Error occurred  when binding parameters '{$param}'",
//                    $this->config,
//                    $this->queryStr,
//                    $bind
//                );
//            }
//        }
    }

    /**
     * SQL指令安全过滤
     * @param string $str SQL字符串
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/3 0003 上午 11:53
     * @version 1.0
     * @return string
     */
    private static function quote($str = '')
    {
        return self::$db->quote($str);
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
     * 析构方法
     * @access public
     */
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