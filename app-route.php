<?php
ini_set('max_execution_time', 999999);
ini_set('memory_limit','999999M');
ini_set('upload_max_filesize', '500M');
ini_set('max_input_time', '-1');
ini_set('max_execution_time', '-1');

  header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization,Developer_Key");

require ABSPATH.'/classes/vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Mailjet\Resources;
use \PhpOffice\PhpSpreadsheet\Reader\IReader;
date_default_timezone_set('Africa/Lagos'); // WAT

function app_db () {
    include_once ABSPATH.'/config/app-config.php';

    $db_conn = array(
        'host' => DB_HOST, 
        'user' => DB_USER,
        'pass' => DB_PASSWORD,
        'database' => DB_NAME, 
    );
    $db = new SimpleDBClass($db_conn);
    return $db;     
}

function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

function getDevAccessKeyHeader(){
    $headers = null;
    if (isset($_SERVER['DeveloDeveloper_Keyper_key'])) {
        $headers = trim($_SERVER["Developer_Key"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Developer_Key'])) {
            $headers = trim($requestHeaders['Developer_Key']);
        }
    }
    return $headers;
}

function getBearerToken() {
    $headers = getAuthorizationHeader();
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function getDeveloperKey() {
    $headers = getDevAccessKeyHeader();
    if (!empty($headers)) {
         return $headers;
    }
    return null;
}

function clean($string) {
    $string = str_replace(' ', '_', $string);

    return preg_replace('/[^A-Za-z0-9._\-]/', '', $string);
}

function save_base64_image($base64_image_string, $output_file_without_extension, $path_with_end_slash="public/uploads/store_products/" ) {
    $splited = explode(',', substr( $base64_image_string , 5 ) , 2);
    $mime=$splited[0];
    $data=$splited[1];

    $mime_split_without_base64=explode(';', $mime,2);
    $mime_split=explode('/', $mime_split_without_base64[0],2);
    if(count($mime_split)==2) {
        $extension=$mime_split[1];
        if($extension=='jpeg')$extension='jpg';
        $output_file_with_extension=$output_file_without_extension.'.'.$extension;
    }
    file_put_contents( $path_with_end_slash . $output_file_with_extension, base64_decode($data) );
    return $output_file_with_extension;
}

$router->map( 'GET', '/', function() {
	$ajax_url = AJAX_URL;
	include  ABSPATH.'/views/index.php';
});

$router->map( 'GET', '/v1/api/test', function() {
	$db = app_db();
	$developer_key = getDeveloperKey();
	// $q0 = $db->select("select * from t1 where email='$email' ");

	// if($q0 > 0) {
        http_response_code(200);
		echo json_encode(array('status'=>'success', 'msg' => 'Endpoint working fine => '.$developer_key));
	// } else {
	// 	echo json_encode(array('status'=>'error', 'msg' => 'no records found', 'emails'=> $q0,));
	// }
});

$router->map( 'GET', '/v1/api/categories', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $q0 = $db->select("select * from tbl_categories");
        if($q0 > 0) {
            http_response_code(200);
            echo json_encode(array('status'=>'success','data'=>$q0,'msg' => 'found records'));
        } else {
            http_response_code(400);
            echo json_encode(array('status'=>'error', 'msg' => 'no records found',));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/sub-categories/[*:action]', function($cat_id) {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    $cat_id =  $db->CleanDBData($cat_id);
    $store_id =  $db->CleanDBData($data->store_id);

    if (!empty($dev_key) && ($dev_key_res)) {
        $q0 = $db->select("select * from tbl_subcategories where cat_list like '%$cat_id%' and store_id='All' and store_id='".$store_id."' ");
        if($q0 > 0) {
            http_response_code(200);
            echo json_encode(array('status'=>'success','data'=>$q0,'msg' => 'found records'));
        } else {
            http_response_code(400);
            echo json_encode(array('status'=>'error', 'msg' => 'no records found',));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-proddly-categories/[*:action]', function($cat_id) {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    $cat_id =  $db->CleanDBData($cat_id);

    if (!empty($dev_key) && ($dev_key_res)) {
        $q0 = $db->select("select * from tbl_subcategories where cat_list like '%$cat_id%' and store_id='All' ");
        if($q0 > 0) {
            http_response_code(200);
            echo json_encode(array('status'=>'success','data'=>$q0,'msg' => 'found records'));
        } else {
            http_response_code(200);
            echo json_encode(array('status'=>'error', 'msg' => 'no records found',));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/payment-mail', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $email = $db->CleanDBData($data->store_email);
        $fullname = $db->CleanDBData($data->store_fullname);
        $str_name = $db->CleanDBData($data->store_name);

        $ch0 = $db->select("select * from tbl_store_account where str_email='".$email."'");
        $check_email = $db->select("select * from tbl_store_account where str_email='".$email."' ");
    
        if ($check_email <= 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'account email '.$email.' not found',));
        } else {
            $iss = 'localhost';
            $iat = time();
            $nbf = $iat; // issued after 1 secs of been created
            $exp = $iat + (86400 * 1); // expired after 1hr of been created
            $aud = "store_owner"; //the type of audience e.g. admin or client

            $secret_key = getenv('HTTP_MY_SECRET');
            $payload = array(
                "iss"=>$iss,"iat"=>$iat,"nbf"=>$nbf,"exp"=>$exp,"aud"=>$aud,
                "store_id"=>$ch0[0]['store_id'],
                "str_country"=>$ch0[0]['str_country'],
                "str_fullname"=>$ch0[0]['str_fullname'],
                "str_email"=>$ch0[0]['str_email'],
                "store_name"=>$ch0[0]['store_name'],
                "str_cat_id"=>$ch0[0]['str_cat_id'],
                "str_category"=>$ch0[0]['str_category'],
                "str_created_on"=>$ch0[0]['str_created_on'],
            );
            $jwt = JWT::encode($payload, $secret_key, 'HS512');

            $mj = new \Mailjet\Client('a3369e2ddc659c7c9ab4f66e9a3cc1bf', 'd8bd68cf2dc82ef75fe422b563c58325', true, ['version' => 'v3.1']);
            $body = ['Messages' => [[
            'From' => ['Email' => "tools@proddly.com", 'Name' => "Proddly"],
            'To' => [
                [
                    'Email' => $email,
                    'Name' => $fullname
                ]
            ],
            'Subject' => "Verify your Proddly email! - ".$str_name,
            'HTMLPart' => '
                <!doctype html>
                <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><head><title>Verify your Proddly email!</title><!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]--><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style type="text/css">#outlook a { padding:0; }
                      body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
                      table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
                      img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
                      p { display:block;margin:13px 0; }</style><!--[if mso]>
                    <noscript>
                    <xml>
                    <o:OfficeDocumentSettings>
                      <o:AllowPNG/>
                      <o:PixelsPerInch>96</o:PixelsPerInch>
                    </o:OfficeDocumentSettings>
                    </xml>
                    </noscript>
                    <![endif]--><!--[if lte mso 11]>
                    <style type="text/css">
                      .mj-outlook-group-fix { width:100% !important; }
                    </style>
                    <![endif]--><style type="text/css">@media only screen and (min-width:480px) {
                    .mj-column-per-67 { width:67% !important; max-width: 67%; }
            .mj-column-per-33 { width:33% !important; max-width: 33%; }
            .mj-column-per-100 { width:100% !important; max-width: 100%; }
                  }</style><style media="screen and (min-width:480px)">.moz-text-html .mj-column-per-67 { width:67% !important; max-width: 67%; }
            .moz-text-html .mj-column-per-33 { width:33% !important; max-width: 33%; }
            .moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">[owa] .mj-column-per-67 { width:67% !important; max-width: 67%; }
            [owa] .mj-column-per-33 { width:33% !important; max-width: 33%; }
            [owa] .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">@media only screen and (max-width:480px) {
                  table.mj-full-width-mobile { width: 100% !important; }
                  td.mj-full-width-mobile { width: auto !important; }
                }</style></head><body style="word-spacing:normal;background-color:#F4F4F4;"><div style="background-color:#F4F4F4;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:402px;" ><![endif]--><div class="mj-column-per-67 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:0px 0px 0px 25px;padding-top:0px;padding-right:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"></div></td></tr></tbody></table></div><!--[if mso | IE]></td><td class="" style="vertical-align:top;width:198px;" ><![endif]--><div class="mj-column-per-33 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 0px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" style="text-align: right; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;" data-testid="c7N2IT-E6038"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;"><u>store.proddly.com</u></span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:0px 0px 0px 0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;"><tbody><tr><td style="width:600px;"><img alt="" height="auto" src="https://0m0ng.mjt.lu/tplimg/0m0ng/b/1kgvx/00h7.png" style="border:none;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="600"></td></tr></tbody></table></td></tr><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:18px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><h1 class="text-build-content" data-testid="8vJ3U63KHGEy" style="margin-top: 10px; font-weight: normal;">&nbsp;</h1><h1 class="text-build-content" data-testid="8vJ3U63KHGEy" style="margin-bottom: 10px; font-weight: normal;"><span style="font-family:Arial, sans-serif;font-size:18px;">Hi '.$str_name.'</span></h1></div></td></tr><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-top: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">Thank you for signing up for your store on Proddly!</span></p><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-bottom: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">To get started using Proddly, we needed to verify your email address. Please click the button below to complete your signup.</span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:0px;padding-top:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px 10px 25px;padding-right:25px;padding-left:25px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;"><tbody><tr><td align="center" bgcolor="#00b0ff" role="presentation" style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px 10px 25px;background:#00b0ff;" valign="middle"><a href="https://proddly.com/confirm-payment/'.$jwt.'" style="display:inline-block;background:#00b0ff;color:#ffffff;font-family:Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px 10px 25px;mso-padding-alt:0px;border-radius:3px;" target="_blank"><span style="font-size:14px;">Complete Signup</span></a></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="iMnv3GwpxrUOb" style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">If you have any trouble clicking the button above, please copy and place the URL below in your web browser.&nbsp;</span><br><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">https://proddly.com/payment/'.$jwt.'</span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#edf6f8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#edf6f8;background-color:#edf6f8;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#edf6f8;background-color:#edf6f8;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;padding-left:0px;padding-right:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td style="vertical-align:top;padding:0;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:10px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">This e-mail has been sent to '.$email.'</span></p><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-bottom: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">Got any questions? We are always happy to help. write to us at </span><span style="color:#00B0FF;font-size:12px;">support@proddly.com</span></p></div></td></tr><tr><td align="center" style="background:transparent;font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://www.facebook.com/sharer/sharer.php?u=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/facebook.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://twitter.com/intent/tweet?url=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/twitter.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/instagram.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/linkedin.png" style="border-radius:50%;display:block;" width="17"></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td></tr></table><![endif]--></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:11px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;" data-testid="JsDQq14HuN0gD">© Proddly LLC</p></div></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></div></body>
                </html>    
            ',
             ]]];
            $response = $mj->post(Resources::$Email, ['body' => $body]);
            if ($response->success()) {
                http_response_code(200);
                echo json_encode(array('status'=>'success', 'msg' => 'Confirmation mail sent'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'Unable to send confirmation mail.'));
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/forgot-password', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {$ch0 = $db->select("select * from tbl_store_account where str_email='".$db->CleanDBData($data->store_email)."'");
        if ($ch0 <= 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'Store email not found',));
        } else {
            $email = $data->store_email;
            $first_name = $ch0[0]['str_fullname'];

            $selector = bin2hex(random_bytes(4));
            $token = random_bytes(15);

            $host = "https://$_SERVER[HTTP_HOST]";
            $url= $host."/reset-password/".$selector."/".bin2hex($token);
            $expires = date("U") + 1200;

            //Delete any existing user token entry
            $db->Qry("DELETE FROM tbl_pwd_reset WHERE reset_email='$email'");
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            $insert_arrays = array
            (
                'reset_email' => $db->CleanDBData($email),
                'reset_selector' => $db->CleanDBData($selector),
                'reset_token' => $db->CleanDBData($hashedToken),
                'reset_expires' => $db->CleanDBData($expires)
            );
            $q0 = $db->Insert('tbl_pwd_reset', $insert_arrays);
            if ($q0 > 0) {
                $mj = new \Mailjet\Client('a3369e2ddc659c7c9ab4f66e9a3cc1bf', 'd8bd68cf2dc82ef75fe422b563c58325', true, ['version' => 'v3.1']);
                $body = ['Messages' => [[
                    'From' => ['Email' => "tools@proddly.com", 'Name' => "Proddly"],
                    'To' => [
                        [
                            'Email' => $email,
                        ]
                    ],
                    'Subject' => "Retrieve your Proddly Password",
                    'HTMLPart' => '
                    <!doctype html><html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><head><title>Retrieve your Proddly Password</title><!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]--><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style type="text/css">#outlook a { padding:0; }
                          body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
                          table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
                          img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
                          p { display:block;margin:13px 0; }</style><!--[if mso]>
                        <noscript>
                        <xml>
                        <o:OfficeDocumentSettings>
                          <o:AllowPNG/>
                          <o:PixelsPerInch>96</o:PixelsPerInch>
                        </o:OfficeDocumentSettings>
                        </xml>
                        </noscript>
                        <![endif]--><!--[if lte mso 11]>
                        <style type="text/css">
                          .mj-outlook-group-fix { width:100% !important; }
                        </style>
                        <![endif]--><style type="text/css">@media only screen and (min-width:480px) {
                        .mj-column-per-67 { width:67% !important; max-width: 67%; }
                .mj-column-per-33 { width:33% !important; max-width: 33%; }
                .mj-column-per-100 { width:100% !important; max-width: 100%; }
                      }</style><style media="screen and (min-width:480px)">.moz-text-html .mj-column-per-67 { width:67% !important; max-width: 67%; }
                .moz-text-html .mj-column-per-33 { width:33% !important; max-width: 33%; }
                .moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">[owa] .mj-column-per-67 { width:67% !important; max-width: 67%; }
                [owa] .mj-column-per-33 { width:33% !important; max-width: 33%; }
                [owa] .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">@media only screen and (max-width:480px) {
                      table.mj-full-width-mobile { width: 100% !important; }
                      td.mj-full-width-mobile { width: auto !important; }
                    }</style></head><body style="word-spacing:normal;background-color:#F4F4F4;"><div style="background-color:#F4F4F4;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:402px;" ><![endif]--><div class="mj-column-per-67 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:0px 0px 0px 25px;padding-top:0px;padding-right:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"></div></td></tr></tbody></table></div><!--[if mso | IE]></td><td class="" style="vertical-align:top;width:198px;" ><![endif]--><div class="mj-column-per-33 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 0px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" style="text-align: right; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;" data-testid="c7N2IT-E6038"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;"><u>store.proddly.com</u></span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:0px 0px 0px 0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;"><tbody><tr><td style="width:600px;"><img alt="" height="auto" src="https://0m0ng.mjt.lu/tplimg/0m0ng/b/1kgvx/00h7.png" style="border:none;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="600"></td></tr></tbody></table></td></tr><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:18px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><h1 class="text-build-content" data-testid="8vJ3U63KHGEy" style="margin-top: 10px; font-weight: normal;">&nbsp;</h1><h1 class="text-build-content" data-testid="8vJ3U63KHGEy" style="margin-bottom: 10px; font-weight: normal;"><span style="font-family:Arial, sans-serif;font-size:18px;">Hi '.$first_name.'</span></h1></div></td></tr><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-top: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">This e-mail has been sent to you because you could not remember the password for your Proddly account. <b>No worries. We\'ve got you!</b></span></p><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">Please click the button below to reset your password.</span></p><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-bottom: 10px;">&nbsp;</p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:0px;padding-top:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px 10px 25px;padding-right:25px;padding-left:25px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;"><tbody><tr><td align="center" bgcolor="#00b0ff" role="presentation" style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px 10px 25px;background:#00b0ff;" valign="middle"><a href="'.$url.'" style="display:inline-block;background:#00b0ff;color:#ffffff;font-family:Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px 10px 25px;mso-padding-alt:0px;border-radius:3px;" target="_blank"><span style="font-size:14px;">Reset Password</span></a></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="iMnv3GwpxrUOb" style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">If you have any trouble clicking the button above, please copy and place the URL below in your web browser.&nbsp;</span><br><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">'.$url.'</span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#edf6f8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#edf6f8;background-color:#edf6f8;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#edf6f8;background-color:#edf6f8;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;padding-left:0px;padding-right:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td style="vertical-align:top;padding:0;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:10px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">This e-mail has been sent to '.$email.'</span></p><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-bottom: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">Got any questions? We are always happy to help. write to us at </span><span style="color:#00B0FF;font-size:12px;">support@proddly.com</span></p></div></td></tr><tr><td align="center" style="background:transparent;font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://www.facebook.com/sharer/sharer.php?u=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/facebook.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://twitter.com/intent/tweet?url=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/twitter.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/instagram.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/linkedin.png" style="border-radius:50%;display:block;" width="17"></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td></tr></table><![endif]--></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:11px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;" data-testid="JsDQq14HuN0gD">© Proddly LLC</p></div></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></div></body>
                    </html>
                    ',
                ]]];
                $response = $mj->post(Resources::$Email, ['body' => $body]);
                if ($response->success()) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Reset email sent'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to send reset mail.'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Unable to send reset mail.'));
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/reset-password', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $currentDate = date("U");
        if (isset($data->reset_selector) && !empty($data->reset_selector) && !empty($data->str_password)) {
            $reset_selector = $data->reset_selector;
            $p_reset_q = $db->select("SELECT * FROM tbl_pwd_reset WHERE reset_selector='$reset_selector' AND reset_expires >= $currentDate");
            if ($p_reset_q > 0 ) {
                $reset_email = $p_reset_q[0]['reset_email'];
                $update_fields = array('str_password' => password_hash($data->str_password, PASSWORD_DEFAULT));
                $array_where = array('str_email' => $db->CleanDBData($reset_email));
                $update_query = $db->Update('tbl_store_account', $update_fields, $array_where);
                if ($update_query > 0) {
                    $db->Qry("DELETE FROM tbl_pwd_reset WHERE reset_email='$reset_email'");
                    http_response_code(200);
                    echo json_encode(array('status' => 'success','msg'=>'Password successfully changed. Proceed to login'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error','msg'=>'Error while trying to reset your password, contact our admin for help.'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Invalid reset token and/or expired reset link'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'Kindly provide the reset selector & new password key to update password'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/create-store', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $insert_arrays = array
        (
            'store_id' => $db->CleanDBData(rand(1000000,9999999)),
            'str_country' => $db->CleanDBData($data->store_country),
            'str_fullname' => $db->CleanDBData($data->store_fullname),
            'str_email' => $db->CleanDBData($data->store_email),
            'store_name' => $db->CleanDBData(ucwords($data->store_name)),
            'str_cat_id' => $db->CleanDBData($data->store_cat_id),
            'str_category' => $db->CleanDBData($data->store_category),
            'str_password' => $db->CleanDBData(password_hash($data->store_password, PASSWORD_DEFAULT)),
            'str_created_on' => $db->CleanDBData(date("d-m-Y H:i:s")),
        );
        $check_email = $db->select("select * from tbl_store_account where str_email='".$db->CleanDBData($data->store_email)."' ");
        $check_str_name = $db->select("select * from tbl_store_account where store_name='".$db->CleanDBData(ucwords($data->store_name))."' ");

        if ($check_email > 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'account user '.$db->CleanDBData($data->store_email).' already exist',));
        } else {
            if ($check_str_name > 0) {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Store with '.ucwords($data->store_name).' already exist',));
            } else {
                $q0 = $db->Insert('tbl_store_account', $insert_arrays);
                if ($q0 > 0) {
                http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Store account created successfully, kindly follow the instruction in your mail to activate your account', "insert_id" => $q0));
                } else {
                http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to create account, try again later'));
                }
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/activate-store', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $check_email = $db->select("select * from tbl_store_account where str_email='".$db->CleanDBData($data->store_email)."' ");
        $ch0 = $db->select("select * from tbl_store_account where str_active='Active' and str_email='".$db->CleanDBData($data->store_email)."'");
        if ($check_email <= 0) { 
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'account email '.$db->CleanDBData($data->store_email).' not found',));
        } else {
            if ($ch0 > 0) {
             http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Account already activated,',));
            } else {
                $reference_id = $db->CleanDBData($data->reference_id);
                $store_id = $ch0[0]['store_id'];
                $pay_amount = $db->CleanDBData($data->amount);
                $pay_status = "Paid";
                $pay_on = date("Y-m-d H:i:s");
                $pay_str_time = strtotime($pay_on);
                $sub_type = $db->CleanDBData($data->subscription_type); 

                if ($sub_type == "Monthly") {
                    $next_pay_due_date = date("Y-m-d H:i:s", strtotime("+1 month", $pay_str_time));
                } else if($sub_type == "Yearly"){
                     $next_pay_due_date = date("Y-m-d H:i:s", strtotime("+1 year", $pay_str_time));
                }

                $strTableName = "tbl_store_account";
                $array_fields = array('str_active' => $db->CleanDBData("Active"),);
                $array_where = array('str_email' => $db->CleanDBData($data->store_email),);
                $q0 = $db->Update($strTableName, $array_fields, $array_where);
                if ($q0 > 0) {
                     $pay_arrays = array
                        (
                            'pay_ref_id' => $db->CleanDBData($reference_id),
                            'store_id' => $db->CleanDBData($store_id),
                            'pay_amount' => $db->CleanDBData($pay_amount),
                            'pay_status' => $db->CleanDBData($pay_status),
                            'pay_on' => $db->CleanDBData($pay_on)
                        );
                        $sub_arrays = array
                        (
                            'pay_ref_id' => $db->CleanDBData($reference_id),
                            'store_id' => $db->CleanDBData($store_id),
                            'sub_type' => $db->CleanDBData($sub_type),
                            'next_pay_due_date' => $db->CleanDBData($next_pay_due_date),
                            'sub_created_on' => $db->CleanDBData($pay_on)
                        );
                    $p0 = $db->Insert('tbl_payments', $pay_arrays);
                    $s0 = $db->Insert('tbl_subscriptions', $sub_arrays);

                     $iss = 'localhost';
                    $iat = time();
                    $nbf = $iat; // issued after 1 secs of been created
                    $exp = $iat + (86400 * 1); // expired after 1hr of been created
                    $aud = "store_owner"; //the type of audience e.g. admin or client

                    $secret_key = getenv('HTTP_MY_SECRET');
                    $payload = array(
                        "iss"=>$iss,"iat"=>$iat,"nbf"=>$nbf,"exp"=>$exp,"aud"=>$aud,
                        "store_id"=>$ch0[0]['store_id'],
                        "str_country"=>$ch0[0]['str_country'],
                        "str_fullname"=>$ch0[0]['str_fullname'],
                        "str_email"=>$ch0[0]['str_email'],
                        "store_name"=>$ch0[0]['store_name'],
                        "str_cat_id"=>$ch0[0]['str_cat_id'],
                        "str_category"=>$ch0[0]['str_category'],
                        "str_created_on"=>$ch0[0]['str_created_on'],
                    );
                    if($p0 <= 0 || $s0 <= 0){
                        http_response_code(400);
                        echo json_encode(array('status' => 'error', 'msg' => 'Unable to activate account'));
                    } else {
                        $jwt = JWT::encode($payload, $secret_key, 'HS512');
                        http_response_code(200);
                        echo json_encode(array("status" => 'success', "jwt" => $jwt, "message" => "Account activated successfully. Login you in shortly.",));
                    }
                } else {
                     http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to activate account'));
                }
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/store-login', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $ch0 = $db->select("select * from tbl_store_account where str_email='".$db->CleanDBData($data->store_email)."'");
        $ch1 = $db->select("select * from tbl_store_account where str_active='Deactivate' and str_email='".$db->CleanDBData($data->store_email)."'");
        if ($ch0 <= 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'Store email not found',));
        } else {
            if ($ch1 > 0) {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Account is inactive, check your mail to activate account or contact our support',));
            } else {
                $password_used = $ch0[0]['str_password'];
                if (password_verify($data->store_password,$password_used)) {
                    $iss = 'localhost';
                    $iat = time();
                    $nbf = $iat; // issued after 1 secs of been created
                    $exp = $iat + (86400 * 1); // expired after 1hr of been created
                    $aud = "store_owner"; //the type of audience e.g. admin or client

                    $secret_key = getenv('HTTP_MY_SECRET');
                    $payload = array(
                        "iss"=>$iss,"iat"=>$iat,"nbf"=>$nbf,"exp"=>$exp,"aud"=>$aud,
                        "store_id"=>$ch0[0]['store_id'],
                        "str_country"=>$ch0[0]['str_country'],
                        "str_fullname"=>$ch0[0]['str_fullname'],
                        "str_email"=>$ch0[0]['str_email'],
                        "store_name"=>$ch0[0]['store_name'],
                        "str_cat_id"=>$ch0[0]['str_cat_id'],
                        "str_category"=>$ch0[0]['str_category'],
                        "str_created_on"=>$ch0[0]['str_created_on'],
                    );
                    $jwt = JWT::encode($payload, $secret_key, 'HS512');
                    http_response_code(200);
                    echo json_encode(array("status" => 'success', "jwt" => $jwt, "message" => "Account logged in successfully",));
                } else {
                    http_response_code(400);
                    echo json_encode(array("status" => 'error', "message" => "Incorrect password, try resetting your password."));
                }
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/process-payment', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {

        $check_email = $db->select("select * from tbl_store_account where str_email='".$db->CleanDBData($data->store_email)."' ");
        if ($check_email > 0) {
            $store_id = $check_email[0]['store_id'];
            $insert_spay_arrays = array
            (
                'store_id' => $db->CleanDBData($store_id),
                'payment_mode' => $db->CleanDBData($data->payment_mode),
                'spay_amount' => $db->CleanDBData($data->spay_amount),
                'spay_ref' => $db->CleanDBData($data->spay_ref),
                'spay_status' => $db->CleanDBData($data->spay_status),
                'spay_date' => $db->CleanDBData(date("d-m-Y H:i:s")),
            );
            $q0 = $db->Insert('tbl_store_payment', $insert_spay_arrays);
            if ($q0 > 0) {
                if ($db->CleanDBData($data->save_card) == 'Yes'){
                    $insert_card_arrays = array
                    (
                        'store_id' => $db->CleanDBData($store_id),
                        'card_number' => $db->CleanDBData($data->card_number),
                        'card_expiry_date' => $db->CleanDBData($data->card_expiry_date),
                        'card_cvv' => $db->CleanDBData($data->card_cvv),
                        'card_name' => $db->CleanDBData($data->card_name),
                    );
                    $q1 = $db->Insert('tbl_spay_card', $insert_card_arrays);
                }
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'payment record inserted successfully', "spay_id" => $q0));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'cannot process payment, server error'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'email does not exist',));
        }
    } else {
            http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/create-store-page', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $logoFileName = "";
            $coverFileName = "";
            if ($_FILES['store_logo']['name'] != "") {
                $logoFileName = strtolower(clean($_FILES['store_logo']['name']));
                $logoTempPath = $_FILES['store_logo']['tmp_name'];
                $logoFileSize = $_FILES['store_logo']['size'];
            }
            if ($_FILES['store_cover_image']['name'] != "") {
                $coverFileName = strtolower(clean($_FILES['store_cover_image']['name']));
                $coverTempPath = $_FILES['store_cover_image']['tmp_name'];
                $coverFileSize = $_FILES['store_cover_image']['size'];
            }
            if (!empty(trim($_REQUEST['upload_product'])) && $_REQUEST['upload_product'] =="Yes") {
                $productsFileName = $_FILES['store_products']['name'];
                $productsPath = realpath($_FILES["store_products"]["tmp_name"]);
            }
            
            /* Logo image upload */
            if($logoFileName != "") {
                $logo_upload_path = 'public/uploads/store_logo/';
                $fileExt = strtolower(pathinfo($logoFileName, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');
                if (in_array($fileExt, $valid_extensions)) {
                    if (!file_exists($logo_upload_path . $logoFileName)) {
                        if ($logoFileSize < 1000000) {
                            move_uploaded_file($logoTempPath, $logo_upload_path . $logoFileName);
                        } else {
                            http_response_code(400);
                            $ErrorMSG = json_encode(array("message" => "Logo is too large, please upload 1 MB size","status" => 'error'));
                            echo $ErrorMSG;
                            exit();
                        }
                    } else {
                        http_response_code(400);
                        $ErrorMSG = json_encode(array('status' => 'error', "message" => "Sorry, file already exists check upload folder"));
                        echo $ErrorMSG;
                        exit();
                    }
                } else {
                    http_response_code(400);
                    $ErrorMSG = json_encode(array('status' => 'error',"message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed"));
                    echo $ErrorMSG;
                    exit();
                }
            }
            /* Cover image upload */
            if($coverFileName != "") {
                $cover_upload_path = 'public/uploads/cover_image/';
                $fileExt2 = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');
                if (in_array($fileExt2, $valid_extensions)) {
                    if (!file_exists($cover_upload_path . $coverFileName)) {
                        if ($coverFileSize < 1000000) {
                            move_uploaded_file($coverTempPath, $cover_upload_path . $coverFileName);
                        } else {
                            http_response_code(400);
                            $ErrorMSG = json_encode(array("message" => "Cover image is too large, please upload 1 MB size","status" => 'error'));
                            echo $ErrorMSG;
                            exit();
                        }
                    } else {
                        http_response_code(400);
                        $ErrorMSG = json_encode(array('status' => 'error', "message" => "Sorry, file already exists check upload folder"));
                        echo $ErrorMSG;
                        exit();
                    }
                } else {
                    http_response_code(400);
                    $ErrorMSG = json_encode(array('status' => 'error',"message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed"));
                    echo $ErrorMSG;
                    exit();
                }
            }

            if (!empty(trim($_REQUEST['upload_product'])) && $_REQUEST['upload_product'] =="Yes") {
                /* Store Product upload */
                if (empty($productsFileName)) {
                    http_response_code(400);
                    $ErrorMSG_XLSX = json_encode(array('status' => 'error', "message" => "please upload store product, using VWIS template"));
                    echo $ErrorMSG_XLSX;
                    exit();
                } else {
                    $fileExt3 = strtolower(pathinfo($productsFileName, PATHINFO_EXTENSION));
                    $valid_extensions = array('xlsx');
                    if (in_array($fileExt3, $valid_extensions)) {
                        class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
                        {
                            public function readCell($columnAddress, $row, $worksheetName = '')
                            {
                                if ($row >= 5) {
                                    return true;
                                }
                                return false;
                            }
                        }

                        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                        $reader->setReadFilter(new MyReadFilter());
                        $spreadsheet = $reader->load($productsPath);

                        $worksheet = $spreadsheet->getActiveSheet();
                        $worksheetArray = $worksheet->toArray();
                        $new_array = array_slice($worksheetArray, 4);

                        foreach ($new_array as $key => $value) {
                            if (trim($value[2]) != "") {
                                $worksheet = $spreadsheet->getActiveSheet();
                                $drawing = $worksheet->getDrawingCollection()[$key + 1];
                                $zipReader = fopen($drawing->getPath(), 'r');
                                $imageContents = '';
                                while (!feof($zipReader)) {
                                    $imageContents .= fread($zipReader, 1024);
                                }
                                fclose($zipReader);
                                $extension = $drawing->getExtension();
                                $newName = uniqid();
                                save_base64_image("data:image/jpeg;base64," . base64_encode($imageContents), $newName);

                                $category_arr = explode("-", $db->CleanDBData($value[11]));
                                $products_arrays = array
                                (
                                    'sps_store_id' => $db->CleanDBData($store_id),
                                    'product_id' => $db->CleanDBData(rand(1000000, 9999999)),
                                    'brand' => $db->CleanDBData($value[1]),
                                    'item_name' => $db->CleanDBData($value[2]),
                                    'item_image' => $db->CleanDBData($newName . 'jpg'),
                                    'item_spec' => $db->CleanDBData($value[4]),
                                    'item_qty' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[5]))),
                                    'unit_price' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[6]))),
                                    'bulk_ord_qty' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[7]))),
                                    'bulk_price' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[8]))),
                                    'expiry_date' => $db->CleanDBData($value[9]),
                                    'item_vat' => $db->CleanDBData($value[10]),
                                    'item_subcategory' => trim($category_arr['1']),
                                    'subcategory_id' => trim($category_arr['0']),
                                    'item_created_on' => $db->CleanDBData(date("d-m-Y H:i:s")),
                                );
                                $q0 = $db->Insert('tbl_store_products', $products_arrays);
                            }
                        }
                    } else {
                        http_response_code(400);
                        $ErrorMSG_XLSX = json_encode(array('status' => 'error', "message" => "Sorry, only XLSX files are allowed (for excel upload)"));
                        echo $ErrorMSG_XLSX;
                        exit();
                    }
                }
            }

            $store_info_arrays = array
            (
                'si_store_id' => $db->CleanDBData($store_id),
                'store_logo' => $db->CleanDBData($logoFileName),
                'store_cover_image' => $db->CleanDBData($coverFileName),
                'store_tagline' => $db->CleanDBData($_REQUEST['store_tagline']),
                'store_state' => $db->CleanDBData($_REQUEST['store_state']),
                'store_areas' => $db->CleanDBData($_REQUEST['store_areas']),
                'store_address' => $db->CleanDBData($_REQUEST['store_address']),
                'str_dated_on' => $db->CleanDBData(date("d-m-Y H:i:s")),
            );
            $store_operational_info_arrays = array
            (
                'soi_store_id' => $db->CleanDBData($store_id),
                'store_op_days_from' => $db->CleanDBData($_REQUEST['operation_days_from']),
                'store_op_days_to' => $db->CleanDBData($_REQUEST['operation_days_to']),
                'sun_open_time' => $db->CleanDBData($_REQUEST['sun_open_time']),
                'sun_close_time' => $db->CleanDBData($_REQUEST['sun_close_time']),
                'sun_enable' => $db->CleanDBData($_REQUEST['sun_enable']),
                'mon_open_time' => $db->CleanDBData($_REQUEST['mon_open_time']),
                'mon_close_time' => $db->CleanDBData($_REQUEST['mon_close_time']),
                'mon_enable' => $db->CleanDBData($_REQUEST['mon_enable']),
                'tue_open_time' => $db->CleanDBData($_REQUEST['tue_open_time']),
                'tue_close_time' => $db->CleanDBData($_REQUEST['tue_close_time']),
                'tue_enable' => $db->CleanDBData($_REQUEST['tue_enable']),
                'wed_open_time' => $db->CleanDBData($_REQUEST['wed_open_time']),
                'wed_close_time' => $db->CleanDBData($_REQUEST['wed_close_time']),
                'wed_enable' => $db->CleanDBData($_REQUEST['wed_enable']),
                'thu_open_time' => $db->CleanDBData($_REQUEST['thu_open_time']),
                'thu_close_time' => $db->CleanDBData($_REQUEST['thu_close_time']),
                'thu_enable' => $db->CleanDBData($_REQUEST['thu_enable']),
                'fri_open_time' => $db->CleanDBData($_REQUEST['fri_open_time']),
                'fri_close_time' => $db->CleanDBData($_REQUEST['fri_close_time']),
                'fri_enable' => $db->CleanDBData($_REQUEST['fri_enable']),
                'sat_open_time' => $db->CleanDBData($_REQUEST['sat_open_time']),
                'sat_close_time' => $db->CleanDBData($_REQUEST['sat_close_time']),
                'sat_enable' => $db->CleanDBData($_REQUEST['sat_enable']),
                'sell_where' => $db->CleanDBData($_REQUEST['where_do_you_sell']),
                'pickup_service' => $db->CleanDBData($_REQUEST['pickup_service']),
                'str_home_delivery' => $db->CleanDBData($_REQUEST['str_home_delivery']),
                'delivery_timeline' => $db->CleanDBData($_REQUEST['delivery_timeline']),
                'google_biz_link' => $db->CleanDBData($_REQUEST['google_business_link'])
            );
            $store_business_arrays = array
            (
                'sbi_store_id' => $db->CleanDBData($store_id),
                'str_biz_phone' => $db->CleanDBData($_REQUEST['str_biz_phone']),
                'str_biz_name' => $db->CleanDBData($_REQUEST['str_biz_name']),
                'str_biz_reg_no' => $db->CleanDBData($_REQUEST['str_biz_reg_no']),
                'str_bank_name' => $db->CleanDBData($_REQUEST['str_bank_name']),
                'str_acct_no' => $db->CleanDBData($_REQUEST['str_acct_no']),
                'cash_option' => $db->CleanDBData($_REQUEST['cash_option']),
                'card_option' => $db->CleanDBData($_REQUEST['card_option']),
                'transfer_option' => $db->CleanDBData($_REQUEST['transfer_option']),
                'cheque_option' => $db->CleanDBData($_REQUEST['cheque_option'])
            );

            $q0 = $db->Insert('tbl_store_info', $store_info_arrays);
            $q1 = $db->Insert('tbl_store_op_info', $store_operational_info_arrays);
            $q3 = $db->Insert('tbl_store_biz_info', $store_business_arrays);

            if ($_REQUEST['str_home_delivery']=='Yes') {
                $store_rates = count($_REQUEST["str_home_lga"]);
                $error = 0;
                for ($i = 0; $i < $store_rates; $i++) {
                    if ($_REQUEST["str_home_lga"][$i] == '' || $_REQUEST["str_home_fee"][$i] == '') {
                        $error = $error + 1;
                    } else {
                        $store_rates_arrays = array
                        (
                            'sri_store_id' => $db->CleanDBData($store_id),
                            'str_home_lga' => $db->CleanDBData($_REQUEST['str_home_lga'][$i]),
                            'str_home_fee' => $db->CleanDBData($_REQUEST['str_home_fee'][$i])
                        );
                        if ($error == 0) {
                            $q2 = $db->Insert('tbl_store_rate_info', $store_rates_arrays);
                        }
                    }
                }
            }
            if (!isset($ErrorMSG) && $q0 > 0 && $q1 > 0 && $q3 > 0) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', "message" => "Proddly page successfully created", "store_id" => $store_id));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/create-subcategory', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;
            $str_cat_id = $decoded_data->str_cat_id;

            $insert_arrays = array
            (
                'cat_list' => $db->CleanDBData($str_cat_id),
                'subcat_id' => $db->CleanDBData(rand(1000,9999)),
                'subcat_name' => $db->CleanDBData($data->subcat_name),
                'store_id' => $db->CleanDBData($store_id)
            );
            $q0 = $db->Insert('tbl_subcategories', $insert_arrays);
            if ($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'record inserted', "insert_id" => $q0));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'cannot insert record'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-products', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $q0 = $db->select("select * from tbl_store_products where store_id='".$store_id."' ");
            $q0Count = $db->select("select count(*) as total from tbl_store_products where store_id='".$store_id."' ");
            if($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','data'=>$q0,'total_count'=>$q0Count[0],'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error', 'msg' => 'no product found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-store-info', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $q0 = $db->select("select si.*,bi.* from tbl_store_info si inner join tbl_store_biz_info bi 
                                on si.si_store_id=bi.sbi_store_id inner join tbl_store_account sa on si.si_store_id=sa.store_id
                                where si.si_store_id='".$store_id."' ");

            $q1 = $db->select("select * from tbl_store_op_info where soi_store_id='".$store_id."' ");
            if($q0 > 0 && $q1 > 0) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','store_info_data'=>$q0,'operational_info'=>$q1,'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error', 'msg' => 'no record found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-store-categories', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;
            $str_cat_id = $decoded_data->str_cat_id;

            $q0 = $db->select("select * from tbl_subcategories where cat_list like '%$str_cat_id%' and (store_id='All' or store_id='".$store_id."') ");

            if($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','store_categories'=>$q0,'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error', 'msg' => 'no record found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-filter-products', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;
            $str_cat_id = $decoded_data->str_cat_id;

            $whereSQL = '';
            if(!empty($_GET['search'])){
                $whereSQL .= " and (item_name like '%".$_GET['search']."%' or brand LIKE '%".$_GET['search']."%' or item_spec LIKE '%".$_GET['search']."%')";
            }
            if(!empty($_GET['category'])){
                $whereSQL .= " and (subcategory_id = '".$_GET['category']."')";
            }
            if(!empty($_GET['stock_status'])){
                $whereSQL .= " and (item_status = '".$_GET['stock_status']."')";
            }

            $q0 = $db->select("select * from tbl_store_products where (store_id='All' or store_id='".$store_id."') $whereSQL");
            $q0Count = $db->select("select count(*) as total from tbl_store_products where (store_id='All' or store_id='".$store_id."') $whereSQL");

            if($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','filter_products'=>$q0,'total_count'=>$q0Count[0],'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error', 'msg' => 'no record found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'DELETE', '/v1/api/delete-selected-product', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $product_list = $db->CleanDBData($data->product_ids);
            $Qry = $db->Qry("delete from tbl_store_products where product_id in ($product_list) and store_id='".$store_id."' ");

            if($Qry == 1) {
            http_response_code(200);
                echo json_encode(array('status'=>'success','msg' => 'products deleted'));
            } else {
            http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to delete products',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'DELETE', '/v1/api/delete-product', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $product_id = $db->CleanDBData($data->product_id);
            $array_where = array(
                'product_id' => $product_id,
                'store_id' => $store_id
            );
            $Qry = $db->Delete('tbl_store_products',$array_where);
            if($Qry) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg' => 'product deleted successfully'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to delete product',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-featured-item', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $update_fields = array(
                'featured' => "Yes"
            );
            $array_where = array(
                'product_id' => $db->CleanDBData($data->product_id),
                'store_id' => $db->CleanDBData($store_id)
            );
            $q0 = $db->Update('tbl_store_products', $update_fields, $array_where);

            if ($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'record updated', "product_id" => $data->product_id));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'cannot update record'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/add-product', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $imgFileName  =  strtolower(clean($_FILES['item_image']['name']));
            $imgTempPath  =  $_FILES['item_image']['tmp_name'];
            $imgFileSize  =  $_FILES['item_image']['size'];

            if(empty($imgFileName)) {
                http_response_code(400);
                $ErrorMSG = json_encode(array('status' => 'error',"message" => "please select product image"));
                echo $ErrorMSG;
            } else {
                $image_upload_path = 'public/uploads/store_products/';
                $fileExt = strtolower(pathinfo($imgFileName,PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png');
                if(in_array($fileExt, $valid_extensions)) {
                    if(!file_exists($image_upload_path . $imgFileName)) {
                        if($imgFileSize < 1000000){
                            move_uploaded_file($imgTempPath, $image_upload_path . $imgFileName);

                            $products_arrays = array
                            (
                                'store_id' => $db->CleanDBData($store_id),
                                'product_id' => $db->CleanDBData(rand(1000000,9999999)),
                                'brand' => $db->CleanDBData($_REQUEST['brand']),
                                'item_name' => $db->CleanDBData($_REQUEST['item_name']),
                                'item_image' => $db->CleanDBData($imgFileName),
                                'item_spec' => $db->CleanDBData($_REQUEST['item_specification']),
                                'item_qty' => $db->CleanDBData((int)$_REQUEST['item_qty']),
                                'unit_price' => $db->CleanDBData(floatval($_REQUEST['unit_price'])),
                                'bulk_ord_qty' => $db->CleanDBData((int)$_REQUEST['bulk_order_qty']),
                                'bulk_price' => $db->CleanDBData(floatval($_REQUEST['bulk_price'])),
                                'expiry_date' => $db->CleanDBData($_REQUEST['expiry_date']),
                                'item_vat' => $db->CleanDBData($_REQUEST['item_vat']),
                                'item_subcategory' => $db->CleanDBData($_REQUEST['category']),
                                'subcategory_id' => $db->CleanDBData($_REQUEST['category_id']),
                                'featured' => $db->CleanDBData($_REQUEST['featured']),
                                'item_created_on' => $db->CleanDBData(date("d-m-Y H:i:s")),
                            );
                            $q0 = $db->Insert('tbl_store_products', $products_arrays);
                            if($q0 > 0) {
                                http_response_code(200);
                                echo json_encode(array('status'=>'success','msg' => 'product added successfully'));
                            } else {
                                http_response_code(400);
                                echo json_encode(array('status'=>'error', 'msg' => 'unable to add product'));
                            }
                        } else {
                            http_response_code(400);
                            $ErrorMSG = json_encode(array("message" => "Image is too large, max size is 1MB","status" => 'error'));
                            echo $ErrorMSG;
                        }
                    } else {
                        http_response_code(400);
                        $ErrorMSG = json_encode(array('status' => 'error',"message" => "Sorry, file already exists rename image filename"));
                        echo $ErrorMSG;
                    }
                } else {
                    http_response_code(400);
                    $ErrorMSG = json_encode(array('status' => 'error',"message" => "Sorry, only JPG, JPEG, & PNG files are allowed"));
                    echo $ErrorMSG;
                }
            }

        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }

    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/edit-product', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;
            $product_id = $db->CleanDBData($_REQUEST['product_id']);

            $imgFileName  =  strtolower(clean($_FILES['item_image']['name']));
            $imgTempPath  =  $_FILES['item_image']['tmp_name'];
            $imgFileSize  =  $_FILES['item_image']['size'];


            if(!empty($imgFileName)) {
                $image_upload_path = 'public/uploads/store_products/';
                $fileExt = strtolower(pathinfo($imgFileName, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png');
                if (in_array($fileExt, $valid_extensions)) {
                    if ($imgFileSize < 1000000) {
                        $q0Old = $db->select("select * from tbl_store_products where product_id='" . $product_id . "' and sps_store_id='" . $store_id . "' ");
                        $oldProductFullPath = $image_upload_path . $q0Old[0]['item_image'];
                        @unlink($oldProductFullPath);

                        move_uploaded_file($imgTempPath, $image_upload_path . $imgFileName);

                        $array_fields = array(
                            'brand' => $db->CleanDBData($_REQUEST['brand']),
                            'item_name' => $db->CleanDBData($_REQUEST['item_name']),
                            'item_image' => $db->CleanDBData($imgFileName),
                            'item_spec' => $db->CleanDBData($_REQUEST['item_specification']),
                            'item_qty' => $db->CleanDBData((int)$_REQUEST['item_qty']),
                            'unit_price' => $db->CleanDBData(floatval($_REQUEST['unit_price'])),
                            'bulk_ord_qty' => $db->CleanDBData((int)$_REQUEST['bulk_order_qty']),
                            'bulk_price' => $db->CleanDBData(floatval($_REQUEST['bulk_price'])),
                            'expiry_date' => $db->CleanDBData($_REQUEST['expiry_date']),
                            'item_vat' => $db->CleanDBData($_REQUEST['item_vat']),
                            'item_subcategory' => $db->CleanDBData($_REQUEST['category']),
                            'subcategory_id' => $db->CleanDBData($_REQUEST['category_id']),
                            'featured' => $db->CleanDBData($_REQUEST['featured']),
                        );

                        $array_where = array(
                            'product_id' => $product_id,
                            'sps_store_id' => $store_id
                        );
                        $q0 = $db->Update('tbl_store_products', $array_fields, $array_where);
                        if($q0) {
                            http_response_code(200);
                            echo json_encode(array('status'=>'success','product_id'=>$product_id,'msg' => 'product updated successfully'));
                        } else {
                            http_response_code(400);
                            echo json_encode(array('status'=>'error', 'msg' => 'unable to add product'));
                        }

                    } else {
                        http_response_code(400);
                        $ErrorMSG = json_encode(array("message" => "Image is too large, max size is 1MB", "status" => 'error'));
                        echo $ErrorMSG;
                    }
                } else {
                    http_response_code(400);
                    $ErrorMSG = json_encode(array('status' => 'error', "message" => "Sorry, only JPG, JPEG, & PNG files are allowed"));
                    echo $ErrorMSG;
                }
            } else {
                $imgFileName = $_REQUEST['old_item_image'];
                $array_fields = array(
                    'brand' => $db->CleanDBData($_REQUEST['brand']),
                    'item_name' => $db->CleanDBData($_REQUEST['item_name']),
                    'item_image' => $db->CleanDBData($imgFileName),
                    'item_spec' => $db->CleanDBData($_REQUEST['item_specification']),
                    'item_qty' => $db->CleanDBData((int)$_REQUEST['item_qty']),
                    'unit_price' => $db->CleanDBData(floatval($_REQUEST['unit_price'])),
                    'bulk_ord_qty' => $db->CleanDBData((int)$_REQUEST['bulk_order_qty']),
                    'bulk_price' => $db->CleanDBData(floatval($_REQUEST['bulk_price'])),
                    'expiry_date' => $db->CleanDBData($_REQUEST['expiry_date']),
                    'item_vat' => $db->CleanDBData($_REQUEST['item_vat']),
                    'item_subcategory' => $db->CleanDBData($_REQUEST['category']),
                    'subcategory_id' => $db->CleanDBData($_REQUEST['category_id']),
                    'featured' => $db->CleanDBData($_REQUEST['featured']),
                );

                $array_where = array(
                    'product_id' => $product_id,
                    'sps_store_id' => $store_id
                );
                $q0 = $db->Update('tbl_store_products', $array_fields, $array_where);
                if($q0) {
                    http_response_code(200);
                    echo json_encode(array('status'=>'success','product_id'=>$product_id,'msg' => 'product updated successfully'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status'=>'error', 'msg' => 'unable to add product'));
                }
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }

    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-featured-products', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $q0 = $db->select("select * from tbl_store_products where sps_store_id='".$store_id."' and featured='Yes' ");
            $q0Count = $db->select("select count(*) as total from tbl_store_products where sps_store_id='".$store_id."'  and featured='Yes'");
            if($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','data'=>$q0,'total_count'=>$q0Count[0],'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error', 'msg' => 'no product found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-featured-stock-status', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $update_fields = array(
                'item_status' => $db->CleanDBData($data->item_status)
            );
            $array_where = array(
                'product_id' => $db->CleanDBData($data->product_id),
                'store_id' => $db->CleanDBData($store_id)
            );
            $q0 = $db->Update('tbl_store_products', $update_fields, $array_where);

            if ($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'record updated', "product_id" => $data->product_id));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'cannot update record'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/upload-bulk-products', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getSecretKey();
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $productsFileName = $_FILES['store_products']['name'];
            $productsPath = realpath($_FILES["store_products"]["tmp_name"]);

            if (empty($productsFileName)) {
                http_response_code(400);
                $ErrorMSG_XLSX = json_encode(array('status' => 'error', "message" => "please upload store product, using VWIS template"));
                echo $ErrorMSG_XLSX;
                exit();
            } else {
                $fileExt3 = strtolower(pathinfo($productsFileName, PATHINFO_EXTENSION));
                $valid_extensions = array('xlsx');
                if (in_array($fileExt3, $valid_extensions)) {
                    class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
                    {
                        public function readCell($columnAddress, $row, $worksheetName = '')
                        {
                            if ($row >= 5) {
                                return true;
                            }
                            return false;
                        }
                    }

                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    $reader->setReadFilter(new MyReadFilter());
                    $spreadsheet = $reader->load($productsPath);

                    $worksheet = $spreadsheet->getActiveSheet();
                    $worksheetArray = $worksheet->toArray();
                    $new_array = array_slice($worksheetArray, 4);

                    foreach ($new_array as $key => $value) {
                        if (trim($value[2]) != "") {
                            $worksheet = $spreadsheet->getActiveSheet();
                            $drawing = $worksheet->getDrawingCollection()[$key + 1];
                            $zipReader = fopen($drawing->getPath(), 'r');
                            $imageContents = '';
                            while (!feof($zipReader)) {
                                $imageContents .= fread($zipReader, 1024);
                            }
                            fclose($zipReader);
                            $extension = $drawing->getExtension();
                            $newName = uniqid();
                            save_base64_image("data:image/jpeg;base64," . base64_encode($imageContents), $newName);

                            $category_arr = explode("-", $db->CleanDBData($value[11]));
                            $products_arrays = array
                            (
                                'sps_store_id' => $db->CleanDBData($store_id),
                                'product_id' => $db->CleanDBData(rand(1000000, 9999999)),
                                'brand' => $db->CleanDBData($value[1]),
                                'item_name' => $db->CleanDBData($value[2]),
                                'item_image' => $db->CleanDBData($newName . 'jpg'),
                                'item_spec' => $db->CleanDBData($value[4]),
                                'item_qty' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[5]))),
                                'unit_price' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[6]))),
                                'bulk_ord_qty' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[7]))),
                                'bulk_price' => $db->CleanDBData(intval(preg_replace('/[^\d.]/', '', $value[8]))),
                                'expiry_date' => $db->CleanDBData($value[9]),
                                'item_vat' => $db->CleanDBData($value[10]),
                                'item_subcategory' => trim($category_arr['1']),
                                'subcategory_id' => trim($category_arr['0']),
                                'item_created_on' => $db->CleanDBData(date("d-m-Y H:i:s")),
                            );
                            $q0 = $db->Insert('tbl_store_products', $products_arrays);
                        }
                    }
                } else {
                    http_response_code(400);
                    $ErrorMSG_XLSX = json_encode(array('status' => 'error', "message" => "Sorry, only XLSX files are allowed (for excel upload)"));
                    echo $ErrorMSG_XLSX;
                    exit();
                }
            }

            if (!isset($ErrorMSG)) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', "message" => "Product(s) successfully uploaded", "store_id" => $store_id));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/store-payment', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    try {
        include_once ABSPATH.'/classes/GlobalApi.class.php';
        $res = (object)[];
        if (isset($api)) {
            $res = $api->curlQueryGet("https://api.paystack.co/transaction/verify/".$data->ref_id);
        }
        if (!empty($res) && $res->status == true) {
            $customer_code = $res->data->customer->customer_code;
            $authorization_code = $res->data->authorization->authorization_code;

            $fields = [
                'customer' => $customer_code,
                'plan' => $data->p_plan,
                'authorization' => $authorization_code
            ];
            $cus_sub = $api->curlQueryPost("https://api.paystack.co/subscription", $fields);

            if ($cus_sub->status == true) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Congrats! you have successfully activated your account'));
            } else {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Account activated, but subscription could not be completed'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 0, 'msg' => 'Unable to verify payment #'.$data->ref_id.' transaction, contact support'));
        }
   } catch (Exception $ex) {
        http_response_code(400);
        echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Could not verify transaction"));
   }
   
});

$router->map( 'GET', '/v1/api/cancel-subscription', function() {
    $db = app_db();
   $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            include_once ABSPATH.'/classes/GlobalApi.class.php';
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $ch0 = $db->select("select sa.*,ss.*,p.* from tbl_store_account sa inner join tbl_payments p on p.store_id = sa.store_id
                                            inner join tbl_subscriptions ss on ss.auth_code = p.auth_code
                                            where sa.store_id='".$store_id."' order by subscribe_sno desc limit 1");

            $sub_details = $api->curlQueryGet("https://api.paystack.co/subscription/".$ch0[0]['subscription_code']);

            if($sub_details->status == true){
                $fields = [
                    'code' => $ch0[0]['subscription_code'],
                    'token' => $sub_details->data->email_token
                ];
                if (!empty($api)) {
                    $cancel_sub = $api->curlQueryPost("https://api.paystack.co/subscription/disable", $fields);
                }

                if ($cancel_sub->status == 1 || $cancel_sub->status == true){
                    $update_fields = array('subscription_status' => $db->CleanDBData($cancel_sub->data->status));
                    $array_where = array('subscription_code' => $db->CleanDBData($ch0[0]['subscription_code']));
                    $q0 = $db->Update('tbl_subscriptions', $update_fields, $array_where);
                    if ($q0 > 0) {
                        http_response_code(200);
                        echo json_encode(array('status' => 1, 'msg' => $cancel_sub->message, 'data_status' => $cancel_sub->data->status));
                    } else {
                        http_response_code(200);
                        echo json_encode(array('status' => 0, 'msg' => 'Cancelled auto-renewal, but cannot update record'));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 0, 'msg' => $cancel_sub->message));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 0, 'msg' => "Could not retrieve subscription details"));
            }

        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/update-subscription-card', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            include_once ABSPATH.'/classes/GlobalApi.class.php';
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $ch0 = $db->select("select sa.*,ss.* from tbl_store_account sa inner join tbl_subscriptions ss on ss.store_id=sa.store_id 
                                            where sa.store_id='".$store_id."' order by subscribe_sno desc limit 1");
            $sub_code = $ch0[0]['subscription_code'];

            if (!empty($api)) {
                $up_card_sub = $api->curlQueryGet("https://api.paystack.co/subscription/".$sub_code."/manage/link");
            }

            if ($up_card_sub->status == true || $up_card_sub->status == true){
                http_response_code(200);
                echo json_encode(array("status" => 1,"message" => $up_card_sub->message,'redirect_link'=>$up_card_sub->data->link));
            } else {
                http_response_code(400);
                echo json_encode(array("status" => 0,"message" => "Unable to update card, kindly try again later"));
            }

        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/request-subscription-status', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            include_once ABSPATH.'/classes/GlobalApi.class.php';
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $ch0 = $db->select("select sa.*,ss.*,p.* from tbl_store_account sa inner join tbl_payments p on p.store_id = sa.store_id
                                            inner join tbl_subscriptions ss on ss.auth_code = p.auth_code
                                            where sa.store_id='".$store_id."' order by subscribe_sno desc limit 1");
            $sub_code = $ch0[0]['subscription_code'];

            if (!empty($api)) {
                $req_sub_status = $api->curlQueryGet("https://api.paystack.co/subscription/".$sub_code);

                http_response_code(200);
                echo json_encode($req_sub_status);
            } else {
                http_response_code(400);
                echo json_encode(array("status" => 0, "msg" => "Internal server error, try again later"));
            }

        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/get-plans', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        include_once ABSPATH.'/classes/GlobalApi.class.php';
 
        if (!empty($api)) {
            $req_plan = $api->curlQueryGet("https://api.paystack.co/plan");

            $plan_arr = array();
            foreach ($req_plan->data as $row) {
                $plan_arr[] = array(
                    "name" => $row->name,
                    "plan_code" => $row->plan_code,
                    "amount" => $row->amount/100,
                    "total_subscriptions" => $row->total_subscriptions
                );
            }

            http_response_code(200);
            // echo json_encode($req_plan);
            echo json_encode(array("status" => 1, "msg" => "record found","data"=>$plan_arr));
        } else {
            http_response_code(400);
            echo json_encode(array("status" => 0, "msg" => "Internal server error, try again later"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-plan-details', function($plan_code) {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        include_once ABSPATH.'/classes/GlobalApi.class.php';

        if (!empty($api)) {
            $req_plan = $api->curlQueryGet("https://api.paystack.co/plan/".$plan_code);

            http_response_code(200);
            echo json_encode($req_plan);
        } else {
            http_response_code(400);
            echo json_encode(array("status" => 0, "msg" => "Internal server error, try again later"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/change-subscription-plan', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            include_once ABSPATH.'/classes/GlobalApi.class.php';
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if (!empty($data->ref_id) && !empty($data->plan_code) && isset($data->ref_id)) {
                $res = (object)[];
                $ch0 = $db->select("select sa.*,ss.*,p.* from tbl_store_account sa inner join tbl_payments p on p.store_id = sa.store_id
                                            inner join tbl_subscriptions ss on ss.auth_code = p.auth_code
                                            where sa.store_id='".$store_id."' order by subscribe_sno desc limit 1");
                $fields = [
                    'code' => $ch0[0]['subscription_code'],
                    'token' => $ch0[0]['email_token']
                ];
                if (!empty($api)) {
                    $api->curlQueryPost("https://api.paystack.co/subscription/disable", $fields);
                }
                if (isset($api)) {
                    $res = $api->curlQueryGet("https://api.paystack.co/transaction/verify/" . $data->ref_id);
                }
                if ($res->status == true) {
                    $customer_code = $res->data->customer->customer_code;
                    $email = $res->data->customer->email;
                    $created_at = $res->data->created_at;
                    $pay_amount = $res->data->requested_amount;
                    $authorization_code = $res->data->authorization->authorization_code;

                    $insert_arrays = array
                    (
                        'pay_ref_id' => $db->CleanDBData($data->ref_id),
                        'store_id' => $db->CleanDBData($store_id),
                        'store_email' => $db->CleanDBData($email),
                        'pay_amount' => $db->CleanDBData($pay_amount / 100),
                        'customer_code' => $db->CleanDBData($customer_code),
                        'auth_code' => $db->CleanDBData($authorization_code),
                        'pay_on' => $db->CleanDBData($created_at)
                    );
                    $q0 = $db->Insert('tbl_payments', $insert_arrays);

                    $fields = [
                        'customer' => $customer_code,
                        'plan' => $data->plan_code,
                        'authorization' => $authorization_code
                    ];
                    if (isset($api)) {
                        $cus_sub = $api->curlQueryPost("https://api.paystack.co/subscription", $fields);
                    }
                    $subscription_code = $cus_sub->data->subscription_code;
                    $email_token = $cus_sub->data->email_token;
                    $next_pay_due_date = $cus_sub->data->next_payment_date;
                    $createdAt = $cus_sub->data->createdAt;

                    $insert_2_arrays = array
                    (
                        'pay_ref_id' => $db->CleanDBData($data->ref_id),
                        'store_id' => $db->CleanDBData($data->store_id),
                        'subscription_code' => $db->CleanDBData($subscription_code),
                        'email_token' => $db->CleanDBData($email_token),
                        'customer_code' => $db->CleanDBData($customer_code),
                        'next_pay_due_date' => $db->CleanDBData($next_pay_due_date),
                        'sub_created_on' => $db->CleanDBData($createdAt)
                    );
                    $q1 = $db->Insert('tbl_subscriptions', $insert_2_arrays);

                    if ($q0 > 0 && $q1 > 0) {
                        http_response_code(200);
                        echo json_encode(array('status' => 1, 'msg' => 'Payment successful, and auto renewal is active'));
                    } else {
                        http_response_code(200);
                        echo json_encode(array('status' => 1, 'msg' => 'Payment successful, but could subscription not completed'));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 0, 'msg' => 'Unable to verify payment #'.$data->ref_id.' transaction, contact support'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 0, 'msg' => 'Error, PaymentId/plan code is required to complete subscription process'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, 0 => $ex->getMessage(), "msg" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 0, 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-vwis-store-info', function() {
//    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $logoFileName = !empty($_REQUEST['curr_logo_file'])?$_REQUEST['curr_logo_file']:"";
            $coverFileName = !empty($_REQUEST['curr_cover_img'])?$_REQUEST['curr_cover_img']:"";

            if(!empty(trim($_REQUEST['str_biz_phone'])) && !empty(trim($_REQUEST['str_biz_name'])) &&!empty(trim($_REQUEST['store_tagline']))
                && !empty(trim($_REQUEST['store_address'])) ) {

                if ($_FILES['store_logo']['name'] != "") {
                    $logoFileName = strtolower(clean($_FILES['store_logo']['name']));
                    $logoTempPath = $_FILES['store_logo']['tmp_name'];
                    $logoFileSize = $_FILES['store_logo']['size'];

                    $logo_upload_path = 'public/uploads/store_logo/';
                    $fileExt = strtolower(pathinfo($logoFileName, PATHINFO_EXTENSION));
                    $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

                    if (in_array($fileExt, $valid_extensions)) {
                        if (!file_exists($logo_upload_path . $logoFileName)) {
                            if ($logoFileSize < 1000000) {
                                move_uploaded_file($logoTempPath, $logo_upload_path . $logoFileName);

                                if (!empty($logoFileName)) {
//                                    unlink( getcwd().'/public/uploads/store_logo/'.$logoFileName);
                                }
                            }
                        }
                    }
                }

                if ($_FILES['store_cover_image']['name'] != "") {
                    $coverFileName = strtolower(clean($_FILES['store_cover_image']['name']));
                    $coverTempPath = $_FILES['store_cover_image']['tmp_name'];
                    $coverFileSize = $_FILES['store_cover_image']['size'];

                    $cover_upload_path = 'public/uploads/cover_image/';
                    $fileExt2 = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                    $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');
                    if (in_array($fileExt2, $valid_extensions)) {
                        if (!file_exists($cover_upload_path . $coverFileName)) {
                            if ($coverFileSize < 1000000) {
                                move_uploaded_file($coverTempPath, $cover_upload_path . $coverFileName);

                                if (!empty($coverFileName)){
//                                    unlink( getcwd().'/public/uploads/cover_image/'.$coverFileName);
                                }
                            }
                        }
                    }
                }

                $biz_update_fields = array(
                    'str_biz_phone' => $db->CleanDBData($_REQUEST['str_biz_phone']),
                    'str_biz_name' => $db->CleanDBData($_REQUEST['str_biz_name'])
                );
                $biz_array_where = array('sbi_store_id' => $db->CleanDBData($store_id));
                $s_info_update_fields = array(
                    'store_logo' => $db->CleanDBData($logoFileName),
                    'store_cover_image' => $db->CleanDBData($coverFileName),
                    'store_tagline' => $db->CleanDBData($_REQUEST['store_tagline']),
                    'store_address' => $db->CleanDBData($_REQUEST['store_address']),
                );
                $s_info__array_where = array('si_store_id' => $db->CleanDBData($store_id));

                $q0 = $db->Update('tbl_store_biz_info', $biz_update_fields, $biz_array_where);
                $q1 = $db->Update('tbl_store_info', $s_info_update_fields, $s_info__array_where);

                if ($q0 > 0 && $q1 > 0) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', "message" => "Store information successfully updated"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'One or more required field empty'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-vwis-operational-info', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $store_op_info_update_fields = array
            (
                'store_op_days_from' => $db->CleanDBData($data->operation_days_from),
                'store_op_days_to' => $db->CleanDBData($data->operation_days_to),
                'sun_open_time' => $db->CleanDBData($data->sun_open_time),
                'sun_close_time' => $db->CleanDBData($data->sun_close_time),
                'sun_enable' => $db->CleanDBData($data->sun_enable),
                'mon_open_time' => $db->CleanDBData($data->mon_open_time),
                'mon_close_time' => $db->CleanDBData($data->mon_close_time),
                'mon_enable' => $db->CleanDBData($data->mon_enable),
                'tue_open_time' => $db->CleanDBData($data->tue_open_time),
                'tue_close_time' => $db->CleanDBData($data->tue_close_time),
                'tue_enable' => $db->CleanDBData($data->tue_enable),
                'wed_open_time' => $db->CleanDBData($data->wed_open_time),
                'wed_close_time' => $db->CleanDBData($data->wed_close_time),
                'wed_enable' => $db->CleanDBData($data->wed_enable),
                'thu_open_time' => $db->CleanDBData($data->thu_open_time),
                'thu_close_time' => $db->CleanDBData($data->thu_close_time),
                'thu_enable' => $db->CleanDBData($data->thu_enable),
                'fri_open_time' => $db->CleanDBData($data->fri_open_time),
                'fri_close_time' => $db->CleanDBData($data->fri_close_time),
                'fri_enable' => $db->CleanDBData($data->fri_enable),
                'sat_open_time' => $db->CleanDBData($data->sat_open_time),
                'sat_close_time' => $db->CleanDBData($data->sat_close_time),
                'sat_enable' => $db->CleanDBData($data->sat_enable)
//                'str_home_delivery' => $db->CleanDBData($data->str_home_delivery)
            );
            $op_info_array_where = array('soi_store_id' => $db->CleanDBData($store_id));
            $q0 = $db->Update('tbl_store_op_info', $store_op_info_update_fields, $op_info_array_where);

            if ($q0 > 0){
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Store operational information successfully updated'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Unable to record, try again later or contact support'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-store-ads-billboard', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $q0 = $db->select("select * from tbl_adds_billboard where sbb_store_id='$store_id'");
            $q0Img_1 = $db->select("select count(*) as total from tbl_adds_billboard where image_1 IS NOT NULL and sbb_store_id='".$store_id."' ");
            $q0Img_2 = $db->select("select count(*) as total from tbl_adds_billboard where image_2 IS NOT NULL and sbb_store_id='".$store_id."' ");
            $q0Img_3 = $db->select("select count(*) as total from tbl_adds_billboard where image_3 IS NOT NULL and sbb_store_id='".$store_id."' ");
            $q0Img_4 = $db->select("select count(*) as total from tbl_adds_billboard where image_4 IS NOT NULL and sbb_store_id='".$store_id."' ");

            $q0Count = $q0Img_1[0]['total'] + $q0Img_2[0]['total'] + $q0Img_3[0]['total'] + $q0Img_4[0]['total'];
            if($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','ads_billboard'=>$q0[0],'total_uploaded_image'=>$q0Count,'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error','total_uploaded_image'=>$q0Count,'msg' => 'no record found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-vwis-adds-billboard', function() {
//    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $q0 = $db->select("select * from tbl_adds_billboard where sbb_store_id='$store_id'");
            if ($q0 <= 0) {
                $adds_arrays = array('sbb_store_id' => $db->CleanDBData($store_id));
                $addQ = $db->Insert('tbl_adds_billboard', $adds_arrays);
            }

            $addsImg_FileName_1 = !empty($_REQUEST['curr_adds_img_1'])?$_REQUEST['curr_adds_img_1']:"";
            $addsImg_FileName_2 = !empty($_REQUEST['curr_adds_img_2'])?$_REQUEST['curr_adds_img_2']:"";
            $addsImg_FileName_3 = !empty($_REQUEST['curr_adds_img_3'])?$_REQUEST['curr_adds_img_3']:"";
            $addsImg_FileName_4 = !empty($_REQUEST['curr_adds_img_4'])?$_REQUEST['curr_adds_img_4']:"";

            if ($_FILES['adds_img_1']['name'] != "") {
                $addsImg_FileName_1 = 'adds_'.strtolower(clean($_FILES['adds_img_1']['name']));
                $addsImg_1_TempPath = $_FILES['adds_img_1']['tmp_name'];
                $addsImg_1_FileSize = $_FILES['adds_img_1']['size'];

                $addsImg_1_upload_path = 'public/uploads/adds_billboards/';
                $fileExt = strtolower(pathinfo($addsImg_FileName_1, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

                if (in_array($fileExt, $valid_extensions)) {
                    if (!file_exists($addsImg_1_upload_path . $addsImg_FileName_1)) {
                        if ($addsImg_1_FileSize < 1000000) {
                            move_uploaded_file($addsImg_1_TempPath, $addsImg_1_upload_path . $addsImg_FileName_1);

                            if (!empty($addsImg_FileName_1)) {
//                                unlink( getcwd().'/public/uploads/store_logo/'.$logoFileName);
                            }
                        }
                    }
                }
            }

            if ($_FILES['adds_img_2']['name'] != "") {
                $addsImg_FileName_2 = 'adds_'.strtolower(clean($_FILES['adds_img_2']['name']));
                $addsImg_2_TempPath = $_FILES['adds_img_2']['tmp_name'];
                $addsImg_2_FileSize = $_FILES['adds_img_2']['size'];

                $addsImg_2_upload_path = 'public/uploads/adds_billboards/';
                $fileExt = strtolower(pathinfo($addsImg_FileName_2, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

                if (in_array($fileExt, $valid_extensions)) {
                    if (!file_exists($addsImg_2_upload_path . $addsImg_FileName_2)) {
                        if ($addsImg_2_FileSize < 1000000) {
                            move_uploaded_file($addsImg_2_TempPath, $addsImg_2_upload_path . $addsImg_FileName_2);

                            if (!empty($addsImg_FileName_2)) {
//                                unlink( getcwd().'/public/uploads/store_logo/'.$logoFileName);
                            }
                        }
                    }
                }
            }

            if ($_FILES['adds_img_3']['name'] != "") {
                $addsImg_FileName_3 = 'adds_'.strtolower(clean($_FILES['adds_img_3']['name']));
                $addsImg_3_TempPath = $_FILES['adds_img_3']['tmp_name'];
                $addsImg_3_FileSize = $_FILES['adds_img_3']['size'];

                $addsImg_3_upload_path = 'public/uploads/adds_billboards/';
                $fileExt = strtolower(pathinfo($addsImg_FileName_3, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

                if (in_array($fileExt, $valid_extensions)) {
                    if (!file_exists($addsImg_3_upload_path . $addsImg_FileName_3)) {
                        if ($addsImg_3_FileSize < 1000000) {
                            move_uploaded_file($addsImg_3_TempPath, $addsImg_3_upload_path . $addsImg_FileName_3);

                            if (!empty($addsImg_FileName_3)) {
//                                unlink( getcwd().'/public/uploads/store_logo/'.$logoFileName);
                            }
                        }
                    }
                }
            }

            if ($_FILES['adds_img_4']['name'] != "") {
                $addsImg_FileName_4 = 'adds_'.strtolower(clean($_FILES['adds_img_4']['name']));
                $addsImg_4_TempPath = $_FILES['adds_img_4']['tmp_name'];
                $addsImg_4_FileSize = $_FILES['adds_img_4']['size'];

                $addsImg_4_upload_path = 'public/uploads/adds_billboards/';
                $fileExt = strtolower(pathinfo($addsImg_FileName_4, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

                if (in_array($fileExt, $valid_extensions)) {
                    if (!file_exists($addsImg_4_upload_path . $addsImg_FileName_4)) {
                        if ($addsImg_3_FileSize < 1000000) {
                            move_uploaded_file($addsImg_4_TempPath, $addsImg_4_upload_path . $addsImg_FileName_4);

                            if (!empty($addsImg_FileName_4)) {
//                                unlink( getcwd().'/public/uploads/store_logo/'.$logoFileName);
                            }
                        }
                    }
                }
            }

            $adds_update_fields = array(
                'image_1' => $db->CleanDBData($addsImg_FileName_1),
                'image_2' => $db->CleanDBData($addsImg_FileName_2),
                'image_3' => $db->CleanDBData($addsImg_FileName_3),
                'image_4' => $db->CleanDBData($addsImg_FileName_4),
            );
            $adds_array_where = array('sbb_store_id' => $db->CleanDBData($store_id));
            $q0 = $db->Update('tbl_adds_billboard', $adds_update_fields, $adds_array_where);

            if ($q0 > 0 ) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', "message" => "Store Adds Billboard successfully updated"));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'DELETE', '/v1/api/delete-vwis-adds-image-1', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if(!empty($data->adds_img_1)) {
                $adds_img_1 = $db->CleanDBData($data->adds_img_1);

                $adds_update_fields = array('image_1' => "null");
                $adds_array_where = array('sbb_store_id' => $db->CleanDBData($store_id));
                $q0 = $db->Update('tbl_adds_billboard', $adds_update_fields, $adds_array_where);
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Adds image one (1) deleted successfully.'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Adds image one (1) cannot be empty.'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'DELETE', '/v1/api/delete-vwis-adds-image-2', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if(!empty($data->adds_img_2)) {
                $adds_img_2 = $db->CleanDBData($data->adds_img_2);

                $adds_update_fields = array('image_2' => "null");
                $adds_array_where = array('sbb_store_id' => $db->CleanDBData($store_id));
                $q0 = $db->Update('tbl_adds_billboard', $adds_update_fields, $adds_array_where);
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Adds image one (2) deleted successfully.'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Adds image one (2) cannot be empty.'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'DELETE', '/v1/api/delete-vwis-adds-image-3', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if(!empty($data->adds_img_3)) {
                $adds_img_3 = $db->CleanDBData($data->adds_img_3);

                $adds_update_fields = array('image_3' => "null");
                $adds_array_where = array('sbb_store_id' => $db->CleanDBData($store_id));
                $q0 = $db->Update('tbl_adds_billboard', $adds_update_fields, $adds_array_where);
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Adds image one (3) deleted successfully.'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Adds image one (3) cannot be empty.'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'DELETE', '/v1/api/delete-vwis-adds-image-4', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if(!empty($data->adds_img_4)) {
                $adds_img_4 = $db->CleanDBData($data->adds_img_4);

                $adds_update_fields = array('image_4' => "null");
                $adds_array_where = array('sbb_store_id' => $db->CleanDBData($store_id));
                $q0 = $db->Update('tbl_adds_billboard', $adds_update_fields, $adds_array_where);
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Adds image one (4) deleted successfully.'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Adds image one (4) cannot be empty.'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-account-profile', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;
            if(!empty(trim($data->str_biz_name)) && !empty(trim($data->str_biz_reg_no)) &&!empty(trim($data->str_bank_name)) && !empty(trim($data->str_acct_no)) ) {
                $acct_info_update_fields = array
                (
                    'str_biz_name' => $db->CleanDBData($data->str_biz_name),
                    'str_biz_reg_no' => $db->CleanDBData($data->str_biz_reg_no),
                    'str_bank_name' => $db->CleanDBData($data->str_bank_name),
                    'str_acct_no' => $db->CleanDBData($data->str_acct_no)
                );
                $acct_info_array_where = array('sbi_store_id' => $db->CleanDBData($store_id));
                $q0 = $db->Update('tbl_store_biz_info', $acct_info_update_fields, $acct_info_array_where);

                if ($q0 > 0) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Store account profile successfully updated'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to update record, try again later or contact support'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'One or more required field empty'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-account-password', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;
            if(!empty(trim($data->current_password)) && !empty(trim($data->new_password)) && !empty(trim($data->confirm_password))) {
                $ch0 = $db->select("select * from tbl_store_account where store_id='$store_id'");
                $password_used = $ch0[0]['str_password'];
                if (password_verify($data->current_password,$password_used)) {
                    if ($data->new_password == $data->confirm_password) {
                        if ($data->current_password != $data->new_password) {
                            $acct_info_update_fields = array('str_password' => $db->CleanDBData(password_hash($data->new_password, PASSWORD_DEFAULT)));
                            $acct_info_array_where = array('store_id' => $db->CleanDBData($store_id));
                            $q0 = $db->Update('tbl_store_account', $acct_info_update_fields, $acct_info_array_where);

                            if ($q0 > 0) {
                                http_response_code(200);
                                echo json_encode(array('status' => 'success', 'msg' => 'Store account password successfully updated'));
                            } else {
                                http_response_code(400);
                                echo json_encode(array('status' => 'error', 'msg' => 'Unable to update password, try again later or contact support'));
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode(array('status' => 'error', 'msg' => 'Password is currently inuse, try another password'));
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(array('status' => 'error', 'msg' => 'Unmatched new password combination'));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Old password entered seems to be incorrect'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'One or more required field empty'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-store-services', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if ($_REQUEST['cash_option']=='Yes' || $_REQUEST['card_option']=='Yes' || $_REQUEST['transfer_option']=='Yes' || $_REQUEST['cheque_option']=='Yes') {
                $payment_method_array = array
                (
                    'cash_option' => $db->CleanDBData($_REQUEST['cash_option']),
                    'card_option' => $db->CleanDBData($_REQUEST['card_option']),
                    'transfer_option' => $db->CleanDBData($_REQUEST['transfer_option']),
                    'cheque_option' => $db->CleanDBData($_REQUEST['cheque_option'])
                );
                $method_array_where = array('sbi_store_id' => $store_id);

                $services_array = array
                (
                    'pickup_service' => $db->CleanDBData($_REQUEST['pickup_service']),
                    'str_home_delivery' => $db->CleanDBData($_REQUEST['home_delivery_service']),
                    'delivery_timeline' => $db->CleanDBData($_REQUEST['delivery_timeline']),
                    'google_biz_link' => $db->CleanDBData($_REQUEST['google_business_link'])
                );
                $services_array_where = array('soi_store_id' => $store_id);

                if ($_REQUEST['home_delivery_service'] == 'Yes') {
                    $del_array_where = array('sri_store_id' => $store_id);
                    $db->Delete('tbl_store_rate_info', $del_array_where);

                    $store_rates = count($_REQUEST["str_home_lga"]);
                    $error = 0;
                    for ($i = 0; $i < $store_rates; $i++) {
                        if ($_REQUEST["str_home_lga"][$i] == '' || $_REQUEST["str_home_fee"][$i] == '') {
                            $error = $error + 1;
                        } else {
                            $store_rates_arrays = array
                            (
                                'sri_store_id' => $db->CleanDBData($store_id),
                                'str_home_lga' => $db->CleanDBData($_REQUEST['str_home_lga'][$i]),
                                'str_home_fee' => $db->CleanDBData($_REQUEST['str_home_fee'][$i])
                            );
                            if ($error == 0) {
                                $q2 = $db->Insert('tbl_store_rate_info', $store_rates_arrays);
                            }
                        }
                    }
                }
                $q0 = $db->Update('tbl_store_biz_info', $payment_method_array, $method_array_where);
                $q1 = $db->Update('tbl_store_op_info', $services_array, $services_array_where);

                if ($q0 > 0 && $q1 > 0) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Store Card & Services successfully updated'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to update record, try again later or contact support'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'At least one payment method is required, try again..'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-discount-item-price/[*:action]', function($product_id) {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $product_id = $db->CleanDBData($product_id);
            $q0 = $db->select("select * from tbl_store_products where sps_store_id='".$store_id."' and product_id='$product_id'");

            if($q0 > 0) {
//                foreach ($q0[0] as $row) {
//                    $item_price_arr[] = array(
                    $item_price_arr = array(
                        "sps_store_id" => $q0[0]['sps_store_id'],
                        "product_id" => $q0[0]['product_id'],
                        "brand" => $q0[0]['brand'],
                        "item_name" => $q0[0]['item_name'],
                        "bulk_price" => $q0[0]['bulk_price'],
                        "unit_price" => $q0[0]['unit_price']
                    );
//                }

                http_response_code(200);
                echo json_encode(array('status'=>'success','data'=>$item_price_arr,'msg' => 'found records'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'product not found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/create-store-discount', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $discount_id = rand(1000000, 9999999);
            if ($_REQUEST["discount_type"] == "Percentage Discount") {
                $insert_arrays = array
                (
                    'discount_id' => $db->CleanDBData($discount_id),
                    'dis_store_id' => $db->CleanDBData($store_id),
                    'dis_product_id' => $db->CleanDBData($_REQUEST["product_id"]),
                    'dis_unit_price' => $db->CleanDBData($_REQUEST["unit_price"]),
                    'dis_bulk_price' => $db->CleanDBData($_REQUEST["bulk_price"]),
                    'discount_type' => $db->CleanDBData($_REQUEST["discount_type"]),
                    'dis_percentage' => $db->CleanDBData($_REQUEST["discount_percentage"]),
                    'dis_start_date' => $db->CleanDBData($_REQUEST["start_date"]),
                    'dis_end_date' => $db->CleanDBData($_REQUEST["end_date"]),
                    'dis_to_bulk_price' => $db->CleanDBData($_REQUEST["dis_to_bulk_price"]),
                    'dis_created_on' => $db->CleanDBData(date("Y-m-d H:i:s")),
                );
                $q0 = $db->Insert('tbl_product_discounts', $insert_arrays);
            } else if ($_REQUEST["discount_type"] == "Free Shipping Discount"){
                if (isset($_REQUEST["dis_lga_name"]) && !empty($_REQUEST["dis_lga_name"])) {
                    $discount_lgas = count($_REQUEST["dis_lga_name"]);
                    $insert_arrays = array
                    (
                        'discount_id' => $db->CleanDBData($discount_id),
                        'dis_store_id' => $db->CleanDBData($store_id),
                        'dis_product_id' => $db->CleanDBData($_REQUEST["product_id"]),
                        'dis_unit_price' => $db->CleanDBData($_REQUEST["unit_price"]),
                        'dis_bulk_price' => $db->CleanDBData($_REQUEST["bulk_price"]),
                        'discount_type' => $db->CleanDBData($_REQUEST["discount_type"]),
                        'dis_start_date' => $db->CleanDBData($_REQUEST["start_date"]),
                        'dis_end_date' => $db->CleanDBData($_REQUEST["end_date"]),
                        'dis_created_on' => $db->CleanDBData(date("Y-m-d H:i:s")),
                    );
                    $q0 = $db->Insert('tbl_product_discounts', $insert_arrays);

                    $error = 0;
                    for ($i = 0; $i < $discount_lgas; $i++) {
                        if ($_REQUEST["dis_lga_name"][$i] == '' || $_REQUEST["dis_lga_name"][$i] == '') {
                            $error = $error + 1;
                        } else {
                            $dis_lgas_arrays = array
                            (
                                'fsd_l_store_id' => $db->CleanDBData($store_id),
                                'fsd_l_discount_id' => $db->CleanDBData($discount_id),
                                'fsd_l_name' => $db->CleanDBData($_REQUEST['dis_lga_name'][$i])
                            );
                            if ($error == 0) {
                                $q2 = $db->Insert('tbl_free_ship_discount_lga', $dis_lgas_arrays);
                            }
                        }
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Invalid Discount process, enter at least one LGA '));
                    exit();
                }
            } else if ($_REQUEST["discount_type"] == "Buy one Get one free" || $_REQUEST["discount_type"] == "Buy One Get One free"){
                $insert_arrays = array
                (
                    'discount_id' => $db->CleanDBData($discount_id),
                    'dis_store_id' => $db->CleanDBData($store_id),
                    'dis_product_id' => $db->CleanDBData($_REQUEST["product_id"]),
                    'dis_unit_price' => $db->CleanDBData($_REQUEST["unit_price"]),
                    'dis_bulk_price' => $db->CleanDBData($_REQUEST["bulk_price"]),
                    'discount_type' => $db->CleanDBData($_REQUEST["discount_type"]),
                    'dis_start_date' => $db->CleanDBData($_REQUEST["start_date"]),
                    'dis_end_date' => $db->CleanDBData($_REQUEST["end_date"]),
                    'dis_created_on' => $db->CleanDBData(date("Y-m-d H:i:s")),
                );
                $q0 = $db->Insert('tbl_product_discounts', $insert_arrays);
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Invalid Discount type, try again'));
                exit();
            }
            if ($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Discount successfully created'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'cannot insert record'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-store-discount', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if ($_REQUEST["discount_type"] == "Percentage Discount") {
                $discount_id = $db->CleanDBData($_REQUEST["discount_id"]);
                $update_arrays = array
                (
                    'dis_product_id' => $db->CleanDBData($_REQUEST["product_id"]),
                    'dis_unit_price' => $db->CleanDBData($_REQUEST["unit_price"]),
                    'dis_bulk_price' => $db->CleanDBData($_REQUEST["bulk_price"]),
                    'discount_type' => $db->CleanDBData($_REQUEST["discount_type"]),
                    'dis_percentage' => $db->CleanDBData($_REQUEST["discount_percentage"]),
                    'dis_start_date' => $db->CleanDBData($_REQUEST["start_date"]),
                    'dis_end_date' => $db->CleanDBData($_REQUEST["end_date"]),
                    'dis_to_bulk_price' => $db->CleanDBData($_REQUEST["dis_to_bulk_price"]),
                    'dis_created_on' => $db->CleanDBData(date("Y-m-d H:i:s")),
                );
                $update_where = array('discount_id' => $discount_id,'dis_store_id' => $store_id);
                $q0 = $db->Update('tbl_product_discounts', $update_arrays, $update_where);
            } else if ($_REQUEST["discount_type"] == "Free Shipping Discount"){
                $discount_id = $db->CleanDBData($_REQUEST["discount_id"]);
                if (isset($_REQUEST["dis_lga_name"]) && !empty($_REQUEST["dis_lga_name"])) {
                    $discount_lgas = count($_REQUEST["dis_lga_name"]);
                    $update_arrays = array
                    (
                        'dis_product_id' => $db->CleanDBData($_REQUEST["product_id"]),
                        'dis_unit_price' => $db->CleanDBData($_REQUEST["unit_price"]),
                        'dis_bulk_price' => $db->CleanDBData($_REQUEST["bulk_price"]),
                        'discount_type' => $db->CleanDBData($_REQUEST["discount_type"]),
                        'dis_start_date' => $db->CleanDBData($_REQUEST["start_date"]),
                        'dis_end_date' => $db->CleanDBData($_REQUEST["end_date"]),
                        'dis_created_on' => $db->CleanDBData(date("Y-m-d H:i:s")),
                    );
                    $update_where = array('discount_id' => $discount_id,'dis_store_id' => $store_id);
                    $q0 = $db->Update('tbl_product_discounts', $update_arrays, $update_where);

                    $del_array_where = array('fsd_l_discount_id' => $discount_id,'fsd_l_store_id' => $store_id);
                    $db->Delete('tbl_free_ship_discount_lga', $del_array_where);

                    $error = 0;
                    for ($i = 0; $i < $discount_lgas; $i++) {
                        if ($_REQUEST["dis_lga_name"][$i] == '' || $_REQUEST["dis_lga_name"][$i] == '') {
                            $error = $error + 1;
                        } else {
                            $dis_lgas_arrays = array
                            (
                                'fsd_l_store_id' => $db->CleanDBData($store_id),
                                'fsd_l_discount_id' => $db->CleanDBData($discount_id),
                                'fsd_l_name' => $db->CleanDBData($_REQUEST['dis_lga_name'][$i])
                            );
                            if ($error == 0) {
                                $q2 = $db->Insert('tbl_free_ship_discount_lga', $dis_lgas_arrays);
                            }
                        }
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Invalid Discount process, enter at least one LGA '));
                    exit();
                }
            } else if ($_REQUEST["discount_type"] == "Buy one Get one free" || $_REQUEST["discount_type"] == "Buy One Get One free"){
                $discount_id = $db->CleanDBData($_REQUEST["discount_id"]);
                $update_arrays = array
                (
                    'dis_product_id' => $db->CleanDBData($_REQUEST["product_id"]),
                    'dis_unit_price' => $db->CleanDBData($_REQUEST["unit_price"]),
                    'dis_bulk_price' => $db->CleanDBData($_REQUEST["bulk_price"]),
                    'discount_type' => $db->CleanDBData($_REQUEST["discount_type"]),
                    'dis_start_date' => $db->CleanDBData($_REQUEST["start_date"]),
                    'dis_end_date' => $db->CleanDBData($_REQUEST["end_date"]),
                    'dis_created_on' => $db->CleanDBData(date("Y-m-d H:i:s")),
                );
                $update_where = array('discount_id' => $discount_id,'dis_store_id' => $store_id);
                $q0 = $db->Update('tbl_product_discounts', $update_arrays, $update_where);
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Invalid Discount type, try again'));
                exit();
            }
            if ($q0 > 0) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Discount successfully updated'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'cannot update discount record'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-discount-status', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if (!empty($data->discount_id) && !empty($data->discount_status)) {
                $discount_id = $db->CleanDBData($data->discount_id);
                $update_arrays = array
                (
                    'dis_status' => $db->CleanDBData($data->discount_status)
                );
                $update_where = array('discount_id' => $discount_id, 'dis_store_id' => $store_id);
                $q0 = $db->Update('tbl_product_discounts', $update_arrays, $update_where);

                if ($q0 > 0) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Discount status successfully updated'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'cannot update discount record'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'One or more required field empty'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'DELETE', '/v1/api/delete-selected-discount-item', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $discount_list = $db->CleanDBData($data->discount_ids);
            $Qry = $db->Qry("delete from tbl_product_discounts where discount_id in ($discount_list) and dis_store_id='".$store_id."' ");

            if($Qry == 1) {
                $db->Qry("delete from tbl_free_ship_discount_lga where fsd_l_discount_id in ($discount_list) and fsd_l_store_id='".$store_id."' ");
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg' => 'discount(s) deleted'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to delete discount(s)',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-filter-discount-item', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $whereSQL = '';
            if(!empty($_GET['search'])){
                $whereSQL .= " and (dis_product_id like '%".$_GET['search']."%' or discount_type LIKE '%".$_GET['search']."%')";
            }
            if(!empty($_GET['discount_type'])){
                $whereSQL .= " and (discount_type = '".$_GET['discount_type']."')";
            }
            if(!empty($_GET['discount_status'])){
                $whereSQL .= " and (dis_status = '".$_GET['discount_status']."')";
            }

            $q0 = $db->select("select * from tbl_product_discounts where dis_store_id='".$store_id."' $whereSQL");

            if($q0 > 0) {
                $item_arr = array();
                $item_lga_arr = array();
                foreach ($q0 as $row) {
                    $q1 = $db->select("select * from tbl_free_ship_discount_lga where fsd_l_store_id='$store_id' and fsd_l_discount_id='".$row['discount_id']."'");
                    if ($q1 > 0){
                        foreach ($q1 as $row1) {
                            $item_lga_arr[] = $row1['fsd_l_name'];
                        }
                    }

                    $item_arr[] = array(
                        "discount_sno" => $row['discount_sno'],
                        "discount_id" => $row['discount_id'],
                        "store_id" => $row['dis_store_id'],
                        "product_id" => $row['dis_product_id'],
                        "unit_price" => $row['dis_unit_price'],
                        "bulk_price" => $row['dis_bulk_price'],
                        "discount_type" => $row['discount_type'],
                        "discount_percentage" => $row['dis_percentage'],
                        "start_date" => $row['dis_start_date'],
                        "end_date" => $row['dis_end_date'],
                        "discount_status" => $row['dis_status'],
                        "dis_created_on" => $row['dis_created_on'],
                        "discount_LGAs" => ($q1 > 0)?$item_lga_arr:null
                    );
                }
                http_response_code(200);
                echo json_encode(array('status'=>'success','discount_items'=>$item_arr,'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error', 'msg' => 'no record found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/v1/api/fetch-discount-item-by-id/[*:action]', function($discount_id) {
//    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            $q0 = $db->select("select pd.*,p.* from tbl_product_discounts pd inner join tbl_store_products p on p.product_id = pd.dis_product_id
                        where pd.dis_store_id='".$store_id."' and pd.discount_id='$discount_id'");

            if($q0 > 0) {
                $item_arr = array();
                foreach ($q0 as $row) {
                    $item_arr[] = array(
                        "discount_sno" => $row['discount_sno'],
                        "discount_id" => $row['discount_id'],
                        "store_id" => $row['dis_store_id'],
                        "product_id" => $row['dis_product_id'],
                        "unit_price" => $row['dis_unit_price'],
                        "bulk_price" => $row['dis_bulk_price'],
                        "discount_type" => $row['discount_type'],
                        "discount_percentage" => $row['dis_percentage'],
                        "start_date" => $row['dis_start_date'],
                        "end_date" => $row['dis_end_date'],
                        "discount_status" => $row['dis_status'],
                        "dis_created_on" => $row['dis_created_on'],

                        "item_name" => $row['item_name'],
                        "brand" => $row['brand'],
                        "item_image" => $row['item_image'],
                        "item_spec" => $row['item_spec'],
                        "item_subcategory" => $row['item_subcategory'],
                        "item_unit_price" => $row['unit_price'],
                        "item_bulk_price" => $row['bulk_price'],
                        "expiry_date" => $row['expiry_date']
                    );
                }
                http_response_code(200);
                echo json_encode(array('status'=>'success','discount_items'=>$item_arr,'msg' => 'found records'));
            } else {
                http_response_code(200);
                echo json_encode(array('status'=>'error', 'msg' => 'no record found',));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "message" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/my-template', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;



        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-store-logo', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if ($_FILES['store_logo']['name'] != "") {
                $logoFileName = strtolower(clean($_FILES['store_logo']['name']));
                $logoTempPath = $_FILES['store_logo']['tmp_name'];
                $logoFileSize = $_FILES['store_logo']['size'];

                $logo_upload_path = 'public/uploads/store_logo/';
                $fileExt = strtolower(pathinfo($logoFileName, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');
                if (in_array($fileExt, $valid_extensions)) {
                    if (!file_exists($logo_upload_path . $logoFileName)) {
                        if ($logoFileSize < 1000000) {
                            if(move_uploaded_file($logoTempPath, $logo_upload_path . $logoFileName)){
                                $store_info_where = array('si_store_id' => $db->CleanDBData($store_id),);
                                $store_info_arrays = array('store_logo' => $db->CleanDBData($logoFileName));
                                $q0 = $db->Update('tbl_store_info', $store_info_arrays,$store_info_where);

                                if ($q0 > 0){
                                    http_response_code(400);
                                    echo json_encode(array('status' => 'success', 'msg' => 'Store logo successfully updated.'));
                                } else {
                                    http_response_code(400);
                                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to update file name, contact our support'));
                                }
                            } else {
                                http_response_code(400);
                                echo json_encode(array('status' => 'error', 'msg' => 'Could not upload file. Please try again later'));
                            }
                        } else {
                            http_response_code(400);
                            $ErrorMSG = json_encode(array("message" => "Logo is too large, please upload 1 MB size","status" => 'error'));
                            echo $ErrorMSG;
                        }
                    } else {
                        http_response_code(400);
                        $ErrorMSG = json_encode(array('status' => 'error', "message" => "Sorry, file already exists check upload folder"));
                        echo $ErrorMSG;
                    }
                } else {
                    http_response_code(400);
                    $ErrorMSG = json_encode(array('status' => 'error',"message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed"));
                    echo $ErrorMSG;
                }
            } else {
                http_response_code(400);
                $ErrorMSG = json_encode(array('status' => 'error',"message" => "Select an image to upload"));
                echo $ErrorMSG;
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/update-store-cover-image', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('HTTP_MY_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $store_id = $decoded_data->store_id;

            if ($_FILES['store_cover_image']['name'] != "") {
                $coverFileName = strtolower(clean($_FILES['store_cover_image']['name']));
                $coverTempPath = $_FILES['store_cover_image']['tmp_name'];
                $coverFileSize = $_FILES['store_cover_image']['size'];

                $cover_upload_path = 'public/uploads/cover_image/';
                $fileExt = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');
                if (in_array($fileExt, $valid_extensions)) {
                    if (!file_exists($cover_upload_path . $coverFileName)) {
                        if ($coverFileSize < 1000000) {
                            if(move_uploaded_file($coverTempPath, $cover_upload_path . $coverFileName)){
                                $store_info_where = array('si_store_id' => $db->CleanDBData($store_id),);
                                $store_info_arrays = array('store_cover_image' => $db->CleanDBData($coverFileName));
                                $q0 = $db->Update('tbl_store_info', $store_info_arrays,$store_info_where);

                                if ($q0 > 0){
                                    http_response_code(400);
                                    echo json_encode(array('status' => 'success', 'msg' => 'Cover image successfully updated.'));
                                } else {
                                    http_response_code(400);
                                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to update cover image, contact our support'));
                                }
                            } else {
                                http_response_code(400);
                                echo json_encode(array('status' => 'error', 'msg' => 'Could not upload file. Please try again later'));
                            }
                        } else {
                            http_response_code(400);
                            $ErrorMSG = json_encode(array("message" => "Logo is too large, please upload 1 MB size","status" => 'error'));
                            echo $ErrorMSG;
                        }
                    } else {
                        http_response_code(400);
                        $ErrorMSG = json_encode(array('status' => 'error', "message" => "Sorry, file already exists check upload folder"));
                        echo $ErrorMSG;
                    }
                } else {
                    http_response_code(400);
                    $ErrorMSG = json_encode(array('status' => 'error',"message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed"));
                    echo $ErrorMSG;
                }
            } else {
                http_response_code(400);
                $ErrorMSG = json_encode(array('status' => 'error',"message" => "Select an image to upload"));
                echo $ErrorMSG;
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

?>