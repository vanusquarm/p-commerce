<?php
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
	
		// create object of functions class
		$function = new functions;
		
		// create array variable to store data from database
		$data = array();
		
		if(isset($_GET['keyword'])){	
			// check value of keyword variable
			$keyword = $function->sanitize($_GET['keyword']);
		}else{
			$keyword = "";
		}
		if(isset($_POST['myform'])){
		$city=$db->escapeString($fn->xss_clean($_POST['cit_id']));
		$data = array();
		if(isset($_GET['page'])){
			$page = $db->escapeString($fn->xss_clean($_GET['page']));
		}else{
			$page = 1;
		}
		
		// number of data that will be display per page
		$offset = 25;
							
		//lets calculate the LIMIT for SQL, and save it $from
		if ($page){
			$from 	= ($page * $offset) - $offset;
		}else{
			//if nothing was given in page request, lets load the first page
			$from = 0;	
		}	
		if($city=='all')
		{
		$sql_query = "SELECT a.id, a.name, c.name as city_name 
					FROM area a, city c
					WHERE a.city_id = c.id  and a.name!='Choose Your Area'
					ORDER BY a.id DESC";
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
			$total_records = $db->numRows($res);
		
		}
		else
		{
		$sql_query = "SELECT a.id, a.name, c.name as city_name 
					FROM area a, city c
					WHERE a.city_id = c.id  and c.id='".$city."' and a.city_id='".$city."' AND a.name!='Choose Your Area'
					ORDER BY a.id DESC";	
		
		// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
			$total_records = $db->numRows($res);
		}				
		// if no data on database show "Areas Not Available"
		if($permissions['locations']['read']==1){
		if($total_records == 0){
	?>
		<section class="content-header">
	<h1>Areas Not Available</h1>
	<ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
          </ol>
	<ol class="breadcrumb">
		<a href="add-area.php">
			<button class="btn btn-block btn-default"><i class="fa fa-plus-square"></i>Add New Area</button>
		</a>
	</ol>
	  
	<?php 
		// otherwise, show data
		}else{
			$row_number = $from + 1;
	?>
        <section class="content-header">
          <h1>
            Areas
          </h1>
		  <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
          </ol>
          <ol class="breadcrumb">
				<a class="btn btn-block btn-default" href="add-area.php"><i class="fa fa-plus-square"></i> Add New Area</a>
          </ol>
        </section>

        <!-- Main content -->
			 
        <section class="content">
          <!-- Main row -->
		 
          <div class="row">
            <!-- Left col -->
				<div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Areas</h3>
				  <form method="post">
            <select id="cit_id" name="cit_id" placeholder="Select Category" required>
				<?php
					$Query="select name, id from city where  name!='Choose Your City'";
					$db->sql($Query);
					$result=$db->getResult();
					if($result)
					{
				?>
					<option value="" <?php if(!isset($_POST['cit_id']) || (isset($_POST['cit_id']) && empty($_POST['cit_id']))) { ?>selected<?php } ?>>Select City</option>
					<option value="all">All</option>
				<?php 

						foreach($result as $row)
						{?>
							
							 <option value="<?php echo $row['id']; ?>" <?php if(isset($_POST['cit_id']) && $_POST['cit_id'] == $row['id']) { ?>selected<?php } ?>><?php echo $row['name']; ?></option>
						<?php }
					}
					else
					{
						echo"error in load";
					}
				?>
				</select>
					
				<input type="submit" name="myform" id="myform" value="Go">
			</form>
                  <div class="box-tools">
				  
			
				  <form  method="get">
                    <div class="input-group" style="width: 150px;">
					
                      <input type="text" name="keyword" class="form-control input-sm pull-right" placeholder="Search">
                      <div class="input-group-btn">
                        <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                      </div>
					 
                    </div>
					</form>
                  </div>
                </div><!-- /.box-header -->
				
                <div class="box-body table-responsive">
                  <table class="table table-hover">
                    <tr>
					<th>No.</th>
						<th>Area Name</th>
						<th>City Name</th>
						<th>Action</th>
                    </tr>
					<?php 
		// get all data using while loop
		$count=1;
		foreach ($res as $row){ ?>
		<tr>
			<td><?php echo $count; ?></td>
			<td><?php echo $row['name'];?></td>
			<td width="15%"><?php echo $row['city_name'];?></td>
			<td width="15%">
				<a href="edit-area.php?id=<?php echo $row['id'];?>"><i class="fa fa-edit"></i>Edit</a>&nbsp;
				<a href="delete-area.php?id=<?php echo $row['id'];?>"><i class="fa fa-trash-o"></i>Delete</a>
			</td>
		</tr>
					<?php $count++; }}} else { ?>
						<div class="alert alert-danger topmargin-sm">You have no permission to view areas</div>
					<?php } ?>
				
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
			<div class="col-sx-12">**
	<h4>
	<?php 
		// for pagination purpose
		$function->doPages($offset, 'areas.php', '', $total_records, $keyword);?>
	</h4>
	</div>
	<div class="separator"> </div>
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
          </div><!-- /.row (main row)* -->

        </section>
	<?php	}
		// get currency symbol from se*tting table
		else
		{
		// get all data from menu table and category table
		if(empty($keyword)){
				$sql_query = "SELECT a.id, a.name, c.name as city_name 
					FROM area a, city c
					WHERE a.city_id = c.id  and a.name!='Choose Your Area'
					ORDER BY a.id DESC";
		}else{
			$sql_query = "SELECT a.id, a.name, c.name as city_name 
					FROM area a, city c
					WHERE a.city_id = c.id  and c.id='".$city."' and a.city_id='".$city."' AND a.name!='Choose Your Area'
					ORDER BY a.id DESC";
		}
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
			
					
			// get total records
			$total_records = $db->numRows($res);
		
		
		// check page parameter
		if(isset($_GET['page'])){
			$page = $db->escapeString($fn->xss_clean($_GET['page']));
		}else{
			$page = 1;
		}
		
		// number of data that will be display per page		
		$offset = 25;
						
		//lets calculate the LIMIT for SQL, and save it $from
		if ($page){
			$from 	= ($page * $offset) - $offset;
		}else{
			//if nothing was given in page request, lets load the first page
			$from = 0;	
		}
		
		// get all data from reservation table
		if(empty($keyword)){
			$sql_query = "SELECT a.id, a.name, c.name as city_name 
					FROM area a, city c
					WHERE a.city_id = c.id  and a.name!='Choose Your Area'
					ORDER BY a.id DESC LIMIT ".$from.", ".$offset."";
		}else{
			$sql_query = "SELECT a.id, a.name, c.name as city_name 
					FROM area a, city c
					WHERE a.city_id = c.id  and c.id='".$city."' and a.city_id='".$city."' AND a.name!='Choose Your Area'
					ORDER BY a.id DESC LIMIT ".$from.", ".$offset."";
		}
		
			// Execute query
			$db->sql($sql_query);
			// store result 
			$db->getResult();
			
			// for paging purpose
			$total_records_paging = $total_records; 
		

		// if no data on database show "No Reservation is Available"
			if($permissions['locations']['read']==1){
		if($total_records_paging == 0){
	
	?>
	<section class="content-header">
	<h1>Areas Not Available</h1>
	<ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
          </ol>
	<ol class="breadcrumb">
		<a href="add-area.php">
			<button class="btn btn-block btn-default"><i class="fa fa-plus-square"></i>Add New Area</button>
		</a>
	</ol>
	  
	<?php 
		// otherwise, show data
		}else{
			$row_number = $from + 1;
	?>
        <section class="content-header">
          <h1>
            Areas
          </h1>
		  <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
          </ol>
          <ol class="breadcrumb">
				<a class="btn btn-block btn-default" href="add-area.php"><i class="fa fa-plus-square"></i> Add New Area</a>
          </ol>
        </section>

        <!-- Main content -->
			 
        <section class="content">
          <!-- Main row -->
		 
          <div class="row">
            <!-- Left col -->
				<div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Areas</h3>
				  <form method="post">
            <select id="cit_id" name="cit_id" placeholder="Select Category" required>
				<?php
					$Query="select name, id from city where  name!='Choose Your City'";
					$db->sql($Query);
					$result=$db->getResult();
					if($result)
					{
				?>
					<option value="" <?php if(!isset($_POST['cit_id']) || (isset($_POST['cit_id']) && empty($_POST['cit_id']))) { ?>selected<?php } ?>>Select City</option>
					<option value="all">All</option>
				<?php 
						if($permissions['locations']['read']==1){
						foreach($result as $row)
						{?>
							
							 <option value="<?php echo $row['id']; ?>" <?php if(isset($_POST['cit_id']) && $_POST['cit_id'] == $row['id']) { ?>selected<?php } ?>><?php echo $row['name']; ?></option>
						<?php } }
					}
					else
					{
						echo"error in load";
					}
				?>
				</select>
					
				<input type="submit" name="myform" id="myform" value="Go">
			</form>
                  <div class="box-tools">
				  
			
				  <form  method="get">
                    <div class="input-group" style="width: 150px;">
					
                      <input type="text" name="keyword" class="form-control input-sm pull-right" placeholder="Search">
                      <div class="input-group-btn">
                        <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                      </div>
					 
                    </div>
					</form>
                  </div>
                </div><!-- /.box-header -->
				
                <div class="box-body table-responsive">
                  <table class="table table-hover">
                    <tr>
					<th>No.</th>
					<th>Area Name</th>
					<th>City Name</th>
					<th>Action</th>
                    </tr>
					<?php 
		// get all data using while loop
		$count=1;
		foreach ($res as $row){ ?>
		<tr>
			<td><?php echo $count; ?></td>
			<td><?php echo $row['name'];?></td>
			<td width="15%"><?php echo $row['city_name'];?></td>
			<td width="15%">
				<a href="edit-area.php?id=<?php echo $row['id'];?>"><i class="fa fa-edit"></i>Edit</a>&nbsp;
				<a href="delete-area.php?id=<?php echo $row['id'];?>"><i class="fa fa-trash-o"></i>Delete</a>
			</td>
		</tr>
					<?php $count++; } } } else { ?>
						<div class="alert alert-danger topmargin-sm">You have no permission to view areas</div>
					<?php } ?>
				
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
			<div class="col-sx-12">
	<h4>
	<?php 
		// for pagination purpose
		$function->doPages($offset, 'areas.php', '', $total_records, $keyword);?>
	</h4>
	</div>
	<div class="separator"> </div>
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
          </div><!-- /.row (main row) -->

        </section><!-- /.content --> 	
<?php 
	
	$db->disconnect();} ?>
					
				