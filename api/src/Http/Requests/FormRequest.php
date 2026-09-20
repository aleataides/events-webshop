<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Exceptions\InvalidRequestException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Wraps the raw request so a controller can type-hint a concrete FormRequest
 * and call `validated()`, Laravel-style, instead of validating in the
 * controller body. Subclasses declare `rules()` (field => 'string'|'int');
 * `ControllerInvocationStrategy` builds the instance via `fromHttpRequest()`
 * when it sees the type-hint.
 */
abstract class FormRequest
{
    /** @var array<string, string|int>|null */
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
     * @return array<string, string|int>
     */
    final public function validated(): array
    {
        return $this->validated ??= $this->runRules();
    }

    /**
     * @return array<string, 'string'|'int'>
     */
    abstract protected function rules(): array;

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
     * @return array<string, string|int>
     */
    private function runRules(): array
    {
        $data = $this->data();
        $validated = [];

        foreach ($this->rules() as $field => $type) {
            $value = $data[$field] ?? null;
            $valid = match ($type) {
                'string' => is_string($value),
                'int' => is_int($value),
            };

            if (!$valid) {
                throw new InvalidRequestException($this->invalidMessage());
            }

            $validated[$field] = $value;
        }

        return $validated;
    }

    private function invalidMessage(): string
    {
        $fields = [];
        foreach ($this->rules() as $field => $type) {
            $fields[] = "\"{$field}\" ({$type})";
        }

        return implode(' and ', $fields) . ' are required.';
    }
}
