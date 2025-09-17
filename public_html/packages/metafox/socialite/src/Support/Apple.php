<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Socialite\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

/**
 * Class Apple.
 */
class Apple
{
    /**
     * @param  array<string, string> $params
     * @return array<string, mixed>
     */
    public function generateClientSecret(array $params = []): array
    {
        $requires     = ['team_id', 'client_id', 'key_id', 'private_key'];
        if (!Arr::has($params, $requires)) {
            throw new InvalidArgumentException('Missing required parameters, could not generate Apple secret.');
        }

        [
            'team_id'     => $teamId,
            'client_id'   => $clientId,
            'key_id'      => $keyId,
            'private_key' => $privateKey
        ] = $params;

        if (empty($privateKey)) {
            throw new InvalidArgumentException('The private key is invalid, could not generate Apple secret.');
        }

        $now            = CarbonImmutable::now();
        $expirationDate = $now->addMonths(3);
        $jwtConfig      = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($privateKey));

        return [
            'client_secret_expiration' => $expirationDate->getTimestamp(),
            'client_secret'            => $jwtConfig
                ->builder()
                ->issuedBy($teamId)
                ->issuedAt($now)
                ->expiresAt($expirationDate)
                ->permittedFor('https://appleid.apple.com')
                ->relatedTo($clientId)
                ->withHeader('kid', $keyId)
                ->getToken($jwtConfig->signer(), $jwtConfig->signingKey())
                ->toString(),
        ];
    }
}
