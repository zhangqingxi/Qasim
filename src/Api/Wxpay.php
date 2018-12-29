<?php
/**
 * Class Wxpay
 * 微信支付  用于APP/wap/web/公众号 支付/退款/查询    可根据需求拓展类库（关闭订单/。）
 * @author Qasim <15750783791@163.com>
 * @since  1.0 2018-04-22
 */
defined('QASIM') or exit('Access Denied');
include_once ED.'payment'.DS. 'wxpay' .DS.'WxPay.Api'.EXT;
class Wxpay{
    /**
     * APP 微信支付
     * url => https://api.mch.weixin.qq.com/pay/unifiedorder
     * @param $args 参数集合
     * 主要参数如下
     * body 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值
     * out_trade_no  商户网站唯一订单号
     * total_fee  订单总金额，单位为分
     * notify_url  接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
     * 可选参数
     * detail 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传，详见
     * @return array $result
     */
    public static function wxpayAppBuild($args = array())
    {

        //实例化统一下单输入对象
        $input = new WxPayUnifiedOrder();

        $input->SetBody($args['body']);//商品或支付单简要描述

        $input->SetOut_trade_no($args['out_trade_no']);

        $input->SetTotal_fee($args['total_fee']);

        $input->SetTrade_type("APP");

        $input->SetNotify_url($args['notify_url']);

        $result = WxPayApi::unifiedorder($input);

        return json_encode($result);
        // return $result;



//        $data = $unifiedOrder -> getParameters('wx6a40f75222151112' , '1512988741' , $prepay_id, 'R6xJ7ehiUhjRcNT9vpK9pUObBuobV3dX');


    }


    /**
     * 扫码支付  只用模式二 扩展性高
     * url => https://api.mch.weixin.qq.com/pay/unifiedorder
     * @param $args 参数集合
     * 主要参数如下
     * body 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值
     * out_trade_no  商户网站唯一订单号
     * total_fee  订单总金额，单位为分
     * notify_url  接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
     * product_id 商品ID
     * 可选参数
     * detail 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传，详见
     * @return array $result
     */
    public static function wxpayNativeBuild($args = array())
    {
        //实例化统一下单输入对象
        $input = new WxPayUnifiedOrder();

        $input->SetBody($args['body']);//商品或支付单简要描述

        $input->SetOut_trade_no($args['out_trade_no']);

        $input->SetTotal_fee($args['total_fee']);

        $input->SetTrade_type("NATIVE");

        $input->SetNotify_url($args['notify_url']);

        $input->SetProduct_id($args['product_id']);

        $result = WxPayApi::unifiedOrder($input);

        return $result;

    }

    /**
     * 公众号/小程序  微信支付
     * url => https://api.mch.weixin.qq.com/pay/unifiedorder
     * tips:GetJsApiParameters ==> 获取jsapi支付的参数、支付授权页面
     * @param $args 参数集合
     * 主要参数如下
     * body 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值
     * out_trade_no  商户网站唯一订单号
     * total_fee  订单总金额，单位为分
     * notify_url  接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
     * openid 微信用户在商户对应appid下的唯一标识。
     * 可选参数
     * detail 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传，详见
     * @return array $result
     */
    public static function wxpayJsapiBuild($args = array())
    {
        //实例化统一下单输入对象
        $input = new WxPayUnifiedOrder();

        $input->SetBody($args['body']);//商品或支付单简要描述

        $input->SetOut_trade_no($args['out_trade_no']);

        $input->SetTotal_fee($args['total_fee']);

        $input->SetTrade_type("JSAPI");

        $input->SetNotify_url($args['notify_url']);

        $input->SetOpenid($args['openid']);

        $result = WxPayApi::unifiedorder($input);

        //实例化JSAPI支付实现类
        $tools = new JsApiPay();

        $result = $tools -> GetJsApiParameters($result);

        return $result;

    }

    /**
     * H5  微信支付
     * url => https://api.mch.weixin.qq.com/pay/unifiedorder
     * @param $args 参数集合
     * 主要参数如下
     * body 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值
     * out_trade_no  商户网站唯一订单号
     * total_fee  订单总金额，单位为分
     * notify_url  接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
     * scene_info 场景信息 H5支付不建议在APP端使用，针对场景1，2请接入APP支付，不然可能会出现兼容性问题 【键名不可修改】
     * //1.IOS移动应用
     * {"h5_info": {"type":"IOS","app_name": "王者荣耀","bundle_id": "com.tencent.wzryIOS"}}
     * //2.安卓移动应用
     * {"h5_info": {"type":"Android","app_name": "王者荣耀","package_name": "com.tencent.tmgp.sgame"}}
     * //3.WAP网站应用
     * {"h5_info": {"type":"Wap","wap_url": "https://pay.qq.com","wap_name": "腾讯充值"}}
     * 可选参数
     * detail 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传，详见
     * @return array $result
     */
    public static function wxpayH5Build($args = array())
    {
        //实例化统一下单输入对象
        $input = new WxPayUnifiedOrder();

        $input->SetBody($args['body']);//商品或支付单简要描述

        $input->SetOut_trade_no($args['out_trade_no']);

        $input->SetTotal_fee($args['total_fee']);

        $input->SetTrade_type("MWEB");

        $input->SetNotify_url($args['notify_url']);

        $input->SetScene_info($args['scene_info']);

        $result = WxPayApi::unifiedorder($input);

        return $result;

    }

    /**
     * 刷卡支付  微信支付
     * url => https://api.mch.weixin.qq.com/pay/micropay
     * @param $args 参数集合
     * 主要参数如下
     * body 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值
     * out_trade_no  商户网站唯一订单号
     * total_fee  订单总金额，单位为分
     * auth_code 授权码 扫码支付授权码，设备读取用户微信中的条码或者二维码信息 （注：用户刷卡条形码规则：18位纯数字，以10、11、12、13、14、15开头）
     * 可选参数
     * detail 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传，详见
     * @return array $result
     */
    public static function wxpayScanBuild($args = array())
    {
        //实例化统一下单输入对象
        $input = new WxPayMicroPay();

        $input->SetBody($args['body']);//商品或支付单简要描述

        $input->SetOut_trade_no($args['out_trade_no']);

        $input->SetTotal_fee($args['total_fee']);

        $input->SetAuth_code($args['auth_code']);

        $result = WxPayApi::micropay($input);

        return $result;

    }

    /**
     * 订单查询
     * url https://api.mch.weixin.qq.com/pay/orderquery
     * @param $transaction_id 微信订单号 / $out_trade_no 商户订单号   二选一
     * @return array $result
     */
    public static function wxpayQuery($transaction_id)
    {

        //实例化统一查询订单对象
        $orderQuery = new WxPayOrderQuery();

        $orderQuery->SetTransaction_id($transaction_id);

        $result = WxPayApi::orderQuery($orderQuery);

        return $result;

    }

    /**
     * 订单退款查询
     * url https://api.mch.weixin.qq.com/pay/refundquery
     * @param $transaction_id 微信订单号 / $out_trade_no 商户订单号 / $out_refund_no 商户退款单号 / $refund_id 微信退款单号  四选一
     * @return array $result
     */
    public static function wxpayRefundQuery($transaction_id)
    {

        //实例化统一查询订单对象
        $refundQuery = new WxPayRefundQuery();

        $refundQuery->SetTransaction_id($transaction_id);

        $result = WxPayApi::refundQuery($refundQuery);

        return $result;

    }

    /**
     * 订单退款
     * url https://api.mch.weixin.qq.com/secapi/pay/refund
     * @param $transaction_id 微信订单号 / out_trade_no 商户订单号   二选一
     * @param $out_refund_no  商户退款单号 商户系统内部的退款单号，商户系统内部唯一，只能是数字、大小写字母_-|*@ ，同一退款单号多次请求只退一笔。
     * @param $total_fee   订单金额
     * @param $refund_fee 退款金额
     * @return array $result
     */
    public static function wxpayRefund($args = array())
    {

        //查询订单是否存在
        $result = self::wxpayQuery($args['transaction_id']);

        if($result["trade_state"] != "SUCCESS")
        {
            return $result;
        }

        //查询订单是否退款成功
        $result = self::wxpayRefundQuery($args['transaction_id']);

        if($result["trade_state"] == "SUCCESS")
        {
            return $result;
        }

        //实例化统一查询订单对象
        $refund = new WxPayRefund();

        $refund->SetTransaction_id($args['transaction_id']);

        $refund->SetOut_refund_no($args['out_refund_no']);

        $refund->SetTotal_fee($args['total_fee']);

        $refund->SetRefund_fee($args['refund_fee']);

        $refund -> SetOp_user_id(WxPayConfig::MCHID);

        $result = WxPayApi::refund($refund);

        return $result;

    }


}
?>