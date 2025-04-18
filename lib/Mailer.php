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

  public function sendRegistrationCode(string $mailTo, int $tokenNumber): void {
    $this->mail->setFrom(SMTP_SENDER_MAIL, 'TrashRunner');
    $this->mail->addAddress($mailTo); 

    $this->mail->isHTML(true);
    $this->mail->Subject = 'TrashRunner - overovaci kod';
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

    $this->mail->send();
  }

  public function sendNotification(): void {
    if (ENABLE_MAIL_NOTIFICATIONS) {
      $this->mail->setFrom(SMTP_SENDER_MAIL, 'TrashRunner');
      $this->mail->addAddress(MAIL_NOTIFICATIONS); 

      $this->mail->isHTML(true);
      $this->mail->Subject = 'TrashRunner - notifikacia';
      $this->mail->Body = "
        Akcia: " . Request::getParam('page') . "</br>
        Zariadenie: " . Request::getParam('device_type'). "
      ";

      $this->mail->send();
    }
  }

}