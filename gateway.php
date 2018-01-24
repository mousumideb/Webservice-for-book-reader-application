<?php

//$payment_json ='{"payment_json":{"bank_name":"","device_email":"dfsdg@gmail.com","payment":"5.99","bkash_transaction_id":"","book_id":15,"bundle_id":0,"device_id":"357715885787248","bank_account_number":"","payment_type":"1","poka_id":" ","activation_code":"5"}}';
//$payment_json = '{"payment_json":{"bank_name":" ","payment":"2.99","bkash_transaction_id":"7825259863","activation_code":"BXRHZQwgv7d","device_email":"mithunaust1990@gmail.com","book_id":7,"bundle_id":0,"device_id":"358718765770248","bank_account_number":" ","payment_type":"2","poka_id":" "}}';
$payment_json = '{"payment_json":{"bank_name":"","device_email":"project.geeft@gmail.com",
"payment":"1.99","bkash_transaction_id":"1005034716","book_id":"","bundle_id":2,
"bdt_price":"50","device_id":"357712885784248","bank_account_number":"","payment_type":"2","poka_id":" ","activation_code":""}}';

//$payment_json = $_POST['payment_process'];
$payment_json_decode = json_decode($payment_json,true);
//echo "<pre>";print_r($payment_json_decode);echo "</pre>";

if(!empty($payment_json_decode)){
	checkactivated($payment_json_decode['payment_json']['book_id'],$payment_json_decode['payment_json']['bundle_id'],$payment_json_decode);
}

function checkactivated($book_id, $bundle_id, $payment_json_decode){
	$con = mysqli_connect('localhost', 'root', '123456', "bbh_apps");
	if (mysqli_connect_errno()){
		echo "db-error";
	} 
	
	if (!empty($book_id)) {
		$s = "SELECT active FROM `books` WHERE `books`.`id` = '".$book_id."' ";
		$result = mysqli_query($con,$s) or die(mysqli_errno());
	
		if($result)
		{
			$row=mysqli_fetch_assoc($result);
			//print_r($row);
			if($row['active']==0){
				$message = array('message'=>'This Book is Unavailable');
			 	echo json_encode($message); die();
			}
		
			else{
			
				if($payment_json_decode['payment_json']['payment_type']==1){
					//echo "you are choosing activationcode.";
					include_once('acodeFinal.php');
					transaction_pro($payment_json_decode['payment_json']);
				}
				if($payment_json_decode['payment_json']['payment_type']==2){
					
					
					include_once('bakashtransction.php');
					bkastransaction_pro($payment_json_decode['payment_json']);
					//transaction_pro($payment_json_decode);
				}
				if($payment_json_decode['payment_json']['payment_type']==3){
					//echo "you are choosing bank.";
					$message = array('message'=>'This Feature is Underconstruction');
		 			echo json_encode($message); die();
				}
			}
		}
		else{
			$message = array('message'=> 'failed');
				echo json_encode($message); die();
		}
	}
	elseif (!empty($bundle_id)) {
		$s = "SELECT * FROM `book_bundle` WHERE `book_bundle`.`id` = '".$bundle_id."' ";
		$result = mysqli_query($con,$s) or die(mysqli_errno());
		if($result)
		{
			$row=mysqli_fetch_assoc($result);
			
			if($row == 0){
				$message = array('message'=>'This Bundle is Unavailable');
			 	echo json_encode($message); die();
			}
			else{
				if($payment_json_decode['payment_json']['payment_type'] == 1)
				{
					//echo "you are choosing activationcode.";
					include_once('acodeFinal.php');
					transaction_pro($payment_json_decode['payment_json']);
				}
			}
			if($payment_json_decode['payment_json']['payment_type']==2){
					
					
					include_once('bakashtransction.php');
					bkastransaction_pro($payment_json_decode['payment_json']);
					//transaction_pro($payment_json_decode);
			}
			
		}
	}
}
	
	
	
	//echo "sdfdsfs";
	////die('asdsadsa...');
    //echo first(1,"omg lol");
	

//echo $device_id;

?>
