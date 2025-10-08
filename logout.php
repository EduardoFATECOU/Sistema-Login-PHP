<?php
/**
 * =====================================================
 * Página de Logout
 * =====================================================
 * Encerra a sessão do usuário e redireciona para o login
 * Limpa todos os dados de sessão e cookies
 */

// Inclui o arquivo de configuração
require_once 'config.php';

/**
 * =====================================================
 * PROCESSO DE LOGOUT
 * =====================================================
 */

// Registra o logout no log (se em modo de desenvolvimento)
if (DEV_MODE && isLoggedIn()) {
    $usuario_email = $_SESSION['usuario_email'] ?? 'desconhecido';
    error_log("Logout realizado: $usuario_email");
}

// Remove todas as variáveis de sessão
$_SESSION = [];

// Remove o cookie de sessão se existir
if (isset($_COOKIE[session_name()])) {
    // Define o cookie com tempo de expiração no passado para deletá-lo
    setcookie(
        session_name(),      // Nome do cookie de sessão
        '',                  // Valor vazio
        time() - 3600,       // Expira 1 hora no passado
        '/',                 // Caminho
        '',                  // Domínio
        false,               // Secure (use true se HTTPS)
        true                 // HttpOnly
    );
}

// Remove o cookie "lembrar-me" se existir
if (isset($_COOKIE['remember_token'])) {
    setcookie(
        'remember_token',    // Nome do cookie
        '',                  // Valor vazio
        time() - 3600,       // Expira no passado
        '/',                 // Caminho
        '',                  // Domínio
        false,               // Secure
        true                 // HttpOnly
    );
}

// Destrói a sessão completamente
session_destroy();

// Redireciona para a página de login com mensagem de sucesso
redirect('login.php?logout=1');
?>
