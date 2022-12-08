<?php
include_once('includes/variables.php');
include_once('includes/crud.php');
include_once('includes/custom-functions.php');
$function = new custom_functions();
if (isset($_GET['id'])) {
    $ID = $db->escapeString($function->xss_clean($_GET['id']));
} else {
    $ID = "";
}
// create array variable to handle error
$update_order_permission = $permissions['orders']['update'];
$error = array();
if (isset($_POST['update_order_status'])) {
    $process = $db->escapeString($function->xss_clean($_POST['status']));
}
    $sql="SELECT oi.*,p.*,v.product_id, v.measurement,o.*,o.total as order_total,o.wallet_balance,oi.active_status as oi_active_status,u.email,u.name as uname,u.country_code,o.status as order_status,p.name as pname,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name 
        FROM `order_items` oi
        JOIN users u ON u.id=oi.user_id
        JOIN product_variant v ON oi.product_variant_id=v.id
        JOIN products p ON p.id=v.product_id
        JOIN orders o ON o.id=oi.order_id
    WHERE o.id=".$ID;
    $db->sql($sql);
    $res=$db->getResult();
    $items=[];
    foreach($res as $row){
            $data=array($row['product_id'],$row['product_variant_id'],$row['pname'],$row['measurement'],$row['mesurement_unit_name'],$row['quantity'],$row['discounted_price'],$row['price'],$row['oi_active_status']);
            array_push($items, $data);
        }
       
?>
<section class="content-header">
    <h1>Order Detail</h1>
    <?php echo isset($error['update_data']) ? $error['update_data'] : ''; ?>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-9">
            <?php
            if($permissions['orders']['read']==1) { 
                if($permissions['orders']['update']==0){?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update orders.</div>
            <?php } ?>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Order Detail</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
<!--                    <form  id="update_status_form">-->
                        <table class="table table-bordered">
                            <tr>
                                <input type="hidden" name="hidden" id="order_id" value="<?php echo $res[0]['id']; ?>">
                                <th style="width: 10px">ID</th>
                                <td><?php echo $res[0]['id']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Name</th>
                                <td><?php echo $res[0]['uname']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Email</th>
                                <td><?php echo $res[0]['email']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Contact</th>
                                <td><?php echo $res[0]['mobile']; ?></td>
                            </tr>
                             <tr>
                                <th style="width: 10px">Items</th>
                                <td><?php $total = 0;
                                
                                    foreach ($items as $item) {
                                        // echo $item[8];
                                        if($item[8]=='received'){
                                            $active_status = '<label class="label label-primary">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='processed'){
                                            $active_status = '<label class="label label-info">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='shipped'){
                                            $active_status = '<label class="label label-warning">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='delivered'){
                                            $active_status = '<label class="label label-success">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='returned' || $item[8]=='cancelled'){
                                            $active_status = '<label class="label label-danger">'.$item[8].'</label>';
                                        }
                                        $total += $subtotal = ($item[6] != 0 && $item[6] < $item[7])?($item[6]*$item[5]) : ($item[7]*$item[5]);
                                        echo "<b>Product Id : </b>" . $item[0];
                                        echo "<b> Product Variant Id : </b>" . $item[1];
                                        echo " <b>Name : </b>" . $item[2];
                                        echo " <b>Unit : </b>" . $item[3]." ".$item[4];
                                        echo " <b>Quantity : </b>" . $item[5];
                                        echo " <b>Price : </b>" . $item[7];
                                        echo " <b>Discounted Price : </b>" . $item[6];
                                        echo " <b>Subtotal : </b>" . $subtotal;
                                        echo " <b>Active Status : </b>" . $active_status."<br>
                                        -----------------------------------<br>";
                                        
                                    }?>

                                </td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Total (<?=$settings['currency']?>)</th>
                                <td ><?php echo $res[0]['order_total']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">D.Charge (<?=$settings['currency']?>)</th>
                                <td ><?php echo $res[0]['delivery_charge']; ?></td>

                            </tr>
                            <tr>
                                <th style="width: 10px">Tax <?=$settings['currency']?>(%)</th>
                                <td ><?php echo $res[0]['tax_amount'].'('.$res[0]['tax_percentage'].'%)'; ?></td>
                            </tr>
                            
                             <?php if($res[0]['discount']>0){
                                $discounted_amount = $res[0]['total'] * $res[0]['discount'] / 100; /*  */
                        	    $final_total = $res[0]['total'] - $discounted_amount;
                                $discount_in_rupees = $res[0]['total']-$final_total;
                                $discount_in_rupees = $discount_in_rupees;
                                // echo $discount_in_rupees;
                            } else {
                                $discount_in_rupees = 0;
                            }?>
                            <tr>
                                <th style="width: 10px">Disc. <?=$settings['currency']?>(%)</th>
                                <td ><?php echo  $discount_in_rupees.'('.$res[0]['discount'].'%)'; ?></td>
                            </tr>
                             
                            <tr>
                                <th style="width: 10px">Promo Disc. (<?=$settings['currency']?>)</th>
                                <td ><?php echo $res[0]['promo_discount']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Wallet Used</th>
                                <td ><?php echo $res[0]['wallet_balance']; ?></td>
                            </tr>
                            
                            
                            <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $res[0]['order_total'];?>">
                            <input type="hidden" name="delivery_charge" id="delivery_charge" value="<?php echo $res[0]['delivery_charge'];?>">
                            <input type="hidden" name="tax_amount" id="tax_amount" value="<?php echo $res[0]['tax_amount'];?>">
                            <input type="hidden" name="promo_discount" id="promo_discount" value="<?php echo $res[0]['promo_discount'];?>">
                            <input type="hidden" name="wallet_balance" id="wallet_balance" value="<?php echo $res[0]['wallet_balance'];?>">
                            <?php
                                $total = $res[0]['total'];
                                $delivery_charge = $res[0]['delivery_charge'];
                                $tax_amount = $res[0]['tax_amount'];
                                $promo_discount = $res[0]['promo_discount'];
                                $wallet = $res[0]['wallet_balance'];
                                $final_total = $total+$delivery_charge+$tax_amount-$discount_in_rupees-$promo_discount-$wallet;
                                $f_total = $total+$delivery_charge+$tax_amount-$promo_discount-$wallet;
                            ?>
                            <input type="hidden" name="final_amount" id="final_amount" value="<?=$f_total;?>">
                            
                            
                             <tr>
                                <th style="width: 10px">Discount %</th>
                                <td ><input type="number" class="form-control" id="input_discount" name="input_discount" value="<?php echo $res[0]['discount']; ?>" min=0 max=100></td>
                                <td><a href="#" title='save_discout' class="btn btn-primary form-control update_order_total_payable" data-id='<?=$row['id'];?>'>Save</a></td>
                            </tr>
                            
                            
                            <tr>
                                <th style="width: 10px">Payable Total(<?=$settings['currency']?>)</th>
                                <td ><input type="text" class="form-control" id="final_total" name="final_total" value="<?=ceil($final_total);?>" disabled ></td>
                            </tr>
                            <tr>
                                <th >Deliver By</th>
                                <td>
                                <?php
                                        $sql="SELECT id,name FROM delivery_boys WHERE status=1";
                                        $db->sql($sql);
                                        $result=$db->getResult();
                                    ?>

                                    <select id='deliver_by' name='deliver_by' class='form-control col-md-7 col-xs-12' required>
                                    <option value=''>Select Delivery Boy</option>
                                    <?php foreach($result as $row1){
                                        if($res[0]['delivery_boy_id'] == $row1['id']){?>
                                            <option value='<?=$row1['id']?>' selected><?=$row1['name']?></option>
                                       <?php } else{?>
                                       <option value='<?=$row1['id']?>'><?=$row1['name']?></option>                                    


                                    
                                    <?php }}?>
                                </select>
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Payment Method</th>
                                <td ><?php echo $res[0]['payment_method']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Promo Code</th>
                                <td ><?=(!empty($res[0]['promo_code']) || $res[0]['promo_code'] != null)?$res[0]['promo_code']:""; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Address</th>
                                <td ><?php echo $res[0]['address']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Order Date</th>
                                <td ><?php echo date('d-m-Y',strtotime($row['date_added'])); ?></td>
                            </tr>
                            <tr>
                                <th >Status</th>
                                <td>
                                <?php
                                    $status = json_decode($res[0]['order_status']);
                                    $i = count($status);
                                    $currentStatus = $status[$i - 1][0];
                                    ?>

                                    <select name="status" id="status" class="form-control">
                                        <option value="received">Received</option>
                                        <option value="processed" >Processed</option>
                                        <option value="shipped" >Shipped</option>
                                        <option value="delivered" >Delivered</option>
                                        <option value="cancelled">Cancel</option>
                                        <option value="returned">Returned</option>
                                    </select>
                                </td>
                            </tr>
                            
                        </table>
                        

                        <!-- /.box-body -->
                        <div class="alert alert-danger" id="result_fail" style="display:none"></div>
                        <div class="alert alert-success" id="result_success" style="display:none"></div>
                        <div class="box-footer clearfix">
                            <?php $whatsapp_message = "Hello ".ucwords($res[0]['uname']).", Your order with ID : ".$res[0]['id']." is ".ucwords($currentStatus).". Please take a note of it. If you have further queries feel free to contact us. Thank you.";?>
                            <a href="#" title='update' id="submit_btn" class="btn btn-primary update_order_status" data-id='<?=$res[0]['id'];?>'>Update</a>
                            <a href="https://api.whatsapp.com/send?phone=<?='+'.$res[0]['country_code'].' '.$res[0]['mobile'];?>&text=<?=$whatsapp_message;?>" target='_blank' title="Send Whatsapp Notification" class="btn btn-success"><i class="fa fa-whatsapp"></i> Send Whatsapp Notification</a>
                        </div>
<!--                    </form>-->
                </div>
               
               <?php 
            //   if($res[0]['delivery_boy_id']!=0){
               if ($currentStatus == "received") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()"  style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i>Generate Invoice</button>
                <?php } elseif ($currentStatus == "processed") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } elseif ($currentStatus == "shipped") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } elseif ($currentStatus == "delivered") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } else { ?>
                    <button class="btn btn-primary disabled pull-right" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } ?>
            </div>
            <?php } else {?>
            <div class="alert alert-danger">You have no permission to view orders</div>
            <?php }  ?>
            <!-- /.box -->
        </div>
        <?php if($permissions['orders']['read']==1){?>
        <div class="col-md-3">
            <ul class="timeline">
            <?php foreach($status as $s){ ?>
                <!-- timeline time label -->
                <li class="time-label">
                    <span class="bg-blue">
                        <?=$s[0];?>
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <!-- timeline icon -->
                    <i class="fa fa-circle bg-blue"></i>
                    <div class="timeline-item">
                        <!--<span class="time"><i class="fa fa-clock-o"></i> 12:05</span>-->
                        <h3 class="timeline-header"><?=$s[1];?></h3>
                        <div class="timeline-body">
                        </div>
                    </div>
                </li>
                <!-- timeline time label -->
            <!-- /.timeline-label -->
            <!-- timeline item -->
            <!-- END timeline item -->
        <?php } ?>
        </ul>
        </div>
        <?php } ?>
    </div>
</section>
<!-- <script>
    var total_amount=$('#total_amount').val();
    $("#final_total").val(total_amount);
</script> -->
<script>
    
$(document).on('click','.update_order_status',function(e){
    e.preventDefault();
        var update_permission = '<?=$update_order_permission;?>';
        if(update_permission==0){
            alert('Sorry! you have no permission to update orders.');
            window.location.reload();
            return false;
        }
        var status = $('#status').val();
        var id = $('#order_id').val();
        var deliver_by = $('#deliver_by').val();
        var dataString ='update_order_status=true&id='+id+'&status='+status+'&delivery_boy_id='+deliver_by+'&ajaxCall=1';
    $.ajax({        
        url: "api-firebase/order-process.php",
        type: "POST",
        data: dataString,
        beforeSend:function(){$('#submit_btn').html('Please wait..');$('#submit_btn').attr('disabled',true);},
        dataType: "json",
        success: function (data) {
            var result = $.map(data, function(value, index) {
                return [value];
            });
            if(result[1][0]=='C'){
                $('#result_fail').html(result[1]);
                $('#result_fail').show().delay(3000).fadeOut();
            }else{
                $('#result_success').html(result[1]);
                $('#result_success').show().delay(3000).fadeOut();
            }
            
            
            $('#submit_btn').attr('disabled',false);
            $('#submit_btn').html('Update');
            
             //alert(result[1]);
//          if(!result[0]){
//              location.reload();
//             }
        }

    });
});
</script>

<script>
$(document).on('click','.update_order_total_payable',function(e){
    e.preventDefault();
        var update_permission = '<?=$update_order_permission;?>';
        if(update_permission==0){
            alert('Sorry! you have no permission to update orders.');
            window.location.reload();
            return false;
        }
        var discount = $('#input_discount').val();
        var total_payble = $('#final_total').val();
        // alert(total_payble);
        var deliver_by = $('#deliver_by').val();
        var id = $('#order_id').val();
        var dataString ='update_order_total_payable=true&id='+id+'&discount='+discount+'&total_payble='+total_payble+'&deliver_by='+deliver_by+'&ajaxCall=1';
    $.ajax({        
        url: "api-firebase/order-process.php" ,
        type: "POST",
        data: dataString,
        beforeSend: function(){$(this).html('...');},
        dataType: "json",
        success: function (data) {
            var result = $.map(data, function(value, index) {
                return [value];
            });
             alert(result[1]);
            if(!result[0]){}
                location.reload();
        }

    });
});
</script>


<script type="text/javascript">


/* function sendMail(){
    var process = $('#status').val();
    window.location.href = './public/send-message.php?process='+process+'&id=<?php //echo $data['id']; ?>';
} */
</script>

<script>
    $(document).ready(function () {
        $("#status").val("<?= $GLOBALS['currentStatus'] ?>");
    });
</script>
<script>
    function myfunction() {
         var create = '<?php echo $permissions['reports']['create']; ?>';
         if(create==0){
            alert('You have no permission to create invoice');
            return false;

         }
        window.location.href = 'invoice.php?id=<?php echo $res[0]['id']; ?>';
    }
</script>

<script>
$('#input_discount').on('input',function() {
    var total=$("#total_amount").val();
    
    var delivery_charge=$("#delivery_charge").val();
    
    var tax_amount=$("#tax_amount").val();
    
    var promo_discount=$("#promo_discount").val();
    
    var wallet_balance=$("#wallet_balance").val();
    
    // alert(total);
          var discount = $('#input_discount').val();
            discounted_amount = total * discount / 100; /*  */
            final_total = total - discounted_amount;
            discount_in_rupees = total-final_total;
            discount_in_rupees = discount_in_rupees;
            var f_total = +total + +delivery_charge + +tax_amount - promo_discount - wallet_balance - discount_in_rupees;
            // alert(f_total);
          if(discount >= 0){
              
        
              $("#final_total").val(Math.round((f_total + Number.EPSILON) * 100) / 100);
          }
});

</script>

<?php $db->disconnect(); ?>