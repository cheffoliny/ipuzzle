<?php
require_once('swiftmailer/swift_required.php');
require_once('db_api/DBNotifications.class.php');
require_once('db_api/DBNotificationsEvents.class.php');

class InvoiceSigner
{
	private $client_email;
	private $invoicePDFPath;
	private $email_subject;
	private $email_content;
	private $id_client;
	private $id_invoice;
	private $result;
    private $mail_server = "";
    private $mail_from = "";
    private $mail_pass = "";

    public function __construct($nIDInvoice, $client_email, $invoicePDFPath,$email_subject, $email_content,$id_client)
	{

	    $oDBNotificationEvent = new DBNotificationsEvents();
        $aNotification = $oDBNotificationEvent->getNotificationEventForMail();

        $this->mail_server = $aNotification['mail_server'];
        $this->mail_from = $aNotification['mail_user_from'];
        $this->mail_pass = $aNotification['mail_user_pass'];

		$this->client_email = $client_email;
		$this->invoicePDFPath = $invoicePDFPath;
		$this->email_subject = $email_subject;
		$this->email_content = $email_content;
		$this->id_client = $id_client;
		$this->id_invoice = $nIDInvoice;
	}

	public function sign()
	{
		$file_name_with_full_path = realpath($this->invoicePDFPath);

        $result = $this->send_email($this->client_email, $file_name_with_full_path, $this->email_subject, $this->email_content);

        /*
		$post = array(
			'token' => $this->token,
			'client_email' => $this->client_email,
			'email_subject' => $this->email_subject,
			'email_content' => $this->email_content,
//			'file_content' => '@' . $file_name_with_full_path,
			'file_content' => new CurlFile($file_name_with_full_path, 'application/pdf'),
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->target_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		*/

		unlink($file_name_with_full_path);

		$this->logSignNotification($this->client_email,$this->id_client);
		$this->result = $result;
	}

	/**
	 * @return Array
	 */

	public function getResult()
	{
		return $this->result;
	}

	public function logSignNotification($targetMail,$clientID) {
		$oDBNotifications = new DBNotifications();

		$aData['id'] = 0;
		$aData['id_event'] = 10;
		$aData['status'] = 'sent';
		$aData['channel'] = 'mail';
		$aData['send_after'] = date('Y-m-d H:i:s');
		$aData['target'] = $targetMail;
		$aData['id_client'] = $clientID;
		$aData['id_by_operator'] = $this->id_invoice;
		$aData['additional_params'] = json_encode(['id_invoice'=> $this->id_invoice]);

		$oDBNotifications->update($aData);

	}

    public function send_email($client_email, $file_content, $email_subject, $email_content) {

        if (!strpos($client_email,';') === false) {
            $client_email = explode(';',$client_email);
        } else {
            $client_email = array($client_email);
        }

        $client_email_valid = array();

        foreach($client_email as $mail) {
            if (!filter_var(trim($mail), FILTER_VALIDATE_EMAIL)) {
                file_put_contents('invalid_emails.txt',$mail."\n",FILE_APPEND);
            } else {
                $client_email_valid[] = trim($mail);
            }
        }



        $transport = Swift_SmtpTransport::newInstance($this->mail_server, 25)
            ->setUsername($this->mail_from)
            ->setPassword($this->mail_pass);

        $mailer  = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance()
            ->setSubject($email_subject)
            ->setFrom($this->mail_from)
            ->setTo($client_email_valid);

        $content = $email_content;

        $message->setBody($content);
        $id_attachment = $message->attach(Swift_Attachment::fromPath($file_content));

        $result = $mailer->send($message);

        $message->detach($id_attachment);
        $msg    = $message->toString();

        return $result;
    }
}