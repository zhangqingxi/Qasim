<?php
/**
 * 入口文件
 */

$database_config = [
    // 数据库类型
    'type'           => 'mysql',
    // 服务器地址/内网IP
    'hostname'       => '127.0.0.1',
    // 数据库名
    'database'       => 'test',
    // 用户名
    'username'       => 'test',
    // 密码
    'password'       => '123456',
    // 端口
    'hostport'       => '3306',
    // 连接dsn
    'dsn'            => '',
    // 数据库连接参数
    'params'         => [],
    // 数据库编码默认采用utf8/需要存储四个字符请用utf8mb4
    'charset'        => 'utf8',
    // 数据库表前缀
    'prefix'         => 'cx'

];

$redis_config = [];

\Qasim\Db::getInstance($database_config);

\Qasim\Redis::getInstance($redis_config);

