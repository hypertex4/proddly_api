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
    if (isset($_SERVER['Developer_Key'])) {
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

function send_account_activation_mail($fname,$email,$shoppers_id){
    $host = "https://$_SERVER[HTTP_HOST]";
    $url = $host."/shoppers-account-activation/".$shoppers_id;
    $mj = new \Mailjet\Client('a3369e2ddc659c7c9ab4f66e9a3cc1bf', 'd8bd68cf2dc82ef75fe422b563c58325', true, ['version' => 'v3.1']);
    $body = ['Messages' => [[
        'From' => ['Email' => "tools@proddly.com", 'Name' => "Proddly"],
        'To' => [
            [
                'Email' => $email,
            ]
        ],
        'Subject' => "Verify your email address!",
        'HTMLPart' => '
        <!doctype html>
        <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><head><title>Verify your email address!</title><!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]--><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style type="text/css">#outlook a { padding:0; }
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
            .mj-column-per-100 { width:100% !important; max-width: 100%; }
          }</style><style media="screen and (min-width:480px)">.moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">[owa] .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">@media only screen and (max-width:480px) {
          table.mj-full-width-mobile { width: 100% !important; }
          td.mj-full-width-mobile { width: auto !important; }
        }</style></head><body style="word-spacing:normal;background-color:#F4F4F4;"><div style="background-color:#F4F4F4;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:0px 0px 10px 0px;padding-bottom:10px;padding-left:0px;padding-right:0px;padding-top:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:0px 0px 0px 0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;" class="mj-full-width-mobile"><tbody><tr><td style="width:600px;" class="mj-full-width-mobile"><img alt="" height="auto" src="https://0m0ng.mjt.lu/tplimg/0m0ng/b/lsgjw/1k10.jpeg" style="border:none;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="600"></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:18px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><h1 class="text-build-content" data-testid="8vJ3U63KHGEy" style="margin-top: 10px; margin-bottom: 10px; font-weight: normal;"><span style="font-family:Arial, sans-serif;font-size:18px;">Hi ' . $fname . '</span></h1></div></td></tr><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-top: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">Congratulations! You now have an account on Proddly.&nbsp;</span></p><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-bottom: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">We needed to verify your email address to keep your account secured. Please click the button below to complete your signup.</span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:0px;padding-top:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px 10px 25px;padding-right:25px;padding-left:25px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;"><tbody><tr><td align="center" bgcolor="#00b0ff" role="presentation" style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px 10px 25px;background:#00b0ff;" valign="middle"><a href="' . $url . '" style="display:inline-block;background:#00b0ff;color:#ffffff;font-family:Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px 10px 25px;mso-padding-alt:0px;border-radius:3px;" target="_blank"><span style="font-size:14px;">Complete Signup</span></a></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="iMnv3GwpxrUOb" style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">If you have any trouble clicking the button above, please copy and place the URL below in your web browser.&nbsp;</span><br><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">' . $url . '</span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#edf6f8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#edf6f8;background-color:#edf6f8;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#edf6f8;background-color:#edf6f8;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;padding-left:0px;padding-right:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td style="vertical-align:top;padding:0;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:10px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">This e-mail has been sent to ' . $email . '</span></p><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-bottom: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">Got any questions? We are always happy to help. write to us at </span><span style="color:#00B0FF;font-size:12px;">support@proddly.com</span></p></div></td></tr><tr><td align="center" style="background:transparent;font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://www.facebook.com/sharer/sharer.php?u=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/facebook.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://twitter.com/intent/tweet?url=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/twitter.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/instagram.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/linkedin.png" style="border-radius:50%;display:block;" width="17"></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td></tr></table><![endif]--></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:11px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;" data-testid="JsDQq14HuN0gD">© Proddly LLC</p></div></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></div>
        </body></html>',
    ]]];
    $response = $mj->post(Resources::$Email, ['body' => $body]);
    if ($response->success()) {
        return true;
    }
    return false;
}

function send_welcome_mail($name,$email){
    $mj = new \Mailjet\Client('a3369e2ddc659c7c9ab4f66e9a3cc1bf', 'd8bd68cf2dc82ef75fe422b563c58325', true, ['version' => 'v3.1']);
    $body = [
        'Messages' => [
            [
                'From' => ['Email' => "tools@proddly.com", 'Name' => "Proddly"],
                'To' => [['Email' => $email, 'Name' => $name]],
                'TemplateID' => 4146939,
                'TemplateLanguage' => true,
                'Subject' => "Welcome, we are delighted to have you onboard!",
                'Variables' => json_decode('{
                    "shp_name": "'.$name.'",
                    "shp_email": "'.$email.'"
                  }', true)
            ]
        ]
    ];
    $response = $mj->post(Resources::$Email, ['body' => $body]);
    $response->success() && var_dump($response->getData());
}

$router->map( 'GET', '/', function() {
	$ajax_url = AJAX_URL;
	include  ABSPATH.'/views/index.php';
});

$router->map( 'POST', '/v1/api/create-shoppers-account', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $insert_arrays = array
        (
            'shoppers_id' => $db->CleanDBData(rand(10000000,99999999)),
            'shp_fullname' => $db->CleanDBData($data->shp_fullname),
            'shp_email' => $db->CleanDBData($data->shp_email),
            'shp_phone' => $db->CleanDBData($data->shp_phone),
            'shp_password' => $db->CleanDBData(password_hash($data->shp_password, PASSWORD_DEFAULT)),
            'shp_created_on' => $db->CleanDBData(date("Y-m-d H:i:s")),
        );
        $check_email = $db->select("select * from tbl_shopper_account where shp_email='".$db->CleanDBData($data->shp_email)."' ");
        $check_shp_phone = $db->select("select * from tbl_shopper_account where shp_phone='".$db->CleanDBData(ucwords($data->shp_phone))."' ");

        if ($check_email > 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => "Shopper's user ".$db->CleanDBData($data->shp_email)." already exist",));
        } else {
            if ($check_shp_phone > 0) {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Shopper\'s user with mobile - '.ucwords($data->shp_phone).' already exist',));
            } else {
                $q0 = $db->Insert('tbl_shopper_account', $insert_arrays);
                if ($q0 > 0) {
                    $c_e = $db->select("select * from tbl_shopper_account where shp_email='".$db->CleanDBData($data->shp_email)."' ");
                    send_account_activation_mail($c_e[0]['shp_fullname'],$c_e[0]['shp_email'],$c_e[0]['shoppers_id']);

                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Shopper\'s account created successfully, Kindly proceed to login', "insert_id" => $q0));
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

$router->map( 'POST', '/v1/api/shoppers-login', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $ch0 = $db->select("select * from tbl_shopper_account where shp_email='".$db->CleanDBData($data->shp_email)."'");
        $ch1 = $db->select("select * from tbl_shopper_account where shp_active='No' and shp_email='".$db->CleanDBData($data->shp_email)."'");
        if ($ch0 <= 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'Shoppers email not found',));
        } else {
            if ($ch1 > 0) {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Account is inactive, contact proddly support',));
            } else {
                $password_used = $ch0[0]['shp_password'];
                if (password_verify($data->shp_password,$password_used)) {
                    $iss = 'localhost';
                    $iat = time();
                    $nbf = $iat; // issued after 1 secs of been created
                    $exp = $iat + (86400 * 1); // expired after 1day of been created
                    $aud = "shoppers_owner"; //the type of audience e.g. admin or client

                    $secret_key = getenv('SHOPPERS_SECRET');
                    $payload = array(
                        "iss"=>$iss,"iat"=>$iat,"nbf"=>$nbf,"exp"=>$exp,"aud"=>$aud,
                        "shoppers_id"=>$ch0[0]['shoppers_id'],
                        "shp_fullname"=>$ch0[0]['shp_fullname'],
                        "shp_email"=>$ch0[0]['shp_email'],
                        "shp_phone"=>$ch0[0]['shp_phone'],
                        "shp_active"=>$ch0[0]['shp_active'],
                        "shp_created_on"=>date("d-m-Y H:i:s", strtotime($ch0[0]['shp_created_on']))
                    );
                    $jwt = JWT::encode($payload, $secret_key, 'HS512');
                    http_response_code(200);
                    echo json_encode(array("status" => 'success', "jwt" => $jwt, "msg" => "Account logged in successfully",));
                } else {
                    http_response_code(400);
                    echo json_encode(array("status" => 'error', "msg" => "Incorrect password, try resetting your password."));
                }
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/v1/api/shoppers-forgot-password', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $ch0 = $db->select("select * from tbl_shopper_account where shp_email='".$db->CleanDBData($data->shp_email)."'");
        if ($ch0 <= 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'Shoppers email not found',));
        } else {
            $email = $data->shp_email;
            $first_name = $ch0[0]['shp_fullname'];

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
                    'Subject' => "Reset your Proddly Password",
                    'HTMLPart' => '
                    <!doctype html><html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><head><title>Reset your Proddly Password</title><!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]--><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style type="text/css">#outlook a { padding:0; }
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
                        .mj-column-per-100 { width:100% !important; max-width: 100%; }
                      }</style><style media="screen and (min-width:480px)">.moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">[owa] .mj-column-per-100 { width:100% !important; max-width: 100%; }</style><style type="text/css">@media only screen and (max-width:480px) {
                      table.mj-full-width-mobile { width: 100% !important; }
                      td.mj-full-width-mobile { width: auto !important; }
                    }</style></head><body style="word-spacing:normal;background-color:#F4F4F4;"><div style="background-color:#F4F4F4;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:10px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;padding-top:10px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:10px 0px 10px 0px;padding-top:10px;padding-right:0px;padding-bottom:10px;padding-left:0px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;"><tbody><tr><td style="width:600px;"><img alt="" height="auto" src="https://0m0ng.mjt.lu/tplimg/0m0ng/b/lsgjh/1k1p.jpeg" style="border:none;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="600"></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:18px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><h1 class="text-build-content" data-testid="8vJ3U63KHGEy" style="margin-top: 10px; margin-bottom: 10px; font-weight: normal;"><span style="font-family:Arial, sans-serif;font-size:18px;">Hi '.$first_name.'</span></h1></div></td></tr><tr><td align="left" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-top: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">This e-mail has been sent to you because you could not remember the password for your Proddly account. <b>No worries. We\'ve got you!</b></span></p><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">Please click the button below to reset your password.</span></p><p class="text-build-content" data-testid="Po4fth2asiVM" style="margin: 10px 0; margin-bottom: 10px;">&nbsp;</p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:0px;padding-top:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px 10px 25px;padding-right:25px;padding-left:25px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;"><tbody><tr><td align="center" bgcolor="#00b0ff" role="presentation" style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px 10px 25px;background:#00b0ff;" valign="middle"><a href="'.$url.'" style="display:inline-block;background:#00b0ff;color:#ffffff;font-family:Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px 10px 25px;mso-padding-alt:0px;border-radius:3px;" target="_blank"><span style="font-size:14px;">Reset Password</span></a></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"><tbody><tr><td align="left" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;"><p class="text-build-content" data-testid="iMnv3GwpxrUOb" style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">If you have any trouble clicking the button above, please copy and place the URL below in your web browser.&nbsp;</span><br><span style="color:#55575d;font-family:Arial;font-size:13px;line-height:22px;">&lt;'.$url.'&gt;</span></p></div></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#edf6f8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]--><div style="background:#edf6f8;background-color:#edf6f8;margin:0px auto;max-width:600px;"><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#edf6f8;background-color:#edf6f8;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;padding-left:0px;padding-right:0px;text-align:center;"><!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]--><div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td style="vertical-align:top;padding:0;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td align="center" style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:10px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">This e-mail has been sent to '.$email.'</span></p><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-bottom: 10px;" data-testid="qI2QyDAZOlf2-"><span style="font-size:12px;">Got any questions? We are always happy to help. write to us at </span><span style="color:#00B0FF;font-size:12px;">support@proddly.com</span></p></div></td></tr><tr><td align="center" style="background:transparent;font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;"><!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://www.facebook.com/sharer/sharer.php?u=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/facebook.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="https://twitter.com/intent/tweet?url=[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/twitter.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><a href="[[SHORT_PERMALINK]]" target="_blank"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/instagram.png" style="border-radius:50%;display:block;" width="17"></a></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td><td><![endif]--><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;"><tbody><tr><td style="padding:4px;vertical-align:middle;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;border-radius:50%;width:17;"><tbody><tr><td style="padding:0px 0px 0px 0px;font-size:0;height:17;vertical-align:middle;width:17;"><img height="17" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/linkedin.png" style="border-radius:50%;display:block;" width="17"></td></tr></tbody></table></td></tr></tbody></table><!--[if mso | IE]></td></tr></table><![endif]--></td></tr><tr><td align="center" style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;"><div style="font-family:Arial, sans-serif;font-size:11px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;"><p class="text-build-content" style="text-align: center; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;" data-testid="JsDQq14HuN0gD">© Proddly LLC</p></div></td></tr></tbody></table></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></td></tr></tbody></table></div><!--[if mso | IE]></td></tr></table><![endif]--></div>
                    </body></html>
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

$router->map( 'POST', '/v1/api/shoppers-reset-password', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $currentDate = date("U");
        if (isset($data->reset_selector) && !empty($data->reset_selector) && !empty($data->shp_password)) {
            $reset_selector = $data->reset_selector;
            $p_reset_q = $db->select("SELECT * FROM tbl_pwd_reset WHERE reset_selector='$reset_selector' AND reset_expires >= $currentDate");
            if ($p_reset_q > 0 ) {
                $reset_email = $p_reset_q[0]['reset_email'];
                $update_fields = array('shp_password' => password_hash($data->shp_password, PASSWORD_DEFAULT));
                $array_where = array('shp_email' => $db->CleanDBData($reset_email));
                $update_query = $db->Update('tbl_shopper_account', $update_fields, $array_where);
                if ($update_query > 0) {
                    $db->Qry("DELETE FROM tbl_pwd_reset WHERE reset_email='$reset_email'");
                    http_response_code(200);
                    echo json_encode(array('status' => 'success','msg'=>'Password successfully changed. Proceed to login'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error','msg'=>'Error while trying to reset your password, contact our proddly support for help.'));
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

$router->map( 'GET', '/shoppers/v1/api/global-products-search', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $whereSQL = '';

        if(!empty($_GET['search'])){
//            $whereSQL .= " and (sp.item_name like '%".$_GET['search']."%' or sp.brand LIKE '%".$_GET['search']."%' or si.store_address LIKE '%".$_GET['search']."%')";
            $whereSQL .= " and (sp.item_name like '%".$_GET['search']."%' or sp.brand LIKE '%".$_GET['search']."%')";
        }

        if(!empty($_GET['home_delivery']) && $_GET['home_delivery']=='Yes'){ $whereSQL .= " and (soi.str_home_delivery = 'Yes')";}
        if(!empty($_GET['pickup_service']) && $_GET['pickup_service']=='Yes'){ $whereSQL .= " and (soi.pickup_service = 'Yes')";}

        if(!empty($_GET['payment_option']) && $_GET['payment_option']== 'Credit/Debit Cards'){ $whereSQL .= " and (sbi.card_option = 'Yes')"; }
        if(!empty($_GET['payment_option']) && $_GET['payment_option']== 'Cash'){ $whereSQL .= " and (sbi.cash_option = 'Yes')"; }
        if(!empty($_GET['payment_option']) && $_GET['payment_option']== 'Electronic Bank Transfer'){ $whereSQL .= " and (sbi.transfer_option = 'Yes')"; }
        if(!empty($_GET['payment_option']) && $_GET['payment_option']== 'Cheque'){ $whereSQL .= " and (sbi.cheque_option = 'Yes')"; }

        if(!empty($_GET['price'] && $_GET['price'] =="Price: Low - high")){ $whereSQL .= " order by sp.unit_price ASC";}
        if(!empty($_GET['price'] && $_GET['price'] =="Price: High - low")){ $whereSQL .= " order by sp.unit_price DESC";}

        $q0 = $db->select(
            "select sp.*,si.*,soi.sell_where,soi.pickup_service,soi.str_home_delivery,
                            sbi.str_biz_phone,sbi.cash_option,sbi.card_option,sbi.transfer_option,sbi.cheque_option, 
                            pd.dis_unit_price,pd.dis_bulk_price,pd.discount_type,pd.dis_percentage,pd.dis_start_date,pd.dis_end_date,pd.dis_to_bulk_price
                            from tbl_store_products sp 
                            inner join tbl_store_info si on si.si_store_id=sp.sps_store_id 
                            inner join tbl_store_op_info soi on soi.soi_store_id=sp.sps_store_id 
                            inner join tbl_store_biz_info sbi on sbi.sbi_store_id=sp.sps_store_id 
                            LEFT join tbl_product_discounts pd on pd.dis_product_id=sp.product_id 
                            where (sp.str_prod_sno > 0) $whereSQL");

        $q0Count = $db->select(
            "SELECT COUNT(*) AS total FROM (
                                SELECT sp.*,si.*,soi.sell_where,soi.pickup_service,soi.str_home_delivery,
                                 sbi.str_biz_phone,sbi.cash_option,sbi.card_option,sbi.transfer_option,sbi.cheque_option, 
                                 pd.dis_unit_price,pd.dis_bulk_price,pd.discount_type,pd.dis_percentage,pd.dis_start_date,pd.dis_end_date,pd.dis_to_bulk_price
                                 from tbl_store_products sp 
                                 inner join tbl_store_info si on si.si_store_id=sp.sps_store_id 
                                 inner join tbl_store_op_info soi on soi.soi_store_id=sp.sps_store_id 
                                 inner join tbl_store_biz_info sbi on sbi.sbi_store_id=sp.sps_store_id 
                                 LEFT join tbl_product_discounts pd on pd.dis_product_id=sp.product_id 
                                where (sp.str_prod_sno > 0) $whereSQL ) sub"
                        );

        $q1 = $db->select("select * from tbl_store_products order by rand() limit 9");

        if($q0 > 0) {
            http_response_code(200);
            echo json_encode(array('status'=>'success','filter_products'=>$q0,'total_count'=>$q0Count[0],'msg' => 'found records'));
        } else {
            http_response_code(200);
            echo json_encode(array('status'=>'error', 'msg' => 'Sorry, we couldn’t find any results matching “'.$_GET['search'].'”','related_search' => $q1));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/shoppers/v1/api/fetch-product-by-id/[*:action]', function($product_id) {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $q0 = $db->select(
            "select sp.*,si.* from tbl_store_products sp inner join tbl_store_info si on si.si_store_id=sp.sps_store_id 
                            where sp.product_id='$product_id'");

        if($q0 > 0) {
            $prod_res = array();
            foreach ($q0 as $row) {
                $q2 = $db->select("select dis_unit_price,dis_bulk_price,discount_type,dis_percentage,
                                        dis_start_date,dis_end_date,dis_to_bulk_price
                                        from tbl_product_discounts where dis_product_id='".$row['product_id']."' and dis_status='Active'");
                $q3 = $db->select("select * from tbl_store_op_info where soi_store_id='".$row['sps_store_id']."'");
                $q4 = $db->select("select str_biz_phone,cash_option,card_option,transfer_option,cheque_option from tbl_store_biz_info 
                                                where sbi_store_id='".$row['sps_store_id']."'");
                $prod_res[] = array(
                    "store_id" => $row['sps_store_id'],
                    "product_id" => $row['product_id'],
                    "item_name" => $row['item_name'],
                    "brand" => $row['brand'],
                    "item_image" => $row['item_image'],
                    "item_spec" => $row['item_spec'],
                    "item_qty" => $row['item_qty'],
                    "featured" => $row['featured'],
                    "bulk_ord_qty" => $row['bulk_ord_qty'],
                    "item_vat" => $row['item_vat'],
                    "item_status" => $row['item_status'],
                    "item_subcategory" => $row['item_subcategory'],
                    "item_unit_price" => $row['unit_price'],
                    "item_bulk_price" => $row['bulk_price'],
                    "expiry_date" => $row['expiry_date'],
                    "extra_discount" => $q2==0?array():$q2,
                    "extra_delivery" => $q3==0?array():$q3,
                    "extra_payment_options" => $q4==0?array():$q4,
                );
            }
            http_response_code(200);
            echo json_encode(array('status'=>'success','filter_products'=>$prod_res,'msg' => 'found records'));
        } else {
            http_response_code(200);
            echo json_encode(array('status'=>'error', 'msg' => 'No product found'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/calculate-product-time-distance', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    $product_id = isset($data->product_id)?$db->CleanDBData($data->product_id):"";
    $point_1 = isset($data->point_1_lonlat)?$db->CleanDBData($data->point_1_lonlat):"";
    $point_2 = isset($data->point_2_lonlat)?$db->CleanDBData($data->point_2_lonlat):"";

    if (!empty($dev_key) && ($dev_key_res)) {
        if (!empty($product_id) && !empty($point_1) && !empty($point_2)) {
//            $q0 = $db->select(
//                "select sp.*,si.* from tbl_store_products sp inner join tbl_store_info si on si.si_store_id=sp.sps_store_id where sp.product_id='$product_id'"
//            );
            try {
                include_once ABSPATH.'/classes/GlobalApi.class.php';
                $res = (object)[];
                $route_url = "https://graphhopper.com/api/1/route";
                $api_key = "87e30598-64f2-4917-91e8-3653bea1b7c5";
                if (isset($api)) {
                    $resCar = $api->curlQueryGet("$route_url?point=$point_1&point=$point_2&vehicle=car&locale=en&calc_points=false&key=$api_key");
                    $resBike = $api->curlQueryGet("$route_url?point=$point_1&point=$point_2&vehicle=bike&locale=en&calc_points=false&key=$api_key");
                    $resFoot = $api->curlQueryGet("$route_url?point=$point_1&point=$point_2&vehicle=foot&locale=en&calc_points=false&key=$api_key");
                }
                if (!empty($resCar->paths) && !empty($resBike->paths) && !empty($resFoot->paths)) {
                    $car_distance = ($resCar->paths[0]->distance)/1000;
                    $car_timeSec = ($resCar->paths[0]->time)/1000;
                    $car_timeMin = ($car_timeSec/60);
                    if ($car_timeMin > 60){
                        $car_timeHour = $car_timeMin/60;
                    }
                    $carDetails = array
                    (
                        'distance' => number_format($car_distance,2)." km",
                        'time' => ($car_timeMin>60) ? number_format($car_timeHour,0).'hrs':number_format(ceil($car_timeMin),0).' min',
                    );

//                    $bike_distance = ($resBike->paths[0]->distance)/1000;
                    $bike_timeSec = ($resBike->paths[0]->time)/1000;
                    $bike_timeMin = $bike_timeSec/60;
                    if ($bike_timeMin > 60){
                        $bike_timeHour = $bike_timeMin/60;
                    }
                    $bikeDetails = array
                    (
                        'distance' => number_format($car_distance,2)." km",
                        'time' => ($bike_timeMin>60) ? number_format($bike_timeHour,0).'hrs':number_format(ceil($bike_timeMin),0).' min',
                    );

//                    $foot_distance = ($resFoot->paths[0]->distance)/1000;
                    $foot_timeSec = ($resFoot->paths[0]->time)/1000;
                    $foot_timeMin = $foot_timeSec/60;
                    if ($foot_timeMin > 60){
                        $foot_timeHour = $foot_timeMin/60;
                    }
                    $footDetails = array
                    (
                        'distance' => number_format($car_distance,2)." km",
                        'time' => ($foot_timeMin>60) ? number_format($foot_timeHour,0).'hrs':number_format(ceil($foot_timeMin),0).' min',
                    );

                    http_response_code(200);
                    echo json_encode(array('status' => 'success','car'=>$carDetails,'bike'=>$bikeDetails,'foot'=>$footDetails));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 0, 'msg' => 'Unable to verify payment #'.$data->ref_id.' transaction, contact support'));
                }
            } catch (Exception $ex) {
                http_response_code(400);
                echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Could not calculate distance, time"));
            }
        } else {
            http_response_code(200);
            echo json_encode(array('status'=>'error', 'msg' => 'One or more required field empty'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/activate-account', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $ch0 = $db->select("select * from tbl_shopper_account where shp_active='No' and shoppers_id='".$db->CleanDBData($data->shoppers_id)."'");
        if ($ch0 <= 0) {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'msg' => 'Expired link, contact proddly for more enquiry',));
        } else {
            $update_fields = array(
                'shp_active' => "Yes"
            );
            $array_where = array(
                'shoppers_id' => $db->CleanDBData($data->shoppers_id)
            );
            $q0 = $db->Update('tbl_shopper_account', $update_fields, $array_where);
            if ($q0 > 0) {
                $am = $db->select("select * from tbl_shopper_account where shoppers_id='".$db->CleanDBData($data->shoppers_id)."'");
                send_welcome_mail($am[0]['shp_fullname'],$am[0]['shp_email']);
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'Congratulation! account successfully activate.'));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Account cannot be activated, try again later.'));
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/add-item-to-cart', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $item_arr = array();
            $ch0 = $db->select("select * from tbl_shopper_account where shp_active='Yes' and shoppers_id='$shoppers_id'");
            $c_item = $db->select("select * from tbl_shopper_cart where shoppers_id='$shoppers_id'");
            if (!empty($c_item)) {
                $qty = 0;
                foreach ($c_item as $row) { array_push($item_arr,$row['item_id']); $qty += $row['qty']; }
            }
            if ($ch0 <= 0) {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Inactive account',));
            } else {
                $cart_array = json_decode(json_encode($data, true));

                $err = 0;
                $Qry = $db->Qry("delete from tbl_shopper_cart where shoppers_id ='$shoppers_id'");

                foreach ($cart_array as $i => $cart){
                    $insert_arrays = array
                    (
                        'shoppers_id' => $db->CleanDBData($shoppers_id),
                        'store_name' => $db->CleanDBData($cart->store_name),
                        'item_name' => $db->CleanDBData($cart->item_name),
                        'item_image' => $db->CleanDBData($cart->item_image),
                        'item_spec' => $db->CleanDBData($cart->item_spec),
                        'item_category' => $db->CleanDBData($cart->item_category),
                        'item_brand' => $db->CleanDBData($cart->item_brand),
                        'item_id' => $db->CleanDBData($cart->item_id),
                        'qty' => $db->CleanDBData($cart->qty),
                        'item_discount' => $db->CleanDBData($cart->item_discount),
                        'item_vat' => $db->CleanDBData($cart->item_vat),
                        'item_unit_price' => $db->CleanDBData($cart->item_unit_price),
                        'item_bulk_price' => $db->CleanDBData($cart->item_bulk_price),
                        'str_biz_phone' => $db->CleanDBData($cart->str_biz_phone),
                        'cartCreatedOn' => date("Y-m-d H:i:s")
                    );
                    if (in_array($cart->item_id,$item_arr)) {

                    } else {
                        $q0 = $db->Insert('tbl_shopper_cart', $insert_arrays);
                        if ($q0 > 0) $err = 0;
                        else $err = $err + 1;
                    }
                }
                if ($err == 0) {
                    $ch0 = $db->select("select * from tbl_shopper_cart where shoppers_id='$shoppers_id'");
                    $cart = $db->select("select count(distinct store_name) as total from tbl_shopper_cart where shoppers_id='$shoppers_id'");

                    http_response_code(200);
                    echo json_encode(array(
                        'status'=>'success',
                        'msg'=>'Cart updated successfully',
                        'cart_items'=>$ch0,
                        'total_store'=>$cart[0]['total'],
                        'total_qty'=>$qty
                    ));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to add item'));
                }
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/remove-item-from-cart', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $item_id = $db->CleanDBData($data->item_id);
            $Qry = $db->Qry("delete from tbl_shopper_cart where shoppers_id='$shoppers_id' and item_id='$item_id'");

            if($Qry == 1) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg' => 'item removed from cart'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to delete cart item'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/save-item', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;


            $item_arr = [];
            $s_item = $db->select("select * from tbl_shopper_saved_item where ssi_shoppers_id='$shoppers_id'");
            if (!empty($s_item)) {
                foreach ($s_item as $row) { array_push($item_arr,$row['ssi_product_id']); }
            }
            $item_id = $db->CleanDBData($data->item_id);
//            print_r($item_arr); die();
            $prod = $db->select(
                "select sp.*,si.*,sa.* from tbl_store_products sp 
                                inner join tbl_store_info si on si.si_store_id=sp.sps_store_id 
                                inner join tbl_store_account sa on sa.store_id=sp.sps_store_id 
                             where sp.product_id='$item_id'
                            ");
            if($prod > 0 && count($prod[0]) > 0) {
                if (in_array($item_id,$item_arr)) {
                    http_response_code(400);
                    echo json_encode(array('status'=>'error', 'msg'=>'Item already exist in saved list'));
                } else {
                    $insert_arrays = array
                    (
                        'ssi_shoppers_id' => $db->CleanDBData($shoppers_id),
                        'ssi_store_id' => $db->CleanDBData($prod[0]['sps_store_id']),
                        'ssi_product_id' => $db->CleanDBData($prod[0]['product_id']),
                        'ssi_store_name' => $db->CleanDBData($prod[0]['store_name']),
                        'ssi_item_name' => $db->CleanDBData($prod[0]['item_name']),
                        'ssi_item_image' => $db->CleanDBData($prod[0]['item_image']),
                        'ssi_item_spec' => $db->CleanDBData($prod[0]['item_spec']),
                        'ssi_item_category' => $db->CleanDBData($prod[0]['item_subcategory']),
                        'ssi_item_unit_price' => $db->CleanDBData($prod[0]['unit_price']),
                        'ssi_item_bulk_price' => $db->CleanDBData($prod[0]['bulk_price']),
                        'savedItemCreatedOn' => date("Y-m-d H:i:s")
                    );
                    $q0 = $db->Insert('tbl_shopper_saved_item', $insert_arrays);
                    if ($q0 > 0){
                        http_response_code(200);
                        echo json_encode(array('status'=>'success','msg'=>'Item successfully saved'));
                    } else {
                        http_response_code(400);
                        echo json_encode(array('status'=>'error', 'msg'=>'Unable to save item'));
                    }
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to get item'));
            }

            if (!empty($c_item)) {
                $qty = 0;
                foreach ($c_item as $row) { array_push($item_arr,$row['item_id']); $qty += $row['qty']; }
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/shoppers/v1/api/fetch-saved-item', function() {
//    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $whereSQL = '';
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            if(!empty($_GET['search'])){
                $whereSQL .= " and (ssi_item_name like '%".$_GET['search']."%' or ssi_item_category LIKE '%".$_GET['search']."%')";
            }

            if(!empty($_GET['sort_store'])){
                $whereSQL .= " and (ssi_store_id like '%".$_GET['sort_store']."%')";
            }

            $s_item = $db->select("select ssi.*,soi.google_biz_link from tbl_shopper_saved_item ssi 
                                                inner join tbl_store_op_info soi on soi.soi_store_id=ssi.ssi_store_id 
                                                where ssi_shoppers_id='$shoppers_id' $whereSQL");

            if ($s_item > 0){
                $saved = $db->select("select count(distinct ssi_store_id) as total from tbl_shopper_saved_item where ssi_shoppers_id='$shoppers_id' $whereSQL");
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg'=>'Done','saved_items'=>$s_item,'total_store'=>$saved[0]['total']));
            } else {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'msg' => 'No match items'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/remove-item-saved-item', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $item_id = $db->CleanDBData($data->item_id);
            $Qry = $db->Qry("delete from tbl_shopper_saved_item where ssi_shoppers_id='$shoppers_id' and ssi_product_id='$item_id'");

            if($Qry == 1) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg' => 'item removed from saved item'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to remove item'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/shoppers/v1/api/fetch-saved-item-stores', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $item_arr = [];
            $s_item = $db->select("select * from tbl_shopper_saved_item where ssi_shoppers_id='$shoppers_id'");
            if (!empty($s_item)) {
                foreach ($s_item as $row) { array_push($item_arr,$row['ssi_store_id']); }
            }
            $item_arr = array_unique($item_arr);
            $array = implode("','",$item_arr);
            $item = $db->select("select store_id,store_name from tbl_store_account where store_id in('".$array."') ");

            if ($item > 0){
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg'=>'Successful','stores'=>$item));
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Unable to get stores'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/shoppers/v1/api/fetch-lagos-areas', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $area = $db->select("select lar_name from tbl_lagos_areas");
        if ($area > 0){
            http_response_code(200);
            echo json_encode(array('status'=>'success','msg'=>'Record found','lagos_area'=>$area));
        } else {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'msg' => 'No record found'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/save-shoppers-review', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $rc = $db->select("select * from tbl_shoppers_review 
                                where sr_shoppers_id='$shoppers_id' and sr_score=".$data->sr_score." and sr_comment='".$data->sr_comment."'");
            if ($rc <= 0) {
                $insert_arrays = array
                (
                    'sr_shoppers_id' => $db->CleanDBData($shoppers_id),
                    'sr_store_id' => $db->CleanDBData($data->sr_store_id),
                    'sr_score' => $db->CleanDBData($data->sr_score),
                    'sr_comment' => $db->CleanDBData($data->sr_comment),
                    'sr_created_at' => date("Y-m-d H:i:s")
                );
                $q0 = $db->Insert('tbl_shoppers_review', $insert_arrays);
                if ($q0 > 0) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Review successfully saved'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'error', 'msg' => 'Unable to save review'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Review exist, unable to save review.'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/shoppers/v1/api/fetch-store-shoppers-review', function() {
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {

        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            if (isset($_GET['store_id']) && !empty($_GET['store_id'])) {
                $store_id = $_GET['store_id'];

                $rate = $db->select("select * from tbl_shoppers_review where sr_store_id='$store_id'");

                $count = $db->select("select count(*) as count from tbl_shoppers_review where sr_store_id='$store_id'");
                $s = $db->select("select sum(sr_score) as sum_score from tbl_shoppers_review where sr_store_id='$store_id'");
                $avg_score = ceil($s[0]['sum_score']/$count[0]['count']);
                if ($rate > 0) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'Record found','average_score'=>$avg_score,'rates' => $rate));
                } else {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'msg' => 'No record found'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg' => 'Missing Store id field'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'GET', '/shoppers/v1/api/fetch-store-categories/[*:action]', function($store_id){
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $sc = $db->select("select * from tbl_store_account where store_id='$store_id'");
            if (isset($sc[0]['str_cat_id']) && !empty($sc[0]['str_cat_id'])) {
                $store_cat_id = $sc[0]['str_cat_id'];

                $q0 = $db->select("select * from tbl_subcategories where cat_list like '%$store_cat_id%' and store_id='All' ");

                if ($q0 > 0) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'store_categories' => $q0, 'msg' => 'found records'));
                } else {
                    http_response_code(200);
                    echo json_encode(array('status' => 'error', 'msg' => 'no record found',));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("status" =>'error',"msg" => "Invalid store id parameter"));
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

$router->map( 'GET', '/shoppers/v1/api/fetch-store-featured-products/[*:action]', function($store_id) {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");
    if (!empty($dev_key) && ($dev_key_res)) {
        $whereSQL = '';

        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            if(!empty($_GET['search']) && isset($_GET['search'])){
                $whereSQL .= " and (sp.item_name like '%".$_GET['search']."%' or sp.brand LIKE '%".$_GET['search']."%')";
            }
            if(!empty($_GET['discount_type'])){
                $whereSQL .= " and (pd.discount_type = '".$_GET['discount_type']."')";
            }
            if(!empty($_GET['category_id'])){
                $whereSQL .= " and (sp.subcategory_id = '".$_GET['category_id']."')";
            }
            if(isset($_GET['price']) && !empty($_GET['price'] && $_GET['price'] =="Low - high")){ $whereSQL .= " order by sp.unit_price ASC";}
            if(isset($_GET['price']) && !empty($_GET['price'] && $_GET['price'] =="High - low")){ $whereSQL .= " order by sp.unit_price DESC";}

            $q0 = $db->select("select sp.*,sb.str_biz_name,pd.discount_type,pd.dis_percentage from tbl_store_products sp 
                                                inner join tbl_store_biz_info sb on sb.sbi_store_id = sp.sps_store_id 
                                                left join tbl_product_discounts pd on pd.dis_product_id = sp.product_id 
                                                where sp.sps_store_id='$store_id' and sp.featured='Yes' $whereSQL");
            $q0Count = $db->select("select count(*) as total from tbl_store_products sp
                                                inner join tbl_store_biz_info sb on sb.sbi_store_id = sp.sps_store_id 
                                                left join tbl_product_discounts pd on pd.dis_product_id = sp.product_id 
                                                where sp.sps_store_id='$store_id' and sp.featured='Yes' $whereSQL");
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

$router->map( 'POST', '/shoppers/v1/api/add-favourite-store', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;
//            'Notifications','Favourites'

            $rc = $db->select("select * from tbl_notify_favourites 
                                where nf_shoppers_id='$shoppers_id' and nf_store_id='".$data->nf_store_id."' and nf_type='Favourites'");
            if ($rc <= 0) {
                $insert_arrays = array
                (
                    'nf_shoppers_id' => $db->CleanDBData($shoppers_id),
                    'nf_store_id' => $db->CleanDBData($data->nf_store_id),
                    'nf_type' => $db->CleanDBData("Favourites"),
                    'nf_created_on' => date("Y-m-d H:i:s")
                );
                $q0 = $db->Insert('tbl_notify_favourites', $insert_arrays);
                if ($q0 > 0) {
                    http_response_code(200);
                    echo json_encode(array('status'=>'success','msg'=>'Favourite store saved.'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status'=>'error','msg'=>'Unable to save favourite store'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg'=>'Store already in favourite list.'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/remove-store-from-favourites', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $nf_store_id = $db->CleanDBData($data->nf_store_id);
            $Qry = $db->Qry("delete from tbl_notify_favourites where nf_shoppers_id='$shoppers_id' and nf_store_id='$nf_store_id'");

            if($Qry == 1) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg' => 'store removed from favourite list'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to remove store from favourite list'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/add-notifications-store', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;
//            'Notifications','Favourites'

            $rc = $db->select("select * from tbl_notify_favourites 
                                where nf_shoppers_id='$shoppers_id' and nf_store_id='".$data->nf_store_id."' and nf_type='Notifications'");
            if ($rc <= 0) {
                $insert_arrays = array
                (
                    'nf_shoppers_id' => $db->CleanDBData($shoppers_id),
                    'nf_store_id' => $db->CleanDBData($data->nf_store_id),
                    'nf_type' => $db->CleanDBData("Notifications"),
                    'nf_created_on' => date("Y-m-d H:i:s")
                );
                $insert_arrays_2 = array
                (
                    'nf_shoppers_id' => $db->CleanDBData($shoppers_id),
                    'nf_store_id' => $db->CleanDBData($data->nf_store_id),
                    'nf_type' => $db->CleanDBData("Favourites"),
                    'nf_created_on' => date("Y-m-d H:i:s")
                );
                $q0 = $db->Insert('tbl_notify_favourites', $insert_arrays);
                if ($q0 > 0) {
                    $q1 = $db->Insert('tbl_notify_favourites', $insert_arrays_2);
                    http_response_code(200);
                    echo json_encode(array('status'=>'success','msg'=>'store notification saved.'));
                } else {
                    http_response_code(400);
                    echo json_encode(array('status'=>'error','msg'=>'Unable to save favourite store'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'msg'=>'Store already in favourite list.'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

$router->map( 'POST', '/shoppers/v1/api/remove-store-from-notifications', function() {
    $data = json_decode(file_get_contents("php://input"));
    $db = app_db();
    $dev_key = getDeveloperKey();
    $dev_key_res = $db->select("select * from tbl_developer_keys where access_code='".$dev_key."' ");

    if (!empty($dev_key) && ($dev_key_res)) {
        $token = getBearerToken();
        try {
            $secret_key = getenv('SHOPPERS_SECRET');
            $decoded_data = JWT::decode($token, $secret_key, array('HS512'));
            $shoppers_id = $decoded_data->shoppers_id;

            $nf_store_id = $db->CleanDBData($data->nf_store_id);
            $Qry = $db->Qry("delete from tbl_notify_favourites where nf_shoppers_id='$shoppers_id' and nf_store_id='$nf_store_id'");

            if($Qry == 1) {
                http_response_code(200);
                echo json_encode(array('status'=>'success','msg' => 'store removed from notification list'));
            } else {
                http_response_code(400);
                echo json_encode(array('status'=>'error', 'msg' => 'unable to remove store from notification list'));
            }
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(array("status" => 0, "error" => $ex->getMessage(), "msg" => "Unauthorized request / Invalid token"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'msg' => 'Unauthorized dev! missing/invalid developer key'));
    }
});

?>