<?php

namespace RaDevs\ApiJsonResponse;

use Symfony\Component\HttpFoundation\Response;

class ResponseDTO
{
    public function __construct(
        private bool $success,
        private string $message,
        private int $statusCode,
        private ?array $data = null,
        private ?array $errors = null,
        private ?array $trace = null,
    ) {}

    public function getResponse(): array
    {
        $this->message = empty($this->message)
            ? (Response::$statusTexts[$this->statusCode] ?? '')
            : $this->message;

        $response = [
            'success' => $this->success,
            'status' => $this->success
                ? 'success'
                : (!empty($this->errors) ? 'fail' : 'error'),
            'message' => $this->message,
        ];

        if (!empty($this->data)) {
            $response['data'] = $this->data;
        }
        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }
        if (!empty($this->trace)) {
            $response['trace'] = $this->trace;
        }

        return $response;
    }
}
