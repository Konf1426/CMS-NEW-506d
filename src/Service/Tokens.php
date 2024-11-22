<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;
use function count;
use function is_array;
use const FILTER_VALIDATE_EMAIL;

final readonly class Tokens
{
    public function __construct(
        #[Autowire(param: 'kernel.secret')]
        private string $secret,
    ) {
    }

    public function generateTokenForUser(string $email, DateTime $expire = new DateTime('+4 hours')): string
    {
        $info = [
            'email' => $email,
            'expire' => $expire->getTimestamp(),
        ];

        $encodedInfo = json_encode($info);
        if (false === $encodedInfo) {
            throw new RuntimeException('Failed to encode token information.');
        }

        $signedData = [
            $encodedInfo,
            $this->sign($encodedInfo),
        ];

        $encodedSignedData = json_encode($signedData);
        if (false === $encodedSignedData) {
            throw new RuntimeException('Failed to encode signed token data.');
        }

        return base64_encode($encodedSignedData);
    }

    public function decodeUserToken(?string $token): ?string
    {
        try {
            $decodedToken = base64_decode($token, true);
            if (false === $decodedToken) {
                return null;
            }

            $data = json_decode($decodedToken, true);
            if (!is_array($data) || 2 !== count($data)) {
                return null;
            }

            [$info, $sign] = $data;

            if ($sign !== $this->sign($info)) {
                return null;
            }

            $decodedInfo = json_decode($info, true);
            if (!is_array($decodedInfo)) {
                return null;
            }

            if ($decodedInfo['expire'] < time()) {
                return null;
            }

            if (isset($decodedInfo['email']) && filter_var($decodedInfo['email'], FILTER_VALIDATE_EMAIL)) {
                return $decodedInfo['email'];
            }

            return null;
        } catch (Throwable) {
            return null;
        }
    }

    private function sign(string $encoded): string
    {
        return hash('sha256', $encoded . '/' . $this->secret);
    }
}
