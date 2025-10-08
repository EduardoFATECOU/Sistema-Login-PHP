<?php
/**
 * =====================================================
 * Keep-Alive - Mantém a Sessão Ativa
 * =====================================================
 * Este arquivo é chamado periodicamente via AJAX para
 * atualizar o timestamp da sessão e prevenir timeout
 * por inatividade durante uso ativo do sistema
 */

// Inclui o arquivo de configuração
require_once 'config.php';

// Define o tipo de resposta como JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (isLoggedIn()) {
    // Atualiza o timestamp da última atividade
    $_SESSION['last_activity'] = time();
    
    // Retorna resposta de sucesso
    echo json_encode([
        'status' => 'success',
        'message' => 'Sessão atualizada',
        'timestamp' => time()
    ]);
} else {
    // Usuário não está logado
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Não autenticado'
    ]);
}
?>
