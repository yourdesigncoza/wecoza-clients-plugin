<?php

namespace WeCozaClients\Services\Database;

use PDO;
use PDOException;
use Exception;

/**
 * Database Service for PostgreSQL connectivity
 *
 * @package WeCozaClients
 * @since 1.0.0
 */
class DatabaseService {
    
    /**
     * PDO instance
     *
     * @var PDO|null
     */
    private static $pdo = null;
    
    /**
     * Connection parameters
     *
     * @var array
     */
    private static $config = null;
    
    /**
     * Last error message
     *
     * @var string
     */
    private static $lastError = '';

    /**
     * Schema inspection cache
     *
     * @var array
     */
    private static $schemaCache = array(
        'columns' => array(),
        'relations' => array(),
    );
    
    /**
     * Get database connection
     *
     * @return PDO|null
     */
    public static function getConnection() {
        if (self::$pdo === null) {
            self::connect();
        }
        
        return self::$pdo;
    }
    
    /**
     * Connect to database
     *
     * @return bool
     */
    private static function connect() {
        try {
            // Load configuration
            self::loadConfig();
            
            // Build DSN
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
                self::$config['host'],
                self::$config['port'],
                self::$config['dbname']
            );
            
            // Connection options
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            );
            
            // Create PDO instance
            self::$pdo = new PDO(
                $dsn,
                self::$config['user'],
                self::$config['password'],
                $options
            );
            
            // Set timezone to match WordPress
            $timezone = wp_timezone_string();
            if ($timezone) {
                self::$pdo->exec("SET TIME ZONE '$timezone'");
            }
            
            return true;
            
        } catch (PDOException $e) {
            self::$lastError = 'Database connection failed: ' . $e->getMessage();
            self::logError(self::$lastError);
            return false;
        }
    }
    
    /**
     * Load database configuration
     */
    private static function loadConfig() {
        if (self::$config === null) {
            $app_config = \WeCozaClients\config('app');
            self::$config = $app_config['database']['postgresql'];
        }
    }
    
    /**
     * Execute a query
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return \PDOStatement|false
     */
    public static function query($sql, $params = array()) {
        try {
            $pdo = self::getConnection();
            if (!$pdo) {
                return false;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt;
            
        } catch (PDOException $e) {
            self::$lastError = 'Query failed: ' . $e->getMessage();
            self::logError(self::$lastError . ' SQL: ' . $sql);
            return false;
        }
    }
    
    /**
     * Get all results
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false
     */
    public static function getAll($sql, $params = array()) {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }
    
    /**
     * Get single row
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false
     */
    public static function getRow($sql, $params = array()) {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }
    
    /**
     * Get single value
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return mixed|false
     */
    public static function getValue($sql, $params = array()) {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->fetchColumn() : false;
    }
    
    /**
     * Insert data
     *
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int|false Last insert ID or false on failure
     */
    public static function insert($table, $data) {
        try {
            $pdo = self::getConnection();
            if (!$pdo) {
                return false;
            }
            
            // Build query
            $fields = array_keys($data);
            $placeholders = array_map(function($field) {
                return ':' . $field;
            }, $fields);
            
            $returningColumn = 'id';
            if (!self::tableHasColumn($table, 'id')) {
                if (self::tableHasColumn($table, 'client_id')) {
                    $returningColumn = 'client_id';
                } else {
                    $returningColumn = null;
                }
            }

            $returningClause = $returningColumn ? ' RETURNING ' . $returningColumn : '';

            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)%s',
                $table,
                implode(', ', $fields),
                implode(', ', $placeholders),
                $returningClause
            );
            
            $stmt = $pdo->prepare($sql);
            
            // Bind parameters
            foreach ($data as $field => $value) {
                $stmt->bindValue(':' . $field, $value);
            }
            
            $stmt->execute();

            if ($returningColumn) {
                return $stmt->fetchColumn();
            }

            return true;
            
        } catch (PDOException $e) {
            self::$lastError = 'Insert failed: ' . $e->getMessage();
            self::logError(self::$lastError);
            return false;
        }
    }
    
    /**
     * Update data
     *
     * @param string $table Table name
     * @param array $data Data to update
     * @param string $where WHERE clause
     * @param array $whereParams WHERE parameters
     * @return int|false Number of affected rows or false on failure
     */
    public static function update($table, $data, $where, $whereParams = array()) {
        try {
            $pdo = self::getConnection();
            if (!$pdo) {
                return false;
            }
            
            // Build SET clause
            $setParts = array();
            foreach ($data as $field => $value) {
                $setParts[] = $field . ' = :set_' . $field;
            }
            
            $sql = sprintf(
                'UPDATE %s SET %s WHERE %s',
                $table,
                implode(', ', $setParts),
                $where
            );
            
            $stmt = $pdo->prepare($sql);
            
            // Bind SET parameters
            foreach ($data as $field => $value) {
                $stmt->bindValue(':set_' . $field, $value);
            }
            
            // Bind WHERE parameters
            foreach ($whereParams as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            self::$lastError = 'Update failed: ' . $e->getMessage();
            self::logError(self::$lastError);
            return false;
        }
    }
    
    /**
     * Delete data
     *
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return int|false Number of affected rows or false on failure
     */
    public static function delete($table, $where, $params = array()) {
        try {
            $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
            $stmt = self::query($sql, $params);
            
            return $stmt ? $stmt->rowCount() : false;
            
        } catch (PDOException $e) {
            self::$lastError = 'Delete failed: ' . $e->getMessage();
            self::logError(self::$lastError);
            return false;
        }
    }
    
    /**
     * Begin transaction
     *
     * @return bool
     */
    public static function beginTransaction() {
        $pdo = self::getConnection();
        return $pdo ? $pdo->beginTransaction() : false;
    }
    
    /**
     * Commit transaction
     *
     * @return bool
     */
    public static function commit() {
        $pdo = self::getConnection();
        return $pdo ? $pdo->commit() : false;
    }
    
    /**
     * Rollback transaction
     *
     * @return bool
     */
    public static function rollback() {
        $pdo = self::getConnection();
        return $pdo ? $pdo->rollBack() : false;
    }
    
    /**
     * Get last error message
     *
     * @return string
     */
    public static function getLastError() {
        return self::$lastError;
    }
    
    /**
     * Log error message
     *
     * @param string $message Error message
     */
    private static function logError($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WeCoza Clients Database: ' . $message);
        }
    }

    /**
     * Check if a relation (table, view, etc.) exists
     *
     * @param string $relation Relation name
     * @return bool
     */
    public static function relationExists($relation) {
        if (empty($relation)) {
            return false;
        }

        $cacheKey = strtolower($relation);
        if (isset(self::$schemaCache['relations'][$cacheKey])) {
            return self::$schemaCache['relations'][$cacheKey];
        }

        $pdo = self::getConnection();
        if (!$pdo) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('SELECT to_regclass(:relation_name)');
            $stmt->execute(array(':relation_name' => $relation));
            $exists = $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            self::logError('Schema check failed: ' . $e->getMessage());
            $exists = false;
        }

        self::$schemaCache['relations'][$cacheKey] = $exists;
        return $exists;
    }

    /**
     * Check if a table has a specific column
     *
     * @param string $table Table name
     * @param string $column Column name
     * @return bool
     */
    public static function tableHasColumn($table, $column) {
        if (empty($table) || empty($column)) {
            return false;
        }

        $cacheKey = strtolower($table . '.' . $column);
        if (isset(self::$schemaCache['columns'][$cacheKey])) {
            return self::$schemaCache['columns'][$cacheKey];
        }

        $pdo = self::getConnection();
        if (!$pdo) {
            return false;
        }

        $schema = null;
        $tableName = $table;

        if (strpos($table, '.') !== false) {
            list($schema, $tableName) = explode('.', $table, 2);
        }

        $sql = 'SELECT 1 FROM information_schema.columns WHERE table_name = :table AND column_name = :column';
        $params = array(
            ':table' => $tableName,
            ':column' => $column,
        );

        if ($schema) {
            $sql .= ' AND table_schema = :schema';
            $params[':schema'] = $schema;
        } else {
            $sql .= ' AND table_schema = ANY (current_schemas(false))';
        }

        $sql .= ' LIMIT 1';

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $exists = $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            self::logError('Schema check failed: ' . $e->getMessage());
            $exists = false;
        }

        self::$schemaCache['columns'][$cacheKey] = $exists;
        return $exists;
    }
    
    /**
     * Close connection
     */
    public static function close() {
        self::$pdo = null;
    }
    
    /**
     * Check if connected
     *
     * @return bool
     */
    public static function isConnected() {
        return self::$pdo !== null;
    }
}