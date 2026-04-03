<?php

namespace App\Http\Integrations\TikTok\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class InitVideoUpload extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $title,
        protected int $videoSize,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/post/publish/inbox/video/init/';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'post_info' => [
                'title' => $this->title,
                'privacy_level' => 'SELF_ONLY',
                'disable_duet' => true,
                'disable_stitch' => true,
                'disable_comment' => true,
            ],
            'source_info' => [
                'source' => 'FILE_UPLOAD',
                'video_size' => $this->videoSize,
                'chunk_size' => $this->videoSize,
                'total_chunk_count' => 1,
            ],
        ];
    }
}
