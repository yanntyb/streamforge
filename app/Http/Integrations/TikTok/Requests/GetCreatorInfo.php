<?php

namespace App\Http\Integrations\TikTok\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCreatorInfo extends Request
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/post/publish/creator_info/query/';
    }
}
