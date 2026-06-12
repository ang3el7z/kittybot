<?php

namespace KittyBot\Storage;

use PDO;

final class SessionState
{
    public function start(string $userId, PDO $pdo): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        session_set_save_handler(new SqliteSessionHandler($pdo), true);
        session_id($userId);
        session_start();
    }

    /** @return array<int|string,array<string,mixed>> */
    public function replies(): array
    {
        return !empty($_SESSION['reply']) && is_array($_SESSION['reply']) ? $_SESSION['reply'] : [];
    }

    /** @return array<string,mixed>|null */
    public function reply(int|string|null $messageId): ?array
    {
        if ($messageId === null) {
            return null;
        }

        $reply = $this->replies()[$messageId] ?? null;
        return is_array($reply) ? $reply : null;
    }

    public function clearReplies(): void
    {
        unset($_SESSION['reply']);
    }

    /** @param array<string,mixed> $reply */
    public function rememberReply(int|string $messageId, array $reply): void
    {
        $_SESSION['reply'][$messageId] = $reply;
    }

    public function removeReply(int|string $messageId): void
    {
        unset($_SESSION['reply'][$messageId]);
        if (empty($_SESSION['reply'])) {
            unset($_SESSION['reply']);
        }
    }

    /** @return list<mixed> */
    public function replyArgs(int|string|null $messageId): array
    {
        $reply = $this->reply($messageId);
        return !empty($reply['args']) && is_array($reply['args']) ? array_values($reply['args']) : [];
    }

    public function proxyListEntryEnabled(): bool
    {
        return !empty($_SESSION['proxylistentry']);
    }

    public function setProxyListEntryEnabled(bool $enabled): void
    {
        if ($enabled) {
            $_SESSION['proxylistentry'] = 1;
            return;
        }

        unset($_SESSION['proxylistentry']);
    }

    /** @return array<string,string> */
    public function hwidTokenPool(string $scope): array
    {
        if (empty($_SESSION['hwidTokens']) || !is_array($_SESSION['hwidTokens'])) {
            $_SESSION['hwidTokens'] = [];
        }
        if (empty($_SESSION['hwidTokens'][$scope]) || !is_array($_SESSION['hwidTokens'][$scope])) {
            $_SESSION['hwidTokens'][$scope] = [];
        }

        return $_SESSION['hwidTokens'][$scope];
    }

    public function rememberHwidToken(string $scope, string $token, string $hwid): void
    {
        $this->hwidTokenPool($scope);
        $_SESSION['hwidTokens'][$scope][$token] = $hwid;
    }

    public function consumeHwidToken(string $scope, string $token): ?string
    {
        $pool = $this->hwidTokenPool($scope);
        if (!isset($pool[$token])) {
            return null;
        }

        $hwid = $pool[$token];
        unset($_SESSION['hwidTokens'][$scope][$token]);

        return $hwid;
    }

    public function clearHwidTokens(string $scope): void
    {
        $this->hwidTokenPool($scope);
        $_SESSION['hwidTokens'][$scope] = [];
    }
}
