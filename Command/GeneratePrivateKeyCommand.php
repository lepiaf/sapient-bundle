<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\Command;

use ParagonIE\Sapient\CryptographyKeys\{
    SigningSecretKey
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GeneratePrivateKeyCommand extends Command
{
    protected function configure()
    {
        $this->setName('lepiaf:sapient:generate-signing-key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $signSecret = SigningSecretKey::generate();

        $io = new SymfonyStyle($input, $output);
        $io->table(
            ['Key type', 'Value'],
            [
                ['Secret key', $signSecret->getString()],
                ['Public key', $signSecret->getPublickey()->getString()],
            ]
        );
    }
}
