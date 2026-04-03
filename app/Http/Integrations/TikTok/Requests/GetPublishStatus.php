<?php

namespace App\Http\Integrations\TikTok\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class GetPublishStatus extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $publishId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/post/publish/status/fetch/';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'publish_id' => $this->publishId,
        ];
    }
}
