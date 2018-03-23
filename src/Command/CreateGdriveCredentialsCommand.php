<?php 

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Utils\Gdrive;


class CreateGdriveCredentialsCommand extends Command
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('app:create-credentials-gdrive')

        // the short description shown while running "php bin/console list"
        ->setDescription('Create Google Drive credentials with the Google Cloud Platform generated client secret.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows to create Google Drive credentials with the Google Cloud Platform generated client secret.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Gdrive();
        $client->getClient();
    }
}