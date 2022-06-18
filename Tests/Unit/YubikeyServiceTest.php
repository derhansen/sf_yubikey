<?php

namespace Derhansen\SfYubikey\Tests\Unit;

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Derhansen\SfYubikey\Service\YubikeyService;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for class Derhansen\SfYubikey\Service\YubikeyService
 */
class YubikeyServiceTest extends UnitTestCase
{
    public function verifyHmacDataProvider()
    {
        $h = 'lHVE5KrfuhWg36MttJOwxWqa/AY=';
        $t = '2021-02-13T05:20:11Z0768';
        $otp = 'abcdefghabcdefghabcdefghabcdefghabcdefghabcd';
        $nonce = '098f6bcd4621d373cade4e832627b4f6';
        $status = 'OK';
        $clientKey = 'SomEraNd0MCliEnTK3y=';

        return [
            'valid response' => [
                'h=' . $h . "\n" . 't=' . $t . "\n" . 'otp=' . $otp . "\n" . 'nonce=' . $nonce . "\n" . 'status=' . $status,
                $clientKey,
                true,
            ],
            'wrong api key' => [
                'h=' . $h . "\n" . 't=' . $t . "\n" . 'otp=' . $otp . "\n" . 'nonce=' . $nonce . "\n" . 'status=' . $status,
                'invalid',
                false,
            ],
            'status modified' => [
                'h=' . $h . "\n" . 't=' . $t . "\n" . 'otp=' . $otp . "\n" . 'nonce=' . $nonce . "\n" . 'status=' . 'NOK',
                $clientKey,
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider verifyHmacDataProvider
     */
    public function verifyHmacWorksAsExpected($response, $apiKey, $expected)
    {
        // Dummy extension settings
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sf_yubikey'] = [
            'yubikeyClientId' => 'test',
            'yubikeyClientKey' => 'test',
            'yubikeyApiUrls' => 'api1,api2',
        ];

        $yubikeyAuthService = GeneralUtility::makeInstance(
            YubikeyService::class,
            GuzzleClientFactory::getClient(),
            new RequestFactory()
        );
        self::assertSame($expected, $yubikeyAuthService->verifyHmac($response, $apiKey));
    }
}
