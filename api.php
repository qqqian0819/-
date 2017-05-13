<?php
/**
 * @author qqqian
 * wechat php test
 * [*****]均需填写自己的内容
*/
//define your token
define("TOKEN", "****");
$wechatObj = new wechatCallbackapiTest();
// $wechatObj->valid();
$wechatObj->responseMsg();
class wechatCallbackapiTest{
	// 验证信息
	public function valid()
	    {
	        $echoStr = $_GET["echostr"];
	        //valid signature , option
	        if($this->checkSignature()){
	        echo $echoStr;
	        exit;
	        }
	    }
    // 响应信息
    public function responseMsg(){
		//$GLOBALS["HTTP_RAW_POST_DATA"]接收http数据与$_POST相似，但是global可以接收xml数据
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
	    //extract post data
		if (!empty($postStr)){
            // 载入xml到字符串中
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            // 微信的响应端 即手机端
            $fromUsername = $postObj->FromUserName;
            // 微信公众平台
            $toUsername = $postObj->ToUserName;
            // 接收用户发送过来的数据存储在keyword中
            $keyword = trim($postObj->Content);
            // 时间戳
            $time = time();
            // 定义接收过来的类型
            $msgType=$postObj->MsgType;
            // 定义文本信息xml模版
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
						</xml>";
			// 用户发送类型：text image voice video shortvideo location link 
			if($msgType=='text'){
				// 判断用户传递过来文本是否为空
				if(!empty( $keyword )){
					//聚合机器人api 申请自己的appkey
					header('Content-type:text/html;charset=utf-8');
					$appkey = "**********************************";
					$url = "http://op.juhe.cn/robot/index";
					// json格式包装数组
					$params=array(
					    "key"=>$appkey,//申请到的本接口专用的APPKEY
					    "info"=>$keyword,//要发送给机器人的内容，不要超过30个字符
					    "dtype"=>"",//,//返回的数据的格式，json或xml，默认为json
					    "loc"=>"",////地点，如北京中关村
					    "lon"=>"",//经度，东经116.234632（小数点后保留6位），需要写为116234632
					    "lat"=>"",//纬度，北纬40.234632（小数点后保留6位），需要写为40234632
					    "userid"=>""//1~32位，此userid针对您自己的每一个用户，用于上下文的关联
					);

					// http_build_query使用给出的关联（或下标）数组生成一个经过 URL-encode 的请求字符串。
					$paramstring=http_build_query($params);

					/**
					 * 请求接口返回内容
					 * @param  string $url [请求的URL地址]
					 * @param  string $params [请求的参数]
					 * @param  int $ipost [是否采用POST形式]
					 * @return  string
					 */

					// ----------------1 初始化-------------------------------
					$ch=curl_init();

					// ----------------2 设置参数-------------------------------


					// 强制curl使用 HTTP/1.1版本
					curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
					// 在HTTP请求中包含一个"User-Agent: "头的字符串
					curl_setopt( $ch, CURLOPT_USERAGENT , 'qinwanqian' );
					// 在尝试连接时等待的秒数。设置为0，则无限等待。
					curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
					//  允许 cURL 函数执行的最长秒数。
					curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
					// 将curl_exec()获取的信息以字符串返回，而不是直接输出。
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
					// 根据服务器返回 HTTP 头中的 "Location: " 重定向。
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					// 设置请求url
					$url="http://op.juhe.cn/robot/index";
					curl_setopt( $ch , CURLOPT_URL , $url.'?'.$paramstring );

					// ----------------3 执行curl-------------------------------

					$output=curl_exec($ch);
					$contentStr=json_decode($output,true)["result"]["text"];
					$httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
					$httpInfo = array();
					$httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );

					// ----------------4 关闭句柄-------------------------------
					curl_close($ch);

					// 返回
					$msgType = "text";
			   	    // sprintf(格式化字符串，格式化变量...)把字符串按指定模式进行格式化%s(字符串)
		     	    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
	          	 	echo $resultStr;
	          		
		        }

			}else if($msgType=='event'&&$event='subscribe'){
	        	// 定义返回信息类型
		        $msgType = "text";
		        // 返回相应信息
		        $contentStr = "我们来尴聊，但是我可能会分分钟把天聊死哦";
		        // sprintf(格式化字符串，格式化变量...)把字符串按指定模式进行格式化%s(字符串)
		        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
	            echo $resultStr; 
	        }
        }else {
        	echo "";
        	exit;
       	}

    }
    // 校验签名
	private function checkSignature(){
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
}
