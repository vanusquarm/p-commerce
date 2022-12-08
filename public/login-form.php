<?php
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
	/* include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/connect_database.php'); 
	include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/variables.php');  */
	include('./includes/variables.php'); 
	
	// start session
	// if user click Login button
	if(isset($_POST['btnLogin'])){
	
		// get username and password
		$username = $db->escapeString($fn->xss_clean($_POST['username']));
		$password = $db->escapeString($fn->xss_clean($_POST['password']));
		
		// set time for session timeout
		$currentTime = time() + 25200;
		$expired = 3600;
		
		// create array variable to handle error
		$error = array();
		
		// check whether $username is empty or not
		if(empty($username)){
			$error['username'] = "*Username should be filled.";
		}
		
		// check whether $password is empty or not
		if(empty($password)){
			$error['password'] = "*Password should be filled.";
		}
		
		// if username and password is not empty, check in database
		if(!empty($username) && !empty($password)){
			
			// change username to lowercase
			$username = strtolower($username);
			
			//encript password to sha256
		    $password = md5($password);
			
			// get data from user table
			$sql_query = "SELECT * 
				FROM admin 
				WHERE username = '".$username."' AND password = '".$password."'";
				// echo $sql_query;
				// Bind your variables to replace the ?s
				// Execute query
				$db->sql($sql_query);
				/* store result */
				$res=$db->getResult();
				$num = $db->numRows($res);
				// Close statement object
				if($num == 1){
					$_SESSION['id'] = $res[0]['id'];
					$_SESSION['role'] = $res[0]['role'];
					$_SESSION['user'] = $username;
					$_SESSION['timeout'] = $currentTime + $expired;
					header("location: home.php");
				}else{
					$error['failed'] = "<span class='label label-danger'>Invalid Username or Password!</span>";
				}
			
			
		}	
	}
	?>
	<?php $sql_logo="select value from `settings` where variable='Logo' OR variable='logo'";
	    $db->sql($sql_logo);
	    $res_logo=$db->getResult();
	    
	    ?>
		<?php echo isset($error['update_user']) ? $error['update_user'] : '';?>
		    <div class="col-md-4 col-md-offset-4 " style="margin-top:150px;">
			<!-- general form elements -->
				<div class='row'>
				<div class="col-md-12 text-center">
					<img src="<?=DOMAIN_URL.'dist/img/'.$res_logo[0]['value']?>" height="110">
					<h3><?=$settings['app_name']?> Dashboard</h3>
				</div>
				<div class="box box-info col-md-12">
                <div class="box-header with-border">
                  <h3 class="box-title"></h3>
				  <center><?php echo isset($error['failed']) ? $error['failed'] : '';?></center>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post" enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Username :</label>
				            <input type="text" name="username" class="form-control" value="" required>
					</div>
					  <div class="form-group">
					  <label for="exampleInputEmail1">Password :</label>
				            <input type="password" class="form-control" name="password" value=""  required>
					   </div>
                  <div class="box-footer">
                    <button type="submit" name="btnLogin" class="btn btn-info pull-right">Login</button><br><br>
                  </div>
                </form>
              </div><!-- /.box -->
			 </div>
			 </div>
			</div>
