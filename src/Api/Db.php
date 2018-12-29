<?php
/**
 * Class Db
 * 数据库类
 * @author Qasim <15750783791@163.com>
 * @since  1.0 2018-04-22
 */
defined('QASIM') or exit('Access Denied');
class Db
{

    private static $dbh;//PDO对象

    private static $statement;//PDOStatement对象

    private static $cfg;//数据


    public static function Init()
    {

        global $_QASIM;

        $cfg = $_QASIM['config']['Pdo'];

        if (empty($cfg))
        {
            exit("The database is not found, Please checking 'data/database.php'");
        }

        $cfg['dsn'] = "mysql:host={$cfg['hostname']};dbname={$cfg['database']};port={$cfg['hostport']}";

        $cfg['option'] = array(PDO::ATTR_PERSISTENT => $cfg['pconnect']);

        Db::connect($cfg);

        self::$cfg = $cfg;

    }

    /**
     * 建立数据库连接
     * @param $cfg
     */
    private static function connect($cfg)
    {

        try {

            $dbh = new PDO($cfg['dsn'], $cfg['username'], $cfg['password']);

            $dbh ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  //设置如果sql语句执行错误则抛出异常，事务会自动回滚

            $dbh ->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //禁用prepared statements的仿真效果(防SQL注入)

            $dbh ->setAttribute(PDO::ATTR_PERSISTENT, $cfg['pconnect']); //禁用prepared statements的仿真效果(防SQL注入)

            $sql = "SET NAMES '{$cfg['charset']}';";

            self::$dbh = $dbh;

            self::query($sql);

        }catch (PDOException $e){

            self::outputError($e->getMessage());

        }

    }

    /**
    * PDO::query执行一条SQL语句，如果通过，则返回一个PDOStatement对象。PDO::query函数有个“非常好处”，就是可以直接遍历这个返回的记录集。
     * PDOStatement::closeCursor()来释放数据库资源与PDOStatement对象。
    * @param string $sql
    * @return mixed
    */
    public static function query($sql, $params = array())
    {

        if (empty($params))
        {

            $result = self::exec($sql);

            return $result;

        }

        $result = self::execute($sql , $params );

        if (!$result)
        {

            return false;

        }
        else {

            return self::$statement -> rowCount();

        }

    }

    /**
     * 返回一条数据
     * @param $sql
     * @param array $params
     * @return bool
     */
    public static function fetch($sql, $params = array())
    {

        $result = self::execute($sql , $params);

        if (!$result)
        {

            return false;
        }
        else {

            return self::$statement->fetch(pdo::FETCH_ASSOC);

        }

    }

    /**
     * 返回单个字段
     * @param $sql
     * @param array $params
     * @param int $column
     * @return bool
     */
    public static function fetchcolumn($sql, $params = array(), $column = 0)
    {

        $result = self::execute($sql , $params);

        if (!$result)
        {

            return false;
        }
        else {

            return self::$statement->fetchColumn($column);

        }

    }

    /**
     * 返回多条数据
     * @param $sql
     * @param array $params
     * @param string $keyfield
     * @return array|bool
     */
    public static function fetchall($sql, $params = array(), $keyfield = '')
    {

        $result = self::execute($sql , $params);

        if (!$result)
        {
            return false;

        }
        else {

            if (empty($keyfield))
            {

                return self::$statement->fetchAll(pdo::FETCH_ASSOC);

            }
            else {

                $temp = self::$statement->fetchAll(pdo::FETCH_ASSOC);

                $rs = array();

                if (!empty($temp))
                {

                    foreach ($temp as $key => &$row)
                    {

                        if (isset($row[$keyfield]))
                        {

                            $rs[$row[$keyfield]] = $row;

                        }
                        else {

                            $rs[] = $row;

                        }

                    }

                }

                return $rs;

            }

        }

    }

    /**
     * 返回指定表中指定字段的一条数据
     * @param $tablename
     * @param array $params
     * @param array $fields
     * @return bool
     */
    public static function get($tablename, $params = array(), $fields = array())
    {

        $select = '*';

        if (!empty($fields))
        {
            if (is_array($fields))
            {

                $select = '`'.implode('`,`', $fields).'`';

            }
            else {

                $select = $fields;

            }

        }

        $condition = self::implode($params, 'AND');

        $sql = "SELECT {$select} FROM " . self::tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " LIMIT 1";

        return self::fetch($sql , $condition['params']);

    }

    /**
     * 返回指定表中指定字段的多条数据
     * @param $tablename
     * @param array $params
     * @param array $fields
     * @param string $keyfield
     * @return array|bool
     */
    public static function getall($tablename, $params = array(), $fields = array(), $keyfield = '')
    {

        $select = '*';

        if (!empty($fields))
        {
            if (is_array($fields))
            {

                $select = '`'.implode('`,`', $fields).'`';

            }
            else {

                $select = $fields;

            }
        }

        $condition = self::implode($params, 'AND');

        $sql = "SELECT {$select} FROM " .self::tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $limitsql;

        return self::fetchall($sql, $condition['params'], $keyfield);

    }

    /**
     * 返回指定表中的指定分页数据与表数据总数
     * @param $tablename
     * @param array $params
     * @param array $limit
     * @param null $total
     * @param array $fields
     * @param string $keyfield
     * @return array|bool
     */
    public static function getslice($tablename, $params = array(), $limit = array(), &$total = null, $fields = array(), $keyfield = '')
    {

        $select = '*';

        if (!empty($fields))
        {

            if (is_array($fields))
            {

                $select = '`'.implode('`,`', $fields).'`';

            }
            else {

                $select = $fields;

            }

        }

        $condition = self::implode($params, 'AND');

        if (!empty($limit))
        {
            if (is_array($limit))
            {

                $limitsql = " LIMIT " . ($limit[0] - 1) * $limit[1] . ', ' . $limit[1];

            }
            else
                {

                $limitsql = Common::strexists(strtoupper($limit), 'LIMIT') ? " $limit " : " LIMIT $limit";

            }

        }

        $sql = "SELECT {$select} FROM " . self::tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $limitsql;

        $total = self::fetchcolumn("SELECT COUNT(*) FROM " . self::tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : ''));

        return self::fetchall($sql, $condition['params'], $keyfield);

    }

    /**
     * 更新指定表中指定字段数据
     * @param $table
     * @param array $data
     * @param array $params
     * @param string $glue
     * @return mixed
     */
    public static function update($table, $data = array(), $params = array(), $glue = 'AND')
    {

        $fields = self::implode($data, ',');

        $condition = self::implode($params , $glue);

        $params = array_merge($fields['params'] , $condition['params']);

        $sql = "UPDATE " . self::tablename($table) . " SET {$fields['fields']}";

        $sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';

        return self::query($sql, $params);

    }

    /**
     * 插入指定字段数据至指定表中
     * @param $table
     * @param array $data
     * @param bool $replace
     * @return mixed
     */
    public static function insert($table, $data = array(), $replace = FALSE)
    {

        $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

        $condition = self::implode($data, ',');

        return self::query("$cmd " . self::tablename($table) . " SET {$condition['fields']}", $condition['params']);

    }

    /**
     * 返回新增数据的返回ID
     * @return mixed
     */
    public static function insertid()
    {
        return self::$dbh->lastInsertId();
    }

    /**
     * 删除指定表中的指定数据 若没有指定数据 则删除所有
     * @param $table
     * @param array $params
     * @param string $glue
     * @return mixed
     */
    public static function delete($table, $params = array(), $glue = 'AND')
    {

        $condition = self::implode($params, $glue);

        $sql = "DELETE FROM " . self::tablename($table);

        $sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';

        return self::query($sql, $condition['params']);

    }

    /**
     * 事物开启
     */
    public static function begin()
    {

        self::$dbh->beginTransaction();

    }

    /**
     * 事物提交
     */
    public static function commit()
    {

        self::$dbh->commit();

    }

    /**
     * 事物回滚
     */
    public static function rollback()
    {

        self::$dbh->rollBack();

    }

    /**
     * 反正完全表名
     * @param $table
     * @return string
     */
    private static function tablename($table)
    {

        $prefix = self::$cfg['prefix'];

        return "`{$prefix}{$table}`";

    }

    /**
     * 数组转字符串
     * @param $params
     * @param string $glue
     * @return array
     */
    private static function implode($params, $glue = ',')
    {

        $result = array('fields' => ' 1 ', 'params' => array());

        $split = '';

        $suffix = '';

        if (in_array(strtolower($glue), array('and', 'or')))
        {

            $suffix = '__';

        }

        if (!is_array($params))
        {

            $result['fields'] = $params;

            return $result;

        }

        if (is_array($params))
        {

            $result['fields'] = '';

            foreach ($params as $fields => $value)
            {

                if (is_array($value))
                {

                    $result['fields'] .= $split . "`$fields` IN ('".implode("','", $value)."')";

                }
                else {

                    $result['fields'] .= $split . "`$fields` =  :{$suffix}$fields";

                    $split = ' ' . $glue . ' ';

                    $result['params'][":{$suffix}$fields"] = is_null($value) ? '' : $value;

                }

            }

        }

        return $result;

    }

    /**
     * 执行一条预处理语句
     * @param $sql
     * @param $params
     * @return mixed
     */
    private static function execute($sql , $params)
    {

        //准备要执行的SQL语句并返回一个 PDOStatement 对象
        self::prepare($sql);

        try{

            $result = self::$statement -> execute($params);

            return $result;

        }
        catch (PDOException $e){

            self::outputError($e->getMessage());
        }

    }

    /**
     * PDO::exec执行一条SQL语句，并返回受影响的行数。此函数不会返回结果集合。
     * @param string $sql
     * @return mixed
     */
    private static function exec($sql = '')
    {

        try{

            $result = self::$dbh -> exec($sql);

            return $result;

        }
        catch (PDOException $e){

            self::outputError($e->getMessage());
        }

    }

    /**
     * @param $sql
     * @return mixed
     */
    private static function prepare($sql)
    {

        try{

            self::$statement =  self::$dbh -> prepare($sql);

        }
        catch (PDOException $e){

            self::outputError($e->getMessage());
        }

    }


    /**
     * 输出错误信息
     * @param String $strErrMsg
     */
    private static function outputError($strErrMsg)
    {
        throw new Exception('MySQL Error: '.$strErrMsg);
    }

    /**
     * 释放资源
     */
    public function __destruct()
    {

        self::$statement = null;
        echo 1111;
        self::$statement = null;

    }

}