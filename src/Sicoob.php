<?php

namespace NFService\Sicoob;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use NFService\Sicoob\Client\HttpClient;
use NFService\Sicoob\Options\EnvironmentUrls;
use NFService\Sicoob\Services\Auth;
use NFService\Sicoob\Services\Dda;

class Sicoob
{
    protected string $base_url;
    protected bool $isProduction;
    protected string $client_id;
    protected string $permissions;
    protected string $token;
    protected string $sandboxToken;
    protected int $expires_in;
    protected array $certificatePub;
    protected array $certificatePriv;
    protected HttpClient $client;

    public function __construct(
        bool $isProduction = true,
        string $client_id,
        string $certificatePubPath,
        ?string $certificatePubPass,
        string $certificatePrivPath,
        ?string $certificatePrivPass,
        ?string $permissions = null,
        ?string $sandboxToken = null,
        bool $debug = false
    ) {
        if (empty($client_id)) {
            throw new Exception('Client ID é obrigatório');
        }
        if (!$isProduction && empty($sandboxToken)) {
            throw new Exception('Sandbox Token é obrigatório');
        }
        if ($isProduction && empty($certificatePubPath)) {
            throw new Exception('Caminho do certificado público é obrigatório');
        }
        if ($isProduction && empty($certificatePrivPath)) {
            throw new Exception('Caminho do certificado privado é obrigatório');
        }

        $this->isProduction = $isProduction;
        $this->base_url = EnvironmentUrls::auth_url;
        $this->client_id = $client_id;
        $this->permissions = !empty($permissions) ? $permissions : 'cob.read cob.write cobv.write cobv.read lotecobv.write lotecobv.read pix.write pix.read webhook.read webhook.write payloadlocation.write payloadlocation.read';
        $this->certificatePub = [$certificatePubPath, $certificatePubPass];
        $this->certificatePriv = [$certificatePrivPath, $certificatePrivPass];
        $this->token = $isProduction ? $this->gerarToken() : $sandboxToken;
        $this->client = new HttpClient($this, $debug);
        $this->expires_in = 0;
    }

    public function gerarToken(): string | GuzzleException
    {
        if ($this->isProduction && !empty($this->token)) {
            return $this->token;
        }

        return $this->auth()->gerarToken();
    }

    public function getToken(): string
    {
        if (empty($this->token)) {
            $this->gerarToken();
        }

        if ($this->isProduction && $this->expires_in < time()) {
            $this->gerarToken();
        }

        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function getExpiresIn(): int
    {
        return $this->expires_in;
    }

    public function setExpiresIn(int $expires_in): void
    {
        $this->expires_in = $expires_in;
    }

    public function getPermissions(): string
    {
        return $this->permissions;
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    public function getIsProduction(): bool
    {
        return $this->isProduction;
    }

    public function getCertificatePub(): array
    {
        return $this->certificatePub;
    }

    public function getCertificatePriv(): array
    {
        return $this->certificatePriv;
    }

    public function getClient(): HttpClient
    {
        return $this->client;
    }

    public function dda(): Dda
    {
        return new Dda($this);
    }

    public function auth(): Auth
    {
        return new Auth($this);
    }
}