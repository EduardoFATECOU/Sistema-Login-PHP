<?php
/**
 * =====================================================
 * Arquivo de Conexão com Banco de Dados usando PDO
 * =====================================================
 * Este arquivo gerencia a conexão com o banco de dados MariaDB/MySQL
 * utilizando PDO (PHP Data Objects) para máxima segurança e flexibilidade
 */

// Inclui o arquivo de configuração
require_once 'config.php';

/**
 * Classe Database
 * Gerencia a conexão com o banco de dados usando padrão Singleton
 * Garante que apenas uma instância de conexão seja criada
 */
class Database {
    // Propriedade estática que armazena a única instância da conexão
    private static $conexao = null;
    
    /**
     * Construtor privado para prevenir instanciação direta
     * Isso força o uso do método estático getConexao()
     */
    private function __construct() {}
    
    /**
     * Método estático para obter a conexão PDO
     * Implementa o padrão Singleton
     * 
     * @return PDO Objeto de conexão PDO
     * @throws PDOException Se houver erro na conexão
     */
    public static function getConexao() {
        // Verifica se a conexão já foi estabelecida
        if (self::$conexao === null) {
            try {
                // Monta a string DSN (Data Source Name)
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );
                
                // Opções de configuração do PDO para segurança e performance
                $opcoes = [
                    // Define o modo de erro para exceções (facilita tratamento de erros)
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    
                    // Define o modo de fetch padrão como array associativo
                    // Retorna dados como ['coluna' => 'valor']
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    
                    // Desabilita a emulação de prepared statements
                    // Usa prepared statements nativos do banco (mais seguro)
                    PDO::ATTR_EMULATE_PREPARES => false,
                    
                    // Define o charset da conexão
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                    
                    // Desabilita o modo de string para números
                    // Retorna inteiros como int, não como string
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    
                    // Habilita o modo de conversão de tipos
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                
                // Cria a nova conexão PDO
                self::$conexao = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
                
            } catch (PDOException $e) {
                // Captura e trata erros de conexão
                if (DEV_MODE) {
                    // Em desenvolvimento: mostra detalhes do erro
                    die("Erro na conexão com o banco de dados: " . $e->getMessage());
                } else {
                    // Em produção: mensagem genérica e registra erro em log
                    error_log("Erro de conexão PDO: " . $e->getMessage());
                    die("Erro ao conectar ao banco de dados. Tente novamente mais tarde.");
                }
            }
        }
        
        // Retorna a conexão estabelecida
        return self::$conexao;
    }
    
    /**
     * Método para fechar a conexão explicitamente
     * Normalmente não é necessário, pois o PHP fecha automaticamente
     */
    public static function fecharConexao() {
        self::$conexao = null;
    }
    
    /**
     * Previne a clonagem da instância (padrão Singleton)
     */
    private function __clone() {}
    
    /**
     * Previne a desserialização da instância (padrão Singleton)
     */
    public function __wakeup() {
        throw new Exception("Não é possível desserializar um singleton.");
    }
}

/**
 * Função auxiliar global para obter a conexão rapidamente
 * Facilita o uso em outros arquivos do sistema
 * 
 * @return PDO Objeto de conexão PDO
 */
function getDB() {
    return Database::getConexao();
}

/**
 * Função auxiliar para executar queries preparadas de forma simplificada
 * 
 * @param string $sql Query SQL com placeholders
 * @param array $params Parâmetros para bind
 * @return PDOStatement Statement executado
 */
function query($sql, $params = []) {
    $pdo = getDB();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Função auxiliar para buscar um único registro
 * 
 * @param string $sql Query SQL
 * @param array $params Parâmetros
 * @return array|false Registro encontrado ou false
 */
function fetchOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

/**
 * Função auxiliar para buscar múltiplos registros
 * 
 * @param string $sql Query SQL
 * @param array $params Parâmetros
 * @return array Array de registros
 */
function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Função auxiliar para inserir dados e retornar o ID inserido
 * 
 * @param string $sql Query SQL de INSERT
 * @param array $params Parâmetros
 * @return string ID do registro inserido
 */
function insert($sql, $params = []) {
    query($sql, $params);
    return getDB()->lastInsertId();
}
?>
