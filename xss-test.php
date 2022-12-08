<?php 
include_once('includes/custom-functions.php');

$fn = new custom_functions;

$post = array(
	'title' => 'post title info',
	'currency' =>  "Rs",
	'delivery_charge' => "<SCRIPT>alert('attack')</SCRIPT>"
);

echo " Data : ";
$post = $fn->xss_clean_array($post);

print_r($post);
//$db->escapeString($fn->xss_clean($data));
?>