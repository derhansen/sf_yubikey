<?php
namespace Derhansen\SfYubikey\Command;

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Derhansen\SfYubikey\Service\YubikeyService;
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
    private YubikeyService $yubikeyService;

    public function __construct(YubikeyService $yubikeyService)
    {
        $this->yubikeyService = $yubikeyService;
        parent::__construct();
    }

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

        $otp = $input->getArgument('otp');
        if ($this->yubikeyService->verifyOtp($otp) === true) {
            $io->success('OK: ' . $otp . ' has been successfully validated.');
            return 0;
        } else {
            $io->error($otp . '  could not be validated. Reasons: ' . implode(' / ', $this->yubikeyService->getErrors()));
            return 1;
        }
    }
}
