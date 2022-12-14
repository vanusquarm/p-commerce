<?php  
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
    $fn = new custom_functions;
if (isset($_POST['btnChange'])) {
    $charge = $db->escapeString($fn->xss_clean($_POST['charge']));
    $charge1 = $db->escapeString($fn->xss_clean($_POST['charge1']));
    // create array variable to handle error
    $error  = array();
    if (empty($charge)) {
        $tax = 0;
    } else if (!is_numeric($charge)) {
        $error['charge'] = "*Charge should be in numeric.";
    }
    if (empty($charge1)) {
        $tax = 0;
    } else if (!is_numeric($charge1)) {
        $error['charge'] = "*Charge should be in numeric.";
    }
    if (is_numeric($charge)) {
        // update tax in setting table
        $sql_query = "UPDATE settings SET Value = ".$charge."  WHERE Variable = 'Delievery Charge'";
            // Execute query
            $db->sql($sql_query);
            // store result 
            $update_result = $db->getResult();
        
    }
    if (is_numeric($charge1)) {
        // update tax in setting table
        $sql_query = "UPDATE settings SET Value = ".$charge1."  WHERE Variable = 'Delievery Charge 1'";
            // Execute query
            $db->sql($sql_query);
            // store result 
            $update_result = $db->getResult();
            if(!empty($update_result)){
                $update_result=0;
            }else{
                $update_result=1;
            }
    }
    // check update result
    if ($update_result==1) {
        $error['update_setting'] = " <h4><div class='alert alert-success'>
		* Settings update successfully</div></h4>";
    } else {
        $error['update_setting'] = "*Failed updating setting data";
    }
}
// get previous tax from setting table
$sql = "select Value from `settings` where id in (3,4)";
$db->sql($sql);
$res = $db->getResult();
$previous_charge = $res[0]['Value'];
$previous_charge1 = $res[1]['Value'];

// get previous currency symbol from setting table
?>
<section class="content-header">
	<h1>Delievery Charge</h1>
	<?php echo isset($error['update_setting']) ? $error['update_setting'] : '';?>
	<ol class="breadcrumb">
		<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
	<hr/>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-6">
		<!-- general form elements -->
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Delievery Charge</h3>
				</div><!-- /.box-header -->
				<!-- form start -->
				<form  method="post" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label for="exampleInputEmail1">Delievery Charge 1:</label><?php echo isset($error['charge']) ? $error['charge'] : '';?>
							<input type="text" class="form-control" name="charge" value="<?php echo $previous_charge; ?>" />
							<label for="exampleInputEmail1">Delievery Charge 2:</label><?php echo isset($error['charge']) ? $error['charge'] : '';?>
							<input type="text" class="form-control" name="charge1" value="<?php echo $previous_charge1; ?>" />
						</div>
					</div><!-- /.box-body -->
					<div class="box-footer">
						<input type="submit" class="btn-primary btn" value="Update" name="btnChange"/>
					</div>
				</form>
			</div><!-- /.box -->
		</div>
	</div>
</section>
<div class="separator"> </div>
<?php $db->disconnect();?>