<?php
function sendPostData($url, $post) {
	$headers = array('Accept: application/json', 'Content-Type: application/json');
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function checkbkashactivation($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id) {

	$data = array("user" => "DATASOFTSYSTEMS", "pass" => "d3ltaf0rc3", "msisdn" => "01858339815", "trxid" => $trxId);

	$url_send = "http://mobioapp.com/mobioapps-ideas/bkash/bkashtest.php?id=$trxId";
	$str_data = json_encode($data);
	$con = mysqli_connect('localhost', 'root', '123456', "bbh_apps");

	//echo " " . sendPostData($url_send, $str_data);
	//$bkashpayment_json = '';
	for ($i = 0; $i < 3; $i++) {
		$bkashpayment_json = sendPostData($url_send, $str_data);
		if (!empty($bkashpayment_json)) {
			break;
		}
	}
	//    $bkashpayment_json=sendPostData($url_send, $str_data);
	if (empty($bkashpayment_json)) {
		$message = array('message' => 'Could not connect to bKash server at this moment. Please Try again later');
		echo json_encode($message);
		die();
	}

	$bkashpayment_json_decode = json_decode($bkashpayment_json, true);
	//echo "<pre>";print_r($bkashpayment_json_decode);echo "</pre>";
	$amount = $bkashpayment_json_decode['transaction']['amount'];
	//echo $amount;
	$transactionarray = array("0" => "0000", "1" => "0010", "2" => "0011", "3" => "0100", "4" => "0111", "5" => "1001", "5" => "1002", "6" => "1003", "7" => "1004", "8" => "9999");

	$bkashTransactionArray = $bkashpayment_json_decode['transaction'];

	$transactionid = -1;
	foreach ($transactionarray as $key => $value) {
		if ($bkashTransactionArray['trxStatus'] == $value) {
			$transactionid = $key;
			break;
		}
	}

	$con = mysqli_connect('localhost', 'mobioapp_appuser', 'Mobi@13#', "mobioapp_bbh_apps");
	$s = "SELECT * FROM `payment_history` WHERE `payment_history`.`trxId` = '" . $trxId . "' ";
	if ($bkashTransactionArray['trxStatus'] == '0000') {

		//	echo 'success';
		// $bkashTransactionArray['trxStatus'] == $transactionarray[$i];
		// $transactionid = $i;
		$date = $bkashTransactionArray['trxTimestamp'];
		if (mysqli_connect_errno()) {
			echo "db-error";
		}

		$result = mysqli_query($con, $s) or die(mysqli_errno());

		if ($result) {
			$row = mysqli_fetch_assoc($result);
			//echo 'Row: '.print_r($row);
			//print_r($row);
			//echo $amount;
			//echo $bookPrice;
			if ($row == 0) {
				if ($amount >= $bookPrice) {

					$query = "INSERT INTO payment_history (trxId, amount,user_email,price,book_id,date,transaction_status,bundle_id,deviceID)
						VALUES ('$trxId', '$amount','$email','$bookPrice','$book_id','$date','$transactionid','$bundle_id','$device_id')";
					$result = mysqli_query($con, $query);
					sendmailsales($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);

					if ($result) {
						$message = array('message' => 'success');
						echo json_encode($message);
						die();
					}
				} else {
					$message = array('message' => 'Failed Amount is less than Price');
					echo json_encode($message);
					die();
				}

			} else {
				if ($row['user_email'] == $email && (($book_id !=0 && $row['book_id'] == $book_id) || ($bundle_id !=0 && $row['bundle_id'] == $bundle_id))) {

					if ($row['transaction_status'] == 0) {
						$message = array('message' => 'Success');
						echo json_encode($message);
						die();
					} else {
						$update_sql = "UPDATE payment_history SET transaction_status = '" . $transactionid . "' WHERE `payment_history`.`trxId` = '" . $trxId . "' ";
						$result = mysqli_query($con, $update_sql) or die(mysqli_errno());

						if ($result) {
							sendmailsales($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);
							$message = array('message' => 'success');
							echo json_encode($message);
							die();
						}
					}
				} else {
					$message = array('message' => 'Failed TransactionId already used');
					echo json_encode($message);
					die();
				}
			}
		} else {
			$message = array('message' => 'Failed');
			echo json_encode($message);
			die();
		}

		if (!empty($payment_json_decode)) {
			// echo $payment_json_decode['transaction']['trxId'];
			checkbkashtranx($bkashTransactionArray['trxId']);
		}
	} else {
		if ($bkashTransactionArray['trxStatus'] == '0010' || $bkashTransactionArray['trxStatus'] == '0011') {

			$result = mysqli_query($con, $s) or die(mysqli_errno());

			if ($result) {
				$row = mysqli_fetch_assoc($result);

				if ($row == 0) {

					$query = "INSERT INTO payment_history (trxId, amount,user_email,price,book_id,date,transaction_status,bundle_id,deviceID)
						VALUES ('$trxId', '$amount','$email','$bookPrice','$book_id','$date','$transactionid','$bundle_id','$device_id')";
					$result = mysqli_query($con, $query);
					$message = array('message' => 'Transaction Pending, please try again later');
					echo json_encode($message);
				} else {

					$update_sql = "UPDATE payment_history SET transaction_status = '" . $transactionid . "' 
				WHERE `payment_history`.`trxId` = '" . $trxId . "' ";
					$result = mysqli_query($con, $update_sql) or die(mysqli_errno());

					$message = array('message' => 'Transaction Pending, please try again later');
					echo json_encode($message);
				}
			}
		} else if ($bkashTransactionArray['trxStatus'] == '0100') {
			$message = array('message' => 'Transaction Reversed. Please try with a valid transaction');
			echo json_encode($message);
			die();
		} else if ($bkashTransactionArray['trxStatus'] == '0111') {
			$message = array('message' => 'Transaction Failure. Please try with a valid transaction');
			echo json_encode($message);
			die();
		} else if ($bkashTransactionArray['trxStatus'] == '1001') {
			$status = $bkashTransactionArray['trxStatus'];
			sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id, $status);
			$message = array('message' => 'Failed to process your request. Please try again later');
			echo json_encode($message);
			die();

		} else if ($bkashTransactionArray['trxStatus'] == '1002') {
			$message = array('message' => 'Invalid transaction ID, please try with a valid transaction ID');
			echo json_encode($message);
			die();
		} else if ($bkashTransactionArray['trxStatus'] == '1003') {
			$status = $bkashTransactionArray['trxStatus'];
			sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id, $status);
			$message = array('message' => 'Can’t process your request at this moment. Please try again later');
			echo json_encode($message);
			die();

		} else if ($bkashTransactionArray['trxStatus'] == '1004') {
			$status = $bkashTransactionArray['trxStatus'];
			sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id, $status);
			$message = array('message' => 'Can’t process your request at this moment. Please try again later');
			echo json_encode($message);
			die();

		} else if ($bkashTransactionArray['trxStatus'] == '9999') {
			$status = $bkashTransactionArray['trxStatus'];
			sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id, $status);
			$message = array('message' => 'Failed. Please try again later');
			echo json_encode($message);
			die();

		}
	}
}

function bkastransaction_pro($payment_post) {
echo "ljklj";
		$con = mysqli_connect('localhost', 'root', '123456', "bbh_apps");
	//$con=mysqli_connect('localhost', 'mobioapp_appuser', 'Mobi@13#',"mobioapp_bbh_apps");
	echo '<pre>'.print_r ($payment_post).'</pre>';
	$paymentarray = $payment_post;
	if (array_key_exists("bdt_price", $paymentarray)) {
		$bookPrice = $payment_post['bdt_price'];
	} else {

		$book_id = $payment_post['book_id'];

		$s = "SELECT bd_price FROM `books` WHERE `books`.`id` = '" . $book_id . "' ";
		$result = mysqli_query($con, $s) or die(mysqli_errno());
		$row = mysqli_fetch_assoc($result);

		$bookPrice = $row['bd_price'];
	}

	$email = $payment_post['device_email'];
	$bundle_id = $payment_post['bundle_id'];
	$book_id = $payment_post['book_id'];
	$device_id = $payment_post['device_id'];
	$trxId = $payment_post['bkash_transaction_id'];
	$s = "SELECT * FROM `payment_history` WHERE `payment_history`.`trxId` = '" . $trxId . "' ";
	$result = mysqli_query($con, $s) or die(mysqli_errno());

	if ($result) {

		$row = mysqli_fetch_assoc($result);

		if ($row == 0) {

			return checkbkashactivation($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);

		} else {

			if ($row['user_email'] == $email && (($book_id !=0 && $row['book_id'] == $book_id) || 
			($bundle_id !=0 && $row['bundle_id'] == $bundle_id))) {
				if ($row['transaction_status'] == 0) {//success
					//sendmailsales($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);
					$message = array('message' => 'success');
					echo json_encode($message);
					die();
				} else if ($row['transaction_status'] == 1 || $row['transaction_status'] == 2) {//pending
					return checkbkashactivation($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);
				} else if ($row['transaction_status'] == 3) {//reversed
					$message = array('message' => 'Transaction Reversed. Please try with a valid transaction');
					echo json_encode($message);
					die();
				} else if ($row['transaction_status'] == 4) {//failure
					$message = array('message' => 'Transaction Failure. Please try with a valid transaction');
					echo json_encode($message);
					die();
				} else if ($row['transaction_status'] == 5) {//format error
					return checkbkashactivation($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);
				} else if ($row['transaction_status'] == 6) {
					$message = array('message' => 'Invalid transaction ID, please try with a valid transaction ID');
					echo json_encode($message);
					die();
				} else if ($row['transaction_status'] == 7) {//authorization error
					$status = $bkashTransactionArray['trxStatus'];
			     sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id, $status);
					$message = array('message' => 'Cannot process your request at this moment. Please try again late');
					echo json_encode($message);
					die();
				} else if ($row['transaction_status'] == 8) {//authorization error
					sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);
					$message = array('message' => 'Cannot process your request at this moment. Please try again late');
					echo json_encode($message);
					die();
				} else {//System error
					sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id);
					$message = array('message' => 'System error could not process request');
					echo json_encode($message);
					die();
				}
			} 
			else {
				$message = array('message' => 'This transaction is already used by some other user or for some other book or bundle');
				echo json_encode($message);
				die();
			}
		}
	} else {
		$message = array('message' => 'Failed');
		echo json_encode($message);
		die();
	}
}

function sendmail($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id, $status) {
		$con = mysqli_connect('localhost', 'root', '123456', "bbh_apps");
	if (mysqli_connect_errno()) {
		echo "db-error";
	}
	$query = "SELECT * FROM `books` WHERE `books`.`id` = '" . $book_id . "' ";
	$result = mysqli_query($con, $query) or die(mysqli_errno());
	$row = mysqli_fetch_assoc($result);

	$s = "SELECT * FROM `payment_history` WHERE `payment_history`.`trxId` = '" . $trxId . "' ";
	$result4 = mysqli_query($con, $s) or die(mysqli_errno());
	$row4 = mysqli_fetch_assoc($result4);
	$query5 = "SELECT * FROM `book_bundle` WHERE `book_bundle`.`id` = '" . $bundle_id . "' ";
	$result5 = mysqli_query($con, $query5) or die(mysqli_errno());
	$row5 = mysqli_fetch_assoc($result);

	if (!empty($book_id)) {

		$bookname = $row['title'];

	} else {
		$bookname = $row5['name'];
	}
	$to = "mousumi368@gmail.com,abdullah.farah@mobioapp.com";
	$subject = "Book Activation Error | bKash | Transaction Status: " . $status . " ";
	$message = "
		<html>
			<head>
				<title>Book Activation Error</title>
			</head>
			<body>
				<p></p>
				<table>
					<tr>
		
						<th>Transaction Id " . $trxId . "
						<br>
						Transaction Status: " . $status . "
						<br>
						Date: " . date("Y/m/d") . "
						<br>
						Price: " . $bookPrice . " </th>
						<th> user Email " . $email . "
						<br>
						</th>
					</tr>
					<tr>
						<td></td>
						<td></td>
					</tr>
				</table>
			</body>
		</html>
		";

	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

	// More headers
	$headers .= 'From: <admin@mobioapp.net>' . "\r\n";
	$headers .= 'Cc: admin@mobioapp.net' . "\r\n";

	mail($to, $subject, $message, $headers);

}

function sendmailsales($email, $trxId, $book_id, $device_id, $bookPrice, $bundle_id) {
	$con = mysqli_connect('localhost', 'mobioapp_appuser', 'Mobi@13#', "mobioapp_bbh_apps");
	if (mysqli_connect_errno()) {
		echo "db-error";
	}

	$query = "SELECT * FROM `books` WHERE `books`.`id` = '" . $book_id . "' ";
	$result = mysqli_query($con, $query) or die(mysqli_errno());
	$row = mysqli_fetch_assoc($result);

	$query2 = "SELECT writers.name FROM writers INNER JOIN books ON writers.id=books.writer_id WHERE `books`.`id` = '" . $book_id . "' ";
	$result2 = mysqli_query($con, $query2) or die(mysqli_errno());
	$row2 = mysqli_fetch_assoc($result2);

	$query3 = "SELECT publishers.name FROM publishers INNER JOIN books ON publishers.id=books.publisher_id WHERE `books`.`id` = '" . $book_id . "' ";
	$result3 = mysqli_query($con, $query3) or die(mysqli_errno());
	$row3 = mysqli_fetch_assoc($result3);
	$s = "SELECT * FROM `payment_history` WHERE `payment_history`.`trxId` = '" . $trxId . "' ";
	$result4 = mysqli_query($con, $s) or die(mysqli_errno());
	$row4 = mysqli_fetch_assoc($result4);

	$query5 = "SELECT * FROM `book_bundle` WHERE `book_bundle`.`id` = '" . $bundle_id . "' ";
	$result5 = mysqli_query($con, $query5) or die(mysqli_errno());
	$row5 = mysqli_fetch_assoc($result);

	if (empty($bundle_id) || $bundle_id==0 ) {

		$bookname = $row['title'];
		$coverimage=$row['front_image'];

	} else {
		$bookname = $row5['name'];
		$coverimage= $row5['image'];
	}
	$to = "mousumi368@gmail.com,abdullah.farah@mobioapp.com";
	$subject = "Book Sale | bKash | " . $bookname . " | BDT " . $row4['amount'] . " .00";
	$message = "
		<html>
			<head>
				<title>Book Sale Notification</title>
			</head>
			<body>
				<p></p>
				<table>
					<tr>
						<th><img src='http://www.banglabookhouse.com/boi_poka/uploads/" . $coverimage . "' width='100' float='left' ></img></th>
						<th>
						
						Name :" . $bookname . "
						
						
						
						<br>
						Author Name: " . $row2['name'] . "
						<br>
						Publisher Name: " . $row3['name'] . "
						<br>  Price: " . $row4['amount'] . "
						<th></th>
					</tr>
					<tr>
						<td></td>
						<td></td>
					</tr>
				</table>
			</body>
		</html>
		";

	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

	// More headers
	$headers .= 'From: <admin@mobioapp.net>' . "\r\n";
	$headers .= 'Cc: admin@mobioapp.net' . "\r\n";

	mail($to, $subject, $message, $headers);
}
?>
