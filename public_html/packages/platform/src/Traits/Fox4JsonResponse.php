<?php

namespace MetaFox\Platform\Traits;

use Illuminate\Http\JsonResponse;
use stdClass;

/**
 * Trait Fox4JsonResponse.
 */
trait Fox4JsonResponse
{
    private array $metadata = [];

    private ?string $metamessage = null;

    public function message(mixed $message)
    {
        $this->metamessage = $message;
    }

    public function navigate(string $url, bool $replace = false)
    {
        $this->metadata['nextAction'] = [
            'type'    => 'navigate',
            'payload' => [
                'url' => $url, 'replace' => $replace,
            ],
        ];
    }

    public function alert(mixed $alert)
    {
        if (is_string($alert)) {
            $alert = ['message' => $alert];
        }

        $this->metadata['alert'] = $alert;
    }

    public function addMetadata(?array $meta = [])
    {
        foreach ($meta as $name => $value) {
            $this->metadata[$name] = $value;
        }
    }

    /**
     * Success Response.
     *
     * @param object|array<mixed>|null $data
     * @param array<string, mixed>     $meta
     * @param array<mixed>|string|null $message
     * @param int                      $code
     * @param array<string, mixed>     $headers
     * @param int                      $options
     *
     * @return JsonResponse
     */
    public function success(
        $data = [],
        array $meta = [],
        $message = null,
        int $code = 200,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        return $this->processReturn('success', $data, $message, null, $code, $headers, $options, $meta);
    }

    /**
     * Tell client keep its cache and does not response data.
     *
     * @param  array  $data
     * @return JsonResponse
     */
    public function keepCacheSuccess(array $data=[]): JsonResponse
    {
        $data['keepCached'] =true;
        return $this->success($data);
    }

    /**
     * Error Response.
     *
     * @param string       $error
     * @param int          $code
     * @param array<mixed> $headers
     * @param int          $options
     *
     * @return JsonResponse
     */
    public function error(string $error = '', int $code = 400, array $headers = [], int $options = 0): JsonResponse
    {
        if (empty($error)) {
            $error = error_get_last()['message'] ?? 'Error';
        }

        return $this->processReturn('failed', [], null, $error, $code, $headers, $options);
    }

    /**
     * Info Response.
     *
     * @param object|array<mixed>|null $data
     * @param array<string, mixed>     $extra
     * @param array<mixed>|string|null $message
     * @param int                      $code
     * @param array<string, mixed>     $headers
     * @param int                      $options
     *
     * @return JsonResponse
     */
    public function info(
        $data = [],
        array $extra = [],
        $message = null,
        int $code = 200,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        return $this->processReturn('info', $data, $message, null, $code, $headers, $options, $extra);
    }

    /**
     * Warning Response.
     *
     * @param object|array<mixed>|null $data
     * @param array<string, mixed>     $extra
     * @param array<mixed>|string|null $message
     * @param int                      $code
     * @param array<string, mixed>     $headers
     * @param int                      $options
     *
     * @return JsonResponse
     */
    public function warning(
        $data = [],
        array $extra = [],
        $message = null,
        int $code = 200,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        return $this->processReturn('warning', $data, $message, null, $code, $headers, $options, $extra);
    }

    /**
     * @param string            $status
     * @param mixed|object      $data
     * @param array|string|null $message
     * @param string|null       $error
     * @param int               $code
     * @param array             $headers
     * @param int               $options
     * @param array<mixed>      $meta
     *
     * @return JsonResponse
     */
    private function processReturn(
        string $status,
        mixed $data,
        mixed $message = null,
        mixed $error = null,
        int $code = 200,
        array $headers = [],
        int $options = JSON_PRETTY_PRINT,
        array $meta = []
    ): JsonResponse {
        \MetaFox\Platform\Facades\LoadReduce::capture($data);

        if ($data === null) {
            $data = new stdClass();
        }

        $response = [
            'status' => $status,
            'data'   => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        } elseif ($this->metamessage) {
            $response['message'] = $this->metamessage;
        }

        if ($error) {
            $response['error'] = $error;
        }

        if (!empty($meta)) {
            $this->addMetadata($meta);
        }

        if (!empty($this->metadata)) {
            $response['meta'] = $this->metadata;
        }

        return response()->json($response, $code, $headers, $options);
    }
}
