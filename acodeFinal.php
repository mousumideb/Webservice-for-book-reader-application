

<?php
global $email, $activiation_code, $bundle_id, $book_id, $device_id,$price;

function getDbCon() {
	$dbhandle = mysql_connect('localhost', 'mobioapp_appuser', 'Mobi@13#');

	$selected = mysql_select_db("mobioapp_bbh_apps", $dbhandle);
}

function checkActivation($email, $activiation_code, $bundle_id, $book_id, $device_id,$price) {
	//getDbCon();
	// Create connection
	$con = mysqli_connect('localhost', 'mobioapp_appuser', 'Mobi@13#', "mobioapp_bbh_apps");
	// Check connection
	if (mysqli_connect_errno()) {
		echo "db-error";
	}

	//	$chk_qry = "SELECT activated FROM `book_bundle_sales` WHERE `book_bundle_sales`.`user_mail` LIKE '%@gmail.com' AND `book_bundle_sales`.`user_mail` = $email AND `book_bundle_sales`.`activation_code` = '".$activiation_code."' ";
	$chk_qry = "SELECT activated FROM `book_bundle_sales` WHERE `book_bundle_sales`.`activation_code` = '" . $activiation_code . "' ";
	//die('asdsada');
	$result = mysqli_query($con, $chk_qry) or die(mysqli_errno());

	//echo "<pre>";print_r($result);echo "</pre>";die('asdsadsaas');

	//if(empty($result))
	if ($result) {
		$row = mysqli_fetch_assoc($result);

		if ($row['activated'] == 0) {
			review_insertion_to_db($email, $activiation_code, $bundle_id, $book_id, $device_id, $price);
			//sendmailsales($email,$book_id, $price,$bundle_id);
		} else {
			$usr_chk = "";
			if ($bundle_id > 1) {// Book bundle
				//				$usr_chk="SELECT count(*) AS num FROM `book_bundle_sales` WHERE `book_bundle_sales`.`user_mail` LIKE '%@gmail.com' AND `book_bundle_sales`.`user_mail` = $email AND `book_bundle_sales`.`activation_code` = $activiation_code  AND device_id=$device_id AND activated=1 AND book_bundle_id=$bundle_id";
				$usr_chk = "SELECT count(*) AS num FROM `book_bundle_sales` WHERE `book_bundle_sales`.`user_mail` LIKE '%@gmail.com' AND `book_bundle_sales`.`user_mail` = $email AND `book_bundle_sales`.`activation_code` = '" . $activiation_code . "' AND activated = 1 AND book_bundle_id = $bundle_id ";
			} else {// Single book
				//				$usr_chk="SELECT count(*) AS num FROM `book_bundle_sales` WHERE `book_bundle_sales`.`user_mail` LIKE '%@gmail.com' AND `book_bundle_sales`.`user_mail` =$email AND `book_bundle_sales`.`activation_code`=$activiation_code  AND device_id=$device_id AND activated=1 AND book_id=$book_id";
				$usr_chk = "SELECT count(*) AS num FROM `book_bundle_sales` WHERE `book_bundle_sales`.`user_mail` LIKE '%@gmail.com' AND `book_bundle_sales`.`user_mail` = $email AND `book_bundle_sales`.`activation_code` = '" . $activiation_code . "' AND `activated` = 1 AND `book_id` = $book_id";
			}

			$result = mysqli_query($con, $usr_chk);

			if ($result) {
				$row = mysqli_fetch_array($result);
				if ($row['num'] >= 1) {
					$message = array('message' => 'success');
					echo json_encode($message);
					die();
				} else {
					$message = array('message' => 'This code is already used by another user or used for another book/bundle.');
					echo json_encode($message);
					die();
				}
			} else {
				$message = array('message' => 'Fail');
				echo json_encode($message);
				die();
			}
		}
	} else {
		$message = array('message' => 'Invalid code.');
		echo json_encode($message);
		die();
	}
}

function review_insertion_to_db($email_id, $activiation_code, $bundle_id, $book_id, $device_id) {

	getDbCon();

	$email = $email_id;

	//echo "<br/>".$email_id."---".$activiation_code."---".$bundle_id."---".$book_id."---".$device_id."<br/>";
	//global $book_id,$review,$rating,$today,$device_id,$email_id,$rating_id,$activiation_code,$bundle_id,$device_id;

	if ($bundle_id > 1) {//Book bundle
		$mysql_puchase_confirmation = "SELECT COUNT(*) AS tag, `book_bundle_sales`.`id` AS id FROM `book_bundle_sales` WHERE `book_bundle_sales`.`activation_code`= '" . $activiation_code . "' AND `book_bundle_sales`.`activated`= 0 AND `book_bundle_sales`.`book_bundle_id`= $bundle_id ";

		//echo json_encode($mysql_puchase_confirmation); die();

		$result = mysql_query($mysql_puchase_confirmation);

		if ($result) {

			while ($row = mysql_fetch_object($result)) {
				if ($row -> tag == 1) {
					//echo "got it";
					$id = $row -> id;

					$update_sql = "UPDATE book_bundle_sales SET `activated`= 1, `created`=now(), 
					`user_mail` = $email, device_id=$device_id WHERE id=$id";
					sendmailsales($email,$book_id, $price,$bundle_id);
					//echo json_encode($update_sql); die();
					$final_result = mysql_query($update_sql);
					if ($final_result) {

						$message = array('message' => 'success');
						echo json_encode($message);
						die();
					} else {
						$message = array('message' => 'failed');
						echo json_encode($message);
						die();
					}
				} else {
					$message = array('message' => 'failed');
					echo json_encode($message);
					die();
				}
			}
		} else {
			$message = array('message' => 'failed');
			echo json_encode($message);
			die();
		}
	}

	if ($bundle_id == 0 || $bundle_id == 1) {//Single book
		//die('ashgdsajhdfasj');
		$mysql_puchase_confirmation = "SELECT COUNT(*) AS tag,
											  `book_bundle_sales`.`id` AS id 
											FROM
											  `book_bundle_sales` 
											  WHERE `book_bundle_sales`.`activation_code` = '" . $activiation_code . "' 
											  AND `book_bundle_sales`.`activated` = 0 
											  AND `book_bundle_sales`.`book_id` = $book_id ";

		//echo json_encode($mysql_puchase_confirmation); die();

		$result = mysql_query($mysql_puchase_confirmation);

		if ($result) {
			while ($row = mysql_fetch_object($result)) {
				if ($row -> tag == 1) {
					//echo "got it";

					$id = $row -> id;

					$update_sql = "UPDATE book_bundle_sales SET `activated`= 1, `created`=now(), `user_mail` = $email, device_id = $device_id WHERE id=$id";
					$final_result = mysql_query($update_sql);

					if ($final_result) {
						$message = array('message' => 'success');

						$myFile = "testFile2.txt";
						$fh = fopen($myFile, 'w');
						//$stringData = "Bobby Bopper\n";
						//fwrite($fh, $stringData);
						fwrite($fh, json_encode($message));
						fclose($fh);

						echo json_encode($message);
						die();
					} else {
						$message = array('message' => 'failed');
						echo json_encode($message);
						die();
					}
				} else {
					$message = array('message' => 'failed');
					echo json_encode($message);
					die();
				}
			}
		} else {
			$message = array('message' => 'failed');
			echo json_encode($message);
			die();
		}
	}
}

function transaction_pro($payment_post) {//function parameters, two variables.

	$acode = $payment_post;
	$bbh_app_payment_json = array();
	$bbh_app_payment_json['payment'] = $acode;
	// json_decode($acode,true);
	//echo "<pre>";print_r($bbh_app_payment_json['payment']);echo "</pre>";

	// ----  imtiaz -- start
	$email = $bbh_app_payment_json['payment']['device_email'];
	$activiation_code = $bbh_app_payment_json['payment']['activation_code'];
	$bundle_id = $bbh_app_payment_json['payment']['bundle_id'];
	$book_id = $bbh_app_payment_json['payment']['book_id'];
	$device_id = $bbh_app_payment_json['payment']['device_id'];
	$price=$bbh_app_payment_json['payment']['bdt_price'];
	// ----  imtiaz  --- end

	/*$myFile = "testFile.txt";
	 $fh = fopen($myFile, 'w');
	 fwrite($fh, $payment_post);
	 fclose($fh);*/

	$activiation_code = $bbh_app_payment_json['payment']['activation_code'];
	// ----  imtiaz
	//die('asdsadsa');
	if (empty($activiation_code)) {
		$result = array('message' => 'failed');
		echo json_encode($result);
		die();
	}

	//$activiation_code="'".$activiation_code."'";
	//$activiation_code = $activiation_code;

	$email = $bbh_app_payment_json['payment']['device_email'];
	if (empty($email)) {

		$result = array('message' => 'failed');

		echo json_encode($result);
		die();
	}

	$email = "'" . $email . "'";

	$bundle_id = 0;

	$bundle_id = $bbh_app_payment_json['payment']['bundle_id'];

	if (empty($bundle_id)) {
		$bundle_id = 0;
	}

	$book_id = $bbh_app_payment_json['payment']['book_id'];

	if ($book_id == " ") {
		$result = array('message' => 'failed');
		echo json_encode($result);
		die();
	}

	if ($bundle_id == " " && $book_id == " ") {

		$result = array('message' => 'failed');

		echo json_encode($result);
		die();
	}

	$device_id = $bbh_app_payment_json['payment']['device_id'];

	if (empty($device_id)) {

		$result = array('message' => 'failed');

		echo json_encode($result);
		die();
	}

	$device_id = "'" . $device_id . "'";

	//checkActivation($email,$activiation_code,$bundle_id,$book_id,$device_id);

	return checkActivation($email, $activiation_code, $bundle_id, $book_id, $device_id,$price);
	//returns the second argument passed into the function
}


function sendmailsales($email,$book_id, $price,$bundle_id) {
	$con = mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD) or die(mysql_error());
// Selecing database
$db = mysql_select_db(DB_DATABASE) or die(mysql_error()) or die(mysql_error());
	if (mysqli_connect_errno()) {
		echo "db-error";
	}

	$query = "SELECT * FROM `books` WHERE `books`.`id` = '" . $book_id . "' ";
	$result = mysqli_query($con, $query) or die(mysqli_errno());
	$row = mysqli_fetch_assoc($result);

	$query2 = "SELECT writers.name FROM writers INNER JOIN books ON writers.id=books.writer_id WHERE `books`.`id` = '" . $book_id . "' ";
	$result2 = mysqli_query($con, $query2) or die(mysqli_errno());
	$row2 = mysqli_fetch_assoc($result2);

	$query3 = "SELECT publishers.name FROM publishers INNER JOIN books ON publishers.id=books.publisher_id WHERE `books`.`id` = '" . $bookId . "' ";
	$result3 = mysqli_query($con, $query3) or die(mysqli_errno());
	$row3 = mysqli_fetch_assoc($result3);
	

	$query5 = "SELECT * FROM `book_bundle` WHERE `book_bundle`.`id` = '" . $bundle_id . "' ";
	$result5 = mysqli_query($con, $query5) or die(mysqli_errno());
	$row5 = mysqli_fetch_assoc($result);

	

		if (empty($bundle_id) || $bundle_id==0 ) {

		$bookname = $row['title'];
		$coverimage=$row['front_image'];

	         }
	 else {
		$bookname = $row5['name'];
		$coverimage= $row5['image'];
	     }

	
		//$bookname = $row5['name'];
		//$coverimage= $row5['image'];
	
	$to = "mousumi368@gmail.com,abdullah.farah@mobioapp.com";
	$subject = "Book Sale | Local Sales(Activation Code) | " . $bookname . " | BDT " . $price . " .00";
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
						<br>  Price: " . $price . "
						
						<br> User Email:" . $email . "
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
