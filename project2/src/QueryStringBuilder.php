<?php

namespace PHPMaker2026\Project1;

/**
 * Query string builder
 */
class QueryStringBuilder
{
    private array $params = [];

    /**
     * Encoding mode for this instance.
     */
    private int $encoding;

    /**
     * Global default encoding.
     */
    private static int $defaultEncoding = PHP_QUERY_RFC3986;

    /**
     * Constructor accepts optional initial data:
     * - query strings like 'a=1&b=2'
     * - associative arrays
     *
     * @param array<string, mixed>|string[] $inputs
     * @param int|null $encoding Optional encoding override
     */
    public function __construct(array|string $inputs = [], ?int $encoding = null)
    {
        $this->add($inputs);
        $this->encoding = $encoding ?? self::$defaultEncoding;
    }

    public static function setDefaultEncoding(int $encoding): void
    {
        self::$defaultEncoding = $encoding;
    }

    public static function getDefaultEncoding(): int
    {
        return self::$defaultEncoding;
    }

    public function setEncoding(int $type): self
    {
        $this->encoding = $type;
        return $this;
    }

    public function getEncoding(): int
    {
        return $this->encoding;
    }

    public function add(array|string $key, mixed $value = null): self
    {
        if (empty($key)) {
            return $this; // No key provided, nothing to add
        }
        if (is_string($key) && $value === null && str_contains($key, '=')) {
            parse_str($key, $parsed);
            return $this->add($parsed);
        }
        if (is_array($key) && $value === null) {
            if (array_is_list($key)) {
                foreach ($key as $input) {
                    $this->add($input);
                }
            } elseif (is_array($key)) {
                foreach ($key as $k => $v) {
                    $this->add($k, $v);
                }
            }
            return $this;
        }
        if (is_string($key)) {
            if (
                array_key_exists($key, $this->params) &&
                is_array($this->params[$key]) &&
                is_array($value)
            ) {
                $this->params[$key] = array_merge($this->params[$key], $value);
            } else {
                $this->params[$key] = $value;
            }
        }
        return $this;
    }

    public function remove(string $key): self
    {
        unset($this->params[$key]);
        return $this;
    }

    public function clear(): self
    {
        $this->params = [];
        return $this;
    }

    public function build(): string
    {
        return http_build_query($this->params, '', '&', $this->encoding);
    }

    public function __toString(): string
    {
        return $this->build();
    }

    public function toArray(): array
    {
        return $this->params;
    }

    public static function buildQuery(array|string ...$inputs): string
    {
        $builder = new self($inputs);
        return $builder->build();
    }

    public static function buildUrl(string $url, array|string ...$inputs): string
    {
        $builder = new self($inputs);
        $queryString = $builder->build();
        if ($queryString === '') {
            return $url;
        }
        $parsedUrl = parse_url($url);
        $fragment = '';
        if (isset($parsedUrl['fragment'])) {
            $fragment = '#' . $parsedUrl['fragment'];
            $url = str_replace($fragment, '', $url);
        }
        if (!isset($parsedUrl['query']) || $parsedUrl['query'] === '') {
            if (str_ends_with($url, '?')) {
                return $url . $queryString . $fragment;
            }
            return $url . '?' . $queryString . $fragment;
        }

        // Merge existing query params with current params
        parse_str($parsedUrl['query'], $existingParams);
        $mergedParams = array_merge($existingParams, $builder->toArray());
        $mergedQuery = http_build_query($mergedParams, '', '&', $builder->getEncoding());
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = $parsedUrl['host'] ?? '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = $parsedUrl['user'] ?? '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsedUrl['path'] ?? '';
        return "$scheme$user$pass$host$port$path?$mergedQuery$fragment";
    }
}
