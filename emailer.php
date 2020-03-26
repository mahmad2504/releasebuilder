<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Emailer
{
	function __construct()
	{
		$this->mail = new PHPMailer(true);	
		$this->mail->isSMTP();     
		$this->mail->Host = 'localhost';
		$this->mail->SMTPAuth = false;
		$this->mail->SMTPAutoTLS = false; 
		$this->mail->Port = 25; 
		$this->mail->Username   = 'release-bot@mentorg.com'; 
		$this->mail->setFrom('release-bot@mentorg.com', 'Release Bot');
	}
	function SendMRCApprovalEmail($releases,$zipfile)
	{
		
		$this->mail->addAddress('mumtaz_ahmad@mentor.com');     // Add a recipient
		$this->mail->addReplyTo('mumtaz_ahmad@mentor.com', 'Information');
		$this->mail->addAttachment($zipfile);    // Optional name
		 // Content
		$this->mail->isHTML(true);                                  // Set email format to HTML
		
		foreach($releases as $release)
		{
			$this->mail->Subject = 'MRC PLM approval ['.substr($release->name,1).']';
			$msg = '<h3>'.substr($release->name,1).'</h3>';
			break;
		}
		$msg .= '<table style="border: 1px solid black;">';
		$msg .= '<tr style="border: 1px solid black;">';
		$msg .= '<th style="border: 1px solid black;">';
					$msg .= 'Release#';
				$msg .= '</th>';
				$msg .= '<th style="border: 1px solid black;">';
					$msg .= 'Release Name';
				$msg .= '</th>';
		$msg .= '</tr>';
		foreach($releases as $release)
		{
			$msg .= '<tr style="border: 1px solid black;">';
				$msg .= '<td style="border: 1px solid black;">';
					$msg .= $release->number;
				$msg .= '</td>';
				$msg .= '<td style="border: 1px solid black;">';
					$msg .= $release->version;
				$msg .= '</td>';
			$msg .= '</tr>';
		}
		$msg .= '</table>';
		$msg .= '<p>Please find all MRC(s) in attached zip file</p>';
		
		$this->mail->Body= $msg;
		$this->mail->AltBody ='<p>Please find all MRC(s) in attached zip file</p>';
		$this->mail->send();
		echo 'MRC Approval mail  sent';
	}

}