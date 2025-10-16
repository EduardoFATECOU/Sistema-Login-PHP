<?php
/**
 * =====================================================
 * Arquivo de Configuração do Sistema de Login
 * =====================================================
 * Este arquivo contém todas as configurações necessárias para o sistema
 * incluindo conexão com banco de dados, sessões e segurança
 */

// =====================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// =====================================================

// Host do banco de dados (geralmente 'localhost' em ambiente local)
define('DB_HOST', 'localhost');

// Nome do banco de dados que será utilizado
define('DB_NAME', 'sistema_login');

// Nome de usuário do banco de dados
define('DB_USER', 'root');

// Senha do usuário do banco de dados
define('DB_PASS', '');

// Charset para garantir suporte a caracteres especiais e acentuação
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// CONFIGURAÇÕES DO SISTEMA
// =====================================================

// URL base do sistema (ajuste conforme seu ambiente)
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/sistema-login/');

// Diretório raiz do sistema
define('ROOT_PATH', __DIR__);

// Diretório para upload de fotos de perfil
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');

// Tamanho máximo de upload em bytes (2MB)
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

// Extensões permitidas para upload de imagem
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// =====================================================
// CONFIGURAÇÕES DE SEGURANÇA
// =====================================================

// Tempo de expiração da sessão em segundos (30 minutos)
define('SESSION_TIMEOUT', 1800);

// Número máximo de tentativas de login antes de bloquear
define('MAX_LOGIN_ATTEMPTS', 5);

// Tempo de bloqueio após exceder tentativas (15 minutos)
define('LOCKOUT_TIME', 900);

// Habilitar modo de desenvolvimento (exibe erros detalhados)
// ATENÇÃO: Definir como false em produção!
define('DEV_MODE', true);

// =====================================================
// CONFIGURAÇÕES GERAIS DO PHP
// =====================================================

// Define o fuso horário padrão para o sistema
date_default_timezone_set('America/Sao_Paulo');

// Configurações de exibição de erros
if (DEV_MODE) {
    // Em desenvolvimento: mostra todos os erros
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Em produção: oculta erros e registra em log
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/php-errors.log');
}

// =====================================================
// CONFIGURAÇÕES DE SESSÃO
// =====================================================

// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de segurança da sessão
    ini_set('session.cookie_httponly', 1);  // Previne acesso via JavaScript
    ini_set('session.use_only_cookies', 1);  // Usa apenas cookies para sessão
    ini_set('session.cookie_secure', 0);     // Define como 1 se usar HTTPS
    ini_set('session.cookie_samesite', 'Strict'); // Proteção contra CSRF
    
    // Inicia a sessão
    session_start();
    
    // Regenera o ID da sessão periodicamente para segurança
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        // Regenera a cada 5 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// =====================================================
// FUNÇÕES AUXILIARES DE CONFIGURAÇÃO
// =====================================================

/**
 * Retorna a URL completa de um arquivo/página
 * 
 * @param string $path Caminho relativo
 * @return string URL completa
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Redireciona para uma URL específica
 * 
 * @param string $path Caminho para redirecionar
 */
function redirect($path = '') {
    header('Location: ' . url($path));
    exit;
}

/**
 * Verifica se o usuário está logado
 * 
 * @return bool True se estiver logado, false caso contrário
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Obtém dados do usuário logado da sessão
 * 
 * @param string|null $key Chave específica ou null para todos os dados
 * @return mixed Dados do usuário
 */
function getUsuarioLogado($key = null) {
    if (!isLoggedIn()) {
        return null;
    }
    
    if ($key === null) {
        return [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nome' => $_SESSION['usuario_nome'] ?? null,
            'email' => $_SESSION['usuario_email'] ?? null,
            'foto_perfil' => $_SESSION['usuario_foto'] ?? null
        ];
    }
    
    return $_SESSION['usuario_' . $key] ?? null;
}

/**
 * Protege uma página exigindo login
 * Redireciona para login se não estiver autenticado
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
    
    // Verifica timeout da sessão
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            // Sessão expirou
            session_destroy();
            redirect('login.php?timeout=1');
        }
    }
    
    // Atualiza o timestamp da última atividade
    $_SESSION['last_activity'] = time();
}

/**
 * Cria o diretório de uploads se não existir
 */
function createUploadDir() {
    if (!file_exists(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
}

// Cria o diretório de uploads automaticamente
createUploadDir();
?>
