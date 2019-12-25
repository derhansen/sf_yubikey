<?php
namespace DERHANSEN\SfYubikey\Command;

/*
 * This file is part of the package DERHANSEN/SfYubikey.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CheckYubiKeyOtpCommand
 */
class CheckYubiKeyOtpCommand extends Command
{
    /**
     * Configuring the command options
     */
    public function configure()
    {
        $this
            ->setDescription('Checks the given OTP against the configured YubiKey endpoints')
            ->addArgument(
                'otp',
                InputArgument::REQUIRED,
                'The YubiKey OTP'
            );
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('sf_yubikey');
        $yubikeyAuth = GeneralUtility::makeInstance(
            \DERHANSEN\SfYubikey\YubikeyAuth::class,
            $extensionConfiguration
        );

        $otp = $input->getArgument('otp');
        if ($yubikeyAuth->checkOtp($otp) === true) {
            $io->success('OK: ' . $otp . ' has been successfully validated.');
        } else {
            $io->error($otp . '  could not be validated. Reasons: ' . implode(' / ', $yubikeyAuth->getErrors()));
        }
    }
}
