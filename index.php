<?php
/**
 * =====================================================
 * Página Inicial (Index)
 * =====================================================
 * Ponto de entrada do sistema
 * Redireciona para dashboard se logado ou para login se não logado
 */

// Inclui o arquivo de configuração
require_once 'config.php';

// Verifica se o usuário está logado
if (isLoggedIn()) {
    // Se estiver logado, redireciona para o dashboard
    redirect('dashboard.php');
} else {
    // Se não estiver logado, redireciona para o login
    redirect('login.php');
}
?>
