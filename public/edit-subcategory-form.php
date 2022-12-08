<?php
	include_once('includes/functions.php');
	$function = new functions;
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
?>
	<?php 
		if(isset($_GET['id'])){
			$ID = $db->escapeString($fn->xss_clean($_GET['id']));
		}else{
			$ID = "";
		}
		
		// create array variable to store subcategory data
		$subcategory_data = array();
				$db->select('subcategory','image',null,'id='.$ID);
			// $db->sql($sql);
			// store result
			$res=$db->getResult();
			$previous_subcategory_image=$res[0]['image'];
		if(isset($_POST['btnEdit'])){
			if($permissions['subcategories']['update']==1){
		  //  echo $_POST['category'];
			$category = $db->escapeString($fn->xss_clean($_POST['category']));
			$name = $db->escapeString($fn->xss_clean($_POST['name']));
			$slug = $function->slugify($db->escapeString($fn->xss_clean($_POST['name'])));
    		  $sql = "SELECT slug FROM subcategory where id=".$_GET['id'];
              $db->sql($sql);
              $res = $db->getResult();
              $i=1;
              foreach($res as $row){
                  if($slug==$row['slug']){
                    $slug = $slug.'-'.$i;
                    $i++;  
                  }
              }
			$subtitle = $db->escapeString($fn->xss_clean($_POST['subtitle']));
			// get image info
			$menu_image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
			$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
			$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
				
			// create array variable to handle error
			$error = array();
				
			if(empty($name)){
				$error['name'] = " <span class='label label-danger'>Required!</span>";
			}
			if(empty($subtitle)){
				$error['subtitle'] = " <span class='label label-danger'>Required!</span>";
			}
			
			// common image file extensions
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			
			// get image file extension
			error_reporting(E_ERROR | E_PARSE);
			$extension = end(explode(".", $_FILES["image"]["name"]));
			
			if(!empty($menu_image)){
				if(!(($image_type == "image/gif") || 
					($image_type == "image/jpeg") || 
					($image_type == "image/jpg") || 
					($image_type == "image/x-png") ||
					($image_type == "image/png") || 
					($image_type == "image/pjpeg")) &&
					!(in_array($extension, $allowedExts))){
					$error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
				}
			}
				
			if(!empty($name) && !empty($subtitle) && empty($error['image'])){
					
				if(!empty($menu_image)){
					
					// create random image file name
					$string = '0123456789';
					$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
					
					$image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
				
					// delete previous image
					$delete = unlink("$previous_subcategory_image");
					
					// upload new image
					$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/images/'.$image);
	  				$upload_image = 'upload/images/'.$image;
					$sql_query = "UPDATE subcategory 
							SET category_id='".$category."', name = '".$name."', slug = '".$slug."',  subtitle = '".$subtitle."',image = '".$upload_image."'
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
			
				}else{
					
					$sql_query = "UPDATE subcategory 
							SET category_id='".$category."', name = '".$name."', slug = '".$slug."', subtitle = '".$subtitle."', image = '".$previous_subcategory_image."'
							WHERE id = '".$ID."'";
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
					$error['update_subcategory'] = " <section class='content-header'>
												<span class='label label-success'>Subcategory updated Successfully</span>
												<h4><small><a  href='subcategories.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Sub Categories</a></small></h4>
												
												</section>";
				}else{
					$error['update_subcategory'] = " <span class='label label-danger'>Failed update Subcategory</span>";
				}
			}
		}else{
			$error['check_permission'] = " <section class='content-header'>
												<span class='label label-danger'>You have no permission to update subcategory</span>
												
												
												</section>";
		}
		}
			
		// create array variable to store previous data
		$data = array();
		
		$sql_query = "SELECT * 
				FROM subcategory 
				WHERE id =".$ID;
			$db->sql($sql_query);
			// store result
			$res_query=$db->getResult();
		if(isset($_POST['btnCancel'])){?>
			<script>
			window.location.href = "subcategories.php";
		</script>
		<?php } ?>
	<section class="content-header">
          <h1>
            Edit Subcategory <small><a  href='subcategories.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Sub Categories</a></small></h1>
            <small><?php echo isset($error['update_subcategory']) ? $error['update_subcategory'] : '';?></small>
          <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
          </ol>
        </section>
        
	<section class="content">
          <!-- Main row -->
		 
          <div class="row">
		  <div class="col-md-6">
		  	<?php if($permissions['subcategories']['update']==0) { ?>
        	<div class="alert alert-danger topmargin-sm">You have no permission to update subcategory.</div>
        	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Edit Subcategory</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">*
                    <div class="form-group">
                      <label for="exampleInputEmail1">category</label><?php echo isset($error['name']) ? $error['name'] : '';?>
                      <?php 
                      // $sql="SELECT * FROM category WHERE  id=".$res_query[0]['category_id'];
                      $db->select("category",'id,name');
                      $res = $db->getResult();
                      // print_r($res);
                      ?>
                      <select class="form-control" id="category" name="category">
                          <?php foreach($res as $row){
                            echo "<option value=".$row['id'];
                            if($row['id']==$res_query[0]['category_id'])
                            {
                                echo " selected";
                            }
                            echo ">".$row['name']."</option>";
                           } ?>
                      </select>
                      <?php 
                      	$db->select('subcategory','*',null,'id='.$ID);
                      	$res=$db->getResult();
                      ?>
                      
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Subcategory Name</label><?php echo isset($error['name']) ? $error['name'] : '';?>
                      <input type="text" class="form-control"  name="name" value="<?php echo $res[0]['name']; ?>">
                    </div>
					<div class="form-group">
                      <label for="exampleInputEmail1">Subcategory Subtitle</label><?php echo isset($error['subtitle']) ? $error['subtitle'] : '';?>
                      <input type="text" class="form-control"  name="subtitle" value="<?php echo $res[0]['subtitle']; ?>">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputFile">Image&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['image']) ? $error['image'] : '';?>
                      <input type="file" name="image" id="image" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." value="<img src='<?php echo $res[0]['image']; ?>'/>">
                      <p class="help-block"><img src="<?php echo $res[0]['image']; ?>" width="280" height="190"/></p>
                    </div>
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name="btnEdit">Update</button>
					<button type="submit" class="btn btn-danger" name="btnCancel">Cancel</button>
                  </div>
                </form>
              </div><!-- /.box -->
              <?php echo isset($error['check_permission']) ? $error['check_permission'] : '';?>	
			 </div>
		  </div>
	</section>

	<div class="separator"> </div>
<?php $db->disconnect(); ?>