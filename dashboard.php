<?php
/**
 * =====================================================
 * Dashboard - Área Restrita
 * =====================================================
 * Página principal após o login
 * Exibe informações do usuário e opções do sistema
 * Requer autenticação para acesso
 */

// Inclui os arquivos necessários
require_once 'config.php';
require_once 'conexao.php';

// Protege a página - redireciona para login se não estiver autenticado
requireLogin();

// Obtém os dados do usuário logado
$usuario = getUsuarioLogado();

/**
 * =====================================================
 * BUSCA INFORMAÇÕES ADICIONAIS DO USUÁRIO
 * =====================================================
 */
try {
    // Busca informações completas do usuário no banco de dados
    $sql = "SELECT id, nome, email, foto_perfil, ativo, criado_em, ultimo_login 
            FROM usuarios WHERE id = ?";
    $usuario_completo = fetchOne($sql, [$usuario['id']]);
    
    // Se não encontrou o usuário (foi excluído), faz logout
    if (!$usuario_completo) {
        session_destroy();
        redirect('login.php');
    }
    
    // Calcula tempo desde o cadastro
    $data_cadastro = new DateTime($usuario_completo['criado_em']);
    $agora = new DateTime();
    $intervalo = $data_cadastro->diff($agora);
    
    if ($intervalo->y > 0) {
        $tempo_cadastro = $intervalo->y . " ano(s)";
    } elseif ($intervalo->m > 0) {
        $tempo_cadastro = $intervalo->m . " mês(es)";
    } elseif ($intervalo->d > 0) {
        $tempo_cadastro = $intervalo->d . " dia(s)";
    } else {
        $tempo_cadastro = "Hoje";
    }
    
    // Formata data do último login
    if ($usuario_completo['ultimo_login']) {
        $ultimo_login = date('d/m/Y H:i', strtotime($usuario_completo['ultimo_login']));
    } else {
        $ultimo_login = "Primeiro acesso";
    }
    
    // Conta total de usuários cadastrados (apenas para exemplo)
    $sql = "SELECT COUNT(*) FROM usuarios WHERE ativo = 1";
    $stmt = query($sql);
    $total_usuarios = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    // Em caso de erro, registra e define valores padrão
    error_log("Erro ao buscar dados do dashboard: " . $e->getMessage());
    $tempo_cadastro = "N/A";
    $ultimo_login = "N/A";
    $total_usuarios = 0;
}

/**
 * Obtém as iniciais do nome para o avatar
 */
function getIniciais($nome) {
    $palavras = explode(' ', $nome);
    if (count($palavras) >= 2) {
        return strtoupper(substr($palavras[0], 0, 1) . substr($palavras[1], 0, 1));
    }
    return strtoupper(substr($nome, 0, 2));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Cabeçalho do dashboard -->
        <div class="dashboard-header">
            <h1>🏠 Dashboard</h1>
            
            <!-- Informações do usuário logado -->
            <div class="user-info">
                <div class="user-avatar">
                    <?php if ($usuario_completo['foto_perfil'] && file_exists($usuario_completo['foto_perfil'])): ?>
                        <img src="<?php echo htmlspecialchars($usuario_completo['foto_perfil']); ?>" 
                             alt="Foto de perfil">
                    <?php else: ?>
                        <?php echo getIniciais($usuario['nome']); ?>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                    <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Corpo do dashboard -->
        <div class="dashboard-body">
            
            <!-- Mensagem de boas-vindas -->
            <div class="welcome-message">
                <h2>👋 Olá, <?php echo htmlspecialchars(explode(' ', $usuario['nome'])[0]); ?>!</h2>
                <p>
                    Bem-vindo ao seu painel de controle. Este é um sistema completo de autenticação 
                    desenvolvido com PHP 8, PDO e MariaDB, implementando as melhores práticas de 
                    segurança como criptografia de senhas com bcrypt, prepared statements para 
                    prevenir SQL Injection, proteção contra força bruta e controle de sessões.
                </p>
            </div>
            
            <!-- Grid de informações -->
            <div class="info-grid">
                
                <!-- Card: Tempo de cadastro -->
                <div class="info-card">
                    <h3>📅 Membro desde</h3>
                    <p><?php echo htmlspecialchars($tempo_cadastro); ?></p>
                </div>
                
                <!-- Card: Último login -->
                <div class="info-card">
                    <h3>🕐 Último acesso</h3>
                    <p><?php echo htmlspecialchars($ultimo_login); ?></p>
                </div>
                
                <!-- Card: Total de usuários -->
                <div class="info-card">
                    <h3>👥 Usuários ativos</h3>
                    <p><?php echo number_format($total_usuarios, 0, ',', '.'); ?></p>
                </div>
                
                <!-- Card: Status da conta -->
                <div class="info-card">
                    <h3>✅ Status da conta</h3>
                    <p><?php echo $usuario_completo['ativo'] ? 'Ativa' : 'Inativa'; ?></p>
                </div>
                
            </div>
            
            <!-- Ações disponíveis -->
            <div class="actions">
                <a href="perfil.php" class="btn btn-primary">
                    📝 Editar Perfil
                </a>
                <a href="usuarios.php" class="btn btn-secondary">
                    👥 Ver Usuários
                </a>
                <a href="logout.php" class="btn btn-danger" 
                   onclick="return confirm('Tem certeza que deseja sair?')">
                    🚪 Sair do Sistema
                </a>
            </div>
            
        </div>
    </div>
    
    <script>
    /**
     * =====================================================
     * JavaScript para funcionalidades do dashboard
     * =====================================================
     */
    
    /**
     * Atualiza o tempo de sessão a cada minuto
     * Previne timeout por inatividade durante uso ativo
     */
    setInterval(function() {
        // Faz uma requisição silenciosa para manter a sessão ativa
        fetch('keep-alive.php', {
            method: 'POST',
            credentials: 'same-origin'
        }).catch(err => console.error('Erro ao manter sessão:', err));
    }, 60000); // A cada 1 minuto
    
    /**
     * Aviso antes de fechar a página (opcional)
     */
    window.addEventListener('beforeunload', function(e) {
        // Descomente para habilitar aviso ao sair
        // e.preventDefault();
        // e.returnValue = '';
    });
    
    /**
     * Exibe data e hora atual
     */
    function atualizarRelogio() {
        const agora = new Date();
        const opcoes = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        const dataHora = agora.toLocaleDateString('pt-BR', opcoes);
        
        // Se houver um elemento para exibir o relógio
        const relogioEl = document.getElementById('relogio');
        if (relogioEl) {
            relogioEl.textContent = dataHora;
        }
    }
    
    // Atualiza o relógio a cada segundo
    setInterval(atualizarRelogio, 1000);
    atualizarRelogio(); // Executa imediatamente
    </script>
</body>
</html>
