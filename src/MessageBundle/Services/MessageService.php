<?php

namespace MessageBundle\Services;

use AuthBundle\Entity\User;
use MessageBundle\Entity\MessageTemplate;
use MessageBundle\Services\Mailers\MailerService;

/**
 * Class MessageService
 * @package MessageBundle\Services
 */
class MessageService
{
    /**
     * @var MailerService
     */
    protected $mailerService;

    /**
     * MessageService constructor.
     * @param MailerService $mailerService
     */
    public function __construct(MailerService $mailerService)
    {
        $this->mailerService = $mailerService;
    }

    /**
     * Function for getting all variables from template
     *
     * @param $start
     * @param $end
     * @param $str
     *
     * @return array
     */
    private function getStringBetweenSymbols($start, $end, $str)
    {
        preg_match_all('/' . $start . '(.*?)' . $end . '/', $str, $matches);
        return $matches[1];
    }

    /**
     * Get allowed variables for replacing
     */
    public function getAllowVariables()
    {
        return [
            'username',
            'email',
        ];
    }

    /**
     * @param $variable - key for replacing
     *
     * @return bool
     */
    private function canReplaced ($variable)
    {
        return in_array($variable, $this->getAllowVariables());
    }

    /**
     * @param MessageTemplate $message
     * @param User $user
     *
     * @return string - message with replaced variables
     */
    protected function getMessage(MessageTemplate $message, User $user)
    {
        $template = $message->getTemplate();
        // get all variables from template
        $variables = $this->getStringBetweenSymbols('{{', '}}', $template);
        foreach ($variables as $variable) {
            if (!$this->canReplaced(trim($variable))) {
                continue;
            }
            $getter = 'get' . ucfirst(trim($variable));
            $template = str_replace('{{' . $variable . '}}', $user->$getter(), $template);
        }
        return $template;
    }

    /**
     * @param MessageTemplate $message
     * @param User $user
     *
     * @return bool|int
     */
    public function sendMessage(MessageTemplate $message, User $user)
    {
        if (!$user || !$message) {
            return false;
        }
        $this->mailerService->setMessage(
            $message->getSubject(),
            $user->getEmail(),
            $this->getMessage($message, $user)
        );
        return $this->mailerService->send();
    }
}
