<?php
//    include_once './config/app-config.php';
    include_once 'classes/Mail.class.php';
    
    $host=DB_HOST;
    $user=DB_USER;
    $password=DB_PASSWORD;
    $db=DB_NAME;
    
    $dbs=mysqli_connect($host,$user, $password, $db);
    
    if (mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      exit();
    } 

    $input = @file_get_contents("php://input");
    $event = json_decode($input);

    $status = $event->data->status;
    $check = $event->event;

     $email = $event->data->customer->email;
     $first_name = $event->data->customer->first_name;
     $last_name = $event->data->customer->last_name;
     $amount = $event->data->amount /100;
     $customer_code = $event->data->customer->customer_code;
     $auth_code = $event->data->authorization->authorization_code;
     
     if($check == "subscription.create"){
         $subscription_code = $event->data->subscription_code;
         $next_pay_due_date = $event->data->next_payment_date;
         $plan_code = $event->data->plan->plan_code;
         $createdAt = $event->data->createdAt;
         $plan_type = $event->data->plan->interval;

         $sql = "INSERT INTO tbl_subscriptions SET subscription_code='$subscription_code',plan_code='$plan_code',auth_code='$auth_code',customer_code='$customer_code',
                    next_pay_due_date='$next_pay_due_date',subscription_status='$status',sub_created_on='$createdAt' ";
         $res = $dbs->query($sql);
         if ($res){
             $paid_on = date('F Y', strtotime($createdAt));
             $next_date = date('F Y', strtotime($next_pay_due_date));
             $due_date = date('F jS, Y', strtotime($next_pay_due_date));
             $mail->subscriptionReceiptMail($first_name,$last_name,$email,$plan_type,$paid_on,$next_date,$due_date);
         }

     } else if($check == "charge.success"){
        $store = $event->data->metadata->store_id;
        $pay_on = $event->data->paid_at;
        $channel = $event->data->channel;
        $reference = $event->data->reference;

        $receipt_no = rand(1000000,9999999);
        $date_name = date("d/m/Y", strtotime($pay_on));
        $file_name = "ProddlyReceipt_".$receipt_no."_".$date_name.".pdf";
        $sql = "INSERT INTO tbl_payments SET pay_ref_id ='$reference',store_id='$store',store_email='$email',pay_amount='$amount',customer_code='$customer_code',
                        auth_code='$auth_code',receipt_no='$receipt_no',pay_receipt='$file_name',pay_on='$pay_on' ";
         $res = $dbs->query($sql);
         if($res){
             $str = $mail->get_store_details($store);
             $fixed_date = date('F d, Y', strtotime($pay_on));
            
             $mail->paymentReceiptMail($str['store_name'],$first_name,$last_name,$email,$amount,$receipt_no,$reference,$channel,$status,$fixed_date);
             $mail->generatePaymentReceipt($str['store_name'],$first_name,$last_name,$email,$amount,$receipt_no,$reference,$channel,$status,$fixed_date,$file_name);
         }
     } else if($check == "subscription.disable"){
         $sub_amt = number_format($event->data->plan->amount,2);
         $currency = $event->data->plan->currency;
         $due_date = date('F jS, Y', strtotime($event->data->next_payment_date));
         $mail->subscriptionCompletedMail($first_name,$last_name,$email,$sub_amt." ".$currency);
     } else if($check == "subscription.not_renew"){
         $mail->subscriptionDisabledMail($first_name,$last_name,$email);
     } else if($check == "invoice.create"){
         $subscription_code = $event->data->subscription->subscription_code;
         $sub_amt = $event->data->subscription->amount;
         $currency = $event->data->transaction->currency;
         $due_date = date('F jS, Y', strtotime($event->data->subscription->next_payment_date));
         $mail->invoiceCreatedMail($first_name,$last_name,$email,$subscription_code,$sub_amt." ".$currency,$due_date);
     }  else if($check == "subscription.expiring_cards"){
         $subscription_code = $event->data->subscription->subscription_code;
         $mail->cardExpiryMail($first_name,$last_name,$email,$subscription_code);
     }  else if($check == "invoice.payment_failed"){
         $subscription_code = $event->data->subscription->subscription_code;
         $amt = $event->data->subscription->amount;
         $mail->invoicePaymentFailedMail($first_name,$last_name,$email,$subscription_code,$amt);
     }

     http_response_code(200);
    
?>