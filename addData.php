<?php

// load connection file
require_once('dbcon.php');


//DB Connection............

try {
    $mysqli = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);

    if ($mysqli->connect_errno) {
        throw new Exception('Database connection failed: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
	


//die();


//if (isset($_POST['pemail']) && isset($_POST['dID']) && isset($_POST['spID']) && isset($_POST['pTime'])) {
if(1==1){

    /*
	$pemail = $_POST['pemail'];
	$dID = $_POST['dID'];

	$spID = $_POST['spID'];
	$pTime = $_POST['pTime'];
	$bookId = $_POST['book_id'];
	$publisherID = $_POST['publisher_id'];
	$price = $_POST['price'];
	
	*/

	
	$pemail = "tanvir@datasoft-bd.com";
	
	//spam check...........
	 $mailcheck = spamcheck($pemail);
    if ($mailcheck==FALSE) {
      
	            $response["success"] = 0;
				$response["message"] = "Server entry failed. Invalid email ID";
                echo json_encode($response);
				die();
	     
	  }
	
	
	
	
	$dID = "242347234rr";

	$spID = "534535e";
	$pTime = "4234234234";
	$bookId = "16";
	$publisherID = "32424";
	$price = "444444trt";
	
	//sendmailsales($pemail,$bookId,$price,$mysqli);
	
	
	// mysql inserting a new row
	$s = "SELECT * FROM `sales_detail` WHERE `sales_detail`.`device_user_email` = '" . $pemail . "' AND `sales_detail`.`pbook_id` = '" . $bookId . "' ";
	$res_check = $mysqli->query($s);
     if ($mysqli->errno) {
        throw new Exception('Database Query Failed: (' . $mysqli->errno . ') ' );
    }
	
	$val_check=$res_check->fetch_assoc();
	//print_r($val_check);die();
	
	
	if (empty($val_check)) {
         
	   $entry_time=date("Y-m-d H:i:s");
	   
	   $query = "INSERT INTO sales_detail (publisher_id,pbook_id,
deviceID,price,date_of_sale,device_user_email,store_product_id,entry_date_time) VALUES (?,?,?,?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			
			if ($mysqli->errno) {
        throw new Exception('Database Query Failed: (' . $mysqli->errno . ')' );
    }
			
			
			
			$stmt->bind_param("iisdisss", $publisherID,$bookId,$dID,$price,$pTime,$pemail,$spID,$entry_time);
			
			$result=$stmt->execute();
			
			if ($stmt->errno) {
        throw new Exception('Database Query Failed: (' . $stmt->errno . ') ' );
    }
			 $stmt->close();
			
			// check if row inserted or not
			if ($result) {
				// successfully inserted into database
				$response["success"] = 1;
				$response["message"] = "Product successfully created.";
				
				echo json_encode($response);
				sendmailsales($pemail,$bookId,$price,$mysqli);
			} else {
				// failed to insert row
				$response["success"] = 0;
				$response["message"] = "Server entry failed";
				
                echo json_encode($response);
			}
		
	}
	else{
	
	            $response["success"] = 0;
				$response["message"] = "You already purchased!!!";
                
				// echoing JSON response
				echo json_encode($response);
	
	}
}


	 

else {
	// required field is missing
	$response["success"] = 0;
	$response["message"] = "Required field(s) is missing";

	// echoing JSON response
	echo json_encode($response); die();
}

}
catch (Exception $e) {
    
	$response["success"] = 0;
	$response["message"] = 'Error Occurred' . $e->getMessage();
	//$response["message"] = 'Error Occurred ';
    echo json_encode($response);
}


function sendmailsales($pemail, $bookId, $price,$mysqli) {
	
	
	//$con = mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD) or die(mysql_error());
	// Selecing database
//	$db = mysql_select_db(DB_DATABASE) or die(mysql_error()) or die(mysql_error());
	//if (mysqli_connect_errno()) {
	//	echo "db-error";
	//}
	
	
try {
	$query1 = "SELECT title,front_image FROM books WHERE id = ?";
	$stmt = $mysqli->prepare($query1);
			
	if ($mysqli->errno) {
        throw new Exception('Database Query Failed: (' . $mysqli->errno . ') ' );
    }
	
			
			$stmt->bind_param("i",$bookId);
			$result=$stmt->execute();
			$stmt->bind_result($title,$front_image);
            $stmt->fetch();
		
	if ($stmt->errno) {
        throw new Exception('Database Query Failed: (' . $stmt->errno . ') ' );
    }
			$stmt->close();
			
	// echo $title."<br>";
	// echo $front_image;
	// die();
	
	//$result = mysqli_query($con, $query) or die(mysqli_errno());
	
	
	$query2 = "SELECT writers.name FROM writers INNER JOIN books ON writers.id=books.writer_id WHERE `books`.`id` = ?";
	$stmt = $mysqli->prepare($query2);
			
	if ($mysqli->errno) {
        throw new Exception('Database Query Failed: (' . $mysqli->errno . ') ' );
    }
		    $stmt->bind_param("i",$bookId);
			$result=$stmt->execute();
			$stmt->bind_result($writer_name);
            $stmt->fetch();
		
	if ($stmt->errno) {
        throw new Exception('Database Query Failed: (' . $stmt->errno . ') ' );
    }
			$stmt->close();
	
	//echo $writer_name; die();
	
	//--------------------end
	
	//$result2 = mysqli_query($con, $query2) or die(mysqli_errno());
	//$row2 = mysqli_fetch_assoc($result2);
    
	
	
	$query3 = "SELECT publishers.name FROM publishers INNER JOIN books ON publishers.id=books.publisher_id WHERE `books`.`id` = ? ";
	$stmt = $mysqli->prepare($query3);
			
	if ($mysqli->errno) {
        throw new Exception('Database Query Failed: (' . $mysqli->errno . ') ' );
    }
		    $stmt->bind_param("i",$bookId);
			$result=$stmt->execute();
			$stmt->bind_result($publisher_name);
            $stmt->fetch();
		
	if ($stmt->errno) {
        throw new Exception('Database Query Failed: (' . $stmt->errno . ') ' );
    }
			$stmt->close();
	
	//echo $publisher_name; die();
	
	/*
	
	//$result3 = mysqli_query($con, $query3) or die(mysqli_errno());
	//$row3 = mysqli_fetch_assoc($result3);

	$query5 = "SELECT * FROM `book_bundle` WHERE `book_bundle`.`id` = ? ";
	$stmt = $mysqli->prepare($query5);
			
	if ($mysqli->errno) {
        throw new Exception('Database Query Failed: (' . $mysqli->errno . ') ' );
    }
		    $stmt->bind_param("i",$bookId);
			$result=$stmt->execute();
			$stmt->bind_result($publisher_name);
            $stmt->fetch();
		
	if ($stmt->errno) {
        throw new Exception('Database Query Failed: (' . $stmt->errno . ') ' );
    }
			$stmt->close(); 
	
	echo 
	
	//$result5 = mysqli_query($con, $query5) or die(mysqli_errno());
	//$row5 = mysqli_fetch_assoc($result);

	*/
	
	}
	catch (Exception $e) {
    
	$response["success"] = 0;
	$response["message"] = 'Error Occurred' . $e->getMessage();
	//$response["message"] = 'Error Occurred ';
    echo json_encode($response); die();
}

	
	//$bookname = $row['title'];
	//$coverimage = $row['front_image'];

	//$bookname = $row5['name'];
	//$coverimage= $row5['image'];

	$to = "atanvir137@gmail.com";
	$subject = "Book Sale | Playstore | " . $bookname . " | USD " . $price;
	$message = "
		<html>
			<head>
				<title>Book Sale Notification</title>
			</head>
			<body>
				<p></p>
				<table>
					<tr>
						<th><img src='http://www.banglabookhouse.com/boi_poka/uploads/" . $front_image . "' width='100' float='left' ></img></th>
						<th>
						
						Name :" . $title . "
						
						
						
						<br>
						Author Name: " . $writer_name . "
						<br>
						Publisher Name: " . $publisher_name . "
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



function spamcheck($email) {
  // Sanitize e-mail address
   $email=filter_var($email, FILTER_SANITIZE_EMAIL);
  // Validate e-mail address
  
 if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return TRUE;
  } else {
    return FALSE;
  }
}

$mysqli->close();

?>
