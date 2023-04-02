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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
     * Execute the command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $otp = $input->getArgument('otp');
        if ($this->yubikeyService->verifyOtp($otp) === true) {
            $io->success('OK: ' . $otp . ' has been successfully validated.');
            return self::SUCCESS;
        }

        $io->error($otp . '  could not be validated. Reasons: ' . implode(' / ', $this->yubikeyService->getErrors()));
        return self::FAILURE;
    }
}
