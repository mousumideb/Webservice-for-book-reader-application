<?php
//die("ami sesh....");
$test=$_GET["id"];

$data = array(
 "user" => "DATASOFTSYSTEMS", "pass" => "d3ltaf0rc3","msisdn" => "01858339814","trxid" => "1005034716"
);
//echo  die();
//echo parse_url($url, PHP_URL_PATH);
//echo $test=$_GET["id"]; die();
	
//echo "<pre>"; print_r($data);

$url_send ="http://www.bkashcluster.com:9080/dreamwave/merchant/trxcheck/sendmsg";
$str_data = json_encode($data);
//print_r($str_data);
function sendPostData($url, $post){

   $headers= array('Accept: application/json','Content-Type: application/json');  
     $ch = curl_init($url);
      
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);   
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 

  $result = curl_exec($ch);
  curl_close($ch);  
  return $result;
 
}

echo " " . sendPostData($url_send, $str_data);
$payment_json=sendPostData($url_send, $str_data);

 $bkashpayment_json_decode = json_decode($payment_json,true);
  echo "<pre>";print_r($payment_json);echo "</pre>";
 // echo "<pre>";print_r($bkashpayment_json_decode);echo "</pre>";

?>
