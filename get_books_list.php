<?php
session_start();
ob_start();
//$con= mysql_connect("db437093938.db.1and1.com","dbo437093938","Mob10@pp!");
//$con= mysql_connect("localhost","mobioapp_appuser","Mobi@13#");

	$con=mysqli_connect('localhost', 'root', '123456',"bbh_apps");
//$con= mysql_connect("localhost","root","");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
//mysql_select_db("mobioapp_bbh_final", $con);

mysql_select_db("mobioapp_bbh_apps", $con);
?>

<?php

//get bundle list..
$get_bundle_count = "SELECT

COUNT(*) AS total_bundle FROM
(

SELECT 
 
  COUNT(books.id) AS bundle_group_list
 
FROM
  books 
  JOIN categories 
    ON categories.id = books.`category_id` 
  JOIN publishers 
    ON publishers.`id` = books.`publisher_id` 
  JOIN writers 
    ON writers.`id` = books.`writer_id` 
WHERE books.is_free > 1 AND books.active=1 
GROUP BY is_free 
) AS a";


$bundle_count = mysql_query($get_bundle_count) or die(mysql_error());	 
$result = array();  


while($row = mysql_fetch_object($bundle_count))
{
    echo "<pre>";print_r($row);
    if($row->total_bundle >0 )
    {
    $result['bundle'] = "Yes";
    } 
    else{
     
      $result['bundle'] = "No";
    
    }
    //$i++;
}




//$result['bundle_count'] = mysql_affected_rows();



// get books-lists
/*
$get_books_lists = "select books.*, categories.`title` as category_name, writers.`name` as writer_name, publishers.`name` as publisher_name 
from books
join categories on categories.id = books.`category_id`
join publishers on publishers.`id` = books.`publisher_id`
join writers on writers.`id` = books.`writer_id`";
*/

$i=0;

/*
$get_bundle_lists_detail="SELECT 
  COUNT(books.id) AS total_book,
  books.`is_free` AS bundle_id 
FROM
  books 
  JOIN categories 
    ON categories.id = books.`category_id` 
  JOIN publishers 
    ON publishers.`id` = books.`publisher_id` 
  JOIN writers 
    ON writers.`id` = books.`writer_id` 
WHERE books.is_free > 1 
GROUP BY is_free ";
*/

$get_bundle_lists_detail="
SELECT 

b.group_id AS group_id,
b.total_book AS total_book,
b.bundle_id AS bundle_id,
a.image AS image,
a.in_app_purchase_id AS in_app_purchase_id,
a.usd_price AS usd_price,
a.bd_price  AS bd_price  		

FROM 

( SELECT 
  books.`is_free` AS group_id, 
  COUNT(books.id) AS total_book,
  books.`is_free` AS bundle_id 
FROM
  books 
  JOIN categories 
    ON categories.id = books.`category_id` 
  JOIN publishers 
    ON publishers.`id` = books.`publisher_id` 
  JOIN writers 
    ON writers.`id` = books.`writer_id` 
WHERE books.is_free > 1 AND books.active=1
GROUP BY is_free
ORDER BY is_free ) AS b

LEFT OUTER JOIN

`book_bundle` AS a ON a.`id`=b.group_id";


$bundle_list = mysql_query($get_bundle_lists_detail) or die(mysql_error());	 
//$result = array();  
//$result['bundle_detail'] = mysql_affected_rows();
while($row = mysql_fetch_object($bundle_list))
{
    //echo "<pre>";print_r($row);
    $result['Bundle'][$i]['total_book'] = $row->total_book;
    $result['Bundle'][$i]['bundle_id'] = $row->bundle_id; 
    $result['Bundle'][$i]['image'] = $row->image;  
    $result['Bundle'][$i]['in_app_purchase_id'] = $row->in_app_purchase_id;
     $result['Bundle'][$i]['usd_price'] = $row->usd_price;
      $result['Bundle'][$i]['bd_price'] = $row->bd_price;
    $i++;
}



/*

$get_books_lists="SELECT 
  books.*,
  categories.`title` AS category_name,
  writers.`name` AS writer_name,
  publishers.`name` AS publisher_name,
  IF(b.`rating` IS NULL,0,b.`rating`) AS rating
    
FROM
  books 
  JOIN categories 
    ON categories.id = books.`category_id` 
  JOIN publishers 
    ON publishers.`id` = books.`publisher_id` 
  JOIN writers 
    ON writers.`id` = books.`writer_id` 
   LEFT OUTER JOIN 
 (SELECT book_id AS book_id,MAX(rating) AS rating  FROM `book_rating` GROUP BY book_id)  AS b  
       ON b.`book_id`=`books`.`id`";
*/

$get_books_lists="SELECT

  c.id AS id,
  c.category_id AS category_id,
  c.publisher_id AS publisher_id,
  c.writer_id AS writer_id,
  c.title AS title,
  c.ISBN AS ISBN,
  c.front_image AS front_image,
  c.`is_free` AS is_free,
  c.`is_downloadable` AS is_downloadable,
  c.`file_name` AS file_name,
  c.`is_archive` AS is_archive,
  c.`in_app_purchased_id` AS in_app_purchased_id,
  c.`last_read_page_no` AS last_read_page_no,
  c.`created_date` AS created_date,
  c.`update_date` AS update_date,
  c.`price` AS price,
  c.`bd_price` AS bd_price,
  c.`total_pages` AS total_pages,
  c.`tag_image` AS tag_image,
  c.`store_product_id` AS store_product_id,
  c.`inactive_image` AS inactive_image,
  c.`preview_image_1` AS preview_image_1,
  c.`preview_image_2` AS preview_image_2,
  c.`preview_image_3` AS preview_image_3,
  c.category_name AS category_name,
  c.writer_name AS writer_name,
  c.publisher_name AS publisher_name,
  c.rating AS rating,
  d.image AS parent_image,
  d.in_app_purchase_id AS parent_in_app_purchase_id
 

FROM 
(SELECT 
  books.id AS id,
  books.`category_id` AS  category_id,
  books.`publisher_id` AS  publisher_id,
  books.`writer_id` AS writer_id,
  books.`title` AS title,
  books.`ISBN` AS ISBN,
  books.`front_image` AS front_image,
  books.`is_free` AS is_free,
  books.`is_downloadable` AS is_downloadable,
  books.`file_name` AS file_name,
  books.`is_archive` AS is_archive,
  books.`in_app_purchased_id` AS in_app_purchased_id,
  books.`last_read_page_no` AS last_read_page_no,
  IF(books.`created_date` IS NULL,DATE_FORMAT(NOW(),'%Y-%m-%d %h:%i:%S'),books.`created_date`) AS created_date,
  books.`update_date` AS update_date,
  books.`price` AS price,
  books.`bd_price` AS bd_price,
  books.`total_pages` AS total_pages,
  books.`tag_image` AS tag_image,
  books.`store_product_id` AS store_product_id,
  books.`inactive_image` AS inactive_image,
  books.`preview_image_1` AS preview_image_1,
  books.`preview_image_2` AS preview_image_2,
  books.`preview_image_3` AS preview_image_3,
 categories.`title` AS category_name,
  writers.`name` AS writer_name,
  publishers.`name` AS publisher_name,
  IF(b.`rating` IS NULL,0,b.`rating`) AS rating
    
FROM
  books 
  JOIN categories 
    ON categories.id = books.`category_id` 
  JOIN publishers 
    ON publishers.`id` = books.`publisher_id` 
  JOIN writers 
    ON writers.`id` = books.`writer_id` 
   LEFT OUTER JOIN 
 (SELECT book_id AS book_id,MAX(rating) AS rating  FROM `book_rating` GROUP BY book_id)  AS b  
       ON b.`book_id`=`books`.`id`
      WHERE books.active=1  
        ) AS c
       
       LEFT OUTER JOIN `book_bundle` AS d ON d.`id`=c.is_free 
        
       ";


$books_list = mysql_query($get_books_lists) or die(mysql_error());
//$result['bundle_detail'] = mysql_affected_rows();



//$result = array();
$i = 0;
$result['items'] = mysql_affected_rows();
while($row = mysql_fetch_object($books_list))
{
    //echo "<pre>";print_r($row);
    $result['Books'][$i]['book'] = $row;
    $categories = mysql_query("SELECT * FROM categories WHERE categories.id = ". $row->category_id) or die(mysql_error());
    $result['Books'][$i]['category'] = mysql_fetch_object($categories);
    $publishers = mysql_query("SELECT * FROM publishers WHERE publishers.id = ". $row->publisher_id) or die(mysql_error());
    $result['Books'][$i]['publisher'] = mysql_fetch_object($publishers);
    $writers = mysql_query("SELECT * FROM writers WHERE writers.id = ". $row-> writer_id) or die(mysql_error());
    $result['Books'][$i]['writer'] = mysql_fetch_object($writers);        
    $i++;
}
print_r($result);
print_r($result);

mysql_close($con);
 
echo json_encode(utf8_encode($result));

?>
