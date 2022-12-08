<?php
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');


include_once('../../api-firebase/send-email.php');
include_once('../../includes/crud.php');
include_once('../../includes/custom-functions.php');
include_once('verify-token.php');
$fn = new custom_functions();
$db=new Database();
$db->connect(); 
include_once('../../includes/variables.php');


/* 
-------------------------------------------
APIs for Delivery Boys
-------------------------------------------
1. login
2. get_delivery_boy_by_id  
3. get_orders_by_delivery_boy_id
4. get_fund_transfers 
5. update_delivery_boy_profile
6. update_order_status
7. delivery_boy_forgot_password
8. get_notifications
9. update_delivery_boy_fcm_id
10. check_delivery_boy_by_mobile
-------------------------------------------

-------------------------------------------

*/

if(!verify_token()){
    return false;
}


if(!$_POST['accesskey'] AND  $db->escapeString($fn->xss_clean($_POST['accesskey'])) == $access_key){
    $response['error'] = true;
	$response['message'] = "No Accsess key found!";
	print_r(json_encode($response));
	return false;
	exit();
}

if(isset($_POST['login'])){
     /* 
    1.Login
        accesskey:90336
        mobile:9876543210
        password:12345678
        fcm_id:YOUR_FCM_ID
        Login:1
    */
    
    if(empty(trim($_POST['mobile']))){
        $response['error'] = true;
    	$response['message'] = "Mobile should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    if(empty($_POST['password'])){
        $response['error'] = true;
    	$response['message'] = "Password should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }

    
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $password = md5($_POST['password']);
    $sql = "SELECT * FROM delivery_boys	WHERE mobile = '".$mobile."' AND password = '".$password."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	
	
	if($num == 1){
	    if($res[0]['status'] == 0){
	        $response['error'] = true;
	        $response['message'] = "It seems your acount is not active please contact admin for more info!"; 
	        $response['data'] = array();
		}else{
		    /* update fcm_id in delivery boy table */
		    $delivery_boy_id = $res[0]['id'];
		    $fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])):"";
			if(!empty($fcm_id)){
			    $sql1 = "update delivery_boys set `fcm_id` ='$fcm_id' where id = '".$delivery_boy_id."'";
			    $db->sql($sql1);
			    $db->sql($sql);
			    $res=$db->getResult();
			    $db->disconnect(); 
			}
			$response['error'] = false;
            $response['message'] = "Delivery Boy Login Susseccfully";
            $response['data'] = $res;
		}
	}else{
	    if($res[0]['mobile'] != $mobile){
	        $response['error'] = true;
		    $response['message'] = "Phone Number is not registered!";
	    }
	    if($res[0]['mobile'] != $mobile && $res[0]['password'] != $password){
	        $response['error'] = true;
		    $response['message'] = "Invalid number or password, Try again.";
	    }
	   
	}
	print_r(json_encode($response));
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}


/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['get_delivery_boy_by_id'])){
    
    /* 
    2.get_delivery_boy_by_id
        accesskey:90336
        id:78
        get_delivery_boy_by_id:1
    */
     if(empty($_POST['id'])){
        $response['error'] = true;
    	$response['message'] = "Id of Delivery boy should be Passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $sql = "SELECT * FROM delivery_boys	WHERE id = '".$id."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	$db->disconnect(); 
	if($num == 1){
    	$response['error'] = false;
        $response['message'] = "Delivery Boy Data Fetched Susseccfully";
		$response['data'] = $res;
		$response['data'][0]['balance'] = ceil($response['data'][0]['balance']);
	}else{
		$response['error'] = true;
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";

}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_orders_by_delivery_boy_id'])){
    
    /* 
    3.get_orders_by_delivery_boy_id
        accesskey:90336
        id:40        // {optional}          
        order_id:1001        // {optional}  
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id / user_id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        filter_order:filter_order_status         // {optional} 
        get_orders_by_delivery_boy_id:1
    */
    $response_data = array();
    
    $id = ( isset($_POST['id']) && !empty(trim($_POST['id'])) && is_numeric($_POST['id']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['id']))) : '';
    $order_id = ( isset($_POST['order_id']) && !empty(trim($_POST['order_id'])) && is_numeric($_POST['order_id']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order_id']))) : '';
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty(trim($_POST['search']))){
        $search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
        $where .= " where (name like '%".$search."%' OR o.id like '%".$search."%' OR o.mobile like '%".$search."%' OR address like '%".$search."%' OR `payment_method` like '%".$search."%' OR `delivery_charge` like '%".$search."%' OR `delivery_time` like '%".$search."%' OR o.`status` like '%".$search."%' OR `date_added` like '%".$search."%')";
    }
    
    if(isset($_POST['filter_order']) && $_POST['filter_order']!=''){
        $filter_order=$db->escapeString($fn->xss_clean($_POST['filter_order']));
        if(isset($_POST['search']) && $_POST['search']!='' ){
            $where .=" and `active_status`='".$filter_order."'";
        }else{
            $where .=" where `active_status`='".$filter_order."'";
        }
    }
    
    if(empty($where)){
        if(empty($id)){
           $where .= (!empty($order_id))?" WHERE o.id = $order_id":""; 
        }else{
           $where .= " WHERE delivery_boy_id = ".$id; 
           $where .= (!empty($order_id))?" AND o.id = $order_id":""; 
        }   
    }else{
        $where .= (!empty($id))?" AND delivery_boy_id = ".$id:""; 
        $where .= (!empty($order_id))?" AND o.id = $order_id":"";
    }
    
    $orders_join = " JOIN users u ON u.id=o.user_id ";
    
    $sql = "SELECT COUNT(o.id) as total FROM `orders` o ".$orders_join." ".$where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach($res as $row){
        $total = $row['total'];
    }
    $sql="select o.*,u.name as name FROM orders o ".$orders_join." ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
    $db->sql($sql);
    $res = $db->getResult();
    
    for($i=0;$i<count($res);$i++) {
       $sql="select oi.*,p.name as name, v.measurement,p.image, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name from `order_items` oi 
            join product_variant v on oi.product_variant_id=v.id 
            join products p on p.id=v.product_id 
            where oi.order_id=".$res[$i]['id'];
            
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
    }
    $rows1 = $tempRow = array();
    $response_data['total'] = $total;
    
    foreach($res as $row){
        $items = $row['items'];
        $items1 = $temp = array();
        $total_amt = 0;
        
        foreach($items as $item){
            $price = $item['discounted_price']==0?$item['price']:$item['discounted_price'];
            $temp = array(
                'id' => $item['id'], 
                'product_variant_id' => $item['product_variant_id'], 
                'name' => $item['name'], 
                'unit' => $item['measurement']." ".$item['mesurement_unit_name'], 
                'product_image' => DOMAIN_URL.$item['image'],
                'price' => $price,
                'quantity' => $item['quantity'], 
                'subtotal' => $item['quantity'] * $price,
                'active_status' => $item['active_status']
            ); 
            $total_amt += $item['sub_total'];
            $items1[] = $temp;
        }
        
        if($row['active_status'] == 'received'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'processed'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'shipped'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='delivered'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='returned' || $row['active_status'] == 'cancelled' ){
            $active_status = $row['active_status'];
        }
        
        $discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100;
        $final_total = $row['total'] - $discounted_amount;
        
        $discount_in_rupees = $row['total']-$final_total;
        $discount_in_rupees = floor($discount_in_rupees);
        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['name'] = $row['name'];
        $tempRow['mobile'] = $row['mobile'];
        $tempRow['delivery_charge'] = $row['delivery_charge'];
        $tempRow['items'] = $items1;
        $tempRow['total'] = $total_amt;
        $tempRow['tax'] = $row['tax_amount'].'('.$row['tax_percentage'].'%)';
        $tempRow['promo_discount'] = $row['promo_discount'];
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['discount'] = $discount_in_rupees.'('.$row['items'][0]['discount'].'%)';
        $tempRow['qty'] = $row['items'][0]['quantity'];
        $tempRow['final_total'] = ceil($row['final_total']);
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['deliver_by'] = "You";
        $tempRow['payment_method'] = $row['payment_method'];
        $tempRow['address'] = $row['address'];
        $tempRow['latitude'] = $row['latitude'];
        $tempRow['longitude'] = $row['longitude'];
        $tempRow['delivery_time'] = $row['delivery_time'];
        $tempRow['active_status'] = $active_status;
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['date_added'] = date('d-m-Y',strtotime($row['date_added']));
        $rows1[] = $tempRow;
    }
    $response_data['error'] = false;
    $response_data['data'] = $rows1;
    print_r(json_encode($response_data));

}

/*
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_fund_transfers'])){
    
    /* 
    4. get_fund_transfers
        accesskey:90336
        id:82
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        get_fund_transfers:1
        
    */
    
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where f.`id` like '%".$search."%' OR d.`name` like '%".$search."%' OR f.`message` like '%".$search."%' OR d.`mobile` like '%".$search."%' OR d.`address` like '%".$search."%' OR f.`opening_balance` like '%".$search."%' OR f.`closing_balance` like '%".$search."%' OR d.`balance` like '%".$search."%' OR f.`date_created` like '%".$search."%'" ;
	}
	
    if(empty($where)){
		$where .= " WHERE delivery_boy_id = ".$id;
	}else{
		$where .= " AND delivery_boy_id = ".$id;
	}
	
	$sql = "SELECT COUNT(f.id) as total FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row)
		$total = $row['total'];
 	$sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach($res as $row){
		$tempRow['id'] = $row['id'];
		$tempRow['name'] = $row['name'];
		$tempRow['mobile'] = $row['mobile'];
		$tempRow['address'] = $row['address'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['type'] = $row['type'];
		$tempRow['amount'] = $row['amount'];
		$tempRow['opening_balance'] = $row['opening_balance'];
		$tempRow['closing_balance'] = $row['closing_balance'];
		$tempRow['status'] = $row['status'];
		$tempRow['message'] = $row['message'];
		$tempRow['date_created'] = $row['date_created'];
		
		$rows[] = $tempRow;
	}
	$json_response['error'] = false;
	$json_response['data'] = $rows;
	print_r(json_encode($json_response));

}
/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['update_delivery_boy_profile'])){
    
    /* 
    5.update_delivery_boy_profile
        accesskey:90336
        id:87
        name:any value       
        address:Jl Komplek Polri   
        old_password:        // {optional}
        update_password:        // {optional}
        confirm_password:        // {optional}
        update_delivery_boy_profile:1
    */
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $name=$db->escapeString(trim($fn->xss_clean($_POST['name'])));
    $address =$db->escapeString(trim($_POST['address']));
    $old_password = (isset($_POST['old_password']) || !empty(trim($_POST['old_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['old_password']))):"";
    $update_password = (isset($_POST['update_password']) || !empty(trim($_POST['update_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['update_password']))):"";
    $confirm_password = (isset($_POST['confirm_password']) || !empty(trim($_POST['confirm_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['confirm_password']))):"";
    $change_password = false;
    
    /* check if id is not empty and there is valid data in it */
    if(!isset($_POST['id']) || empty(trim($_POST['id'])) || !is_numeric($_POST['id'])){
        $json_response['error'] = true;
        $json_response['message'] = "Invalid Id of Delivery Boy";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    $sql="SELECT * from delivery_boys where id='$id'";
    $db->sql($sql);
    $res_id = $db->getResult();
    $num = $db->numRows($res);
    if($num != 1){
        $json_response['error'] = true;
        $json_response['message'] = "Delivery Boy is not Registered.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* if any of the password field is set and old password is not set */
    if(( !empty($confirm_password) || !empty($update_password)) && empty($old_password)){
        $json_response['error'] = true;
        $json_response['message'] = "Please enter old password.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* either of the password field is not empty and is they don't match */
    if(( !empty($confirm_password) || !empty($update_password)) && ($update_password != $confirm_password)){
        $json_response['error'] = true;
        $json_response['message'] = "Password and Confirm Password mismatched.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* when all conditions are met check for old password in database */
    if( !empty($confirm_password) && !empty($update_password) && !empty($old_password)){
        $old_password = md5($old_password);
        $sql = "Select password from `delivery_boys` where id = '$id' and password = '$old_password' ";
        $db->sql($sql);
        $res = $db->getResult();
     
        if(empty($res)){
            $json_response['error'] = true;
            $json_response['message'] = "Old password mismatched.";
            print_r(json_encode($json_response));
            return false;
            exit();
        }
        $change_password = true;
        $confirm_password = md5($confirm_password);
    }
    
    $sql = "Update delivery_boys set `name`='".$name."',`address`='".$address."' ";
    $sql .= ( $change_password )?", `password`='".$confirm_password."' ":"";
    $sql .= " where `id` = '$id' ";
    
    if($db->sql($sql)){
        $json_response['error'] = false;
        $json_response['message'] = "Information Updated Successfully.";
        $json_response['message'] .= ( $change_password )?" and password also updated successfully.":"";
    } else {
        $json_response['error'] = true;
        $json_response['message'] = "Some Error Occurred! Please Try Again.";
    }
    print_r(json_encode($json_response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['update_order_status']) && isset($_POST['id'])) {
    
    /* 
    6.update_order_status
        accesskey:90336
		update_order_status:1
		id:26
		status:cancelled
		delivery_boy_id:40        // {optional}
    */
	$id = $db->escapeString($fn->xss_clean($_POST['id']));
	$postStatus = $db->escapeString($fn->xss_clean($_POST['status']));
	$delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
	
	if(isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != ''){
		$sql="UPDATE orders SET `delivery_boy_id`='".$delivery_boy_id."' WHERE id=".$id;
		$db->sql($sql);	
		$sql_get_name="select name from delivery_boys where id='$delivery_boy_id'";
		$db->sql($sql_get_name);
		$delivery_boy_name = $db->getResult();
		$message_delivery_boy = "Hello, Dear ".ucwords($delivery_boy_name[0]['name']).", You have new order to deliver. Here is your order ID : #".$id.". Please take a note of it.";
		$fn->send_notification_to_delivery_boy($delivery_boy_id,"Your new order has been ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id); 
        $fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order has been ".ucwords($postStatus),$message_delivery_boy,'order_reward');
	}
    $sql = "SELECT COUNT(id) as cancelled FROM `orders` WHERE id='".$id."' && (active_status LIKE '%cancelled%' OR active_status LIKE '%returned%')";
	$db->sql($sql);
	$res_cancelled = $db->getResult();
	if($res_cancelled[0]['cancelled']>0){
    	$response['error'] = true;
		$response['message'] = 'Could not update order status!';
		print_r(json_encode($response));
		return false;
	}
    $sql="select user_id,payment_method,wallet_balance,total,delivery_charge,tax_amount,status from orders where id=".$id;
	$db->sql($sql);
	$res = $db->getResult();
	
	$sql = "SELECT sub_total FROM order_items WHERE order_id=".$id;
	$db->sql($sql);
	$res_query = $db->getResult();
	
	$sql = "SELECT COUNT(id) as total FROM `orders` WHERE user_id=".$res[0]['user_id']." && status LIKE '%delivered%'";
	$db->sql($sql);
	$res_count = $db->getResult();
	
	$sql = "SELECT * FROM `users` WHERE id=".$res[0]['user_id'];
	$db->sql($sql);
	$res_user = $db->getResult();
    if(!empty($res)){
    	$status = json_decode($res[0]['status']);
    	$user_id =  $res[0]['user_id'];
    	foreach($status as $each){
    		if (in_array($postStatus, $each)) {
    			$response['error'] = true;
    			$response['message'] = 'Delivery Boy updated, But order status not due to duplicate status!';
    			print_r(json_encode($response));
    			return false;
    		}
    	}
    	if($postStatus=='cancelled' || $postStatus=='returned'){
    	    $sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE `order_id`='.$id;
    	    $db->sql($sql);
    	    $res_oi = $db->getResult();
    	    for($i=0;$i<count($res_oi);$i++){
        	    if($res_oi[$i]['type']=='packet'){
        	        $sql = "UPDATE product_variant SET stock = stock + ".$res_oi[$i]['quantity']." WHERE id='".$res_oi[$i]['product_variant_id']."'";
        			$db->sql($sql);
        			$sql = "select stock from product_variant where id=".$res_oi[0]['product_variant_id'];
        			$db->sql($sql);
        			$res_stock = $db->getResult();
        			if($res_stock[0]['stock']>0){
            			$sql = "UPDATE product_variant set serve_for='Available' WHERE id='".$res_oi[0]['product_variant_id']."'";
            			$db->sql($sql);
        			}
        	    }else{
        	        if($res_oi[$i]['measurement_unit_id'] != $res_oi[$i]['stock_unit_id']){
        	            $stock = $fn->convert_to_parent($res_oi[$i]['measurement'],$res_oi[$i]['measurement_unit_id']);
        	            $stock = $stock * $res_oi[$i]['quantity'];
        	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[$i]['product_id']."'";
        			    $db->sql($sql);
        	        }else{
        	            $stock = $res_oi[$i]['measurement'] * $res_oi[$i]['quantity'];
        	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[$i]['product_id']."'";
        			    $db->sql($sql);
        	        }
        	        $sql = "select stock from product_variant where product_id=".$res_oi[0]['product_id'];
                    $db->sql($sql);
                    $res_stck= $db->getResult();
                    if($res_stck[0]['stock']>0){
                        $sql = "UPDATE product_variant set serve_for='Available' WHERE product_id='".$res_oi[0]['product_id']."'";
            			$db->sql($sql);
                    }
        	    }
    	    }
    	    if($res[0]['payment_method'] != 'cod' && $res[0]['payment_method'] !='COD'){
                $user_id = $res[0]['user_id'];
                $total = $res[0]['total']+$res[0]['delivery_charge']+$res[0]['tax_amount'];
                $user_wallet_balance = $fn->get_wallet_balance($user_id);
                $new_balance = $user_wallet_balance + $total;
                $fn->update_wallet_balance($new_balance,$user_id);
        	    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
            }else{
                if($res[0]['wallet_balance']!=0){
                    $user_id = $res[0]['user_id'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
                    $fn->update_wallet_balance($new_balance,$user_id);
        		    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
                }
            }
        }
    	if($postStatus=='delivered'){
    		$sql = "SELECT delivery_boy_id,final_total FROM orders WHERE id=".$id;
    		$db->sql($sql);
    		$res_boy = $db->getResult();
    		if($res_boy[0]['delivery_boy_id']!=0){
    			$sql = "SELECT bonus FROM delivery_boys WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$res_bonus = $db->getResult();
    		    $reward = $res_boy[0]['final_total']/100*$res_bonus[0]['bonus'];
    			$sql = "UPDATE delivery_boys SET balance = balance + $reward WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$comission=$fn->add_delivery_boy_commission($delivery_boy_id,'credit',$reward,$message='Order Delivery Boy Commission.');
    			
    			$sql = "SELECT value FROM `settings` WHERE variable='currency'";
    			$db->sql($sql);
    			$currency = $db->getResult();
    		    if($postStatus == 'delivered'){
				    $message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name[0]['name']) . ", your order has been delivered. order ID : #" . $id . ". Please take a note of it.";
    			}else{
    				$message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name[0]['name']) . ", You have new order to deliver. Here is your order ID : #" . $id . ". Please take a note of it.";
    			}
    			$fn->send_notification_to_delivery_boy($delivery_boy_id,"Your commission ".$reward." ".$currency[0]['value']." has been credited","$message_delivery_boy",'delivery_boys',$id);
    			$fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your commission ".$reward." ".$currency[0]['value']." has been credited",$message_delivery_boy,'order_reward');

    		}
    		if($config['is-refer-earn-on']==1){
    			if($res_boy[0]['final_total']>=$config['min-refer-earn-order-amount']){
    				if($res_count[0]['total']==0){
    					if($res_user[0]['friends_code'] != ''){
    						if($config['refer-earn-method']=='percentage'){
    							$percentage = $config['refer-earn-bonus'];
    							$bonus_amount = $res_boy[0]['final_total']/100*$percentage;
    							if($bonus_amount>$config['max-refer-earn-amount']){
    								$bonus_amount = $config['max-refer-earn-amount'];
    							}
    						}else{
    							$bonus_amount = $config['refer-earn-bonus'];
    						}
    						$sql  = "SELECT name,friends_code FROM users WHERE id=".$res[0]['user_id'];
    						$db->sql($sql);
    						$res_data = $db->getResult();
    						
    						$sql = " select id from `users` where `referral_code` = '".$res_data[0]['friends_code']."'";
    						$db->sql($sql);
    						$friend_user = $db->getResult();
    						if(!empty($friend_user))
    						    $fn->add_wallet_transaction($friend_user[0]['id'],'credit',floor($bonus_amount),'Refer & Earn Bonus on first order by '.ucwords($res_data[0]['name']));
    						
    						$sql = "UPDATE users SET balance = balance + floor($bonus_amount) WHERE referral_code='".$res_data[0]['friends_code']."'";
    						$db->sql($sql);
    					}
    				}
    			}
    		}
    	}
    	$temp=[];
    	foreach($status as $s){
    	    array_push($temp,$s[0]);
    	}
    	$sql = "SELECT id,active_status FROM order_items WHERE order_id=".$id;
        $db->sql($sql);
        $result = $db->getResult();
    	if($postStatus=='cancelled'){
    	    if (!in_array('cancelled', $temp)) {
    	        $status[] = array('cancelled',date("d-m-Y h:i:sa") );
	            $data = array(
	                'status' => $db->escapeString(json_encode($status)),
    	        );
    	    }
    	    $db->update('orders',$data,'id='.$id);
    	    foreach($result as $item){
    	        if($item['active_status'] != 'cancelled'){
    	            $item_data = array(
        	            'status' => $db->escapeString(json_encode($status)),
            	        'active_status' => 'cancelled'
    	            );
    	        $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    }
    	}
    	if($postStatus=='processed'){
    	    if (!in_array('processed', $temp)) {
    	        $status[] = array('processed',date("d-m-Y h:i:sa") );
    	        $data = array(
    	            'status' => $db->escapeString(json_encode($status))
    	       );
    	    }
    	    $db->update('orders',$data,'id='.$id);
    	    foreach($result as $item){
    	        $item_data = array(
    	            'status' => $db->escapeString(json_encode($status)),
        	        'active_status' => 'processed'
    	            );
    	        if($item['active_status'] != 'cancelled'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    }
    	}
    	if($postStatus=='shipped'){
    	    if (!in_array('processed', $temp)) {
    	        $status[] = array('processed',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    if (!in_array('shipped', $temp)) {
    	        $status[] = array('shipped',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    $db->update('orders',$data,'id='.$id);
    	    foreach($result as $item){
    	        $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
    	        'active_status' => 'shipped'
    	            );
    	        if($item['active_status'] != 'cancelled'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    }
    	}
    	if($postStatus=='delivered'){
    	    if (!in_array('processed', $temp)) {
    	        $status[] = array('processed',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    if (!in_array('shipped', $temp)) {
    	        $status[] = array('shipped',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    if (!in_array('delivered', $temp)) {
    	        $status[] = array('delivered',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    $db->update('orders',$data,'id='.$id);
        	 $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'delivered'
             );
    	    foreach($result as $item){
    	        if($item['active_status'] != 'cancelled'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    } 
    	}
    	$i = sizeof($status);
        $currentStatus = $status[$i-1][0];
        $final_status = array(
        	'active_status' => $currentStatus
    	);
     	if($db->update('orders',$final_status,'id='.$id)){
    		$response['error'] = false;
    		$response['message'] = $postStatus=='cancelled'?"Order has been cancelled!":"Order updated successfully.";
    		$res = $db->getResult();
    		$sql = "select name,email,mobile,country_code from `users` where id=".$user_id;
    		$db->sql($sql);
    		$res_user = $db->getResult();
    		
    		$to = $res_user[0]['email'];
    		$mobile = $res_user[0]['mobile'];
    		$country_code = $res_user[0]['country_code'];
    		$subject = "Your order has been ".ucwords($postStatus);
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services!You will receive future updates on your order via Email!";
    		$fn->send_order_update_notification($user_id,"Your order has been ".ucwords($postStatus),$message,'order');
    		send_email($to,$subject,$message);
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services! Contact us for more information";
    		
    		print_r(json_encode($response));
    	} else {
    		$response['error'] = true;
    		$response['message'] = "Delivery Boy updated, But Could not update order status. Try again!";
    		print_r(json_encode($response));
    	}
    }else{
		$response['error'] = true;
		$response['message'] = "Sorry Invalid order ID";
		print_r(json_encode($response));
	}
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['delivery_boy_forgot_password']) && isset($_POST['mobile'])) {
    
    /* 
    7.delivery_boy_forgot_password
        accesskey:90336
		mobile:8989898989
		password:1234567
		delivery_boy_forgot_password:1
    */
    if(empty($_POST['password'])){
        $response['error'] = true;
    	$response['message'] = "Password should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    
    if(empty($_POST['mobile'])){
        $response['error'] = true;
    	$response['message'] = "Mobile Number id not passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    //$id = $db->escapeString($_POST['id']);
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $password = md5($db->escapeString($fn->xss_clean($_POST['password'])));
    
    $sql="SELECT mobile from delivery_boys where mobile='$mobile'";
    $db->sql($sql);
    $res_mobile = $db->getResult();
    
	if($res_mobile[0]['mobile'] == $mobile){
	    $sql_update = "UPDATE `delivery_boys` SET `password`='$password' WHERE `mobile`='$mobile'";	
	    $db->sql($sql_update);
		$response["error"]   = false;
		$response["message"] = "Password updated successfully";
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number id not Registered!";
	}
	print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_notifications'])){
    
    /* 
    8. get_notifications
        accesskey:90336
        id:114
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        get_notifications:1
        
    */
    
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where `id` like '%".$search."%' OR `title` like '%".$search."%' OR `message` like '%".$search."%' OR `type` like '%".$search."%' OR `date_created` like '%".$search."%'  " ;
	}
	
    if(empty($where)){
		$where .= " WHERE delivery_boy_id = ".$id;
	}else{
		$where .= " AND delivery_boy_id = ".$id;
	}
	
	$sql = "SELECT COUNT(id) as total FROM `delivery_boy_notifications` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row)
		$total = $row['total'];
 	$sql = "SELECT * FROM `delivery_boy_notifications` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach($res as $row){
		$tempRow['id'] = $row['id'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['order_id'] = $row['order_id'];
		$tempRow['title'] = $row['title'];
		$tempRow['message'] = $row['message'];
		$tempRow['type'] = $row['type'];
		$tempRow['date_created'] = $row['date_created'];
		
		$rows[] = $tempRow;
	}
	$json_response['error'] = false;
	$json_response['data'] = $rows;
	print_r(json_encode($json_response));

}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['update_delivery_boy_fcm_id'])){
     /* 
    9.update_delivery_boy_fcm_id
        accesskey:90336
        id:114
        fcm_id:YOUR_FCM_ID
        update_delivery_boy_fcm_id:1
    */
    
    if(empty($_POST['fcm_id'])){
        $response['error'] = true;
    	$response['message'] = "Please pass the fcm_id!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    
    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    if(isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])){
        $fcm_id = $db->escapeString($$fn->xss_clean($_POST['fcm_id']));
        $sql1 = "update delivery_boys set `fcm_id` ='$fcm_id' where id = '".$id."'";
	    if($db->sql($sql1)){
	        $response['error'] = false;
            $response['message'] = "Delivery Boy fcm_id Updeted Susseccfully.";
            print_r(json_encode($response));
	    } else {
	        $response['error'] = true;
            $response['message'] = "Can not update fcm_id of delivery boy.";
            print_r(json_encode($response));
	    }
   }
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['check_delivery_boy_by_mobile']) && isset($_POST['mobile'])) {
    
    /* 
    10.check_delivery_boy_by_mobile
        accesskey:90336
		mobile:8989898989
		check_delivery_boy_by_mobile:1
    */
  
    if(empty($_POST['mobile'])){
        $response['error'] = true;
    	$response['message'] = "Mobile Number id not passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    //$id = $db->escapeString($_POST['id']);
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    
    $sql="SELECT mobile from delivery_boys where mobile='$mobile'";
    $db->sql($sql);
    $res_mobile = $db->getResult();
    
	if($res_mobile[0]['mobile'] == $mobile){
		$response["error"]   = false;
		$response["message"] = "Mobile number is Registered.";
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number is not Registered!";
	}
	print_r(json_encode($response));
}



/*if(isset($_POST['test'])) {
    // echo $fn->add_delivery_boy_commission('114','credit',12,'Test Order commission');
    $a = $fn->send_notification_to_delivery_boy('114','test notification ','Sample notification goes here','order_status',612);
    echo $a;
}*/
