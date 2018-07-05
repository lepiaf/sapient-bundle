<?php
declare(strict_types=1);

namespace lepiaf\SapientBundle\Command;

use ParagonIE\Sapient\CryptographyKeys\{
    SigningSecretKey,
    SealingSecretKey
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateConfigurationCommand extends Command
{
    protected function configure()
    {
        $this->setName('sapient:configure')
            ->setDescription('Generate configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $signingKey = SigningSecretKey::generate();
        $sealKey = SealingSecretKey::generate();
        $output->writeln('Copy generated configuration below into sapient.yml configuration file.');
        $output->writeln(<<<CONFIG
sapient:
    sign:
        public: '{$signingKey->getPublickey()->getString()}'
        private: '{$signingKey->getString()}'
        host: ~
    seal:
        public: '{$sealKey->getPublickey()->getString()}'
        private: '{$sealKey->getString()}'
    sealing_public_keys: ~
    verifying_public_keys: ~

CONFIG
        );
    }
}
