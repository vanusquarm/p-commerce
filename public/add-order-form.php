<?php 
    include_once('includes/functions.php'); 
	date_default_timezone_set('Asia/Kolkata');
	$function = new functions;
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    
    $sql_query = "SELECT id, name 
    	FROM category 
    	ORDER BY id ASC";	
    	// Execute query
    	$db->sql($sql_query);
    	// store result 
    	$res=$db->getResult();
    $sql_query = "SELECT value FROM settings WHERE variable = 'Currency'";
  
        $db->sql($sql_query);
    	// store result 
    	
        $res_cur=$db->getResult();
    	
    	
    if(isset($_POST['btnAdd'])){
        if($permissions['products']['create']==1){
        // print_r($_POST);
		$name = $db->escapeString($fn->xss_clean($_POST['name']));
		$slug = $function->slugify($fn->xss_clean($_POST['name']));
    	$category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    	$subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
    	$serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
    	$description = $db->escapeString($fn->xss_clean($_POST['description']));
    		
    	// get image info
    	$image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
    	$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
    	$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
		
    	// create array variable to handle error
    	$error = array();
    	
    	if(empty($name)){
    		$error['name'] = " <span class='label label-danger'>Required!</span>";
    	}
    		
    	if(empty($category_id)){
    		$error['category_id'] = " <span class='label label-danger'>Required!</span>";
    	}				
    		
    	if(empty($price)){
    		$error['price'] = " <span class='label label-danger'>Required!</span>";
    	}/* else if(!is_numeric($price)){
    		$error['price'] = " <span class='label label-danger'>Price in number!</span>";
    	} */
    	
    	if(empty($measurement)){
    		$error['measurement'] = " <span class='label label-danger'>Required!</span>";
    	}
    	
    	if(empty($quantity)){
    		$error['quantity'] = " <span class='label label-danger'>Required!</span>";
    	}else if(!is_numeric($quantity)){
    		$error['quantity'] = " <span class='label label-danger'>Quantity in number!</span>";
    	}
    		
    	if(empty($serve_for)){
    		$error['serve_for'] = " <span class='label label-danger'>Not choosen</span>";
    	}			
    
    	if(empty($description)){
    		$error['description'] = " <span class='label label-danger'>Required!</span>";
    	}
    	// common image file extensions
    	$allowedExts = array("gif", "jpeg", "jpg", "png");
    	
    	// get image file extension
    	error_reporting(E_ERROR | E_PARSE);
    	$extension = end(explode(".", $_FILES["image"]["name"]));
		
    	if($image_error > 0){
    		$error['image'] = " <span class='label label-danger'>Not uploaded!</span>";
    	}else if(!(($image_type == "image/gif") || 
    		($image_type == "image/jpeg") || 
    		($image_type == "image/jpg") || 
    		($image_type == "image/x-png") ||
    		($image_type == "image/png") || 
    		($image_type == "image/pjpeg")) &&
    		!(in_array($extension, $allowedExts))){
    	
    		$error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
    	}
		$error['other_images'] = '';
		if($_FILES["other_images"]["error"][0] == 0){
			for($i=0;$i<count($_FILES["other_images"]["name"]);$i++){
				$_FILES["other_images"]["type"][$i];
				if($_FILES["other_images"]["error"][$i] > 0){
					$error['other_images'] = " <span class='label label-danger'>Images not uploaded!</span>";
				}else if(!(($_FILES["other_images"]["type"][$i] == "image/gif") || 
					($_FILES["other_images"]["type"][$i] == "image/jpeg") || 
					($_FILES["other_images"]["type"][$i] == "image/jpg") || 
					($_FILES["other_images"]["type"][$i] == "image/x-png") ||
					($_FILES["other_images"]["type"][$i] == "image/png") || 
					($_FILES["other_images"]["type"][$i] == "image/pjpeg")) &&
					!(in_array($_FILES["other_images"]["type"][$i], $allowedExts))){
					$error['other_images'] = " <span class='label label-danger'>Images type must jpg, jpeg, gif, or png!</span>";
				}
			}
		}
    
    if(!empty($name) && !empty($category_id) && !empty($serve_for) && empty($error['image']) && empty($error['other_images']) && !empty($description)){
    		
			// create random image file name
    		$string = '0123456789';
    		$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
    		
    		$image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
    			
    		// upload new image
    		$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/images/'.$image);
			$other_images = '';
			if(isset($_FILES['other_images']) && ($_FILES['other_images']['size'][0] > 0 )){
				//Upload other images
				$file_data = array();
				$target_path = 'upload/other_images/';
				for($i=0;$i<count($_FILES["other_images"]["name"]);$i++){
					
					$filename = $_FILES["other_images"]["name"][$i];
					$temp = explode('.',$filename);
					$filename = microtime(true) . '.' . end($temp);
					$file_data[] = $target_path.''.$filename;
					if(!move_uploaded_file($_FILES["other_images"]["tmp_name"][$i], $target_path.''.$filename))
						echo "{$_FILES['image']['name'][$i]} not uploaded<br/>";
				}
				$other_images = json_encode($file_data);
			}
			
			$upload_image = 'upload/images/'.$image;
            if (strpos($name, "'") !== false) {
                $name=str_replace("'", "''", "$name");
                if(strpos($description, "'") !== false)
                    $description=str_replace("'", "''", "$description");
            }
    		// insert new data to product table
                $sql="INSERT INTO products (name,slug,category_id,subcategory_id,image,other_images,description) VALUES('$name','$slug','$category_id','$subcategory_id','$upload_image','$other_images','$description')";
                $db->sql($sql);
    			$product_id = $db->getResult();
                 if(!empty($product_id)){
                    $product_id=0;
                }
                else{
                    $product_id=1;

                }
                // print_r($product_id);
                $sql="SELECT id from products ORDER BY id DESC";
                $db->sql($sql);
                $res_inner=$db->getResult();
			if($db->escapeString($fn->xss_clean($_POST['type'])) == 'packet'){
			    for($i=0;$i<count($fn->xss_clean_array($_POST['packate_measurement']));$i++){
                    $product_id=$db->escapeString($res_inner[0]['id']);
                    $type=$db->escapeString($fn->xss_clean($_POST['type']));
                    $measurement=$db->escapeString($fn->xss_clean($_POST['packate_measurement'][$i]));
                    $measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['packate_measurement_unit_id'][$i]));
                    $price=$db->escapeString($fn->xss_clean($_POST['packate_price'][$i]));
                    $discounted_price=!empty($fn->xss_clean($_POST['packate_discounted_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['packate_discounted_price'][$i])) : 0;
                    $serve_for=$db->escapeString($fn->xss_clean($_POST['packate_serve_for'][$i]));
                    $stock=$db->escapeString($fn->xss_clean($_POST['packate_stock'][$i]));
                    $stock_unit_id=$db->escapeString($fn->xss_clean($_POST['packate_stock_unit_id'][$i]));

                    $sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,price,discounted_price,serve_for,stock,stock_unit_id) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$price','$discounted_price','$serve_for','$stock','$stock_unit_id')";
                     $db->sql($sql);
                     $product_variant = $db->getResult();   
			    }
                    if(!empty($product_variant)){
                    $product_variant=0;
                }
                else{
                    $product_variant=1;
                }
                // print_r($product_variant);
			    
			}elseif($db->escapeString($fn->xss_clean($_POST['type'])) == "loose"){
			    for($i=0;$i<count($fn->xss_clean_array($_POST['loose_measurement']));$i++){
                    $product_id=$db->escapeString($res_inner[0]['id']);
                    $type=$db->escapeString($fn->xss_clean($_POST['type']));
                    $measurement=$db->escapeString($fn->xss_clean($_POST['loose_measurement'][$i]));
                    $measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['loose_measurement_unit_id'][$i]));
                    $price=$db->escapeString($fn->xss_clean($_POST['loose_price'][$i]));
                    $discounted_price=!empty($fn->xss_clean($_POST['loose_discounted_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['loose_discounted_price'][$i])) : 0;
                    $serve_for=$db->escapeString($fn->xss_clean($_POST['serve_for']));
                    $stock=$db->escapeString($fn->xss_clean($_POST['loose_stock']));
                    $stock_unit_id=$db->escapeString($fn->xss_clean($_POST['loose_stock_unit_id']));

                    $sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,price,discounted_price,serve_for,stock,stock_unit_id) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$price','$discounted_price','$serve_for','$stock','$stock_unit_id')";
                     $db->sql($sql);
                     $product_variant = $db->getResult();
               
			    }
                     if(!empty($product_variant)){
                     $product_variant=0;
                    }
                else{
                    $product_variant=1;
                }
                // print_r($product_variant);
			}
    		if($product_variant==1){
    			$error['add_menu'] = "<section class='content-header'>
                                                <span class='label label-success'>Product Added Successfully</span>
                                                <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                                
                                                </section>";
    		}else {
    			$error['add_menu'] = " <span class='label label-danger'>Failed</span>";
    		}
    	}
        }else{
        $error['check_permission'] = " <section class='content-header'>
                                                <span class='label label-danger'>You have no permission to create product</span>
                                                
                                                
                                                </section>";

        
    }
    }
    ?>
<section class="content-header">
    <h1>Add Order</h1>
    <?php echo isset($error['add_menu']) ? $error['add_menu'] : '';?>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
      
</section>
<section class="content">
                                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                         
                            <div class="box-body">
                                <ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#home">Customer Information</a></li>
  <li><a data-toggle="tab" href="#menu1">Order Information</a></li>
  <li><a data-toggle="tab" href="#menu2">Payment</a></li>
</ul>
 <form class="form-horizontal" method="POST" action="add_cake_order.php" enctype="multipart/form-data">
<div class="tab-content">
  <div id="home" class="tab-pane fade in active">
       <div class="modal-body">
    
             <div class="form-group">
                        <label for="customer" class="col-sm-1 control-label">Customer Name <span style="color: red">*  </span></label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required="">
                        </div>
                        <label for="customer" class="col-sm-1 control-label">Contact Number <span style="color: red">*  </span></label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="primary_contact" name="primary_contact" required="">
                        </div>
                     

                      
                    </div>
                    <div class="form-group">
                        <label for="colour" class="col-sm-1 control-label">Location Address <span style="color: red">*  </span></label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="location" name="location" required="">
                        </div>

                        <label for="inscription" class="col-sm-1 control-label">Referral Person </label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="referred_by" name="referred_by">
                        </div>
                    </div>
                    <div class="form-group">
                        

                        <label for="inscription" class="col-sm-1 control-label">Referral Contact </label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="referral_contact" name="referral_contact">
                        </div>
                    </div>
                  
                        
                  
        
            <div class="modal-footer">
              
                
                <a href="#menu1" data-toggle="tab" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Next Order Details</a>
            </div>
            
       </div>
  </div>
  <div id="menu1" class="tab-pane fade">
    <div class="modal-body">
               
                    <div class="form-group">
          
                        <label for="cake_type" class="col-sm-1 control-label">Cake Type</label>

                        <div class="col-sm-5">
                           
                           <select class="form-control" id="cake_type" name="cake_type" required="">
                                <option value="" selected="">- Select -</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Birthday">Birthday</option>
                                <option value="Anniversary">Anniversary</option>
                                <option value="Treat">Treat</option>
                                <option value="Surprise">Surprise</option>
                                <option value="Slices">Slices</option>
                            </select>
                        </div>
                        
                         <label for="colour" class="col-sm-1 control-label">Color</label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="colour" name="colour" required="">
                        </div>
                    </div>
                    <div class="form-group">
                       

                        <label for="inscription" class="col-sm-1 control-label">Inscription</label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="inscription" name="inscription" required="">
                        </div>
                        
                         <label for="quantity" class="col-sm-1 control-label">Quantity</label>

                        <div class="col-sm-5">
                            <input type="number" class="form-control" id="quantity" name="quantity" required="">
                        </div>
                    </div>
                    <div class="form-group">
                       
                        <label for="flavour" class="col-sm-1 control-label">Flavour</label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="flavour" name="flavour" required="">
                        </div>
                        
                         <label for="icing" class="col-sm-1 control-label">Icing</label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="icing" name="icing" required="">
                        </div>
                    </div>
                    <div class="form-group">
                       
                        <label for="size" class="col-sm-1 control-label">Size</label>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="size" name="size" required="">
                        </div>
                        
                          <label for="icing" class="col-sm-1 control-label">Amount (Per Qty)</label>

                        <div class="col-sm-5">
                            <input type="number" class="form-control" id="amount" name="amount" required="">
                        </div>
                    </div>
                    <div class="form-group">
                      
                 
                           <label for="inscription" class="col-sm-1 control-label">Design</label>
                
                        <div class="col-sm-5">
                        <textarea name="description" id="description" class="form-control" rows="8"></textarea>
                            <script type="text/javascript" src="css/js/ckeditor/ckeditor.js"></script>
							<script type="text/javascript">CKEDITOR.replace('description');</script>
                        </div>
 <label for="pickup_datetime" class="col-sm-1 control-label">Pickup DateTime</label>
                     <div class="col-sm-5">
                            <input type="datetime-local" class="form-control" id="pickup_datetime" name="pickup_datetime" required="">
                        </div>
                
                    </div>
           
                        
                  
            </div>
            <div class="modal-footer">
 <a href="#home" data-toggle="tab" class="btn btn-default btn-flat pull-left">Back </a>
           
             <a href="#menu2" data-toggle="tab" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Next Payment Details</a>
           
            </div>
      
  </div>
  <div id="menu2" class="tab-pane fade">
  <div class="modal-body">
               
               
                <div class="form-group">
                   
                     
                       <label for="cake_type" class="col-sm-1 control-label">Payment Mode</label>

                        <div class="col-sm-5">
                           
                           <select class="form-control" id="payment_mode" name="payment_mode" required="">
                                <option value="" selected="">- Select -</option>
                                <option value="Cash">Cash</option>
                                <option value="Momo">Momo</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                              
                            </select>
                        </div>
                       
                           <label for="momo_transid" class="col-sm-1 control-label">Momo TransID/Bank Acc </label>

                         <div class="col-sm-5">
                            <input type="text" class="form-control" id="bankacc_momoid" name="bankacc_momoid">
                        </div>
                </div>
                <div class="form-group">
                    
                     
                   
                       <label for="amount_received" class="col-sm-1 control-label">Amount Received</label>

                         <div class="col-sm-5">
                            <input type="number" class="form-control" id="amount_received" name="amount_received">
                        </div>
                </div>
                        
                  
            </div>
            <div class="modal-footer">
            <a href="#menu1" data-toggle="tab" class="btn btn-default btn-flat pull-left">Back </a>
                <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Place Order</button>
           
            </div>
  </div>
</div>
 </form>
                              
                            </div>
                        </div>
                    </div>
                </div>
            </section>
<div class="separator"> </div>

