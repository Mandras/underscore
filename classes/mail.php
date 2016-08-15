<?php

require_once(__DIR__ . '/../api/swiftmailer/swift_required.php');

class mail {
    public static $encoding = 'iso-8859-2';

    public static $from_email = '';
    public static $from_name = '';
    public static $subject = '';
    public static $message = '';
    public static $replyto = '';
    public static $files = array();
    public static $bcc = array();
    public static $cc = array();
    public static $to = array();

    public static function send() {
        $swift_transport = Swift_SmtpTransport::newInstance(_::$mail_smtp, _::$mail_port)
            ->setUsername(_::$mail_username)
            ->setPassword(_::$mail_password);

        if (empty(self::$to) || empty(self::$message) || empty(self::$from_email) || empty(self::$from_name))
            return (false);

        if (self::$message != strip_tags(self::$message)) $type = 'text/html';
        else $type = 'text/plain';

        $swift_message = Swift_Message::newInstance();

        $swift_message->setSubject(self::$subject);

        $swift_message->setTo(self::$to);

        $swift_message->setBody(self::$message, $type, self::$encoding);

        if (!empty(self::$cc)) $swift_message->setCc(self::$cc);

        if (!empty(self::$bcc)) $swift_message->setBcc(self::$bcc);

        if (!empty(self::$replyto)) $swift_message->setReplyTo(self::$replyto);

        if (!empty(self::$from_email) && !empty(self::$from_name))
            $swift_message->setFrom(array(self::$from_email => self::$from_name));

        if (!empty($files)) {
            foreach ($files as $file) {
                if (file_exists(__DIR__ . '/../' . $file))
                    $swift_message->attach(Swift_Attachment::fromPath(__DIR__ . '/../' . $file));
            }
        }

        $swift_mailer = Swift_Mailer::newInstance($swift_transport);

        $return = $swift_mailer->send($swift_message);

        self::$from_email = '';
        self::$from_name = '';
        self::$subject = '';
        self::$message = '';
        self::$replyto = '';
        self::$files = array();
        self::$bcc = array();
        self::$cc = array();
        self::$to = array();

        return ($return);
    }
}

?>