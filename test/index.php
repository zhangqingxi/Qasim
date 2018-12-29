<?php
/**
 * 核心入口
 * @auther Qasim <15750783791@163.com>
 * @since 1.0 2018-04-21
 */
include_once "core/base.php";
global $_QASIM;


//****************************测试支付宝**************************
//测试异步回调
if(!empty($_QASIM['args']['A_Notify']))
{
    Alipay::alipayNotify();
    exit;
}
//测试页面回调跳转
if(!empty($_QASIM['args']['A_Return']))
{
    print_r($_QASIM);
    echo "-------------'\r\n";
    print_r($_QASIM['args']);
    exit;
}
//测试退款请求
if(!empty($_QASIM['args']['A_Refund']))
{
    $args = array(
        'out_trade_no' => '1524541730',
        'trade_no' => '2018042421001004080585187168',
        'refund_amount' => '0.01',
        'out_request_no' => TIMESTAMP
    );
    $result = Alipay::alipayRefund($args);
    var_dump($result);
    exit;
}
//测试APP支付
if(!empty($_QASIM['args']['A_APP']))
{
    $args = array(
        'body' => '描述信息',
        'total_amount' =>  '0.01',
        'subject' => '标题',
        'out_trade_no' => TIMESTAMP,
        'notify_url' =>  $_QASIM['siteroot'].'index'.EXT.'?A_Notify=1'
    );
    $result = Alipay::alipayAppBuild($args);
    echo $result;
    exit;
}
//测试Wap支付
if(!empty($_QASIM['args']['A_Wap']))
{
    $args = array(
        'body' => '描述信息',
        'total_amount' => '0.01',
        'subject' => '标题',
        'out_trade_no' => TIMESTAMP,
        'notify_url' =>  $_QASIM['siteroot'].'index'.EXT.'?A_Notify=1',
        'return_url' => $_QASIM['siteroot'].'index'.EXT.'?A_Return=1',
    );
    $result = Alipay::alipayWapBuild($args);
    echo $result;
    exit;
}
//测试Web支付
if(!empty($_QASIM['args']['A_Web']))
{
    $args = array(
        'body' => '描述信息',
        'total_amount' => '0.01',
        'subject' => '标题',
        'out_trade_no' => TIMESTAMP,
        'notify_url' => $_QASIM['siteroot'].'index'.EXT.'?A_Notify=1',
        'return_url' => $_QASIM['siteroot'].'index'.EXT.'?A_Return=1',
    );
    $result = Alipay::alipayWebBuild($args);
    echo $result;
    exit;
}
//****************************测试微信**************************
//测试退款请求
if(!empty($_QASIM['args']['Refund']))
{
    $args = array(
        'transaction_id' => '交易号',
        'total_fee' => '交易金额',
        'refund_fee' => '退款金额',
        'out_refund_no' => '退款编号'
    );
    $result = Wxpay::wxpayRefund($args);
    var_dump($result);
    exit;
}
//测试APP支付
if(!empty($_QASIM['args']['APP']))
{
    $args = array(
        'body' => '标题',
        'total_fee' =>  '100',
        'out_trade_no' => TIMESTAMP,
        'notify_url' =>  $_QASIM['siteroot'].'index'.EXT.'?A_Notify=1',
    );
    $result = Wxpay::wxpayAppBuild($args);
    echo $result;
    exit;
}
//测试扫描支付
if(!empty($_QASIM['args']['Native']))
{
    $args = array(
        'body' => '标题',
        'total_fee' => '1',
        'out_trade_no' => TIMESTAMP,
        'notify_url' =>  $_QASIM['siteroot'].'index'.EXT.'?A_Notify=1',
        'product_id' => '产品编号'
    );
    $result = Wxpay::wxpayNativeBuild($args);
    include_once ED.'qrcode'.DS.'phpqrcode'.EXT;
    $url = urldecode($result['code_url']);
    QRcode::png($url);
    exit;
}
//测试H5支付
if(!empty($_QASIM['args']['H5']))
{
    $args = array(
        'body' => '标题',
        'total_fee' =>  '100',
        'out_trade_no' => TIMESTAMP,
        'notify_url' => $_QASIM['siteroot'].'index'.EXT.'?A_Notify=1',
        'scene_info' => '{"h5_info":{"type":"Wap/Android/IOS","wap_url":"商品网址","wap_name":"商品名称"}}'
    );
    $result = Wxpay::wxpayH5Build($args);
    var_dump($result);
    exit;
}

//测试小程序 公众号支付
if(!empty($_QASIM['args']['Jsapi']))
{
    $args = array(
        'body' => '标题',
        'total_fee' =>  '100',
        'out_trade_no' => TIMESTAMP,
        'openid' => '微信openid',
        'notify_url' =>  $_QASIM['siteroot'].'index'.EXT.'?A_Notify=1',
    );
    $result = Wxpay::wxpayJsapiBuild($args);
    var_dump($result);
    exit;
}
//测试刷卡支付
if(!empty($_QASIM['args']['Scan']))
{
    $args = array(
        'body' => '标题',
        'total_fee' =>  '100',
        'out_trade_no' => TIMESTAMP,
        'auth_code' => '134630858458877388',
    );
    $result = Wxpay::wxpayScanBuild($args);
    var_dump($result);
    exit;
}
//***********************************通用




