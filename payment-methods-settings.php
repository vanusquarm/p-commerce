<?php

session_start();

// set time for session timeout
$currentTime = time() + 25200;
$expired = 3600;

// if session not set go to login page
if (!isset($_SESSION['user'])) {
    header("location:index.php");
}
include_once('includes/custom-functions.php');
$fn = new custom_functions;

// if current time is more than session timeout back to login page
if ($currentTime > $_SESSION['timeout']) {
    session_destroy();
    header("location:index.php");
}

// destroy previous session timeout and create new one
unset($_SESSION['timeout']);
$_SESSION['timeout'] = $currentTime + $expired;

include "header.php"; ?>
<html>

<head>
    <title>Payment Gateways & Payment Methods Settings | <?= $settings['app_name'] ?> - Dashboard</title>
</head>
</body>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <?php
    $fn = new custom_functions();
    $db = new Database();
    $db->connect();
    $data = $fn->get_settings('payment_methods', true);
    // print_r($data);
    $message = '';

    if (isset($_POST) && isset($_POST['btn_update'])) {
        unset($_POST['btn_update']);
        // print_r(json_encode($_POST));
        if (empty($data)) {
            $data = $fn->xss_clean_array($_POST);
            $json_data = json_encode($data);
            $sql = "INSERT INTO `settings`(`variable`, `value`) VALUES ('payment_methods','$json_data')";
            $db->sql($sql);
            $message = "<div class='alert alert-success'> Settings created successfully!</div>";
        } else {
            $data = $fn->xss_clean_array($_POST);
            $json_data = json_encode($data);
            $sql = "UPDATE `settings` SET `value`='$json_data' WHERE `variable`='payment_methods'";
            $db->sql($sql);
            $message = "<div class='alert alert-success'> Settings updated successfully!</div>";
        }
        // echo $sql;
        $db->disconnect();
    }

    ?>
    <section class="content-header">

        <h2>Payment Gateways & Methods Settings</h2>
        <h4><?= $message ?></h4>
        <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        </ol>
        <hr />
    </section>
    <?php if ($permissions['settings']['read'] == 1) { ?>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Payment Methods Settings</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <div class="box-body">
                            <div class="col-md-4">
                                <form method="post" enctype="multipart/form-data">
                                    <h5>Paypal Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="paypal_payment_method">Paypal Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="paypal_payment_method_btn" class="js-switch" <?php if (!empty($data['paypal_payment_method']) && $data['paypal_payment_method'] == '1') {
                                                                                                                    echo 'checked';
                                                                                                                } ?>>
                                        <input type="hidden" id="paypal_payment_method" name="paypal_payment_method" value="<?= (isset($data['paypal_payment_method']) && !empty($data['paypal_payment_method'])) ? $data['paypal_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="paypal_mode" class="form-control" required>
                                            <option value="">Select Mode </option>
                                            <option value="sandbox" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'sandbox') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                            <option value="production" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'production') ? "selected" : "" ?>>Production ( Live )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal_business_email">Paypal Business Email</label>
                                        <input type="text" class="form-control" name="paypal_business_email" value="<?= (isset($data['paypal_business_email'])) ? $data['paypal_business_email'] : '' ?>" placeholder="Paypal Business Email" />
                                    </div>
                                    <hr>
                                    <h5>PayUMoney Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="payumoney_payment_method">PayUMoney Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="payumoney_payment_method_btn" class="js-switch" <?php if (!empty($data['payumoney_payment_method']) && $data['payumoney_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="payumoney_payment_method" name="payumoney_payment_method" value="<?= (isset($data['payumoney_payment_method']) && !empty($data['payumoney_payment_method'])) ? $data['payumoney_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="paypal_mode" class="form-control" required>
                                            <option value="">Select Mode </option>
                                            <option value="sandbox" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'sandbox') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                            <option value="production" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'production') ? "selected" : "" ?>>Production ( Live )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_merchant_key">Merchant key</label>
                                        <input type="text" class="form-control" name="payumoney_merchant_key" value="<?= (isset($data['payumoney_merchant_key'])) ? $data['payumoney_merchant_key'] : '' ?>" placeholder="PayUMoney Merchant Key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_merchant_id">Merchant ID</label>
                                        <input type="text" class="form-control" name="payumoney_merchant_id" value="<?= (isset($data['payumoney_merchant_id'])) ? $data['payumoney_merchant_id'] : '' ?>" placeholder="PayUMoney Merchant ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_salt">Salt</label>
                                        <input type="text" class="form-control" name="payumoney_salt" value="<?= (isset($data['payumoney_salt'])) ? $data['payumoney_salt'] : '' ?>" placeholder="PayUMoney Merchant ID" />
                                    </div>
                                    <hr>
                                    <h5>Razorpay Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="razorpay_payment_method">Razorpay Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="razorpay_payment_method_btn" class="js-switch" <?php if (!empty($data['razorpay_payment_method']) && $data['razorpay_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="razorpay_payment_method" name="razorpay_payment_method" value="<?= (isset($data['razorpay_payment_method']) && !empty($data['razorpay_payment_method'])) ? $data['razorpay_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="razorpay_key">Razorpay key ID</label>
                                        <input type="text" class="form-control" name="razorpay_key" value="<?= (isset($data['razorpay_key'])) ? $data['razorpay_key'] : '' ?>" placeholder="Razor Key ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="razorpay_secret_key">Secret Key</label>
                                        <input type="text" class="form-control" name="razorpay_secret_key" value="<?= (isset($data['razorpay_secret_key'])) ? $data['razorpay_secret_key'] : '' ?>" placeholder="Razorpay Secret Key " />
                                    </div>

                                    <input type="submit" class="btn-primary btn" value="Update" name="btn_update" />
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </section>
    <?php } else { ?>
        <div class="alert alert-danger">You have no permission to view settings</div>
    <?php } ?>
    <div class="separator"> </div>
</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>
<script type="text/javascript" src="css/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    CKEDITOR.replace('contact_us');
</script>
<script type="text/javascript">
    var changeCheckbox1 = document.querySelector('#paypal_payment_method_btn');
    var changeCheckbox2 = document.querySelector('#payumoney_payment_method_btn');
    var changeCheckbox3 = document.querySelector('#razorpay_payment_method_btn');
    var init1 = new Switchery(changeCheckbox1);
    var init2 = new Switchery(changeCheckbox2);
    var init3 = new Switchery(changeCheckbox3);
    /* paypal change button value */
    changeCheckbox1.onchange = function() {
        // alert(changeCheckbox1.checked);
        if (changeCheckbox1.checked)
            $('#paypal_payment_method').val(1);
        else
            $('#paypal_payment_method').val(0);
    };

    /* payumoney change button value */
    changeCheckbox2.onchange = function() {
        if (changeCheckbox2.checked)
            $('#payumoney_payment_method').val(1);
        else
            $('#payumoney_payment_method').val(0);
    };

    /* razorpay change button value */
    changeCheckbox3.onchange = function() {
        if (changeCheckbox3.checked)
            $('#razorpay_payment_method').val(1);
        else
            $('#razorpay_payment_method').val(0);
    };
</script>