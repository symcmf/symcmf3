<?php

namespace MessageBundle\Services\Mailers;

use Swift_Mailer;
use Swift_Message;
use Swift_Mime_Message;

/**
 * Class MailerService
 * @package EmailBundle\Services
 */
class MailerService
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Swift_Mime_Message
     */
    private $message;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $fromEmail;

    /**
     * MailerService constructor.
     *
     * @param $mailer
     * @param $fromEmail
     * @param array|null $parameters
     */
    public function __construct($mailer, $fromEmail, array $parameters = null)
    {
        $this->mailer = $mailer;
        $this->fromEmail = $fromEmail;
        $this->parameters = $parameters;
    }

    /**
     * @param $subject
     * @param $toEmail
     * @param $body
     */
    public function setMessage($subject, $toEmail, $body)
    {
        $this->message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->fromEmail)
            ->setTo($toEmail)
            ->setBody($body);
    }

    /**
     * @return Swift_Mime_Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Function for sending message
     */
    public function send()
    {
        $message = $this->getMessage();
        return $this->mailer->send($message);
    }
}
