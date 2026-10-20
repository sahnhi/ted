<?php

namespace App\MessageHandler;

use App\Message\Mail;
use App\Services\Mail\MailerEmail;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MailHandler implements MessageHandlerInterface
{
    public function __construct(
        protected MailerEmail $mailer
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Mail $message)
    {
        $this->mailer->sendEmail($message->getType(), $message->getOrder(), $message->getData());
    }
}
