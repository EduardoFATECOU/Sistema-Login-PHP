<?php
/**
 * =====================================================
 * Página de Perfil do Usuário
 * =====================================================
 * Permite visualizar e editar informações do perfil
 * Área restrita - requer autenticação
 */

// Inclui os arquivos necessários
require_once 'config.php';
require_once 'conexao.php';

// Protege a página
requireLogin();

// Obtém o usuário logado
$usuario_logado = getUsuarioLogado();

// Inicializa variáveis
$mensagem = '';
$tipo_mensagem = '';

/**
 * =====================================================
 * BUSCA DADOS COMPLETOS DO USUÁRIO
 * =====================================================
 */
try {
    $sql = "SELECT id, nome, email, foto_perfil, ativo, criado_em, ultimo_login 
            FROM usuarios WHERE id = ?";
    $usuario = fetchOne($sql, [$usuario_logado['id']]);
    
    if (!$usuario) {
        session_destroy();
        redirect('login.php');
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar perfil: " . $e->getMessage());
    redirect('dashboard.php');
}

/**
 * =====================================================
 * PROCESSAMENTO DE ATUALIZAÇÃO DO PERFIL
 * =====================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erros = [];
    
    // Obtém dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    
    // Valida nome
    if (empty($nome)) {
        $erros[] = "O campo Nome é obrigatório.";
    } elseif (strlen($nome) < 3) {
        $erros[] = "O Nome deve ter no mínimo 3 caracteres.";
    }
    
    // Valida email
    if (empty($email)) {
        $erros[] = "O campo E-mail é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O E-mail informado não é válido.";
    } else {
        // Verifica se o email já está em uso por outro usuário
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ? AND id != ?";
        $stmt = query($sql, [$email, $usuario['id']]);
        if ($stmt->fetchColumn() > 0) {
            $erros[] = "Este e-mail já está sendo usado por outro usuário.";
        }
    }
    
    // Se está alterando a senha
    if (!empty($nova_senha)) {
        // Valida senha atual
        if (empty($senha_atual)) {
            $erros[] = "Digite sua senha atual para alterar a senha.";
        } elseif (!password_verify($senha_atual, $usuario['senha'])) {
            $erros[] = "Senha atual incorreta.";
        }
        
        // Valida nova senha
        if (strlen($nova_senha) < 6) {
            $erros[] = "A nova senha deve ter no mínimo 6 caracteres.";
        }
        
        // Valida confirmação
        if ($nova_senha !== $confirma_senha) {
            $erros[] = "A confirmação de senha não coincide.";
        }
    }
    
    // Se não houver erros, atualiza
    if (empty($erros)) {
        try {
            if (!empty($nova_senha)) {
                // Atualiza com nova senha
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
                query($sql, [$nome, $email, $senha_hash, $usuario['id']]);
            } else {
                // Atualiza sem alterar senha
                $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
                query($sql, [$nome, $email, $usuario['id']]);
            }
            
            // Atualiza dados na sessão
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            
            $mensagem = "Perfil atualizado com sucesso!";
            $tipo_mensagem = "success";
            
            // Atualiza variável local
            $usuario['nome'] = $nome;
            $usuario['email'] = $email;
            
        } catch (PDOException $e) {
            $erros[] = "Erro ao atualizar perfil: " . $e->getMessage();
        }
    }
    
    if (!empty($erros)) {
        $mensagem = implode("<br>", $erros);
        $tipo_mensagem = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Sistema de Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-header">
            <h1>👤 Meu Perfil</h1>
            <p>Gerencie suas informações pessoais</p>
        </div>
        
        <div class="auth-body">
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        required 
                        value="<?php echo htmlspecialchars($usuario['nome']); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="<?php echo htmlspecialchars($usuario['email']); ?>"
                    >
                </div>
                
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">
                
                <h3 style="margin-bottom: 20px; color: #495057;">Alterar Senha (opcional)</h3>
                
                <div class="form-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input 
                        type="password" 
                        id="senha_atual" 
                        name="senha_atual" 
                        placeholder="Digite sua senha atual"
                    >
                </div>
                
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input 
                        type="password" 
                        id="nova_senha" 
                        name="nova_senha" 
                        placeholder="Mínimo 6 caracteres"
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirma_senha">Confirmar Nova Senha</label>
                    <input 
                        type="password" 
                        id="confirma_senha" 
                        name="confirma_senha" 
                        placeholder="Digite a nova senha novamente"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
                
                <a href="dashboard.php" class="btn btn-secondary" style="margin-top: 10px;">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
    
    <script>
    // Remove mensagens automaticamente
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 5000);
    </script>
</body>
</html>
