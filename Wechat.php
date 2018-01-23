<?php
define("TOKEN", "123123");
$obj=new Wechat();
$obj->GetAccesstoken();
//效验
$obj->valid();
//消息验证
$obj->responseMsg();
//自定义菜单
$obj->CreateNenu();


Class Wechat
{
    public $appID="wxe6d6f58f6380064e";
    public $appsecret="bf265c3b3c76932a321e11c26ef94878";
    //效验url
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    //消息验证
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = file_get_contents("php://input") ;//将用户端放松的数据保存到变量postStr中

        //extract post data如果用户端数据不为空，执行30-55否则56-58  

        if (!empty($postStr)){

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);//将postStr变量进行解析并赋予变量postObj
            $fromUsername = $postObj->FromUserName;//将微信用户端的用户名赋予变量FromUserName
            $toUsername = $postObj->ToUserName;//将你的微信公众账号ID赋予变量ToUserName
            $keyword = trim($postObj->Content);//将用户微信发来的文本内容去掉空格后赋予变量keyword
            $time = time();//将系统时间赋予变量time
            //构建XML格式的文本赋予变量textTpl，注意XML格式为微信内容固定格式，详见文档  

            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                        </xml>";
            if ($postObj->MsgType == 'event') {
                //判断事件类型是否为订阅事件
                if ($postObj->Event == 'subscribe') {
                    $msgType = "text";
                    $contentStr = '欢迎来到微信世界';

                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
            }
            if(!empty( $keyword ))
            {
                if($keyword=="谢谢"){
                    $msgType ="text";
                    $contentStr ="不用客气，我就是个机器人而已";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else{
                    //图灵机器人回复
                    $count=$postObj->Content;
                    $url="http://api.qingyunke.com/api.php?key=free&appid=0&msg=".urlencode($count);
                    $json_str=file_get_contents($url);
                    $arr_str=json_decode($json_str,true);
                    $msgType = "text";
                    $contentStr = $arr_str['content'];
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
                if($keyword=="群发"){
                    //群发
                    $this->SendAll();die;
                }
                if($keyword=="模板"){
                    //发送模板
                    $this->GetTemplate();die;
                    $this->CreateNenu();die;
                }

            }
            else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
    }
    //加密
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    //群发
    public function SendAll()
    {

        $url="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$this->GetAccesstoken();
        $post_data = '{
   "touser":'.$this->GetOpenid().',
    "msgtype": "text",
    "text": { "content": "12132"}
}';
        echo $this->Curl($url,$post_data);
    }
    //获取access_token
    public function GetAccesstoken(){
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appID&secret=$this->appsecret";
        $res=file_get_contents($url);
        $re =json_decode($res,true);
        return $re['access_token'];
    }
    //获取关注的用户列表
    public function GetOpenid(){
        $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->GetAccesstoken();
        $res=file_get_contents($url);
        $openid=json_decode($res,true)['data']['openid'];
        $re=json_encode($openid);
        return $re;
    }
    //模拟请求
    public function Curl($url,$post_data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $output = curl_exec($ch);
        curl_close($ch);

        //打印获得的数据
        return $output;
    }
    //获取模板消息
    public function GetTemplate(){
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->GetAccesstoken();
        $post_data='{
           "touser":"oY3bMw8iUeSO8f3uKvMPwWPb1coo",
           "template_id":"Xml8k9OFzEdPLtbFweR77htp7Za7mWqbrqGRQOy6EiY",
           "url":"http://baidu.com",  
         
           "data":{
                   "first": {
                       "value":"恭喜你购买成功！",  
                       "color":"#173177"
                   },
                   "keynote1":{
                       "value":"巧克力",
                       "color":"#173177"
                   },
                   "keynote2": {
                       "value":"39.8元",
                       "color":"#173177"
                   },
                   "keynote3": {
                       "value":"2014年9月22日",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"欢迎再次购买！",
                       "color":"#173177"
                   }
           }
       }';
       // $res=json_decode($post_data,true);
       // echo $res;die;
       echo $this->Curl($url,$post_data);
    }




    //自定义菜单及授权登录
    public function CreateNenu(){
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->GetAccesstoken();
        $post_data='{
    "button": [
        {
            "name": "扫码",
            "sub_button": [
                {
                    "type": "scancode_waitmsg",
                    "name": "扫码带提示",
                    "key": "rselfmenu_0_0",
                    "sub_button": [ ]
                },
                {
                    "type": "scancode_push",
                    "name": "扫码推事件",
                    "key": "rselfmenu_0_1",
                    "sub_button": [ ]
                },
                {
                    "type":"view",
                    "name":"微信授权",
                    "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx520c15f417810387&redirect_uri=http://39.106.204.146/chang/user.php&response_type=code&scope=snsapi_base&state=123#wechat_redirect"
                },
                {
                    "type":"view",
                    "name":"抽奖",
                    "url":"'.$uri.'"
                }
            ]
        },
        {
            "name": "发图",
            "sub_button": [
                {
                    "type": "pic_sysphoto",
            "name": "扫码",
            "sub_button": [
                {
                    "type": "scancode_waitmsg",
                    "name": "扫码带提示",
                    "key": "rselfmenu_0_0",
                    "sub_button": [ ]
                },
                {
                    "type": "scancode_push",
                    "name": "扫码推事件",
                    "key": "rselfmenu_0_1",
                    "sub_button": [ ]
                },
                {
                    "type":"view",
                    "name":"微信授权",
                    "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx520c15f417810387&redirect_uri=http://39.106.204.146/chang/user.php&response_type=code&scope=snsapi_base&state=123#wechat_redirect"
                },
                {
                    "type":"view",
                    "name":"获取信息",
                    "url":"'.$uri.'"
                },
                {
                    "type":"view",
                    "name":"抽奖",
                    "url":"'.$uri.'"
                }
            ]
        },
        {
            "name": "发图",
            "sub_button": [
                {
                    "type": "pic_sysphoto",
                    "name": "系统拍照发图",
                    "key": "rselfmenu_1_0",
                   "sub_button": [ ]
                 },
                {
                    "type": "pic_photo_or_album",
                    "name": "拍照或者相册发图",
                    "key": "rselfmenu_1_1",
                    "sub_button": [ ]
                }
            ]
        },
        {
            "name": "发送位置",
            "type": "location_select",
            "key": "rselfmenu_2_0"
        },
    ]
}';
       echo $this->Curl($url,$post_data);
    }
}

?>