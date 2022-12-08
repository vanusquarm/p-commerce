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
		$sql_query = "DELETE FROM tbl_phone 
				WHERE Number_ID =".$ID;
		
			
			// Execute query
			$db->sql($sql_query);
			// store result 
			$delete_result = $db->getResult();
			if(!empty($delete_result)){
				$delete_result=0;
			}else{
				$delete_result=1;
			}
		
		// if delete data success back to pemesanan page
		if($delete_result==1){
			header("location: loginvarification.php");
		}
	}

	if(isset($_POST['btnNo'])){
		header("location: loginvarification.php");
	}
	?>
	<h1>Confirm Action</h1>
	<hr />
	<form method="post">
		<p>Are you sure want to delete this data?</p>
		<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete"/>
		<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo"/>
	</form>
	<div class="separator"> </div>
</div>
			
<?php $db->disconnect(); ?>