<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Exceptions\InvalidRequestException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Wraps the raw request so a controller can type-hint a concrete FormRequest
 * and call `validated()`, Laravel-style — see docs/conventions.md.
 */
abstract class FormRequest
{
    /** @var array<string, mixed>|null */
    private ?array $validated = null;

    final public function __construct(private readonly ServerRequestInterface $request)
    {
    }

    public static function fromHttpRequest(ServerRequestInterface $request): static
    {
        return new static($request);
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Validates on first access (throws `InvalidRequestException`) and
     * memoizes the result.
     *
     * @return array<string, mixed>
     */
    final public function validated(): array
    {
        return $this->validated ??= $this->validate();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validate(): array
    {
        return $this->requireTypes($this->data(), $this->rules());
    }

    /**
     * @return array<string, 'string'|'int'>
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Values checked against `rules()`. Defaults to the parsed body;
     * override to fold in route attributes (e.g. an `{itemId}` param).
     *
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        $body = $this->request->getParsedBody();

        return is_array($body) ? $body : [];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, 'string'|'int'> $types
     * @return array<string, string|int>
     */
    private function requireTypes(array $data, array $types): array
    {
        $checked = [];

        foreach ($types as $field => $type) {
            $value = $data[$field] ?? null;
            $valid = match ($type) {
                'string' => is_string($value),
                'int' => is_int($value),
            };

            if (!$valid) {
                $fields = [];
                foreach ($types as $f => $t) {
                    $fields[] = "\"{$f}\" ({$t})";
                }

                throw new InvalidRequestException(implode(' and ', $fields) . ' are required.');
            }

            $checked[$field] = $value;
        }

        return $checked;
    }
}
