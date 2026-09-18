<?php

namespace App\Services\Webauthn;

use LaravelWebauthn\Services\Webauthn\CredentialAssertionValidator;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Facades\Log;
use Webauthn\PublicKeyCredential;

class LocalCredentialAssertionValidator extends CredentialAssertionValidator
{
    /**
     * Validate an authentication request.
     *
     * @param User|null $user
     * @param array $data
     * @return bool
     */
    public function __invoke(?User $user, array $data): bool
    {
        Log::info('LocalCredentialAssertionValidator: Called', ['host' => $this->request->host()]);

        // Load the data
        $content = json_encode($data, flags: JSON_THROW_ON_ERROR);
        $publicKeyCredential = $this->loader->deserialize($content, PublicKeyCredential::class, 'json');

        // Check the response against the request
        $this->validator->check(
            $this->getCredentialSource($user, $publicKeyCredential),
            $this->getResponse($publicKeyCredential),
            $this->pullPublicKey($user),
            $this->request->host(),
            $user?->getAuthIdentifier(), 
            [$this->request->host()] // Pass host as secured Relying Party ID to bypass HTTPS check
        );

        return true;
    }
}
