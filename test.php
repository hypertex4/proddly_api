<?php
// (A) LOAD MPDF
require "classes/vendor/autoload.php";
$mpdf = new \Mpdf\Mpdf();

$html = '
    <!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title>Your Proddly Invoice for March 2022</title>
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style type="text/css">
        #outlook a {
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        p {
            display: block;
            margin: 13px 0;
        }
    </style>
    <!--[if mso]>
        <noscript>
        <xml>
        <o:OfficeDocumentSettings>
          <o:AllowPNG/>
          <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
        </xml>
        </noscript>
        <![endif]-->
    <!--[if lte mso 11]>
        <style type="text/css">
          .mj-outlook-group-fix { width:100% !important; }
        </style>
        <![endif]-->
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Roboto:300,400,500,700);
        @import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
    </style>
    <!--<![endif]-->
    <style type="text/css">
        @media only screen and (min-width:480px) {
            .mj-column-per-100 {
                width: 100% !important;
                max-width: 100%;
            }

            .mj-column-per-67 {
                width: 67% !important;
                max-width: 67%;
            }

            .mj-column-per-33 {
                width: 33% !important;
                max-width: 33%;
            }

            .mj-column-per-66 {
                width: 66% !important;
                max-width: 66%;
            }
        }
    </style>
    <style media="screen and (min-width:480px)">
        .moz-text-html .mj-column-per-100 {
            width: 100% !important;
            max-width: 100%;
        }

        .moz-text-html .mj-column-per-67 {
            width: 67% !important;
            max-width: 67%;
        }

        .moz-text-html .mj-column-per-33 {
            width: 33% !important;
            max-width: 33%;
        }

        .moz-text-html .mj-column-per-66 {
            width: 66% !important;
            max-width: 66%;
        }
    </style>
    <style type="text/css">
        [owa] .mj-column-per-100 {
            width: 100% !important;
            max-width: 100%;
        }

        [owa] .mj-column-per-67 {
            width: 67% !important;
            max-width: 67%;
        }

        [owa] .mj-column-per-33 {
            width: 33% !important;
            max-width: 33%;
        }

        [owa] .mj-column-per-66 {
            width: 66% !important;
            max-width: 66%;
        }
    </style>
    <style type="text/css">
        @media only screen and (max-width:480px) {
            table.mj-full-width-mobile {
                width: 100% !important;
            }

            td.mj-full-width-mobile {
                width: auto !important;
            }
        }
    </style>
</head>

<body style="word-spacing:normal;background-color:#F4F4F4;">
    <div style="background-color:#F4F4F4;">
        <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;padding-top:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                    style="vertical-align:top;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td align="center"
                                                style="font-size:0px;padding:0px 0px 0px 0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;word-break:break-word;">
                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                                    style="border-collapse:collapse;border-spacing:0px;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width:600px;"><img alt="" height="auto"
                                                                    src="https://0m0ng.mjt.lu/tplimg/0m0ng/b/1kgvx/00h7.png"
                                                                    style="border:none;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;"
                                                                    width="600"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:10px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;padding-top:10px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                    style="vertical-align:top;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td align="left"
                                                style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                    <h1 class="text-build-content"
                                                        style="line-height:18px;; margin-top: 10px; margin-bottom: 10px; font-weight: normal;"
                                                        data-testid="8vJ3U63KHGEy"><span
                                                            style="font-family:Roboto, Helvetica, Arial, sans-serif;font-size:17px;">Subscription
                                                            &nbsp;Invoice &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span><span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:13px;">January
                                                            5, 2022 INV0239183 [[<b>PAID/UNPAID]]</b> &nbsp;</span></h1>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;padding-left:0px;padding-right:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:right;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                    style="vertical-align:top;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td align="right"
                                                style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                <span
                                                    style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                    <span class="text-build-content"
                                                        style="text-align: right; margin: 10px 0; margin-top: 10px;"
                                                        data-testid="Z-Foks151"><span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:13px;"><b>INVOICE</b></span>
                                                    </span><br>
                                                    <span class="text-build-content"
                                                        style="text-align: right; margin: 10px 0;"
                                                        data-testid="Z-Foks151"><span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:13px;"><b>Proddly
                                                                LLC</b></span></span><br>
                                                    <span class="text-build-content"
                                                        style="text-align: right; margin: 10px 0;"
                                                        data-testid="Z-Foks151"><span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:13px;"><b>Lagos,
                                                                Nigeria</b></span></span><br>
                                                    <span class="text-build-content"
                                                        style="text-align: right; margin: 10px 0; margin-bottom: 10px;"
                                                        data-testid="Z-Foks151"><span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:13px;"><b>VAT
                                                                No: 24140517-0001</b></span></span><br>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;padding-top:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                    style="vertical-align:top;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td align="left"
                                                style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                    <p class="text-build-content" data-testid="HrXFMPKJp"
                                                        style="margin: 10px 0; margin-top: 10px;"><span
                                                            style="font-size:13px;"><b>BILL TO</b></span></p>
                                                    <p class="text-build-content" data-testid="HrXFMPKJp"
                                                        style="margin: 10px 0;"><span style="font-size:13px;"><b>Jack
                                                                Alli</b></span></p>
                                                    <p class="text-build-content" data-testid="HrXFMPKJp"
                                                        style="margin: 10px 0;"><span style="font-size:13px;">Atkins
                                                            Pharmacy Store,</span></p>
                                                    <p class="text-build-content" data-testid="HrXFMPKJp"
                                                        style="margin: 10px 0;"><span style="font-size:13px;">13,
                                                            Chukwumah street, yaba</span></p>
                                                    <p class="text-build-content" data-testid="HrXFMPKJp"
                                                        style="margin: 10px 0; margin-bottom: 10px;"><span
                                                            style="font-size:13px;">Lagos, Nigeria. &nbsp; &nbsp;</span>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:20px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                    style="vertical-align:top;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td align="left"
                                                style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                    <p class="text-build-content" data-testid="AscL6SIXX"
                                                        style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;">
                                                        <span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;"><b>Invoice
                                                                Details</b></span></p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:20px 0px 10px 0px;padding-bottom:10px;padding-left:0px;padding-right:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0;line-height:0;text-align:left;display:inline-block;width:100%;direction:ltr;vertical-align:top;">
                                <!--[if mso | IE]><table border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td style="vertical-align:top;width:402px;" ><![endif]-->
                                <div class="mj-column-per-67 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:67%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content"
                                                            style="line-height: 25px; margin: 10px 0; margin-top: 10px;"
                                                            data-testid="ZfvRRcu7r"><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;"><b>Description
                                                                    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                                    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                                    &nbsp; &nbsp; &nbsp; &nbsp;</b></span></p>
                                                        <p class="text-build-content"
                                                            style="line-height: 25px; margin: 10px 0;"
                                                            data-testid="ZfvRRcu7r"><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;">Monthly
                                                                Subscription &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                                &nbsp;&nbsp;</span></p>
                                                        <p class="text-build-content" data-testid="ZfvRRcu7r"
                                                            style="margin: 10px 0; margin-bottom: 10px;"><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;">FEBRUARY
                                                                - MARCH 2022</span></p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td><td style="vertical-align:top;width:198px;" ><![endif]-->
                                <div class="mj-column-per-33 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:33%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content" data-testid="0ojGn2wlW"
                                                            style="margin: 10px 0; margin-top: 10px;"><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;"><b>Amount</b></span>
                                                        </p>
                                                        <p class="text-build-content" data-testid="0ojGn2wlW"
                                                            style="margin: 10px 0; margin-bottom: 10px;"><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;">N23,700.00</span>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td></tr></table><![endif]-->
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;padding-top:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0;line-height:0;text-align:left;display:inline-block;width:100%;direction:ltr;vertical-align:top;">
                                <!--[if mso | IE]><table border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td style="vertical-align:top;width:396px;" ><![endif]-->
                                <div class="mj-column-per-66 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:66%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content"
                                                            style="text-align: right; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"
                                                            data-testid="m1tUtvlxo"><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;"><b>&nbsp;Subtotal</b></span>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td><td style="vertical-align:top;width:198px;" ><![endif]-->
                                <div class="mj-column-per-33 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:33%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content" data-testid="Msv8Vf4JR"
                                                            style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;">
                                                            <span
                                                                style="color:#55575d;font-family:Arial, Helvetica, sans-serif;font-size:13px;">&nbsp;</span><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;">N23,700.00</span>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td></tr></table><![endif]-->
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;padding-top:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0;line-height:0;text-align:left;display:inline-block;width:100%;direction:ltr;vertical-align:top;">
                                <!--[if mso | IE]><table border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td style="vertical-align:top;width:396px;" ><![endif]-->
                                <div class="mj-column-per-66 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:66%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content"
                                                            style="text-align: right; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"
                                                            data-testid="IwccxfDjzf"><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;"><b>VAT</b></span>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td><td style="vertical-align:top;width:198px;" ><![endif]-->
                                <div class="mj-column-per-33 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:33%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content" data-testid="d7Klv6eVMQ"
                                                            style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;">
                                                            <span
                                                                style="color:#55575d;font-family:Arial, Helvetica, sans-serif;font-size:13px;">&nbsp;</span><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;">N0.00</span>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td></tr></table><![endif]-->
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;padding-bottom:0px;padding-left:0px;padding-right:0px;padding-top:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0;line-height:0;text-align:left;display:inline-block;width:100%;direction:ltr;vertical-align:top;">
                                <!--[if mso | IE]><table border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td style="vertical-align:top;width:396px;" ><![endif]-->
                                <div class="mj-column-per-66 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:66%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content"
                                                            style="text-align: right; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"
                                                            data-testid="jOhsG41oay"><b>Total</b></p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td><td style="vertical-align:top;width:198px;" ><![endif]-->
                                <div class="mj-column-per-33 mj-outlook-group-fix"
                                    style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:33%;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                        style="vertical-align:top;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td align="left"
                                                    style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                    <div
                                                        style="font-family:Arial, sans-serif;font-size:14px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                        <p class="text-build-content" data-testid="9-2unqzbYo"
                                                            style="margin: 10px 0; margin-top: 10px; margin-bottom: 10px;">
                                                            <span
                                                                style="color:#55575d;font-family:Arial, Helvetica, sans-serif;font-size:13px;">&nbsp;</span><span
                                                                style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:14px;">N23,700.00</span>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]></td></tr></table><![endif]-->
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#ffffff;background-color:#ffffff;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:0px 0px 20px 0px;padding-left:0px;padding-right:0px;padding-top:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                    style="vertical-align:top;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td style="font-size:0px;word-break:break-word;">
                                                <div style="height:25px;line-height:25px;">&#8202;</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                                style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                    <p class="text-build-content"
                                                        style="line-height: 14px; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"
                                                        data-testid="iMnv3GwpxrUOb"><span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:13px;">Thank
                                                            you for being a PARTNER!</span></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                                style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                <div
                                                    style="font-family:Arial, sans-serif;font-size:13px;letter-spacing:normal;line-height:1;text-align:left;color:#000000;">
                                                    <p class="text-build-content"
                                                        style="line-height: 14px; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"
                                                        data-testid="YufRJqAft"><span
                                                            style="color:#55575d;font-family:Roboto, Helvetica, Arial, sans-serif;font-size:13px;">Your
                                                            Proddly Team.</span></p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#edf6f8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
        <div style="background:#edf6f8;background-color:#edf6f8;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#edf6f8;background-color:#edf6f8;width:100%;">
                <tbody>
                    <tr>
                        <td
                            style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;padding-left:0px;padding-right:0px;text-align:center;">
                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                    <tbody>
                                        <tr>
                                            <td style="vertical-align:top;padding:0;">
                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                                    width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td align="center"
                                                                style="font-size:0px;padding:0px 25px 0px 25px;padding-top:0px;padding-right:25px;padding-bottom:0px;padding-left:25px;word-break:break-word;">
                                                                <div
                                                                    style="font-family:Arial, sans-serif;font-size:18px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;">
                                                                    <p class="text-build-content"
                                                                        style="text-align: center; margin: 10px 0; margin-top: 10px;"
                                                                        data-testid="qI2QyDAZOlf2-"><span
                                                                            style="color:#000000;font-size:18px;">Grow
                                                                            with Proddly</span></p>
                                                                    <p class="text-build-content"
                                                                        style="text-align: center; margin: 10px 0; margin-bottom: 10px;"
                                                                        data-testid="qI2QyDAZOlf2-"><span
                                                                            style="font-size:12px;">Got any questions?
                                                                            We are always happy to help. write to us at
                                                                        </span><span
                                                                            style="color:#00B0FF;font-size:12px;">support@proddly.com</span>
                                                                    </p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="center"
                                                                style="font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;word-break:break-word;">
                                                                <div
                                                                    style="font-family:Arial, sans-serif;font-size:11px;letter-spacing:normal;line-height:22px;text-align:center;color:#000000;">
                                                                    <p class="text-build-content"
                                                                        style="text-align: center; margin: 10px 0; margin-top: 10px; margin-bottom: 10px;"
                                                                        data-testid="JsDQq14HuN0gD">© Proddly LLC</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]></td></tr></table><![endif]-->
    </div>
</body>

</html>
';
$mpdf->WriteHTML($html);
$mpdf->Output();

// (E3) SAVE TO FILE ON SERVER
// $mpdf->Output("demo.pdf");
