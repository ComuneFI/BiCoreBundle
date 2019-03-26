<?php

namespace App\Command;

use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
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
    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;

        // you *must* call the parent constructor
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $message = new Swift_Message("Test");

        $message->setBody("Test email");
        $message->setFrom("bicorebundle@test.mail");
        $message->setTo("manzolo@libero.it");
        $filename = sys_get_temp_dir() . "/test.dat";

        $ptr = fopen($filename, 'wb');
        fwrite($ptr, pack("nvc*", 0x1234, 0x5678, 65, 66));
        fclose($ptr);

        $message->attach(Swift_Attachment::fromPath($filename));

        // Send the message
        try {
            $result = $this->mailer->send($message);
        } catch (\Swift_TransportException $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $e->getMessage();
        }
    }
}
