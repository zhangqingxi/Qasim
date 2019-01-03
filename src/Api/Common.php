<?php

namespace Qasim;

class Common
{

    /**
     * Curl 请求路径
     * @param string $request_type
     * @param string $url
     * @param array $data
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/29 0029 下午 6:08
     * @version 1.0
     * @return mixed|string
     */
    public static function curl($request_type = 'post', $url = '', $data = [])
    {

        //初始化
        $ch = curl_init();

        if (strtolower($request_type) == 'post') {

            curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置post参数

        } else {

            $url = $url.(strstr($url,'?') !== false ? '&' : '?').http_build_query($data);//设置get参数

        }

        curl_setopt($ch, CURLOPT_URL, $url);// 要访问的地址

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在

        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环

        curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $output = curl_exec($ch);//执行操作

        //有错误返回错误数据
        if (curl_errno($ch)) {

            return curl_error($ch);

        }

        //关闭URL请求
        curl_close($ch);

        //返回获得的数据;
        return json_decode($output, true);

    }

    /**
     * xml格式转数组
     * @param string $xml
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/2 0002 下午 2:02
     * @version 1.0
     * @return array|mixed
     */
    public static function XmlToArray($xml = '')
    {

        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        //创建 XML 解析器。
        $xml_parser = xml_parser_create();

        if (!xml_parse($xml_parser, $xml, true)) {

            xml_parser_free($xml_parser);

            return [];

        }

        //释放 XML 解析器。
        xml_parser_free($xml_parser);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

    }

    /**
     * 数组格式转XML
     * @param array $arr
     * @return string
     */
    public static function arrayToXml($arr = [])
    {

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
     * 提取html内的图片数据
     * @param string $html
     * @author Qasim <15750783791@163.com>
     * @time 2018/12/25 0025 下午 3:27
     * @version 1.0
     * @return mixed
     */
    public static function returnImageFromHtml($html = '')
    {

        $pattern = '/<img.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/i';

        preg_match_all($pattern, $html,$matches);

        if(empty($matches[1])){

            return [];

        }

        return $matches[1];

    }

    /**
     * 从所给的内容提取纯文字
     * @param string $content
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/2 0002 下午 2:03
     * @version 1.0
     * @return string
     */
    public static function returnTextFromContent($content = '')
    {

        preg_match_all("/([\x{4e00}-\x{9fa5}]+)/u", $content, $match);

        if(empty($match[0])){

            return '';

        }

        return join('', $match[0]);

    }

    /**
     * 数组转JSON
     * @param array $arr
     * @param string $options
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/2 0002 下午 2:05
     * @version 1.0
     * @return false|string
     */
    public static function arrayToJson($arr = [], $options = '')
    {

        if (empty($options)) {

            return json_encode($arr);

        }

        return json_encode($arr, $options);

    }

    /**
     * JSON转数组
     * @param string $json
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/2 0002 下午 2:05
     * @version 1.0
     * @return mixed
     */
    public static function jsonToArray($json = '')
    {

        return json_decode($json, true);

    }

    /**
     * 对象转数组
     * @param $object
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/2 0002 下午 2:05
     * @version 1.0
     * @return mixed
     */
    public static function objectToArray($object)
    {

        return json_decode(json_encode($object), true);

    }

    /**
     * 二维数组按某一键值排序
     * @param array $arr
     * @param string $keys
     * @param string $type
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/2 0002 下午 2:57
     * @version 1.0
     * @return array
     */
    public static function arraySort( $arr = [] , $keys = '' , $type = 'asc')
    {

        $keys_value = $new_array = [];

        foreach ($arr as $k => $v)
        {
            $keys_value[$k] = $v[$keys];
        }

        $type == 'asc' ? asort($keys_value) : arsort($keys_value);

        reset($keys_value);

        foreach ($keys_value as $k => $v)
        {
            $new_array[$k] = $arr[$k];
        }

        return $new_array;

    }

    /**
     * 获取客户端IP地址
     * @param int $type  $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param bool $adv $adv 是否进行高级模式获取（有可能被伪装）
     * @author Qasim <15750783791@163.com>
     * @time 2019/1/2 0002 下午 3:01
     * @version 1.0
     * @return mixed
     */
    public static function ip($type = 0, $adv = false)
    {

        $type      = $type ? 1 : 0;

        static $ip = null;

        if (null !== $ip) {

            return $ip[$type];

        }

        if ($adv) {

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                $pos = array_search('unknown', $arr);

                if (false !== $pos) {

                    unset($arr[$pos]);

                }

                $ip = trim(current($arr));

            } elseif (isset($_SERVER['HTTP_C LIENT_IP'])) {

                $ip = $_SERVER['HTTP_CLIENT_IP'];

            } elseif (isset($_SERVER['REMOTE_ADDR'])) {

                $ip = $_SERVER['REMOTE_ADDR'];

            }

        } elseif (isset($_SERVER['REMOTE_ADDR'])) {

            $ip = $_SERVER['REMOTE_ADDR'];

        }

        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));

        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];

        return $ip[$type];

    }



}