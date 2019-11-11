
<?php
/**
 * author
 * description
 * 
 */
// include config
include 'config.php';

// load library
require 'phpmailer/PHPMailerAutoload.php';


// function clean text
function cleanSpecialCharacter($string)
{
  $string  = cleanText($string);
  $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
  $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

  return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

// function validate email string
function cleanText($string)
{
    $string = trim($string);
    $string = stripslashes($string);
    $string = htmlspecialchars($string);

    return $string;
}

// funtion send email out
function sendMailOut($emailTo, $emailFrom, $senderName, $subject, $message, $language, $infoEmail)
{
  // new instance
  $mail      = new PHPMailer(true);

  try
  {
    //$infoEmail          = 'info@cotafer.com';
    // config
    $emailTo = $infoEmail;
    $mail->isHTML(true);
    $mail->setFrom($emailFrom);
    $mail->addAddress($emailTo);
    $mail->addReplyTo($emailFrom);

    $mail->Subject  = 'Form Submission:' . $subject;
    $mail->Body    = '<h3> Name :' . $senderName . '<br> Email: ' . $emailFrom . '<br> Message: ' . $message . '</h3>';
    $mail->Username  = $emailTo;
    $mail->Password  = '';

    $mail->send();

    return '<div class="alert alert-success" role="alert">' . $language['email-function']['result-thanks-sent-mail'] . '</div>';
  }
  catch(phpmailerException $e)
  {
    return '<div class="alert alert-danger" role="alert">' . $language['email-function']['result-error-sent-mail'] . '</div>';
  }
  catch(Exception $e)
  {
    return '<div class="alert alert-danger" role="alert">' . $language['email-function']['result-error-sent-mail'] . '</div>';
  }
}

// declare variables
// can send out
$isOkayToSendEmailOut  = true;

$emailAddress          = '';
$emailSenderName       = '';
$emailMessage          = '';
$emailSubject          = '';
$emailSystemAddress    = '';
$emailStatusMessage    = '';

// validate token empty
if (isset($_POST['token']) && empty($_POST['token']))
{
  $emailStatusMessage   .= '<div class="alert alert-danger" role="alert">' . 'csrf token missing'. '</div>';
  $isOkayToSendEmailOut  = false;
}
else if  ($_POST['token'] != $_SESSION['token'])
{
  $emailStatusMessage   .= '<div class="alert alert-danger" role="alert">' . 'incorrect csrf token'. '</div>';
  $isOkayToSendEmailOut  = false;
}
else
{
  
  // sender name validation
  if (isset($_POST['name']) && !empty($_POST['name']) && $isOkayToSendEmailOut)
  {
    // lalidate name preg_match
    $emailSenderName  = $_POST['name'];

    // validate name
    if(preg_match("/[a-zA-Z'-]/", $emailSenderName))
    {
      $isOkayToSendEmailOut  = true;
    }
    else
    {
      $emailStatusMessage    .= $language['email-function']['name-error']. '<br/>';
      $isOkayToSendEmailOut  = false;
    }
  }
  else
  {
    $emailStatusMessage      .= $language['email-function']['name-error']. '<br/>';
    $isOkayToSendEmailOut    = false;
  } // check $_POST['name']

  
  // message validation
  if (isset($_POST['message']) && !empty($_POST['message']) && $isOkayToSendEmailOut)
  {
    // validate message
    $emailMessage  = $_POST['message'];

    // 
    if(preg_match("/^[a-zA-Z0-9']/", $emailMessage))
    {
      $isOkayToSendEmailOut  = true;
    }
    else
    {
      $emailStatusMessage   .= $language['email-function']['message-error'];
      $isOkayToSendEmailOut  = false;
    }
  }  
  else
  {
    $emailStatusMessage    .= $language['email-function']['message-error'] . '<br/>';
    $isOkayToSendEmailOut  = false;
  }// check $_POST['message']


  // email validation
  if (isset($_POST['email']) && !empty($_POST['email']) && $isOkayToSendEmailOut)
  {
    // validate email filter_var
    $emailAddress    = $_POST['email'];


// filter email
    if(preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,7})$^", $emailAddress))
    {
      $isOkayToSendEmailOut  = true;
    }
    else
    {
      $emailStatusMessage   .= $language['email-function']['email-error'] . '<br/>';
      $isOkayToSendEmailOut  = false;
    }
  }
  else
  {
    $emailStatusMessage    .= $language['email-function']['email-error'] . '<br/>';
    $isOkayToSendEmailOut  = false;
  }// check $_POST['email']

  // is okay to send out
  if ($isOkayToSendEmailOut)
  {
    $emailStatusMessage = sendMailOut($emailSystemAddress, $emailAddress, $emailSenderName, $emailSubject, $emailMessage, $language, $infoEmail);
  }
  else
  {
    $emailStatusMessage = '<div class="alert alert-danger" role="alert">' . $emailStatusMessage . '</div>';
  }
} // end of token