<?php
/*
 Insert data into database........

 */

define('DB_USER', "mobioapp_appuser");
// db user
define('DB_PASSWORD', "Mobi@13#");
// db password (mention your db password here)
define('DB_DATABASE', "mobioapp_bbh_apps");
// database name
define('DB_SERVER', "localhost");
// db server

$con = mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD) or die(mysql_error());
// Selecing database
$db = mysql_select_db(DB_DATABASE) or die(mysql_error()) or die(mysql_error());

if (isset($_POST['pemail']) && isset($_POST['dID']) && isset($_POST['spID']) && isset($_POST['pTime'])) {

	$pemail = $_POST['pemail'];
	$dID = $_POST['dID'];

	$spID = $_POST['spID'];
	$pTime = $_POST['pTime'];
	$bookId = $_POST['book_id'];
	$publisherID = $_POST['publisher_id'];
	$price=$_POST['price'];
	// include db connect class
	// require_once __DIR__ . '/db_connect.php';

	// connecting to db
	// $db = new DB_CONNECT();

	// mysql inserting a new row
	$s = "SELECT * FROM `sales_detail` WHERE `sales_detail`.`device_user_email` = '".$pemail."' AND `sales_detail`.`pbook_id` = '".$bookId."' ";
    $result1 = mysqli_query($con,$s) or die(mysqli_errno());
    if($result1) {
		
			$row=mysqli_fetch_assoc($result1);
			
			if($row==0){
				$result = mysql_query("INSERT INTO sales_detail(publisher_id,pbook_id,
	deviceID,date_of_sale,device_user_email,store_product_id) VALUES('$publisherID','$bookId','$dID',$pTime,'$pemail','$spID')");
           sendmailsales($pemail,$bookId, $price);
	// check if row inserted or not
	if ($result)
	 {
		// successfully inserted into database
		$response["success"] = 1;
		$response["message"] = "Product successfully created.";

		// echoing JSON response
		echo json_encode($response);
	} 
	else 
	{
		// failed to insert row
		$response["success"] = 0;
		$response["message"] = "Oops! An error occurred.";

		// echoing JSON response
		echo json_encode($response);
	}
}
else{
	
	 $message = array('message'=>'success');
	echo json_encode($message); die();
	
	
	
	}




				
				
				}
    }
	
	
	
	

 else {
	// required field is missing
	$response["success"] = 0;
	$response["message"] = "Required field(s) is missing";

	// echoing JSON response
	echo json_encode($response);
}


function sendmailsales($pemail,$bookId, $price) {
	$con = mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD) or die(mysql_error());
// Selecing database
$db = mysql_select_db(DB_DATABASE) or die(mysql_error()) or die(mysql_error());
	if (mysqli_connect_errno()) {
		echo "db-error";
	}

	$query = "SELECT * FROM `books` WHERE `books`.`id` = '" . $bookId . "' ";
	$result = mysqli_query($con, $query) or die(mysqli_errno());
	$row = mysqli_fetch_assoc($result);

	$query2 = "SELECT writers.name FROM writers INNER JOIN books ON writers.id=books.writer_id WHERE `books`.`id` = '" . $bookId . "' ";
	$result2 = mysqli_query($con, $query2) or die(mysqli_errno());
	$row2 = mysqli_fetch_assoc($result2);

	$query3 = "SELECT publishers.name FROM publishers INNER JOIN books ON publishers.id=books.publisher_id WHERE `books`.`id` = '" . $bookId . "' ";
	$result3 = mysqli_query($con, $query3) or die(mysqli_errno());
	$row3 = mysqli_fetch_assoc($result3);
	

	$query5 = "SELECT * FROM `book_bundle` WHERE `book_bundle`.`id` = '" . $bundle_id . "' ";
	$result5 = mysqli_query($con, $query5) or die(mysqli_errno());
	$row5 = mysqli_fetch_assoc($result);

	

		$bookname = $row['title'];
		$coverimage=$row['front_image'];

	
		//$bookname = $row5['name'];
		//$coverimage= $row5['image'];
	
	$to = "mousumi368@gmail.com,abdullah.farah@mobioapp.com";
	$subject = "Book Sale | Playstore | " . $bookname . " | USD " . $price . " .00";
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
						
						<br> User Email:" . $pemail . "
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
