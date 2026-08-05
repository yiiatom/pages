<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Sort;

use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final readonly class Action
{
    public function __construct(
        private DataResponseFactoryInterface $responseFactory,
        private PageRepository $pageRepository,
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $contentType = $request->getHeaderLine(Header::CONTENT_TYPE);
        if (!str_contains($contentType, 'application/json')) {
            return $this->responseFactory
                ->createResponse(Status::UNSUPPORTED_MEDIA_TYPE);
        }

        $json = $request->getBody()->getContents();
        $positions = json_decode($json, true);

        if (empty($positions) || !is_array($positions)) {
            return $this->responseFactory
                ->createResponse(Status::UNPROCESSABLE_ENTITY);
        }

        $code = Status::OK;
        $data = ['success' => true];

        try {
            $this->pageRepository->updatePositions($positions);
        } catch (\Throwable) {
            $code = Status::INTERNAL_SERVER_ERROR;
            $data['success'] = false;
        }

        return $this->responseFactory
            ->createResponse($data, $code)
            ->withResponseFormatter(new JsonDataResponseFormatter());
    }
}
