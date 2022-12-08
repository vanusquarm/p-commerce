<?php
 include_once('includes/custom-functions.php');
    $fn = new custom_functions;
	include_once('includes/functions.php');
?>
	<?php 
	
		if(isset($_GET['id'])){
			$ID = $db->escapeString($fn->xss_clean($_GET['id']));
		}else{
			$ID = "";
		}
		
		// create array variable to store category data
		$faq_data = array();
			
		$sql_query = "SELECT id, question, answer, status 
				FROM faq
				ORDER BY id ASC";
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
			
		
		if(isset($_POST['btnEdit'])){
			if($permissions['faqs']['update']==1){
			
			$question = $db->escapeString($fn->xss_clean($_POST['question']));
			$answer = $db->escapeString($fn->xss_clean($_POST['answer']));
			$status = $db->escapeString($fn->xss_clean($_POST['status']));
			// create array variable to handle error
			$error = array();
			
			if(empty($question)){
				$error['question'] = " <span class='label label-danger'>Required!</span>";
			}
			if(empty($answer)){
				$error['answer'] = " <span class='label label-danger'>Required!</span>";
			}
			if(empty($status)){
				$error['status'] = " <span class='label label-danger'>Required!</span>";
			}
			
					
			if(!empty($question) && !empty($answer) && !empty($status)){
				
				
					
					// create random image file name
					$function = new functions;
					// updating all data
					$sql_query = "UPDATE faq 
							SET question = '".$question."' , answer = '".$answer."' , status = '".$status."' WHERE id =".$ID;
						// Execute query
						
						$db->sql($sql_query);
						// store result 
						$update_result = $db->getResult();

						if(!empty($update_result)){
							$update_result =0;
						}else{
							$update_result =1;
						}
	
				// check update result
				if($update_result==1){
					$error['update_data'] = "<section class='content-header'>
												<span class='label label-success'>Query updated Successfully</span>
												<h4><small><a  href='faq.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Faqs</a></small></h4>
												
												</section>";
				}else{
					$error['update_data'] = " <span class='label label-danger'>failed update</span>";
				}
			
			
		}
		}else{
		$error['update_data'] = "<section class='content-header'>
												<span class='label label-danger'>You have no permission to edit faq</span>
												
												</section>";

	}
	}
		
		// create array variable to store previous data
		$data = array();
			
		$sql_query = "SELECT * FROM faq WHERE id =".$ID;	
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
		
		
			
	?>
	<section class="content-header">
          <h1>
            Edit FAQ</h1>
            <small><?php echo isset($error['update_data']) ? $error['update_data'] : '';?></small>
			 <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
          </ol>
        </section>
	<section class="content">
          <!-- Main row -->
		 
          <div class="row">
		  <div class="col-md-6">
		  	<?php if($permissions['faqs']['update']==0) { ?>
		  		<div class="alert alert-danger topmargin-sm">You have no permission to edit faq</div>
		  	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Edit FAQ</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Question</label><?php echo isset($error['question']) ? $error['question'] : '';?>
			<input type="text" name="question" class="form-control" value="<?php echo $res[0]['question']; ?>"/>
					</div>
					  <div class="form-group">
					  <label for="exampleInputEmail1">Answer :</label><?php echo isset($error['answer']) ? $error['answer'] : '';?>
		<input type="text" name="answer" class="form-control" value="<?php echo $res[0]['answer'];?>"/>
					   </div>
					   <div class="form-group">
					  <label for="exampleInputEmail1">Status :</label><?php echo isset($error['status']) ? $error['status'] : '';?>
					  <select name="status" class="form-control">	
						<?php if($res[0]['status'] == 1){ ?>
							<option value="1" selected="selected">Pending</option>
							<option value="2" >Answered</option>
						<?php }else{ ?>
							<option value="1" >Pending</option>
							<option value="2" selected="selected">Answered</option>
						<?php } ?>
					</select>
					   </div>
                  <div class="box-footer">
                    <input type="submit" class="btn-primary btn" value="Update" name="btnEdit" />
                  </div>
                </form>
              </div><!-- /.box -->
			 </div>
		  </div>
	</section>

	<div class="separator"> </div>
<?php 
	$db->disconnect(); ?>