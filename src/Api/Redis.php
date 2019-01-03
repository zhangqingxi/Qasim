<?php

namespace Qasim;

/**
 * redis类
 * @name Redis
 * @author Qasim <15750783791@163.com>
 * @time 2018/12/12 0012 下午 5:45
 * @since 1.0
 * @package Qasim
 */
class Redis
{

    private static $redis;

    //当前数据库ID号
    protected $db_id = 0;

    //当前权限认证码
    protected $auth;

    /**
     * 实例化的对象,单例模式.
     */
    private static $_instance = [];

    //什么时候重新建立连接
    protected $expire_time;

    protected $host;

    protected $port;

    protected static $options = [
        'host'       => '127.0.0.1',//主机
        'port'       => 6379,//端口
        'auth'       => '',//密码权限验证
        'timeout'    => 0,//超时
        'select'     => 0,//数据库ID
        'expire'     => 0,//过期
        'persistent' => false//是否长连接
    ];

    private function __construct($options = [])
    {

        //判断php是否支持redis扩展
        if (extension_loaded('redis')) {

            self::$options = array_merge(self::$options, $options);

            self::$redis = new \Redis();

            $func = self::$options['persistent'] ? 'pconnect' : 'connect';

            self::$redis->$func(self::$options['host'], self::$options['port'], self::$options['timeout']);

            if (self::$options['auth']) {

                self::auth(self::$options['auth']);

            }

            if (self::$options['select']) {

                self::select(self::$options['select']);

            }

            self::$options['expire'] = time() + self::$options['timeout'];

        } else {

            throw new \BadFunctionCallException('not support: redis');

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

        //如果是一个字符串，将其认为是数据库的ID号。以简化写法。
        if (!is_array($options)) {

            $options = ['select' => $options];

        }

        if (!(self::$_instance instanceof self)) {

            self::$_instance = new self($options);

        } elseif (time() > self::$options['expire']) {

            self::close();

            self::$_instance = new self($options);

        }

        return self::$_instance;

    }

    //防止克隆
    private function __clone()
    {
    }

    /**
     * 执行原生的redis操作
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/12 0012 下午 6:52
     * @version 1.0
     * @return \Redis
     */
    public function getRedis()
    {

        return self::$redis;

    }

    //TODO*************** hash表操作函数********************

    /**
     * 得到hash表中一个字段的值
     * @param string $key 缓存key
     * @param string $field 字段
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/12 0012 下午 6:52
     * @version 1.0
     * @return string
     */
    public static function hGet($key, $field)
    {

        return self::$redis->hGet($key, $field);

    }

    /**
     * 为hash表设定一个字段的值
     * @param string $key 缓存key
     * @param string $field 字段
     * @param string $value 值
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/12 0012 下午 6:52
     * @version 1.0
     * @return bool|int
     */
    public static function hSet($key, $field, $value)
    {

        return self::$redis->hSet($key, $field, $value);

    }

    /**
     * 判断hash表中，指定field是不是存在
     * @param string $key 缓存key
     * @param string $field 字段
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/12 0012 下午 6:53
     * @version 1.0
     * @return bool
     */
    public static function hExists($key, $field)
    {

        return self::$redis->hExists($key, $field);

    }

    /**
     * 删除hash表中指定字段 ,支持批量删除
     * @param string $key 缓存key
     * @param string $field 字段
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/12 0012 下午 6:53
     * @version 1.0
     * @return bool|int
     */
    public static function hDel($key, $field)
    {

        $fieldArr = explode(',', $field);

        $delNum = 0;

        foreach ($fieldArr as $row) {

            $row = trim($row);

            $delNum += self::$redis->hDel($key, $row);

        }

        return $delNum;

    }

    /**
     * 返回hash表元素个数
     * @param string $key 缓存key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:17
     * @version 1.0
     * @return int
     */
    public static function hLen($key)
    {

        return self::$redis->hLen($key);

    }

    /**
     * 为hash表设定一个字段的值,如果字段存在，返回false
     * @param string $key 缓存key
     * @param string $field 字段
     * @param string $value 值
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:17
     * @version 1.0
     * @return bool
     */
    public static function hSetNx($key, $field, $value)
    {

        return self::$redis->hSetNx($key, $field, $value);

    }

    /**
     * 为hash表多个字段设定值
     * @param string $key 缓存key
     * @param string $value 字段
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:19
     * @version 1.0
     * @return bool
     */
    public static function hMSet($key, $value)
    {

        if (!is_array($value)) {

            return false;

        }

        return self::$redis->hMSet($key, $value);

    }

    /**
     * 获取hash表多个字段值
     * @param string $key 缓存key
     * @param array|string $field 值 string以','号分隔字段
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:22
     * @version 1.0
     * @return array
     */
    public static function hMGet($key, $field)
    {

        if (!is_array($field)) {

            $field = explode(',', $field);

        }

        return self::$redis->hMGet($key, $field);

    }

    /**
     * 为hash表字段累加，可以负数
     * @param string $key
     * @param string $field
     * @param int $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:25
     * @version 1.0
     * @return int
     */
    public static function hIncrBy($key, $field, $value)
    {

        $value = intval($value);

        return self::$redis->hIncrBy($key, $field, $value);

    }

    /**
     * 返回所有hash表的所有字段
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:28
     * @version 1.0
     * @return array
     */
    public static function hKeys($key)
    {

        return self::$redis->hKeys($key);

    }

    /**
     * 返回所有hash表的字段值，为一个索引数组
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:28
     * @version 1.0
     * @return array
     */
    public static function hVals($key)
    {

        return self::$redis->hVals($key);

    }

    /**
     * 返回所有hash表的字段值，为一个关联数组
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:28
     * @version 1.0
     * @return array
     */
    public static function hGetAll($key)
    {

        return self::$redis->hGetAll($key);

    }


    //TODO*************** 有序集合操作********************

    /**
     * 增加一个或多个元素，如果该元素已经存在，更新它的score值
     * 虽然有序集合有序，但它也是集合，不能重复元素，添加重复元素只会
     * 更新原有元素的score值
     * @param $key
     * @param $score
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:30
     * @version 1.0
     * @return int
     */
    public static function zAdd($key, $score, $value)
    {

        return self::$redis->zAdd($key, $score, $value);

    }

    /**
     * 给$value成员的值 增加$num,可以为负数
     * @param $key
     * @param $num
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:34
     * @version 1.0
     * @return float
     */
    public static function zIncrBy($key, $num, $value)
    {

        return self::$redis->zIncrBy($key, $num, $value);

    }

    /**
     * 从有序集合中删除指定的成员
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:37
     * @version 1.0
     * @return int
     */
    public function zRem($key, $value)
    {

        return self::$redis->zRem($key, $value);

    }

    /**
     * 取得特定范围内的排序元素,0代表第一个元素,1代表第二个以此类推。-1代表最后一个,-2代表倒数第二个...
     * @param $key
     * @param $start
     * @param $end
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:38
     * @version 1.0
     * @return array
     */
    public static function zRange($key, $start, $end)
    {

        return self::$redis->zRange($key, $start, $end);

    }

    /**
     * 返回key对应的有序集合中指定区间的所有元素。
     * 这些元素按照score从高到低的顺序进行排列。
     * 对于具有相同的score的元素而言，
     * 将会按照递减的字典顺序进行排列。
     * 该命令与ZRANGE类似，
     * 只是该命令中元素的排列顺序与前者不同。
     * @param $key
     * @param $start
     * @param $end
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:43
     * @version 1.0
     * @return array
     */
    public static function zRevRange($key, $start, $end)
    {

        return self::$redis->zRevRange($key, $start, $end);

    }

    /**
     * 返回key对应的有序集合中score介于min和max之间的所有元素（包哈score等于min或者max的元素）。
     * 元素按照score从低到高的顺序排列。如果元素具有相同的score，那么会按照字典顺序排列。
     * 可选的选项LIMIT可以用来获取一定范围内的匹配元素。
     * 如果偏移值较大，有序集合需要在获得将要返回的元素之前进行遍历，因此会增加O(N)的时间复杂度。
     * 可选的选项WITHSCORES可以使得在返回元素的同时返回元素的score，该选项自从Redis 2.0版本后可用。
     * @param $key
     * @param string $start
     * @param string $end
     * @param array $option
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:44
     * @version 1.0
     * @return array
     */
    public static function zRangeByScore($key, $start = '-inf', $end = "+inf", $option = [])
    {

        return self::$redis->zRangeByScore($key, $start, $end, $option);

    }

    /**
     * 返回key对应的有序集合中score介于min和max之间的所有元素（包哈score等于min或者max的元素）。
     * 元素按照score从低到高的顺序排列。如果元素具有相同的score，那么会按照字典顺序排列。
     * 可选的选项LIMIT可以用来获取一定范围内的匹配元素。
     * 如果偏移值较大，有序集合需要在获得将要返回的元素之前进行遍历，因此会增加O(N)的时间复杂度。
     * 可选的选项WITHSCORES可以使得在返回元素的同时返回元素的score，该选项自从Redis 2.0版本后可用。
     * @param $key
     * @param string $start
     * @param string $end
     * @param array $option
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:45
     * @version 1.0
     * @return array
     */
    public static function zRevRangeByScore($key, $start = '-inf', $end = "+inf", $option = [])
    {

        return self::$redis->zRevRangeByScore($key, $start, $end, $option);

    }

    /**
     * 返回key对应的有序集合中介于min和max间的元素的个数。
     * @param $key
     * @param $start
     * @param $end
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:46
     * @version 1.0
     * @return int
     */
    public static function zCount($key, $start, $end)
    {
        return self::$redis->zCount($key, $start, $end);
    }

    /**
     * 返回key对应的有序集合中member的score值。如果member在有序集合中不存在，那么将会返回nil。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:47
     * @version 1.0
     * @return float
     */
    public static function zScore($key, $value)
    {

        return self::$redis->zScore($key, $value);

    }

    /**
     * 返回key对应的有序集合中member元素的索引值，
     * 元素按照score从低到高进行排列。rank值（或index）是从0开始的，
     * 这意味着具有最低score值的元素的rank值为0。
     * 使用ZREVRANK可以获得从高到低排列的元素的rank（或index）。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:47
     * @version 1.0
     * @return int
     */
    public static function zRank($key, $value)
    {

        return self::$redis->zRank($key, $value);

    }

    /**
     * 返回key对应的有序集合中member元素的索引值，
     * 元素按照score从低到高进行排列。rank值（或index）是从0开始的，
     * 这意味着具有最低score值的元素的rank值为0。
     * 使用ZREVRANK可以获得从高到低排列的元素的rank（或index）。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:48
     * @version 1.0
     * @return int
     */
    public static function zRevRank($key, $value)
    {

        return self::$redis->zRevRank($key, $value);

    }

    /**
     * 移除key对应的有序集合中scroe位于min和max（包含端点）之间的所哟元素。
     * 从2.1.6版本后开始，区间端点min和max可以被排除在外，这和ZRANGEBYSCORE的语法一样。
     * @param $key
     * @param $start
     * @param $end
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:48
     * @version 1.0
     * @return int
     */
    public function zRemRangeByScore($key, $start, $end)
    {

        return self::$redis->zRemRangeByScore($key, $start, $end);

    }

    /**
     * 返回存储在key对应的有序集合中的元素的个数。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:50
     * @version 1.0
     * @return int
     */
    public static function zCard($key)
    {

        return self::$redis->zCard($key);

    }


    //TODO*************** 队列操作********************

    /**
     * 将一个或多个值插入到列表的尾部(最右边)。
     * 如果列表不存在，一个空列表会被创建并执行 rPush 操作。 当列表存在但不是列表类型时，返回一个错误。
     * 注意：在 Redis 2.4 版本以前的 rPush 命令，都只接受单个 value 值。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:51
     * @version 1.0
     * @return bool|int
     */
    public static function rPush($key, $value)
    {

        return self::$redis->rPush($key, $value);

    }

    /**
     * 用于将一个值插入到已存在的列表尾部(最右边)。如果列表不存在，操作无效。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:53
     * @version 1.0
     * @return int
     */
    public static function rPushx($key, $value)
    {

        return self::$redis->rPushx($key, $value);

    }

    /**
     * 将一个或多个值插入到列表头部。
     * 如果 key 不存在，一个空列表会被创建并执行 lPush 操作。
     * 当 key 存在但不是列表类型时，返回一个错误。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:54
     * @version 1.0
     * @return bool|int
     */
    public static function lPush($key, $value)
    {

        return self::$redis->lPush($key, $value);

    }

    /**
     * 将一个值插入到已存在的列表头部，列表不存在时操作无效。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:55
     * @version 1.0
     * @return mixed
     */
    public static function lPushx($key, $value)
    {

        return self::$redis->lPushx($key, $value);

    }

    /**
     * 返回列表的长度。
     * 如果列表 key 不存在，则 key 被解释为一个空列表，返回 0 。
     * 如果 key 不是列表类型，返回一个错误。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:55
     * @version 1.0
     * @return int
     */
    public static function lLen($key)
    {

        return self::$redis->lLen($key);

    }

    /**
     * 返回列表中指定区间内的元素，区间以偏移量 START 和 END 指定。
     * 其中 0 表示列表的第一个元素， 1 表示列表的第二个元素，以此类推。
     * 也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     * @param $key
     * @param $start
     * @param $end
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:56
     * @version 1.0
     * @return array
     */
    public static function lRange($key, $start, $end)
    {

        return self::$redis->lrange($key, $start, $end);

    }

    /**
     * 通过索引获取列表中的元素。
     * 也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     * @param $key
     * @param $index
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:57
     * @version 1.0
     * @return String
     */
    public static function lIndex($key, $index)
    {

        return self::$redis->lIndex($key, $index);

    }

    /**
     * 通过索引来设置元素的值。
     * 当索引参数超出范围，或对一个空列表进行 lSet 时，返回一个错误。
     * @param $key
     * @param $index
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:58
     * @version 1.0
     * @return bool
     */
    public static function lSet($key, $index, $value)
    {

        return self::$redis->lSet($key, $index, $value);

    }

    /**
     * 根据参数 COUNT 的值，移除列表中与参数 VALUE 相等的元素。
     * COUNT 的值可以是以下几种：
     *  count > 0 : 从表头开始向表尾搜索，移除与 VALUE 相等的元素，数量为 COUNT 。
     *  count < 0 : 从表尾开始向表头搜索，移除与 VALUE 相等的元素，数量为 COUNT 的绝对值。
     *  count = 0 : 移除表中所有与 VALUE 相等的值。
     * @param $key
     * @param $count
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 10:59
     * @version 1.0
     * @return int
     */
    public static function lRem($key, $count, $value)
    {

        return self::$redis->lRem($key, $value, $count);

    }

    /**
     * 移除并返回列表的第一个元素
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:00
     * @version 1.0
     * @return string
     */
    public static function lPop($key)
    {

        return self::$redis->lPop($key);

    }

    /**
     * 移除列表的最后一个元素，返回值为移除的元素。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:00
     * @version 1.0
     * @return string
     */
    public static function rPop($key)
    {

        return self::$redis->rPop($key);

    }

    //TODO*************** 字符串操作********************

    /**
     * 设置指定 key 的值
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:01
     * @version 1.0
     * @return bool
     */
    public static function set($key, $value)
    {

        return self::$redis->set($key, $value);

    }

    /**
     * 获取指定 key 的值。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:04
     * @version 1.0
     * @return bool|string
     */
    public static function get($key)
    {

        return self::$redis->get($key);

    }

    /**
     * 将值 value 关联到 key ，并将 key 的过期时间设为 seconds (以秒为单位)。
     * @param $key
     * @param $expire
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:04
     * @version 1.0
     * @return bool
     */
    public static function setex($key, $expire, $value)
    {

        return self::$redis->setex($key, $expire, $value);

    }

    /**
     * 只有在 key 不存在时设置 key 的值。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:05
     * @version 1.0
     * @return bool
     */
    public static function setnx($key, $value)
    {

        return self::$redis->setnx($key, $value);

    }

    /**
     * 批量设置一个或多个 key-value 对。
     * @param $arr
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:06
     * @version 1.0
     * @return bool
     */
    public static function mset($arr)
    {

        return self::$redis->mset($arr);

    }


    //TODO*************** 无序集合操作********************

    /**
     * 返回集合中的所有成员
     * 不存在的集合 key 被视为空集合。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:08
     * @version 1.0
     * @return array
     */
    public static function sMembers($key)
    {

        return self::$redis->sMembers($key);

    }

    /**
     * 返回给定所有集合的差集
     * 不存在的集合 key 将视为空集。
     * 差集的结果来自前面的 FIRST_KEY ,而不是后面的 OTHER_KEY1，也不是整个 FIRST_KEY OTHER_KEY1..OTHER_KEYN 的差集。
     * @param $key1
     * @param $key2
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:08
     * @version 1.0
     * @return array
     */
    public static function sDiff($key1, $key2)
    {

        return self::$redis->sDiff($key1, $key2);

    }

    /**
     * 将一个或多个成员元素加入到集合中，已经存在于集合的成员元素将被忽略。
     * 假如集合 key 不存在，则创建一个只包含添加的元素作成员的集合。
     * 当集合 key 不是集合类型时，返回一个错误。
     * 注意：在Redis2.4版本以前， sAdd 只接受单个成员值。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:12
     * @version 1.0
     */
    public static function sAdd($key, $value)
    {

        if (!is_array($value)) {

            $arr = array($value);

        } else {

            $arr = $value;

        }

        foreach ($arr as $row) {

            self::$redis->sAdd($key, $row);

        }

    }

    /**
     * 返回集合中元素的数量。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:13
     * @version 1.0
     * @return int
     */
    public static function sCard($key)
    {

        return self::$redis->sCard($key);

    }

    /**
     * 命令用于移除集合中的一个或多个成员元素，不存在的成员元素会被忽略。
     * 当 key 不是集合类型，返回一个错误。
     * 在 Redis 2.4 版本以前， sRem 只接受单个成员值。
     * @param $key
     * @param $value
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:13
     * @version 1.0
     * @return int
     */
    public static function sRem($key, $value)
    {
        return self::$redis->sRem($key, $value);
    }

    //TODO*************** 服务器|连接操作********************

    /**
     * 选择数据库
     * @param int $select 数据库ID号
     * @return bool
     */
    public static function select($select)
    {

        return self::$redis->select($select);

    }

    /**
     * 关闭服务器链接
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:34
     * @version 1.0
     */
    public static function close()
    {

        self::$redis->close();

    }

    /**
     * 向 Redis 服务器发送一个 PING ，如果服务器运作正常的话，会返回一个 PONG 。
     *  通常用于测试与服务器的连接是否仍然生效，或者用于测量延迟值。
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:52
     * @version 1.0
     * @return string
     */
    public static function ping()
    {

        return self::$redis->ping();

    }

    /**
     * 检测给定的密码和配置文件中的密码是否相符。
     * @param $auth
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 9:48
     * @version 1.0
     * @return bool
     */
    public static function auth($auth)
    {

        return self::$redis->auth($auth);

    }

    /**
     * 删除当前数据库的所有key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:15
     * @version 1.0
     * @return bool
     */
    public static function flushDB()
    {

        return self::$redis->flushDB();

    }


    /**
     * 返回关于 Redis 服务器的各种信息和统计数值
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:16
     * @version 1.0
     * @return string
     */
    public static function info()
    {

        return self::$redis->info();

    }

    /**
     * 将当前 Redis 实例的所有数据快照(snapshot)以 RDB 文件的形式保存到硬盘
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:18
     * @version 1.0
     * @return bool
     */
    public static function save()
    {

        return self::$redis->save();

    }

    /**
     * 异步保存当前数据库的数据到磁盘。
     * bgSave 命令执行之后立即返回 OK ，然后 Redis fork 出一个新子进程，
     * 原来的 Redis 进程(父进程)继续处理客户端请求，而子进程则负责将数据保存到磁盘，然后退出。
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:19
     * @version 1.0
     * @return bool
     */
    public static function bgSave()
    {

        return self::$redis->bgSave();

    }

    /**
     * 返回最近一次 Redis 成功将数据保存到磁盘上的时间，以 UNIX 时间戳格式表示。
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:20
     * @version 1.0
     * @return int
     */
    public static function lastSave()
    {

        return self::$redis->lastSave();

    }

    /**
     * 返回当前数据库的 key 的数量。
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:39
     * @version 1.0
     * @return int
     */
    public function dbSize()
    {

        return self::$redis->dbSize();

    }



    //TODO*************** 键操作********************

    /**
     * 查找所有符合给定模式 pattern 的 key
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:21
     * @version 1.0
     * @return array
     */
    public static function keys($key)
    {

        return self::$redis->keys($key);

    }

    /**
     * 删除已存在的键。不存在的 key 会被忽略。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:22
     * @version 1.0
     * @return int
     */
    public static function del($key)
    {

        return self::$redis->del($key);

    }

    /**
     * 检查给定 key 是否存在。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:22
     * @version 1.0
     * @return bool
     */
    public static function exists($key)
    {

        return self::$redis->exists($key);

    }

    /**
     * 设置 key 的过期时间，key 过期后将不再可用。单位以秒计。
     * @param $key
     * @param $expire
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:23
     * @version 1.0
     * @return bool
     */
    public static function expire($key, $expire)
    {

        return self::$redis->expire($key, $expire);

    }

    /**
     * 以秒为单位返回 key 的剩余过期时间。
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:26
     * @version 1.0
     * @return int
     */
    public static function ttl($key)
    {

        return self::$redis->ttl($key);

    }

    /**
     * 以UNIX 时间戳(unix timestamp)格式设置 key 的过期时间。key 过期后将不再可用。
     * @param $key
     * @param $time
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:26
     * @version 1.0
     * @return bool
     */
    public static function expireAt($key, $time)
    {

        return self::$redis->expireAt($key, $time);

    }

    /**
     * 从当前数据库中随机返回一个 key 。
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:42
     * @version 1.0
     * @return string
     */
    public static function randomKey()
    {

        return self::$redis->randomKey();

    }


    //TODO*************** 事务操作********************

    /**
     * 监视一个(或多个) key ，如果在事务执行之前这个(或这些) key 被其他命令所改动，那么事务将被打断
     * @param $key
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:43
     * @version 1.0
     */
    public static function watch($key)
    {

        self::$redis->watch($key);

    }

    /**
     * 取消 WATCH 命令对所有 key 的监视。
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:44
     * @version 1.0
     */
    public static function unwatch()
    {

        self::$redis->unwatch();

    }

    /**
     * 用于标记一个事务块的开始。
     * 事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令原子性(atomic)地执行。
     * 事务的调用有两种模式Redis::MULTI和Redis::PIPELINE，
     * 默认是Redis::MULTI模式，
     * Redis::PIPELINE管道模式速度更快，但没有任何保证原子性有可能造成数据的丢失
     * @param int $type
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:45
     * @version 1.0
     * @return \Redis
     */
    public static function multi($type = \Redis::MULTI)
    {

        return self::$redis->multi($type);

    }

    /**
     * 执行所有事务块内的命令。
     * 事务中任意命令执行失败，其余的命令依然被执行
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:48
     * @version 1.0
     * @return array
     */
    public static function exec()
    {

        return self::$redis->exec();

    }

    /**
     * 取消事务，放弃执行事务块内的所有命令。
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/13 0013 上午 11:50
     * @version 1.0
     */
    public static function discard()
    {

        self::$redis->discard();

    }

}