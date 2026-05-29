<?php
/**
 * ANRDI — Connexion base de données (singleton PDO)
 */

declare(strict_types=1);

if (!defined('ANRDI_BOOTSTRAP')) { http_response_code(403); die(); }

class Database
{
    private static ?PDO $pdo = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$pdo !== null) return self::$pdo;

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        if (defined('PDO::MYSQL_ATTR_FOUND_ROWS')) {
            $options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
        }

        try {
            if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
                throw new RuntimeException('Le driver PDO MySQL n\'est pas disponible.');
            }

            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$pdo->exec(
                "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';" .
                "SET time_zone='+00:00';"
            );
        } catch (Throwable $e) {
            error_log('[ANRDI DB] ' . $e->getMessage());
            throw new RuntimeException('Service de base de données indisponible.', 0, $e);
        }

        return self::$pdo;
    }

    /** Requête préparée + exécution en une ligne */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Prépare sans exécuter (pour les boucles) */
    public static function prepare(string $sql): PDOStatement
    {
        return self::getInstance()->prepare($sql);
    }

    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    public static function rollback(): void
    {
        if (self::getInstance()->inTransaction()) {
            self::getInstance()->rollBack();
        }
    }

    /** Nombre de lignes affectées par la dernière requête */
    public static function rowCount(PDOStatement $stmt): int
    {
        return $stmt->rowCount();
    }
}
