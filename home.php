<?php session_start();
// print_r($_SESSION);

    include_once ('includes/custom-functions.php');
    include_once ('includes/functions.php');
    $function = new custom_functions;
    
    // set time for session timeout
    $currentTime = time() + 25200;
    $expired = 3600;
    // if session not set go to login page
    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }
    // if current time is more than session timeout back to login page
    if ($currentTime > $_SESSION['timeout']) {
        session_destroy();
        header("location:index.php");
    }
    // destroy previous session timeout and create new one
    unset($_SESSION['timeout']);
    $_SESSION['timeout'] = $currentTime + $expired;
    $function = new custom_functions;
    $permissions = $function->get_permissions($_SESSION['id']);
    
    include "header.php";?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?=$settings['app_name']?> - Dashboard</title>
	</head>
    <body>
       
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>Home</h1>
                <ol class="breadcrumb">
                    <li>
                        <a href="home.php"> <i class="fa fa-home"></i> Home</a>
                    </li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-lg-4 col-xs-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3><?=$function->rows_count('orders');?></h3>
                                <p>Orders</p>
                            </div>
                            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                            <a href="orders.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3><?=$function->rows_count('products');?></h3>
                                <p>Products</p>
                            </div>
                            <div class="icon"><i class="fa fa-cubes"></i></div>
                            <a href="products.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3><?=$function->rows_count('users');?></h3>
                                <p>Registered Customers</p>
                            </div>
                            <div class="icon"><i class="fa fa-users"></i></div>
                            <a href="customers.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="box box-success">
                             <?php $year = date("Y");
                                $curdate = date('Y-m-d');
                              $sql = "SELECT SUM(final_total) AS total_sale,DATE(date_added) AS order_date FROM orders WHERE YEAR(date_added) = '$year' AND DATE(date_added)<'$curdate' GROUP BY DATE(date_added) ORDER BY DATE(date_added) DESC  LIMIT 0,7";
                                $db->sql($sql);
                                $result_order = $db->getResult(); ?>
                                <div class="tile-stats" style="padding:10px;">
                                    <div id="earning_chart" style="width:100%;height:350px;"></div>
                                </div>
                        </div>
                        
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="box box-danger">
                            <?php
                             $sql="SELECT `name`,(SELECT count(id) from `products` p WHERE p.category_id = c.id ) as `product_count` FROM `category` c";
                                $db->sql($sql);
                                $result_products = $db->getResult(); ?>
                                <div class="tile-stats" style="padding:10px;">
                                    <div id="piechart" style="width:100%;height:350px;"></div>
                                </div>
                        </div>
                        
                    </div>
                    
                </div>

              
                <!-- Latest Orders omitted -->
			</section>
        </div>
<script>
	$('#filter_order').on('change',function(){
    $('#orders_table').bootstrapTable('refresh');
    });
</script>
<script>
function queryParams(p){
	return {
		"filter_order": $('#filter_order').val(),
		limit:p.limit,
		sort:p.sort,
		order:p.order,
		offset:p.offset,
		search:p.search
	};
}
</script>
<?php include "footer.php";?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawPieChart);

    function drawPieChart() {

        var data1 = google.visualization.arrayToDataTable([
            ['Product', 'Count'],
            <?php
                foreach($result_products as $row){ echo "['".$row['name']."',".$row['product_count']."],";}
            ?>
        ]);
    
        var options1 = {
          title: 'Category Wise Product\'s Count',
          is3D: true
        };
    
        var chart1 = new google.visualization.PieChart(document.getElementById('piechart'));
    
        chart1.draw(data1, options1);
    }
</script>

<script>
    google.charts.load('current', {'packages':['bar']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Date', 'Total Sale In <?=$settings['currency']?>'],
            <?php foreach($result_order as $row){
                $date = date('d-M', strtotime($row['order_date']));
                echo "['".$date."',".$row['total_sale']."],"; 
            } ?>]);
        var options = {
            chart: {
                title: 'Weekly Sale',
                subtitle: 'Total Sale In Last Week (Month: <?php echo date("M"); ?>)',
            }
        };
    var chart = new google.charts.Bar(document.getElementById('earning_chart'));
    chart.draw(data,google.charts.Bar.convertOptions(options));
    }
</script>
  </body>
</html>