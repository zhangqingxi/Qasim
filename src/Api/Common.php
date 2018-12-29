<?php
/**
 * 通用类
 * Class common
 * @author Qasim <15750783791@163.com>
 * @since 1.0 2018-04-24
 */
defined('QASIM') or exit('Access Denied');
class Common
{

    /***********************************************常用函数*****************************************
    /**
     * Curl 函数
     * @access static
     * @name qasim_curl
     * @param $requestType  请求方式
     * @param $url  路径
     * @param $data  参数
     * @return array
     */
    public static function curl($requestType = 'post' , $url = '' , $data = array())
    {

        //初始化
        $curl = curl_init();

        if($requestType == 'post')
        {
            //设置post参数
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        else{ //设置get参数
            $url .= '?'.http_build_query($data);
        }

        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);

        //执行命令
        $result = curl_exec($curl);

        //有错误返回错误数据
        if (curl_errno($curl))
        {
            return curl_error($curl);
        }

        //关闭URL请求
        curl_close($curl);

        //返回获得的数据;
        return $result;

    }

    /**
     * XMl格式转数组 如果不是xml格式  返回false
     * @param string $xml
     * @return array
     */
    public static function xmlToArray($xml = '')
    {

        $xml_parser = xml_parser_create();

        if(!xml_parse($xml_parser,$xml,true))
        {

            xml_parser_free($xml_parser);

            return false;

        }

        xml_parser_free($xml_parser);

        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $result;

    }

    /**
     * 数组格式转XML
     * @param array $arr
     * @return string
     * @throws Exception
     */
    public static function arrayToXml($arr = array())
    {

        if(!is_array($arr))
        {

            throw Exception::errorMessage('数组数据异常!');

        }

        $xml = "<xml>";

        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {

                $xml.="<".$key.">".$val."</".$key.">";

            }
            else{

                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";

            }

        }

        $xml.="</xml>";

        return $xml;

    }

    /**
     * 如果是json数据 对其进行转换数组 如果不是返回false
     * @param string $josn
     * @return bool|array
     */
    public static function jsonToArray($josn = '')
    {

        $josn = str_replace('＼＼', '', $josn);

        preg_match('/{.*}/', $josn, $result);

        return empty($result) ? false : json_decode($result[0] , TRUE);

    }


    /**
     * 去除字符串中的反斜线字符，如果有两个连续的反斜线，则只去掉一个
     * @param $var
     * @return array|string
     */
    public static function istripslashes($var)
    {

        if (is_array($var))
        {

            foreach ($var as $key => $value)
            {

                $var[stripslashes($key)] = self::istripslashes($value);

            }

        }
        else {

            $var = stripslashes($var);

        }

        return $var;

    }


    /**
     * 预定义的字符转换为 HTML 实体：
     * @param $var
     * @return array|string
     */
    public static function ihtmlspecialchars($var)
    {

        if (is_array($var))
        {

            foreach ($var as $key => $value)
            {

                $var[htmlspecialchars($key)] = self::ihtmlspecialchars($value);

            }

        }
        else {

            $var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));

        }

        return $var;

    }

    /**
     * 获取客户端IP
     * @return string
     */
    public static function clientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if(isset($_SERVER['HTTP_CDN_SRC_IP']))
        {

            $ip = $_SERVER['HTTP_CDN_SRC_IP'];

        }
        elseif(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])){

            $ip = $_SERVER['HTTP_CLIENT_IP'];

        }
        elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {

            foreach ($matches[0] AS $xip)
            {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip))
                {

                    $ip = $xip;

                    break;

                }

            }

        }

        return $ip;

    }

    /**
     * 获取服务端IP
     * @return string
     */
    public static function serverIp()
    {

        $ip = $_SERVER['SERVER_ADDR'];

        return $ip;

    }

    /**
     * 查找字符串是否在另一个字符串里面
     * @param $string
     * @param $find
     * @return bool
     */
    public static function strexists($string , $find)
    {
        return !(strpos($string, $find) === FALSE);
    }

    /**
     * 数组分页函数  核心函数  array_slice
     * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中
     * $count   每页多少条数据
     * $page   当前第几页
     * $array   查询出来的所有数组
     * order 0 - 不变     1- 反序
     */
    public function page_array($count = 20 ,$page = 1,$array = array() , $order = 0)
    {

        $start = ($page - 1) * $count; #计算每次分页的开始位置

        if($order == 1)
        {
            $array = array_reverse($array);
        }

        $pagedata = array_slice($array,$start,$count);

        return $pagedata;  #返回查询数据

    }

    /**
     * 二维数组按某一键值排序
     * @param $arr
     * @param $keys
     * @param string $type asc|desc
     * @return array
     */
    static function arraySort( $arr = array() , $keys = '' , $type = 'asc')
    {

        $keysvalue = $new_array = array();

        foreach ($arr as $k => $v)
        {
            $keysvalue[$k] = $v[$keys];
        }

        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);

        reset($keysvalue);

        foreach ($keysvalue as $k => $v)
        {
            $new_array[$k] = $arr[$k];
        }

        return $new_array;

    }

}

?>