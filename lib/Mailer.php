<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Mailer {

  public $mail = null;

  public function __construct() {
    $this->mail = new PHPMailer(true);

    try {
      //$this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
      $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  
      $this->mail->isSMTP();
      $this->mail->SMTPAuth = true;
      $this->mail->Host = SMTP_HOST;
      $this->mail->Port = SMTP_PORT;
      $this->mail->Username = SMTP_USERNAME;
      $this->mail->Password = SMTP_PASSWORD;
    } catch (Exception $e) {
      echo "{$this->mail->ErrorInfo}";
    }
  }

  public function sendRegistrationCode(string $mailTo, int $tokenNumber) {
    $this->mail->setFrom(SMTP_SENDER_MAIL, 'TrashRunner');
    $this->mail->addAddress($mailTo); 

    $this->mail->isHTML(true);
    $this->mail->Subject = 'ČistéSlovensko - potvrdzovací kód';
    $this->mail->Body = "
    <!DOCTYPE html>
    <html lang='en' xmlns='http://www.w3.org/1999/xhtml' xmlns:o='urn:schemas-microsoft-com:office:office'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width,initial-scale=1'>
      <meta name='x-apple-disable-message-reformatting'>
      <title></title>
      <style>
        table, td, div, h1, p {font-family: Arial, sans-serif;}
      </style>
    </head>
    <body style='margin:0;padding:0;'>
      <table role='presentation' style='border-radius:20px;width:100%;border-collapse:collapse;background:#ffffff;'>
        <tr>
          <td align='center'>
            <table role='presentation' style='border-radius:20px;width:602px;border:1px solid #cccccc;border-spacing:0;text-align:left;'>
              <tr>
                <td align='center' style='border-top-left-radius:20px;border-top-right-radius:20px;padding:40px 0 30px 0;background: linear-gradient(#4f86f7, #4feff7);'>
                  <img src='" . ROOT_URL . "/images/logo.png' alt='' width='300' style='height:auto;display:block;' />
                </td>
              </tr>
              <tr>
                <td style='padding:36px 30px 42px 30px;text-align:center;font-size:60px;letter-spacing: .7rem;'>
                  " . $tokenNumber . "
                </td>
              </tr>
              <tr>
                <td style='border-bottom-left-radius:20px;border-bottom-right-radius:20px;padding:30px;background: linear-gradient(#4feff7, #4f86f7);'>
                  <table role='presentation' style='border-radius:20px;width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;'>
                    <tr>
                      <td style='padding:0;width:50%;' align='left'>
                        <p style='margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;'>
                          &reg; TrashRunner - Čisté Slovensko<br/>
                        </p>
                      </td>
                      <td style='padding:0;width:50%;' align='right'>
                        <table role='presentation' style='border-collapse:collapse;border:0;border-spacing:0;'>
                          <tr>
                            <td style='padding:0 0 0 10px;width:38px;'>
                              <a href='http://www.twitter.com/' style='color:#ffffff;'><img src='https://assets.codepen.io/210284/tw_1.png' alt='Twitter' width='38' style='height:auto;display:block;border:0;' /></a>
                            </td>
                            <td style='padding:0 0 0 10px;width:38px;'>
                              <a href='http://www.facebook.com/' style='color:#ffffff;'><img src='https://assets.codepen.io/210284/fb_1.png' alt='Facebook' width='38' style='height:auto;display:block;border:0;' /></a>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>



    ";
    //$this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $this->mail->send();
  }

  public function test() {
    $this->mail->setFrom(SMTP_SENDER_MAIL);
    $this->mail->addAddress('holespato@gmail.com'); 

    $this->mail->isHTML(true);
    $this->mail->Subject = 'Here is the subject';
    $this->mail->Body    = "
      <!DOCTYPE HTML PUBLIC>
      <html xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'>
      <head>
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta name='x-apple-disable-message-reformatting'>
        <!--[if !mso]><!--><meta http-equiv='X-UA-Compatible' content='IE=edge'><!--<![endif]-->
        <title></title>
        
        <style type='text/css'>
            @media only screen and (min-width: 620px) {
        .u-row {
          width: 600px !important;
        }
        .u-row .u-col {
          vertical-align: top;
        }

        .u-row .u-col-100 {
          width: 600px !important;
        }

      }

      @media (max-width: 620px) {
        .u-row-container {
          max-width: 100% !important;
          padding-left: 0px !important;
          padding-right: 0px !important;
        }
        .u-row .u-col {
          min-width: 320px !important;
          max-width: 100% !important;
          display: block !important;
        }
        .u-row {
          width: calc(100% - 40px) !important;
        }
        .u-col {
          width: 100% !important;
        }
        .u-col > div {
          margin: 0 auto;
        }
        }
      body {
        margin: 0;
        padding: 0;
      }

      table,
      tr,
      td {
        vertical-align: top;
        border-collapse: collapse;
      }

      p {
        margin: 0;
      }

      .ie-container table,
      .mso-container table {
        table-layout: fixed;
      }

      * {
        line-height: inherit;
      }

      a[x-apple-data-detectors='true'] {
        color: inherit !important;
        text-decoration: none !important;
      }

      table, td { color: #000000; } #u_body a { color: #0000ee; text-decoration: underline; } @media (max-width: 480px) { #u_content_image_1 .v-container-padding-padding { padding: 30px 10px 10px 30px !important; } #u_content_image_1 .v-src-width { width: auto !important; } #u_content_image_1 .v-src-max-width { max-width: 35% !important; } #u_content_image_1 .v-text-align { text-align: left !important; } #u_content_heading_1 .v-container-padding-padding { padding: 10px 10px 30px 30px !important; } #u_content_heading_1 .v-font-size { font-size: 60px !important; } #u_content_heading_1 .v-text-align { text-align: left !important; } #u_content_divider_1 .v-container-padding-padding { padding: 10px 0px 30px !important; } }
          </style>
        
        

      <!--[if !mso]><!--><link href='https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap' rel='stylesheet' type='text/css'><link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap' rel='stylesheet' type='text/css'><!--<![endif]-->

      </head>

      <body class='clean-body u_body' style='margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #ecf0f1;color: #000000'>
        <!--[if IE]><div class='ie-container'><![endif]-->
        <!--[if mso]><div class='mso-container'><![endif]-->
        <table id='u_body' style='border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #ecf0f1;width:100%' cellpadding='0' cellspacing='0'>
        <tbody>
        <tr style='vertical-align: top'>
          <td style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
          <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td align='center' style='background-color: #ecf0f1;'><![endif]-->
          

      <div class='u-row-container' style='padding: 0px;background-color: transparent'>
        <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;'>
          <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
            <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: transparent;'><![endif]-->
            
      <!--[if (mso)|(IE)]><td align='center' width='600' style='background-color: #000000;width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;' valign='top'><![endif]-->
      <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
        <div style='background-color: #000000;height: 100%;width: 100% !important;'>
        <!--[if (!mso)&(!IE)]><!--><div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;'><!--<![endif]-->
        
      <table style='font-family:\"Montserrat\",sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
        <tbody>
          <tr>
            <td class='v-container-padding-padding' style='overflow-wrap:break-word;word-break:break-word;padding:23px 10px 10px;font-family:\"Montserrat\",sans-serif;' align='left'>
              
        <div class='v-text-align' style='color: #f1eeee; line-height: 140%; text-align: left; word-wrap: break-word;'>
          <p style='font-size: 14px; line-height: 140%;'><span style='font-size: 18px; line-height: 25.2px;'>    Čisté</span><strong><span style='font-size: 18px; line-height: 25.2px;'>Slovensko</span></strong></p>
        </div>

            </td>
          </tr>
        </tbody>
      </table>

      <table id='u_content_image_1' style='font-family:\"Montserrat\",sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
        <tbody>
          <tr>
            <td class='v-container-padding-padding' style='overflow-wrap:break-word;word-break:break-word;padding:43px 10px 10px 30px;font-family:\"Montserrat\",sans-serif;' align='left'>
              
      <table width='100%' cellpadding='0' cellspacing='0' border='0'>
        <tr>
          <td class='v-text-align' style='padding-right: 0px;padding-left: 0px;' align='left'>
            
            <img align='left' border='0' src='" . ROOT_URL . "/images/mail/image-6.png' alt='image' title='image' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 19%;max-width: 106.4px;' width='106.4' class='v-src-width v-src-max-width'/>
            
          </td>
        </tr>
      </table>

            </td>
          </tr>
        </tbody>
      </table>

      <table id='u_content_heading_1' style='font-family:\"Montserrat\",sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
        <tbody>
          <tr>
            <td class='v-container-padding-padding' style='overflow-wrap:break-word;word-break:break-word;padding:30px 10px 30px 30px;font-family:\"Montserrat\",sans-serif;' align='left'>
              
        <h1 class='v-text-align v-font-size' style='margin: 0px; color: #ffffff; line-height: 120%; text-align: left; word-wrap: break-word; font-weight: normal; font-family: \"Open Sans\",sans-serif; font-size: 75px;'>
          <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div>
      <div><strong>Zadajte tento kód do aplikácie</strong></div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
      </div>
        </h1>

            </td>
          </tr>
        </tbody>
      </table>

      <table id='u_content_divider_1' style='font-family:\"Montserrat\",sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
        <tbody>
          <tr>
            <td class='v-container-padding-padding' style='overflow-wrap:break-word;word-break:break-word;padding:10px 0px 30px 30px;font-family:\"Montserrat\",sans-serif;' align='left'>
              
        <table height='0px' align='left' border='0' cellpadding='0' cellspacing='0' width='60%' style='border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 2px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%'>
          <tbody>
            <tr style='vertical-align: top'>
              <td style='word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%'>
                <span>&#160;</span>
              </td>
            </tr>
          </tbody>
        </table>

            </td>
          </tr>
        </tbody>
      </table>

      <table style='font-family:\"Montserrat\",sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
        <tbody>
          <tr>
            <td class='v-container-padding-padding' style='overflow-wrap:break-word;word-break:break-word;padding:13px 10px 36px;font-family:\"Montserrat\",sans-serif;' align='left'>
              
        <div class='v-text-align' style='color: #ffffff; line-height: 140%; text-align: left; word-wrap: break-word;'>
          <p style='font-size: 14px; line-height: 140%; text-align: center;'><span style='font-size: 36px; line-height: 50.4px;'>0  3  5  7</span></p>
        </div>

            </td>
          </tr>
        </tbody>
      </table>

        <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
        </div>
      </div>
      <!--[if (mso)|(IE)]></td><![endif]-->
            <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
          </div>
        </div>
      </div>



      <div class='u-row-container' style='padding: 0px;background-color: transparent'>
        <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;'>
          <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
            <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: transparent;'><![endif]-->
            
      <!--[if (mso)|(IE)]><td align='center' width='600' style='background-color: #ffffff;width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;' valign='top'><![endif]-->
      <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
        <div style='background-color: #ffffff;height: 100%;width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;'>
        <!--[if (!mso)&(!IE)]><!--><div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;'><!--<![endif]-->
        
      <table style='font-family:\"Montserrat\",sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
        <tbody>
          <tr>
            <td class='v-container-padding-padding' style='overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:\"Montserrat\",sans-serif;' align='left'>
              
      <div align='center'>
        <div style='display: table; max-width:184px;'>
        <!--[if (mso)|(IE)]><table width='184' cellpadding='0' cellspacing='0' border='0'><tr><td style='border-collapse:collapse;' align='center'><table width='100%' cellpadding='0' cellspacing='0' border='0' style='border-collapse:collapse; mso-table-lspace: 0pt;mso-table-rspace: 0pt; width:184px;'><tr><![endif]-->
        
          
          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 5px;' valign='top'><![endif]-->
          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 5px'>
            <tbody><tr style='vertical-align: top'><td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
              <a href='https://www.facebook.com/profile.php?id=100087583057328' title='Facebook' target='_blank'>
                <img src='" . ROOT_URL . "/images/mail/image-1.png' alt='Facebook' title='Facebook' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
              </a>
            </td></tr>
          </tbody></table>
          <!--[if (mso)|(IE)]></td><![endif]-->
          
          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 5px;' valign='top'><![endif]-->
          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 5px'>
            <tbody><tr style='vertical-align: top'><td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
              <a href='https://www.youtube.com/channel/UC9Rm0m6vML6CcAQJUKOyiZA' title='YouTube' target='_blank'>
                <img src='" . ROOT_URL . "/images/mail/image-5.png' alt='YouTube' title='YouTube' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
              </a>
            </td></tr>
          </tbody></table>
          <!--[if (mso)|(IE)]></td><![endif]-->
          
          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 5px;' valign='top'><![endif]-->
          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 5px'>
            <tbody><tr style='vertical-align: top'><td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
              <a href='https://www.instagram.com/cisteslovensko.appka/' title='Instagram' target='_blank'>
                <img src='" . ROOT_URL . "/images/mail/image-3.png' alt='Instagram' title='Instagram' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
              </a>
            </td></tr>
          </tbody></table>
          <!--[if (mso)|(IE)]></td><![endif]-->
          
          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 5px;' valign='top'><![endif]-->
          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 5px'>
            <tbody><tr style='vertical-align: top'><td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
              <a href='https://www.tiktok.com/@cisteslovensko' title='TikTok' target='_blank'>
                <img src='" . ROOT_URL . "/images/mail/image-2.png' alt='TikTok' title='TikTok' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
              </a>
            </td></tr>
          </tbody></table>
          <!--[if (mso)|(IE)]></td><![endif]-->
          
          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 0px;' valign='top'><![endif]-->
          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 0px'>
            <tbody><tr style='vertical-align: top'><td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
              <a href='mailto:cisteslovensko.app@gmail.com' title='Email' target='_blank'>
                <img src='" . ROOT_URL . "/images/mail/image-4.png' alt='Email' title='Email' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
              </a>
            </td></tr>
          </tbody></table>
          <!--[if (mso)|(IE)]></td><![endif]-->
          
          
          <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
        </div>
      </div>

            </td>
          </tr>
        </tbody>
      </table>

        <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
        </div>
      </div>
      <!--[if (mso)|(IE)]></td><![endif]-->
            <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
          </div>
        </div>
      </div>


          <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
          </td>
        </tr>
        </tbody>
        </table>
        <!--[if mso]></div><![endif]-->
        <!--[if IE]></div><![endif]-->
      </body>

      </html>
    ";
    //$this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $this->mail->send();
    echo 'Message has been sent';
  }
}