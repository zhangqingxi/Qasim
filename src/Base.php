<?php
/**
 * 核心入口/
 * @author Qasim <15750783791@163.com>
 * @since 1.0 2018-04-24
 */
header('Content-Type: text/html; charset=utf-8');
//*************************************常量定义**************
define('QASIM' , TRUE);//权限定义

defined('TIMESTAMP') or define('TIMESTAMP', time());//时间戳

defined('MICROTIME') or define('MICROTIME', microtime());//时间戳

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

defined('ROOT') or define('ROOT', __DIR__ . '/qasim/');//根目录

defined('CE') or define('CE', ROOT .'core'.DS);

defined('CS') or define('CS', CE .'class'.DS);

defined('ED') or define('ED', CE .'extend'.DS);

defined('EXT') or define('EXT','.php');

defined('DATA') or define('DATA', ROOT .'data'.DS);

defined('LOG') or define('LOG',DATA.'log'.DS);

include_once CS.'Loader'.EXT;

//注册自动加载
Loader::register();

include_once DATA.'database'.EXT;

//**********************************全局参数
$_QASIM = array();

$_QASIM['siteroot'] = htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . DS);

//当前请求url
$_QASIM['siteurl']  = $_QASIM['siteroot'] . $_SERVER['SCRIPT_NAME'] . (empty($_SERVER['QUERY_STRING']) ? "" : "?{$_SERVER['QUERY_STRING']}" );

$_QASIM['isajax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$_QASIM['ispost'] = $_SERVER['REQUEST_METHOD'] == 'POST';

//客户端IP
$_QASIM['clientip'] = Common::clientIp();

//服务端IP
$_QASIM['serverip'] = Common::serverIp();

//请求参数【$_GET ， $_POST ， $_COOKIE】
$_QASIM['args'] = Common::ihtmlspecialchars
                    (
                        array_merge(
                            Common::istripslashes($_GET),
                            Common::istripslashes($_POST),
                            Common::istripslashes($_COOKIE)
                        )
                    );

if(!$_QASIM['isajax'])
{

    $input = file_get_contents("php://input");

    if(!(empty($input)))
    {
        if(Common::jsonToArray($input))
        {

            $_QASIM['args']['input'] = Common::jsonToArray($input);

        }
        elseif(Common::xmlToArray($input)){

            $_QASIM['args']['input'] = Common::xmlToArray($input);

        }
    }

}

$_QASIM['config']['Pdo'] = $db_config;
