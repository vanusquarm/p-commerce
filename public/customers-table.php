<section class="content-header">
    <h1>Customers</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
      
</section>
<!-- search form -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <?php if($permissions['customers']['read']==1){?>
            <div class="box">
                <div class="box-header with-border">
                <a id="myBtn" href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
                    
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-striped table-hover" data-toggle="table" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=users"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-filter-control="true" data-filter-show-clear="true"
                        data-sort-name="id" data-sort-order="desc"
                        data-query-params="queryParams_1">
                        <thead>
                          <tr>
                              <th data-field="name" data-sortable="true">Name</th>
                              <th data-field="email" data-sortable="true">Email Adress</th>
                              <th data-field="mobile" data-sortable="true">Contact Number</th>
                              <th data-field="location" data-sortable="true">Location</th>
                              <th data-field="digital_address" data-sortable="true">Digital Address</th>
                              <th data-field="referred_by" data-sortable="true">Referred By</th>
                              <th data-field="status" data-sortable="true" data-formatter="statusFormatter">Status</th>
                              <th data-field="created_at" data-sortable="true" >Date Added</th>
                              <th data-field="actions" data-formatter="operateFormatter" data-events="operateEvents">Actions</th>
    
                          </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view customers</div>
        <?php } ?>
            <!-- /.box -->
        </div>
    </div>
    <!-- /.row (main row) -->
</section>
<!-- Added Modal -->

<div id="myModal" class="modal">

  <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span></button>
              <h4 class="modal-title"><b>Add New Customer</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="add_customer.php" enctype="multipart/form-data">
         
              
                <div class="form-group">
                    <label for="firstname" class="col-sm-3 control-label">First Name</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="firstname" name="firstname" required="">
                    </div>
                </div>
                <div class="form-group">
                    <label for="surname" class="col-sm-3 control-label">Last Name</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="surname" name="surname" required="">
                    </div>
                </div>
                         <div class="form-group">
                    <label for="email_address" class="col-sm-3 control-label">Email Address</label>

                    <div class="col-sm-9">
                      <input type="email" class="form-control" id="email_address" name="email_address" required="">
                    </div>
                </div>
                <div class="form-group">
                    <label for="address" class="col-sm-3 control-label">Location Address</label>

                    <div class="col-sm-9">
                      <textarea class="form-control" id="location" name="location"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="digital_address" class="col-sm-3 control-label">Digital Address</label>

                    <div class="col-sm-9">
                      <textarea class="form-control" id="digital_address" name="digital_address"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="contact" class="col-sm-3 control-label">Contact Number</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="primary_contact" name="primary_contact">
                    </div>
                </div>
                <div class="form-group">
                    <label for="referred_by" class="col-sm-3 control-label">Referred By</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="referred_by" name="referred_by">
                    </div>
                </div>
              
            </form></div>
            <div class="modal-footer">
              <button id="close" type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Save</button>
              
            </div>
        </div>
    </div>

</div>

<script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];
var close = document.getElementById("close");

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}
close.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

// Actions for table row
window.operateEvents = {
    'click .like': function (e, value, row, index) {
      alert('You click like icon, row: ' + JSON.stringify(row))
      console.log(value, row, index)
    },
    'click .edit': function (e, value, row, index) {
      alert('You click edit icon, row: ' + JSON.stringify(row))
      console.log(value, row, index)
    }
  }

  function operateFormatter(value, row, index) {
    return [
      
      '<a href="customers-sales-history?user=355" class="btn btn-info btn-sm btn-flat">',
        '<i class="fa fa-search"></i>Sales History</a>',
      '<button class="btn btn-success btn-sm edit btn-flat">',
        '<i class="fa fa-edit"></i> Edit Info',
      '</button>'
    ].join('')
  }
  // status formatter
  function statusFormatter(value, row, index) {
    return value?
    [
      '<span class="label label-success">active <span class="badge">1</span></span>'
    ].join(''): 
    [
      '<span class="label label-danger">inactive</span>'
    ].join('');
  }

</script>
<script>
function queryParams_1(p){
	return {
	    limit:p.limit,
		sort:p.sort,
		order:p.order,
		offset:p.offset,
		search:p.search
	};
}
</script>
<!-- /.content -->