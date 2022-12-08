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
			
			// delete data from pemesanan table
			$sql_query = "DELETE FROM orders 
					WHERE ID =".$ID;	
				$db->sql($sql_query);
				
				// store result 
				$delete_result = $db->getResult();
				$sql = "DELETE FROM order_items 
					WHERE order_id =".$ID;	
				$db->sql($sql);
				if(!empty($delete_result)){
					$delete_result=0;
				}else{
					$delete_result=1;
				}
			
			// if delete data success back to pemesanan page
			if($db->sql($sql_query)){
				header("location: orders.php");
			}
		}
		if(isset($_POST['btnNo'])){
			header("location: orders.php");
		}
		if(isset($_POST['btncancel'])){
			header("location: orders.php");
		}


	?>
	<?php if($permissions['orders']['delete']==1){?>
	<h1>Confirm Action</h1>
	<hr />
	<form method="post">
		<p>Are you sure want to delete this order?</p>
		<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete"/>
		<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo"/>
	</form>
	<div class="separator"> </div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm">Sorry! you have no permission to delete orders.</div>
		<form method="post">
		<input type="submit" class="btn btn-danger" value="Back" name="btncancel"/>
	</form>
	<?php } ?>
</div>
			
<?php $db->disconnect(); ?>