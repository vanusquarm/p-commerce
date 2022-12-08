<?php 
    
session_start();
  include_once('./includes/custom-functions.php');
    
    $fn = new custom_functions;
    
    // set time for session timeout
    $currentTime = time() + 25200;
    $expired = 3600;
    
    // if session not set go to login page
    if(!isset($_SESSION['user'])){
        header("location:index.php");
    }
    
    // if current time is more than session timeout back to login page
    if($currentTime > $_SESSION['timeout']){
        session_destroy();
        header("location:index.php");
    }
    
    // destroy previous session timeout and create new one
    unset($_SESSION['timeout']);
    $_SESSION['timeout'] = $currentTime + $expired;
    include"header.php";?>
<html>
<head>
<title>Store Information | <?=$settings['app_name']?> - Dashboard</title>
</head>
</body>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <?php 
            	$sql = "SELECT * FROM settings WHERE variable='about_us'";
                $db->sql($sql);
                $res = $db->getResult();
            	$message = '';
            	if(isset($_POST['btn_update'])){
            		if(!empty($_POST['about_us'])){
            			
            			$about_us = $db->escapeString($fn->xss_clean($_POST['about_us']));
            			
            			$sql = "UPDATE `settings` SET `value`='".$about_us."' WHERE `variable` = 'about_us'";
            			$db->sql($sql);
            			
            			$sql = "SELECT * FROM settings WHERE `variable`='about_us'";
            			$db->sql($sql);
            			$res = $db->getResult();
            			$message .= "<div class='alert alert-success'> Information Updated Successfully!</div>";
            			
            		}
            	}
            ?>
            <section class="content-header">

                <h2>About Us</h2>
            	<h4><?=$message?></h4>
                <ol class="breadcrumb">
                    <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
                </ol>
                <hr />
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Update Information</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <form  method="post" enctype="multipart/form-data">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="app_name">About US :</label>
                                        <textarea rows="10" cols="10" class="form-control" name="about_us" id="about_us" required><?=$res[0]['value']?></textarea>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <input type="submit" class="btn-primary btn" value="Update" name="btn_update"/>
                                </div>
                            </form>
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
            </section>
            <div class="separator"> </div>
      </div><!-- /.content-wrapper -->
  </body>
</html>
<?php include"footer.php";?>
<script type="text/javascript" src="css/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">CKEDITOR.replace('about_us');</script>
