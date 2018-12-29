<?php
/**
 * Class Alipay
 * 支付宝  用于APP/wap/web 支付/退款/查询    可根据需求拓展类库（关闭订单/。）
 * @author Qasim <15750783791@163.com>
 * @since  1.0 2018-04-21
 */
defined('QASIM') or exit('Access Denied');
include_once ED.'payment'.DS. 'alipay' .DS.'AopClient'.EXT;
class Alipay
{

    /**
     * APP 支付宝支付
     * @param $bizContent 参数集合
     * 主要参数如下 【app_id / method / format / charset / sign_type / timestamp / version  不需要传 特殊情况除外】
     * body 对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
     * subject 	商品的标题/交易标题/订单标题/订单关键字等。
     * out_trade_no  商户网站唯一订单号
     * total_amount  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
     * notify_url   	支付宝服务器主动通知商户服务器里指定的页面http/https路径。建议商户使用https
     * 可选参数
     * product_code 销售产品码，商家和支付宝签约的产品码，示例值 QUICK_MSECURITY_PAY
     * timeout_express 该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。注：若为空，则默认为15d。
     * @return string
     */
    static function alipayAppBuild($bizContent = array())
    {

        //检查请求参数
        self::checkPayArgs($bizContent);

        $bizcontent = json_encode([
            'body'          => $bizContent['body'],      //	对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
            'subject'       => $bizContent['subject'],  //商品的标题/交易标题/订单标题/订单关键字等。
            'out_trade_no' => $bizContent['out_trade_no'], //此订单号为商户唯一订单号
            'total_amount' => $bizContent['total_amount']    //	订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
        ]);

        $aop = new AopClient();

        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();

        //设置异步回调
        /************
         Array(
            [alipay_notify] => 1
            [gmt_create] => 2018-04-21 10:37:53
            [charset] => UTF-8
            [seller_email] => alloneaustin@vip.qq.com
            [subject] => 这是商品标题
            [sign] => VabLzkdiPDg+80pscYzYDJbEzRgBSqHd/nOVRqg3/EIrLNPsP77k+ZItrX50w3aXvyrJgzKs+JKNkicbEkdMHp+mTp1IUINpC04vefDLpLNOhUXBUDaaO+ILcORiFimirEewcCXgX52I+o8jfSFhW1T4lu/zsidan7JZEd5ImDe0vkGvLMK/h6t8DEiawVlgEdEgbjroafiX/2GMV3HZ4l++X0sHbayDd9jK+ecG3A1bMQYcIxJdgloYR53AoLinNqkOjIi/v76iprDIUWNoQGz/b7ARtoWcQeHDBpeaE5DAPEbae669hkvqVz1jw/ZWV2INaxZCMlY7qumw+ir1BA==
            [body] => 这是商品详情
            [buyer_id] => 2088312479093080
            [invoice_amount] => 0.01
            [notify_id] => b27a5eff2878939149dc11a498469aegmd
            [fund_bill_list] => [{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]
            [notify_type] => trade_status_sync
            [trade_status] => TRADE_SUCCESS
            [receipt_amount] => 0.01
            [app_id] => 2017030906136833
            [buyer_pay_amount] => 0.01
            [sign_type] => RSA2
            [seller_id] => 2088621252187600
            [gmt_payment] => 2018-04-21 10:37:53
            [notify_time] => 2018-04-21 10:37:54
            [version] => 1.0
            [out_trade_no] => 15242782676847893
            [total_amount] => 0.01
            [trade_no] => 2018042121001004080572719760
            [auth_app_id] => 2017030906136833
            [buyer_logon_id] => 157****3791
            [point_amount] => 0.00
        )
         ***************/
        $request->setNotifyUrl(urlencode($bizContent['notify_url']));

        $request->setBizContent($bizcontent);

        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);

        // 注意：这里不需要使用htmlspecialchars进行转义，直接返回即可
        return $response;

    }

    /**
     * WAP 支付宝支付
     * @param $bizContent 参数集合
     * 主要参数如下 【app_id / method / format / charset / sign_type / timestamp / version  不需要传 特殊情况除外】
     * body 对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
     * subject 	商品的标题/交易标题/订单标题/订单关键字等。
     * out_trade_no  商户网站唯一订单号
     * total_amount  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
     * notify_url   	支付宝服务器主动通知商户服务器里指定的页面http/https路径。建议商户使用https
     * 可选参数
     * product_code  销售产品码，商家和支付宝签约的产品码	QUICK_WAP_WAY
     * timeout_express 该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。注：若为空，则默认为15d。
     * @return string
     */
    static function alipayWapBuild($bizContent = array())
    {

        //检查请求参数
        self::checkPayArgs($bizContent);

        $aop = new AopClient ();

        $bizcontent = json_encode([
            'body'          => $bizContent['body'],      //	对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
            'subject'       => $bizContent['subject'],  //商品的标题/交易标题/订单标题/订单关键字等。
            'out_trade_no' => $bizContent['out_trade_no'], //此订单号为商户唯一订单号
            'total_amount' => $bizContent['total_amount']    //	订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
        ]);

        $request = new AlipayTradeWapPayRequest();

        //设置请求参数
        $request->setBizContent($bizcontent);

        //设置前台回调
        /************参数如下
         Array (
            [total_amount] => 0.01
            [timestamp] => 2018-04-19 15:17:28
            [sign] => S0mylHgqzc5ki1+QDwKOqjmW1BdXoZ6q75pWGbQQXyHWslsPBC4PVCVBGa1j32DFdi/578i7uSYs6uxQJQtWWPvo/2vRvq4mX6XXuXBTQLVgIM+s0abdRuRSMGMV9pyNBkT8QpDHIsR2cfbYLd/7HNzt8/tSIrPAuGMZhLlEPbG+X9MWFysut13NmBugrdL4wfOJnzJqeYJZaqIL/ii8x3iAVpo9TbLXGinAaCswhP8JfhIJJHhq9t+pPZqozHzV+1+XJZQ0jXfK1ank5cIbFyAmgNsHo4wy7LhX1ptee3WvwXj31gZusdwCzPWOdHXxbbj65YbapI29LB6eGlWHbg==
            [trade_no] => 2018041921001004080566524146
            [sign_type] => RSA2
            [auth_app_id] => 2017030906136833
            [charset] => UTF-8
            [seller_id] => 2088621252187600
            [method] => alipay.trade.wap.pay.return
            [app_id] => 2017030906136833
            [out_trade_no] => 15241222407931418
            [version] => 1.0
         );
         **********************/
        $request -> setReturnUrl($bizContent['return_url']);

        //设置异步回调
        /************参数如下
        Array(
            [alpay_notify] => 1
            [gmt_create] => 2018-04-19 15:43:56
            [charset] => UTF-8
            [seller_email] => alloneaustin@vip.qq.com
            [subject] => 这是商品标题
            [sign] => lughBH/Ssysie7XkuM87JIlsOQ8HoFjr30gP5WuyMJvTXHinNRgk3yaKvLLn88Gy8b85bHFlS2OzduwKUxO59V43yASPaN8CWJLdRMVg3fVc1Jiw0qOxzLFB5xRgGxqSe2S2zqhsXGWSYhUXtGvu1jsG0PFlW+W1zvu5fGjMR47K9LCdkIONx39a/lkq6srydQtN0+ezOkqv9Zhz6tnO8vIvpDJcBq5ffVBzUpfFlNyHwVhAptlmo6BfY9imfGxQrVyBnLbdWCX+vpIwdYDJus+JAPzjZ0k8WJ0QmVGH9Vc3G9eQSQt/XGt2yi5eDjUw5gcPnoeg7M/pwddF9D7/EQ==
            [body] => 这是商品详情
            [buyer_id] => 2088312479093080
            [invoice_amount] => 0.01
            [notify_id] => 34d00e47a3dc415bc87d8029baebec0gmd
            [fund_bill_list] => [{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]
            [notify_type] => trade_status_sync
            [trade_status] => TRADE_SUCCESS
            [receipt_amount] => 0.01
            [buyer_pay_amount] => 0.01
            [app_id] => 2017030906136833
            [sign_type] => RSA2
            [seller_id] => 2088621252187600
            [gmt_payment] => 2018-04-19 15:43:56
            [notify_time] => 2018-04-19 15:43:57
            [version] => 1.0
            [out_trade_no] => 15241238309580809
            [total_amount] => 0.01
            [trade_no] => 2018041921001004080566741185
            [auth_app_id] => 2017030906136833
            [buyer_logon_id] => 157****3791
            [point_amount] => 0.00
        )
         **********************/
        $request -> setNotifyUrl($bizContent['notify_url']);

        $result = $aop->pageExecute($request);

        return $result;

    }

    /**
     * WEB 支付宝支付
     * @param $bizContent 参数集合
     * 主要参数如下 【app_id / method / format / charset / sign_type / timestamp / version  不需要传 特殊情况除外】
     * body 对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
     * subject 	商品的标题/交易标题/订单标题/订单关键字等。
     * out_trade_no  商户网站唯一订单号
     * total_amount  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
     * notify_url   	支付宝服务器主动通知商户服务器里指定的页面http/https路径。建议商户使用https
     * 可选参数
     * product_code  销售产品码，与支付宝签约的产品码名称。 注：目前仅支持FAST_INSTANT_TRADE_PAY
     * timeout_express 该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。注：若为空，则默认为15d。
     * @return string
     */
    static function alipayWebBuild($bizContent = array())
    {

        //检查请求参数
        self::checkPayArgs($bizContent);

        $aop = new AopClient ();

        $request = new AlipayTradePagePayRequest ();

        $bizcontent = json_encode([
            'body'          => $bizContent['body'],      //	对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
            'subject'       => $bizContent['subject'],  //商品的标题/交易标题/订单标题/订单关键字等。
            'out_trade_no' => $bizContent['out_trade_no'], //此订单号为商户唯一订单号
            'total_amount' => $bizContent['total_amount'] ,   //	订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
            'product_code' => 'FAST_INSTANT_TRADE_PAY'//销售产品码，与支付宝签约的产品码名称。 注：目前仅支持FAST_INSTANT_TRADE_PAY'
        ]);

        //设置请求参数
        $request->setBizContent($bizcontent);

        //设置前台回调
        /*******
        Array(
            [total_amount] => 0.01
            [timestamp] => 2018-04-21 11:31:55
            [sign] => FgC7fYCqjPgpJNMg2xe9LtCMOW/cwuKN8iRUkX12ua3yYdndAIaKkjSJDepPmKZTBUcYMkxotUKvaJZ6qh+0nOa60fVzJpOCT3+oBldQWOJRHDrsDN72hjK7Iqy4vP1Wi8KheWArlWIImYZakJOoQe7woXalhn5nMdgstjKw6TJYrT36JWU4OeCkrb2HhOFcG89yClTgKEv6tgDwitZpB5zcZqXouEd9P/B0aksCH00/mu62M61KFYsRPiGm6RjN9C/mIjWTvyZsQpqwfOhtrEuVAXp66kf7ZIJstevG4cuiVqkqWsi1Pkzy9dWjcjb+j2aj/AJg+64EqThbA79jrA==
            [trade_no] => 2018042121001004080572861593
            [sign_type] => RSA2
            [auth_app_id] => 2017030906136833
            [charset] => UTF-8
            [seller_id] => 2088621252187600
            [method] => alipay.trade.page.pay.return
            [app_id] => 2017030906136833
            [out_trade_no] => 15242814935484174
            [version] => 1.0
        )
         **********************/
        $request->setReturnUrl($bizContent["return_url"]);

        //设置异步回调
        /************参数如下
        Array(
            [alipay_notify] => 1
            [gmt_create] => 2018-04-21 11:31:39
            [charset] => UTF-8
            [gmt_payment] => 2018-04-21 11:31:44
            [notify_time] => 2018-04-21 11:31:45
            [subject] => 这是商品标题
            [sign] => TJIKUZh22QRTyTxA2CTN7jGeBkFEUkm8g1GK6kDWnw6Ez5IOL458piAJld5PrdBwEfOcLuvMZDpQwdI3iOqlInr7l+mn+jGGGdiIUXTrdGLlRPXOA6KSTFPnx0WX5WkgQv9yRdsaNWzZi53CuidQp/LrorSjxzAu0Xjx1FFnPe8fhysT6+dnKvuOkAPEP5NASA9k0KCcQtTwK5fdtAM2e99+vv29jaxENwEwHe4Cgy2yUfBPcTvLj0rtrnJ574Hipy9edO32oyVr+EnOcfw8XQkIO/TL2hk2EJ8yvpAsdLcpuQGsGV8yjncGoZ58XuX+G4NDeI7EY112uMjlknYKrQ==
            [buyer_id] => 2088312479093080
            [body] => 这是商品详情
            [invoice_amount] => 0.01
            [version] => 1.0
            [notify_id] => 472c02a2b67ef297d67b505e878cec0gmd
            [fund_bill_list] => [{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]
            [notify_type] => trade_status_sync
            [out_trade_no] => 15242814935484174
            [total_amount] => 0.01
            [trade_status] => TRADE_SUCCESS
            [trade_no] => 2018042121001004080572861593
            [auth_app_id] => 2017030906136833
            [receipt_amount] => 0.01
            [point_amount] => 0.00
            [app_id] => 2017030906136833
            [buyer_pay_amount] => 0.01
            [sign_type] => RSA2
            [seller_id] => 2088621252187600
        )
         **********************/
        $request->setNotifyUrl($bizContent["notify_url"]);

        $result = $aop->pageExecute ($request);

        return $result;

    }

    /**
     * 支付宝支付回调
     */
    static function alipayNotify()
    {

        global $_QASIM;

        $notifyData = $_QASIM['args'];

        $isRefund = $notifyData['refund_fee'] > 0 ? true :false;

        $filename = $isRefund ? LOG. 'alipay' .DS.'refund'.DS.date('Ymd').DS .date('H').'.log' : LOG. 'alipay' .DS.date('Ymd').DS .date('H').'.log';

        //初始化日志
        Log::Init($filename);

        Log::DEBUG("回调参数 \r\n".print_r($notifyData , true)."******************************************\r\n");

        /**********支付成功异步回调
        Array
        (
            [A_Notify] => 1
            [gmt_create] => 2018-04-24 11:49:02
            [charset] => UTF-8
            [gmt_payment] => 2018-04-24 11:49:10
            [notify_time] => 2018-04-24 11:49:11
            [subject] => 标题
            [sign] => FOzKTi3NZ9G0Cbegpvj30IvK1X9tUp3dmNCT6++K1bSWOirfnFQWRgT+6jrLVige9qD35PTx2lVynU9tzJo2zelmpc6VqHqLeNSYXWTCTeuxGG/Y90eRYwcdHXyL39AqsZer6M8TAzEBep7qndGvrrjbJ6jum1f7LvHTy44mW1IwleUrPKn5x6W9o+1tuznCVGCOOIuBz9WnCYExuuHSyU/bqmkxPbOugiQ8gjiXAqv/hdIyUnwtcOifys+PxZGx21QOEqVhzGSHo3bECv9gt3euPiAt4PsP5BlBaXqzP0vHHoVZgLWkmUeAzcyxFoFqRI6WzFla1vwPRJSGWuE3SA==
            [buyer_id] => 2088312479093080
            [body] => 描述信息
            [invoice_amount] => 0.01
            [version] => 1.0
            [notify_id] => 6f85a6fd1992b1401b7019695317627gmd
            [fund_bill_list] => [{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]
            [notify_type] => trade_status_sync
            [out_trade_no] => 1524541730
            [total_amount] => 0.01
            [trade_status] => TRADE_SUCCESS
            [trade_no] => 2018042421001004080585187168
            [auth_app_id] => 2017030906136833
            [receipt_amount] => 0.01
            [point_amount] => 0.00
            [app_id] => 2017030906136833
            [buyer_pay_amount] => 0.01
            [sign_type] => RSA2
            [seller_id] => 2088621252187600
        )
        *********/

        /**********退款成功异步回调
        Array
        (
            [A_Notify] => 1
            [gmt_create] => 2018-04-24 11:49:02
            [charset] => UTF-8
            [gmt_payment] => 2018-04-24 11:49:10
            [notify_time] => 2018-04-24 11:52:28
            [subject] => 标题
            [gmt_refund] => 2018-04-24 11:52:28.316
            [sign] => nB1dY+FZPd96pN5VUbcnCxuOmCoC2fykqz/yu+hqt+Cmlnvb9yqnToer+MocydpWVrERLfuLTbhru4MSxvwLkAKKNfNmFtAtnQqK2U2bJYkxqgaryl6b/EMyEFovGxfvEDJI8Qn7B3O01t0Cdtwa62hdKZK4bzroB8V9AhuHUkkNjYHO57Tx12Q9vpBSyk/r9RS8/XlyxLKBbiny3QMRlWjHEvYDVmTPuazg9K3vHbeCvulqFcvuJC6PpmIy817plIv8TyYQ0p+rj8CKX+0QukDIYVDB9sfBxj6gWDOZ7S6rfhXP1Hq0Y+XJxw7OalYAZezSPYbun2brE/uumQ0UZw==
            [out_biz_no] => 1524541730
            [buyer_id] => 2088312479093080
            [body] => 描述信息
            [version] => 1.0
            [notify_id] => 840e3005699c797a51316b93e668afagmd
            [notify_type] => trade_status_sync
            [out_trade_no] => 1524541730
            [total_amount] => 0.01
            [trade_status] => TRADE_SUCCESS
            [refund_fee] => 0.01
            [trade_no] => 2018042421001004080585187168
            [auth_app_id] => 2017030906136833
            [app_id] => 2017030906136833
            [sign_type] => RSA2
            [seller_id] => 2088621252187600
        )
         *********/
        //判断支付结果或退款结果
        if($notifyData['trade_status'] == 'TRADE_SUCCESS'){
            //是否验签
            //在通知返回参数列表中，除去sign、sign_type两个参数外，凡是通知返回回来的参数皆是待验签的参数
            //将剩下参数进行url_decode, 然后进行字典排序，组成字符串，得到待签名字符串
            //对待签名字符串进行签名  得出签名字符串 generateSign(签名字符串 , 签名算法类型);
            //签名字符串与回调的签名字符串进行匹配
            //打印成功数据
            if($isRefund)//退款处理逻辑
            {

            }else{//支付成功处理逻辑

            }
            echo 'success';
        }
        else{
            //失败处理逻辑
            //打印日志
            echo 'fail';
        }

    }


    /**
     * 支付宝订单查询
     * @param $bizContent 查询参数
     * @return bool
     */
    static function alipayQuery($bizContent = array())
    {

        $aop = new AopClient ();

        $request = new AlipayTradeQueryRequest ();

        $bizcontent = json_encode([
            'out_trade_no' => $bizContent['out_trade_no'],
            'trade_no' => $bizContent['trade_no']
        ]);

        $request->setBizContent($bizcontent);

        $result = $aop->execute ( $request);
        /************参数如下
        object(stdClass)#8 (2) {
            ["alipay_trade_query_response"]=>
            object(stdClass)#7 (13) {
            ["code"]=>
            string(5) "10000"
            ["msg"]=>
            string(7) "Success"
            ["buyer_logon_id"]=>
            string(11) "157****3791"
            ["buyer_pay_amount"]=>
            string(4) "0.00"
            ["buyer_user_id"]=>
            string(16) "2088312479093080"
            ["invoice_amount"]=>
            string(4) "0.00"
            ["out_trade_no"]=>
            string(17) "15241238309580809"
            ["point_amount"]=>
            string(4) "0.00"
            ["receipt_amount"]=>
            string(4) "0.00"
            ["send_pay_date"]=>
            string(19) "2018-04-19 15:43:56"
            ["total_amount"]=>
            string(4) "0.01"
            ["trade_no"]=>
            string(28) "2018041921001004080566741185"
            ["trade_status"]=>
            string(13) "TRADE_SUCCESS"
            }
            ["sign"]=>
            string(344) "JUwQLmvl+mHBgL0ngnBYJSXsz3latYjc4i2RzjEV7xBYUeM/7zXQfGtgK0K44edp1gYaejlF8BCC5bIO4uLxktS4F610cX0iY8uMaO8U9VqK4r2GiBzXwCeSueVbD86pACmARSamQ+C4YPD5TyM84pxn+W6+GVu74o3NWB6v1i3D0mKK/si6anszIz5k1+cs19/Yy77glH38SOF4zmnsb41Mak7ws7bIUltungmsuiDNwphJCs7jDvknhy7TVxE4n0HM7tgOF+PR8iQnz44VtTiCN+J3dnPIfdExZUg6y7zMalhWamhsUN13wTJAzyiNPf332K0YQmYV/mBK+51APg=="
        }
         **********************/
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        $resultCode = $result->$responseNode->code;
//        $ret = json_decode(json_encode($result->$responseNode , true) , true) ;//数组形式

        if (!empty($resultCode) && $resultCode == 10000) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 支付宝退款
     * @param $args 参数集合
     * out_trade_no  商户网站唯一订单号
     * trade_no 支付宝交易号
     * refund_amount  需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
     * out_request_no 标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
     * 可选参数
     * refund_reason  	退款的原因说明
     * @return string
     */
    static function alipayRefund($bizContent = array())
    {

        empty($bizContent['out_request_no']) and exit("商户订单号不能为空！");

        empty($bizContent['trade_no']) and exit("支付宝交易号不能为空！");

        empty($bizContent['refund_amount']) and exit("退款金额不能为空！");

        $aop = new AopClient ();

        $request = new AlipayTradeRefundRequest ();

        //查询订单是否存在
        $checkOrder =  self::alipayQuery($bizContent);

        if(!$checkOrder)
        {
            exit("订单交易不存在！");
        }

        $bizcontent = json_encode([
            'out_trade_no' => $bizContent['out_trade_no'],
            'trade_no' => $bizContent['trade_no'],
            'refund_amount' => $bizContent['refund_amount'],
            'refund_reason' => @$bizContent['refund_reason']
        ]);

        $request->setBizContent($bizcontent);

        $result = $aop->execute($request);

        /************参数如下
        object(stdClass)#6 (2) {
            ["alipay_trade_refund_response"]=>
            object(stdClass)#5 (10) {
            ["code"]=>
            string(5) "10000"
            ["msg"]=>
            string(7) "Success"
            ["buyer_logon_id"]=>
            string(11) "157****3791"
            ["buyer_user_id"]=>
            string(16) "2088312479093080"
            ["fund_change"]=>
            string(1) "N"
            ["gmt_refund_pay"]=>
            string(19) "2018-04-19 17:22:33"
            ["out_trade_no"]=>
            string(17) "15241238309580809"
            ["refund_fee"]=>
            string(4) "0.01"
            ["send_back_fee"]=>
            string(4) "0.00"
            ["trade_no"]=>
            string(28) "2018041921001004080566741185"
            }
            ["sign"]=>
            string(344) "PM0RW40jY66DUjCtKfHffxDd0ufBY6d9qmPRwlyHAWRd8r33pHGhelPG3UUrBB9cVhrqH6KQ5gByyUB72RJ6I6OR3Rjap8uHcwcnWvcIY0Dq3BOLmTIK55pCDVIvSDRvH9XrEbw46oraRu5ITlKZlaIsSe+y+xNkpSG6T9BwhLtr7HKI0x04tHGWpcuXuS9VBcPmLO3yq+SP/ZeP183pzj1XxzD//yxF77ynOBvyMYrPuRlKP/KIU6ZRzOAIqpenzNpoLIdcGMe90bYpwQslwlSW/mGjTpCV+VPYA5cU5vaqYQ9hJQGOaBUHIn1QRgTe2bqOSp+ZVBH+MmFo0PO4AQ=="
        }
         **********************/

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        $resultCode = $result->$responseNode->code;
        //$ret = json_decode(json_encode($result->$responseNode , true) , true) ;//数组形式
        if (!empty($resultCode) && $resultCode == 10000) {
            //逻辑处理
            //退款日志
            return "退款成功";
        } else {
            //退款日志
            return "退款失败";
        }
    }

    /**
     * 支付宝退货查询
     * @param $bizContent 退款参数
     * @return bool
     */
    static function alipayRefundQuery($bizContent = array())
    {

        $aop = new AopClient ();

        $request = new AlipayTradeFastpayRefundQueryRequest ();

        $bizcontent = json_encode([
            'out_trade_no' => $bizContent['out_trade_no'],//创建请求编号
            'trade_no' => $bizContent['trade_no'],//交易串号
            'out_request_no' => $bizContent['out_request_no']//原退款请求编号
        ]);

        $request->setBizContent($bizcontent);

        $result = $aop->execute ($request);

        /************参数如下
        object(stdClass)#8 (2) {
            ["alipay_trade_fastpay_refund_query_response"]=>
            object(stdClass)#7 (7) {
            ["code"]=>
            string(5) "10000"
            ["msg"]=>
            string(7) "Success"
            ["out_request_no"]=>
            string(17) "15241238309580809"
            ["out_trade_no"]=>
            string(17) "15241238309580809"
            ["refund_amount"]=>
            string(4) "0.01"
            ["total_amount"]=>
            string(4) "0.01"
            ["trade_no"]=>
            string(28) "2018041921001004080566741185"
            }
            ["sign"]=>
            string(344) "fFrIiAOELdQc89FKRcsgORcumCSBPeEu3bjRZDZA3mi373vUj2EXqkJQA4JOsdtY+lgPCAx/QWFs0ylLZYowwHKJJ/EZQGtfrUVobkIxUJRwK5Tc7//EHhCq04EgEQojpBSD2sjSkqJEgBMOaBA4ZYQjUvAZ5NyDM+4sO94kNeytDLHYq+IfM6FO8iwNjcMnONlNObxzPn6wnefCn67tW9D6kPt5RYiflE+Uu9oqrl2Ym00fbGXBLHS0AxJJYLOZaC/jiwGQFzSyJ0J+mB0wjMXsVBrn50AkKOnOscpG/ZRE8OS20OsOw5tFctSO0HhAVGV83g+y93gNBkocrZYeIQ=="
        }
         **********************/

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        $resultCode  = $result->$responseNode->code;

//        $ret = json_decode(json_encode($result->$responseNode , true) , true) ;//数组形式

        if(!empty($resultCode) && $resultCode == 10000){
            return true;
        } else {
            return false;
        }

    }

    /**
     * 检查必要查询是否为空请求
     */
    static function  checkPayArgs($args)
    {

        empty($args['body']) and exit("商品描述不能为空！");

        empty($args['subject']) and exit("商品标题不能为空！");

        empty($args['out_trade_no']) and exit("商户订单号不能为空！");

        empty($args['total_amount']) and exit("商品金额不能为空！");

        empty($args['notify_url']) and exit("异步回调不能为空！");

    }

}

