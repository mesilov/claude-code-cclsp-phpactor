<?php

declare(strict_types=1);

namespace App;

/**
 * Interface to demonstrate LSP analysis.
 */
interface LoggerInterface
{
    public function log(string $level, string $message, array $context = []): void;
}

/**
 * Log level enumeration.
 */
enum LogLevel: string
{
    case Debug = 'debug';
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
}

/**
 * File-based logger implementation.
 */
class FileLogger implements LoggerInterface
{
    /** @var list<array{level: string, message: string, context: array}> */
    private array $entries = [];

    public function __construct(
        private readonly string $filePath,
        private readonly LogLevel $minLevel = LogLevel::Debug,
    ) {}

    public function log(string $level, string $message, array $context = []): void
    {
        $this->entries[] = [
            'level' => $level,
            'message' => $this->interpolate($message, $context),
            'context' => $context,
        ];
    }

    /**
     * @return list<array{level: string, message: string, context: array}>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $value) {
            if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr($message, $replace);
    }
}

/**
 * Generic-like typed collection (for type inference testing).
 *
 * @template T
 */
class TypedCollection
{
    /** @var list<T> */
    private array $items = [];

    /**
     * @param class-string<T> $type
     */
    public function __construct(
        private readonly string $type,
    ) {}

    /**
     * @param T $item
     */
    public function add(mixed $item): void
    {
        if (!$item instanceof $this->type) {
            throw new \InvalidArgumentException(
                sprintf('Expected %s, got %s', $this->type, get_debug_type($item))
            );
        }

        $this->items[] = $item;
    }

    /**
     * @return list<T>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @param callable(T): bool $predicate
     * @return list<T>
     */
    public function filter(callable $predicate): array
    {
        return array_values(array_filter($this->items, $predicate));
    }

    public function count(): int
    {
        return count($this->items);
    }
}

/**
 * Readonly DTO with promoted properties.
 */
readonly class UserDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {}

    public function withName(string $name): self
    {
        return new self($this->id, $name, $this->email, $this->createdAt);
    }
}

/**
 * Service demonstrating dependency injection and method call chains.
 */
class UserService
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return TypedCollection<UserDto>
     */
    public function findActiveUsers(): TypedCollection
    {
        $this->logger->log(LogLevel::Info->value, 'Fetching active users');

        $collection = new TypedCollection(UserDto::class);
        $collection->add(new UserDto(1, 'Alice', 'alice@example.com'));
        $collection->add(new UserDto(2, 'Bob', 'bob@example.com'));

        $this->logger->log(
            LogLevel::Debug->value,
            'Found {count} active users',
            ['count' => (string) $collection->count()],
        );

        return $collection;
    }

    /**
     * @return list<UserDto>
     */
    public function findUsersByName(string $name): array
    {
        $collection = $this->findActiveUsers();

        return $collection->filter(
            fn(UserDto $user): bool => str_contains($user->name, $name),
        );
    }
}

// --- Entry point for quick verification ---

$logger = new FileLogger('/tmp/app.log', LogLevel::Debug);
$service = new UserService($logger);

$users = $service->findActiveUsers();
$filtered = $service->findUsersByName('Ali');

foreach ($filtered as $user) {
    echo sprintf("[%d] %s <%s>\n", $user->id, $user->name, $user->email);
}