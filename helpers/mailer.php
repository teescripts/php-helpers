<?php
namespace Teescripts\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail_path	=constKey("BASE")."library/php-mailer/";

if (is_dir($mail_path)) {
	include $mail_path.'Exception.php';
	include $mail_path.'PHPMailer.php';
	include $mail_path.'SMTP.php';
}

function email_send($array) {
	$mail_to	=arrayKey("to", $array);
	$mail_from	=arrayKey("from", $array);
	$mail_type	=arrayKey("type", $array, "smtp");

	$attach		=arrayKey("attach", $array);
	$subject	=arrayKey("subject", $array);
	$message	=arrayKey("text", $array);
	
	if (!$mail_from) {	
		$domain	=constKey("domain");
		$prefix	=arrayKey("prefix", $array);
		if (!$prefix) $prefix="no-reply";#info
		$sitename	=constKey("sitename");
		$mail_from	=$sitename." <{$prefix}@{$domain}>";
	}
	$mail_reply	=$mail_from;

	$handle = new PHPMailer(true);#

	if ($mail_type=="smtp") {
		$ssl_opt	=['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true];

		$smtp_port = 587;//465, 587
		$smtp_host = 'mail.teescripts.com';
		$smtp_user = 'scriptmail@teescripts.com';#mailboss
		$smtp_pass = 'gjJ7$.jjhh*JKHJH5@6786787/ko=joj';
		$mail_from	=$sitename." <{$smtp_user}>";

		$handle->isSMTP();
		$handle->Host       = $smtp_host;
		$handle->Port       = $smtp_port;
		$handle->Username   = $smtp_user;
		$handle->Password   = $smtp_pass;
		$handle->SMTPAuth   = true;
		$handle->SMTPSecure = 'tls';

		$handle->SMTPOptions =['ssl'=>$ssl_opt];
		$handle->ConfirmReadingTo=$smtp_user;
		#$handle->SMTPDebug = 2;
		#$mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";}
	}
	else {
		$handle->isSendmail();
	}

	try {
		$status	=1;
		$text	="Mail sent to {$mail_to}";

		$dest	=email_name($mail_to);
		$sender	=email_name($mail_from);
		$reply	=email_name($mail_reply);

		$handle->addReplyTo($reply["email"], $reply["name"]);
		$handle->setFrom($sender["email"], $sender["name"]);
		$handle->addAddress($dest["email"], $dest["name"]);

		#$handle->WordWrap=78;
		$handle->Subject    =$subject;
		$handle->AltBody    =strip_tags($message);

		//addReplyTo
		if ($attach) $handle->addAttachment($attach);

		$handle->msgHTML($message);
		$handle->isHTML(true);
		$handle->Send();
	} 
	catch (phpmailerException $e) {
		$status	=3;
		$text	=$e->getMessage();
	} 
	catch (Exception $e) {
		$status	=4;
		$text	=$e->getMessage();
	}
	$result	=[$status, $text];
	return $result;
}

function email_name($text) {
	$join	="<";
	$array	=explode($join, $text);
	$name	=arrayKey(0, $array);
	$email	=arrayKey(1, $array);
	if (!$email) {
		$email	=$name;
		$name	="";
	}
	$email	=ltrim($email, "<");
	$email	=rtrim($email, ">");
	$array	=["name"=>trim($name), "email"=>trim($email)];
	return $array;
}
