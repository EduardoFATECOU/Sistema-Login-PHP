<?php
/**
 * =====================================================
 * Página de Cadastro de Novos Usuários
 * =====================================================
 * Permite que novos usuários se registrem no sistema
 * Inclui validações e criptografia de senha
 */

// Inclui os arquivos necessários
require_once 'config.php';
require_once 'conexao.php';

// Se o usuário já estiver logado, redireciona para o dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Inicializa variáveis
$mensagem = '';
$tipo_mensagem = '';
$erros = [];

/**
 * =====================================================
 * PROCESSAMENTO DO FORMULÁRIO DE CADASTRO
 * =====================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtém e sanitiza os dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    
    // =====================================================
    // VALIDAÇÕES DOS DADOS
    // =====================================================
    
    // Valida nome
    if (empty($nome)) {
        $erros[] = "O campo Nome é obrigatório.";
    } elseif (strlen($nome) < 3) {
        $erros[] = "O Nome deve ter no mínimo 3 caracteres.";
    } elseif (strlen($nome) > 100) {
        $erros[] = "O Nome deve ter no máximo 100 caracteres.";
    }
    
    // Valida email
    if (empty($email)) {
        $erros[] = "O campo E-mail é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O E-mail informado não é válido.";
    } else {
        // Verifica se o email já está cadastrado
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
            $stmt = query($sql, [$email]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $erros[] = "Este e-mail já está cadastrado no sistema.";
            }
        } catch (PDOException $e) {
            $erros[] = "Erro ao verificar e-mail: " . $e->getMessage();
        }
    }
    
    // Valida senha
    if (empty($senha)) {
        $erros[] = "O campo Senha é obrigatório.";
    } elseif (strlen($senha) < 6) {
        $erros[] = "A Senha deve ter no mínimo 6 caracteres.";
    } elseif (strlen($senha) > 255) {
        $erros[] = "A Senha deve ter no máximo 255 caracteres.";
    }
    
    // Valida confirmação de senha
    if (empty($confirma_senha)) {
        $erros[] = "O campo Confirmar Senha é obrigatório.";
    } elseif ($senha !== $confirma_senha) {
        $erros[] = "As senhas não coincidem.";
    }
    
    // =====================================================
    // CADASTRO DO USUÁRIO
    // =====================================================
    
    // Se não houver erros, processa o cadastro
    if (empty($erros)) {
        try {
            // Criptografa a senha usando password_hash()
            // PASSWORD_DEFAULT usa o algoritmo bcrypt (mais seguro)
            // O hash gerado tem 60 caracteres e inclui o salt automaticamente
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Prepara a query SQL para inserção
            $sql = "INSERT INTO usuarios (nome, email, senha, ativo) VALUES (?, ?, ?, 1)";
            
            // Executa a inserção e obtém o ID do novo usuário
            $usuario_id = insert($sql, [$nome, $email, $senha_hash]);
            
            // Registra a tentativa de cadastro bem-sucedida
            if (DEV_MODE) {
                error_log("Novo usuário cadastrado: ID $usuario_id - $email");
            }
            
            // Define mensagem de sucesso
            $mensagem = "Cadastro realizado com sucesso! Você já pode fazer login.";
            $tipo_mensagem = "success";
            
            // Limpa os campos do formulário
            $nome = '';
            $email = '';
            
            // Opcional: Redireciona automaticamente para o login após 2 segundos
            header("Refresh: 2; url=login.php");
            
        } catch (PDOException $e) {
            // Captura erros de banco de dados
            if (DEV_MODE) {
                $erros[] = "Erro ao cadastrar usuário: " . $e->getMessage();
            } else {
                $erros[] = "Erro ao cadastrar usuário. Tente novamente mais tarde.";
            }
            error_log("Erro no cadastro: " . $e->getMessage());
        }
    }
    
    // Se houver erros, monta a mensagem
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
    <title>Cadastro - Sistema de Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <!-- Cabeçalho do formulário -->
        <div class="auth-header">
            <h1>📝 Criar Conta</h1>
            <p>Preencha os dados para se cadastrar</p>
        </div>
        
        <!-- Corpo do formulário -->
        <div class="auth-body">
            <?php
            /**
             * Exibe mensagens de feedback
             */
            if (!empty($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulário de cadastro -->
            <form method="POST" action="" id="formCadastro">
                
                <!-- Campo: Nome -->
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        required 
                        placeholder="Digite seu nome completo"
                        value="<?php echo htmlspecialchars($nome ?? ''); ?>"
                        maxlength="100"
                    >
                </div>
                
                <!-- Campo: Email -->
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="seu@email.com"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        maxlength="100"
                    >
                </div>
                
                <!-- Campo: Senha -->
                <div class="form-group">
                    <label for="senha">Senha *</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            required 
                            placeholder="Mínimo 6 caracteres"
                            minlength="6"
                            maxlength="255"
                        >
                        <span class="password-toggle" onclick="togglePassword('senha')">
                            👁️
                        </span>
                    </div>
                    <!-- Indicador de força da senha -->
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>
                
                <!-- Campo: Confirmar Senha -->
                <div class="form-group">
                    <label for="confirma_senha">Confirmar Senha *</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="confirma_senha" 
                            name="confirma_senha" 
                            required 
                            placeholder="Digite a senha novamente"
                            minlength="6"
                            maxlength="255"
                        >
                        <span class="password-toggle" onclick="togglePassword('confirma_senha')">
                            👁️
                        </span>
                    </div>
                </div>
                
                <!-- Botão de cadastro -->
                <button type="submit" class="btn btn-primary">
                    Criar Conta
                </button>
            </form>
        </div>
        
        <!-- Rodapé com link para login -->
        <div class="auth-footer">
            <p>Já possui uma conta?</p>
            <a href="login.php">Fazer Login</a>
        </div>
    </div>
    
    <script>
    /**
     * =====================================================
     * JavaScript para funcionalidades do formulário
     * =====================================================
     */
    
    /**
     * Alterna visibilidade da senha
     * @param {string} fieldId ID do campo de senha
     */
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
        field.setAttribute('type', type);
    }
    
    /**
     * Verifica força da senha e atualiza indicador visual
     */
    const senhaInput = document.getElementById('senha');
    const strengthBar = document.getElementById('strengthBar');
    
    senhaInput.addEventListener('input', function() {
        const senha = this.value;
        let strength = 0;
        
        // Critérios de força
        if (senha.length >= 6) strength++;
        if (senha.length >= 10) strength++;
        if (/[a-z]/.test(senha) && /[A-Z]/.test(senha)) strength++;
        if (/\d/.test(senha)) strength++;
        if (/[^a-zA-Z\d]/.test(senha)) strength++;
        
        // Remove classes anteriores
        strengthBar.className = 'password-strength-bar';
        
        // Aplica classe baseada na força
        if (strength <= 2) {
            strengthBar.classList.add('strength-weak');
        } else if (strength <= 4) {
            strengthBar.classList.add('strength-medium');
        } else {
            strengthBar.classList.add('strength-strong');
        }
    });
    
    /**
     * Validação adicional no lado do cliente
     */
    document.getElementById('formCadastro').addEventListener('submit', function(e) {
        const senha = document.getElementById('senha').value;
        const confirmaSenha = document.getElementById('confirma_senha').value;
        
        // Verifica se as senhas coincidem
        if (senha !== confirmaSenha) {
            e.preventDefault();
            alert('As senhas não coincidem!');
            return false;
        }
        
        // Verifica comprimento mínimo
        if (senha.length < 6) {
            e.preventDefault();
            alert('A senha deve ter no mínimo 6 caracteres!');
            return false;
        }
    });
    
    /**
     * Remove mensagens de alerta automaticamente após 5 segundos
     */
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert && !alert.classList.contains('alert-success')) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 5000);
    </script>
</body>
</html>
