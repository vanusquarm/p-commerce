<div id="content" class="container col-md-12">
	<?php 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
		if(isset($_POST['btnDelete'])){
		    
			if(isset($_GET['id'])){
				$ID = $db->escapeString($fn->xss_clean($_GET['id']));
			}else{
				$ID = "";
			}
			$sql_query="DELETE FROM product_variant WHERE product_id=".$ID;
				// Execute query
				$db->sql($sql_query);
				// store result 
				$delete_variant_result=$db->getResult();
				if(!empty($delete_variant_result)){
					$delete_variant_result=0;
				}else{
					$delete_variant_result=1;
				}
		
			// get image file from menu table
			$sql_query = "SELECT image, other_images 
					FROM products 
					WHERE id =".$ID;
				// Execute query
				$db->sql($sql_query);
				// store result 
				$res=$db->getResult();
				foreach($res as $row){
					unlink($res[0]['image']);
			}
				
			// delete image file from directory
			
			// delete data from menu table
			$sql_query = "DELETE FROM products 
					WHERE id =".$ID;
				// Execute query
				$db->sql($sql_query);
				// store result 
				$delete_product_result = $db->getResult();
				if(!empty($delete_product_result)){
					$delete_product_result=0;
				}
					$delete_product_result=1;
				
			// if delete data success back to reservation page
			if($delete_product_result==1 && $delete_variant_result==1){
				header("location: products.php");
			}
		}		

		if(isset($_POST['btnNo'])){
			header("location: products.php");
		}
		if(isset($_POST['btncancel'])){
			header("location: products.php");
		}

	?>
	<?php
	if($permissions['products']['delete']==1) { ?>
	<h1>Confirm Action</h1>
	  
	<form method="post">
		<p>Are you sure want to delete this Product?</p>
		<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete"/>
		<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo"/>
	</form>
	<div class="separator"> </div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to delete product.</div>
		<form method="post">
				<input type="submit" class="btn btn-danger" value="Back" name="btncancel"/>
			</form>

		<?php } ?>
</div>
			
<?php $db->disconnect(); ?>