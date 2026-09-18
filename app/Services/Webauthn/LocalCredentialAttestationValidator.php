<?php

namespace App\Services\Webauthn;

use LaravelWebauthn\Services\Webauthn\CredentialAttestationValidator;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredential;
use Illuminate\Support\Facades\Log;

class LocalCredentialAttestationValidator extends CredentialAttestationValidator
{
    /**
     * Validate a creation request.
     *
     * @param User $user
     * @param array $data
     * @return PublicKeyCredentialSource
     */
    public function __invoke(User $user, array $data): PublicKeyCredentialSource
    {
        Log::info('LocalCredentialAttestationValidator: Called', ['host' => $this->request->host()]);

        // Load the data
        $content = json_encode($data, flags: JSON_THROW_ON_ERROR);
        $publicKeyCredential = $this->loader->deserialize($content, PublicKeyCredential::class, 'json');

        Log::info('LocalCredentialAttestationValidator: About to check validator', [
            'rpId' => $this->request->host()
        ]);

        // Check the response against the request
        return $this->validator->check(
            $this->getResponse($publicKeyCredential),
            $this->pullPublicKey($user),
            $this->request->host(),
            [$this->request->host()] // Pass host as secured Relying Party ID to bypass HTTPS check
        );
    }
}
