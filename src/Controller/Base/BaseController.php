<?php

declare(strict_types=1);

namespace Controller\Base;

abstract class BaseController
{
    protected function response($data = null): ?string
    {
        http_response_code(200);
        if ($data) {
            return json_encode($data);
        }
        return null;
    }

    protected function response_not_found($data = null): ?string
    {
        http_response_code(404);
        if ($data) {
            return json_encode(['errors' => $data]);
        }
        return null;
    }

    protected function response_bad_request($data = null): string
    {
        http_response_code(400);
        if ($data) {
            return json_encode($data);
        }
    }

    protected function response_unauthorized($data = null): string
    {
        http_response_code(401);
        if ($data) {
            return json_encode($data);
        }
    }

    protected function response_created($data = null): string
    {
        http_response_code(201);
        if ($data) {
            return json_encode($data);
        }
    }

    function get_bearer_token(): ?string
    {
        $bearer_token = getallheaders()['Authorization'] ?? null;
        if ($bearer_token) {
            if (preg_match('/Bearer\s(\S+)/', $bearer_token, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function get_body(): \stdClass
    {
        $body = file_get_contents('php://input');
        return json_decode($body);
    }
}