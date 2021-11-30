<?php

namespace App\Command;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMailCommand extends Command
{
    protected function configure()
    {

        $this
                ->setName('bicoredemo:testmail')
                ->setDescription('Invia mail di test')
        ;
    }
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;

        // you *must* call the parent constructor
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {

        $message = new Swift_Message("Test");

        $message->setBody("Test email");
        $message->setFrom("bicorebundle@test.mail");
        $message->setTo("dst@test.mail");
        $filename = sys_get_temp_dir() . "/test.dat";

        $ptr = fopen($filename, 'wb');
        fwrite($ptr, pack("nvc*", 0x1234, 0x5678, 65, 66));
        fclose($ptr);

        $message->attach(Swift_Attachment::fromPath($filename));

        // Send the message
        try {
            $email = (new Email())
                    ->from("bicorebundle@test.mail")
                    ->to("dst@test.mail")
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject("Test email")
                    ->text("Text email")
                    ->html("Html mail");

            $this->mailer->send($email);
        } catch (\Swift_TransportException $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $e->getMessage();
        }
    }
}
