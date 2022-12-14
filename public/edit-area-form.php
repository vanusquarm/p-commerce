<?php
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
?>
	<?php 
	
		if(isset($_GET['id'])){
			$ID = $db->escapeString($fn->xss_clean($_GET['id']));

		}else{
			$ID = "";
		}
		$sql_query = "SELECT id, name 
				FROM city 
				ORDER BY id ASC";
			
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res_city=$db->getResult();
		if(isset($_POST['btnEdit'])){
			if($permissions['locations']['update']==1){
			$area_name = $db->escapeString($fn->xss_clean($_POST['area_name']));
			$city_ID = $db->escapeString($fn->xss_clean($_POST['city_ID']));	
			// create array variable to handle error
			$error = array();
			
			if(empty($area_name)){
				$error['area_name'] = " <span class='label label-danger'>Required!</span>";
			}
				
			if(empty($city_ID)){
				$error['city_ID'] = " <span class='label label-danger'>Required!</span>";
			}		
			if(!empty($area_name) && !empty($city_ID)){
				
					
					// updating all data except image file
					$sql_query = "UPDATE area 
						SET name = '".$area_name."' , city_id = ".$city_ID." 
						WHERE id =".$ID;
						// Execute query
						$db->sql($sql_query);
						// store result 
						$update_result = $db->getResult();
						if(!empty($update_result)){
							$update_result=0;
						}
						else{
							$update_result=1;
						}
					
				}
					
				// check update result
				if($update_result==1){
					$error['update_data'] = "<section class='content-header'>
						<span class='label label-success'>Area updated Successfully</span>
						<h4><small><a  href='areas.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to areas</a></small></h4>
						</section>";
				}else{
					$error['update_data'] = " <span class='label label-danger'>failed update</span>";
				}
			}else{
				$error['update_data'] = "<section class='content-header'>
						<span class='label label-danger'>You have no permission to update area</span>
						</section>";

			}
			}
			

		
		// create array variable to store previous data
		
			
		$sql_query = "SELECT * FROM area WHERE id =".$ID;
			// Execute query
			$db->sql($sql_query); 
			$res_area=$db->getResult();
			
	?>
	<section class="content-header">
          <h1>
            Edit Area</h1>
            <small><?php echo isset($error['update_data']) ? $error['update_data'] : '';?></small>
          <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
          </ol>
        </section>
	<section class="content">
          <!-- Main row -->
		 
          <div class="row">
		  <div class="col-md-6">
		  	<?php if($permissions['locations']['update']==0){?>
		  		<div class="alert alert-danger">You have no permission to update area</div>
		  	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Edit Area</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Area Name</label><?php echo isset($error['area_name']) ? $error['area_name'] : '';?>
                     
			<input type="text" name="area_name" class="form-control" value="<?php echo $res_area[0]['name']; ?>"/>
					</div>
					<label for="exampleInputEmail1">City :</label><?php echo isset($error['city_ID']) ? $error['city_ID'] : '';?>
						
						<select name="city_ID" class="form-control">
    						<?php foreach($res_city as $row){ 

    						if($row['id'] == $res_area[0]['city_id']){

    							?>
    						<option value="<?php echo $row['id']; ?>" selected="<?php echo $res_area[0]['city_id']; ?>" ><?php echo $row['name']; ?></option>
    						<?php }else{ if($permissions['locations']['read']==1){?>
    						<option value="<?php echo $row['id']; ?>" ><?php echo $row['name']; ?></option>
    						<?php }} }?>
						</select>
						</div>

                    
                  </div><!-- /.box-body -->

                  <div>
                    <input type="submit" class="btn-primary btn" value="Update" name="btnEdit"/>
                  </div>
                </form>
              </div><!-- /.box -->
			 </div>
		  </div>
	</section>

	<div class="separator"> </div>
<?php 
	$db->disconnect(); ?>