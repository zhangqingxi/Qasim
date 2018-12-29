<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
include_once $_SERVER['DOCUMENT_ROOT']."/core/base.php";
include_once ED.'payment'.DS."wxpay".DS."WxPay.Api".EXT;
include_once ED.'payment'.DS."wxpay".DS.'WxPay.Notify'.EXT;
//初始化日志
Log::Init(LOG. 'wxpay' .DS.date('Ymd').DS .date('H').'.log');
class PayNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        Log::DEBUG("订单查询反馈:" . print_r($result , true)."******************************************\r\n");
        if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS"&& $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        Log::DEBUG("回调处理:" . print_r($data , true)."******************************************\r\n" );
        if(!array_key_exists("transaction_id", $data)){
            Log::DEBUG("处理反馈:" . print_r(array('transaction_id' => '交易单号为空！') , true)."******************************************\r\n" );
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            Log::DEBUG("处理反馈:" . print_r(array('transaction_id' => '订单查询失败！') , true)."******************************************\r\n" );
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }
}

$input = file_get_contents('php://input');
//$input = "<xml><appid><![CDATA[wx602ab9b1555a49e9]]></appid>
//<bank_type><![CDATA[CMB_CREDIT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[N]]></is_subscribe>
//<mch_id><![CDATA[1275147801]]></mch_id>
//<nonce_str><![CDATA[k9g8anxmpor5ma1b1h501agvfr7jntb6]]></nonce_str>
//<openid><![CDATA[oGhx7wT1JDsTh2szh4OrJEfd85ac]]></openid>
//<out_trade_no><![CDATA[1524391818]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[D38E6AD726B6305B829468ABAA35BC2C]]></sign>
//<time_end><![CDATA[20180422181025]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[APP]]></trade_type>
//<transaction_id><![CDATA[4200000062201804223417371482]]></transaction_id>
//</xml>";
Log::DEBUG("接收xml参数 \r\n".$input."\r\n******************************************\r\n");
$notify = new PayNotifyCallBack();
$notify->Handle(false , $input);
