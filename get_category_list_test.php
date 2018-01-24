<?php
session_start();
ob_start();

//$con= mysql_connect("localhost","mobioapp_appuser","Mobi@13#");
$con=mysqli_connect('localhost', 'root', '123456',"bbh_apps");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
  //mysql_select_db("mobioapp_bbh_final", $con);
mysql_select_db("bbh_apps", $con);
//mysql_select_db("mobioapp_bbh_apps", $con);
?>

<?php	
$result = mysqli_query($con,"SELECT * FROM categories");
$allcategory[]=array();
while($row = mysqli_fetch_array($result)) {
echo json_encode($row);


}

mysqli_close($con);
?> 
