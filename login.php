<?php
/**
 * =====================================================
 * Página de Login
 * =====================================================
 * Permite que usuários cadastrados façam login no sistema
 * Inclui validação de credenciais e controle de sessão
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
$email = '';

// Verifica se há mensagem de timeout de sessão
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $mensagem = "Sua sessão expirou por inatividade. Faça login novamente.";
    $tipo_mensagem = "warning";
}

// Verifica se há mensagem de logout
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $mensagem = "Você saiu do sistema com sucesso.";
    $tipo_mensagem = "success";
}

/**
 * =====================================================
 * PROCESSAMENTO DO FORMULÁRIO DE LOGIN
 * =====================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtém os dados do formulário
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $lembrar = isset($_POST['lembrar']);
    
    // Array para armazenar erros
    $erros = [];
    
    // =====================================================
    // VALIDAÇÕES BÁSICAS
    // =====================================================
    
    if (empty($email)) {
        $erros[] = "O campo E-mail é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O E-mail informado não é válido.";
    }
    
    if (empty($senha)) {
        $erros[] = "O campo Senha é obrigatório.";
    }
    
    // =====================================================
    // VERIFICAÇÃO DE TENTATIVAS DE LOGIN (Proteção contra força bruta)
    // =====================================================
    
    if (empty($erros)) {
        try {
            // Obtém o IP do usuário
            $ip_address = $_SERVER['REMOTE_ADDR'];
            
            // Verifica número de tentativas falhas nas últimas horas
            $sql = "SELECT COUNT(*) FROM tentativas_login 
                    WHERE email = ? AND ip_address = ? 
                    AND sucesso = 0 
                    AND data_tentativa > DATE_SUB(NOW(), INTERVAL ? SECOND)";
            
            $stmt = query($sql, [$email, $ip_address, LOCKOUT_TIME]);
            $tentativas = $stmt->fetchColumn();
            
            // Se excedeu o número máximo de tentativas
            if ($tentativas >= MAX_LOGIN_ATTEMPTS) {
                $minutos = ceil(LOCKOUT_TIME / 60);
                $erros[] = "Muitas tentativas de login falhadas. Tente novamente em $minutos minutos.";
            }
            
        } catch (PDOException $e) {
            // Se houver erro ao verificar tentativas, registra mas continua
            error_log("Erro ao verificar tentativas de login: " . $e->getMessage());
        }
    }
    
    // =====================================================
    // AUTENTICAÇÃO DO USUÁRIO
    // =====================================================
    
    if (empty($erros)) {
        try {
            // Busca o usuário pelo email
            $sql = "SELECT id, nome, email, senha, foto_perfil, ativo FROM usuarios WHERE email = ?";
            $usuario = fetchOne($sql, [$email]);
            
            // Verifica se o usuário existe
            if (!$usuario) {
                $erros[] = "E-mail ou senha incorretos.";
                $login_sucesso = false;
            } 
            // Verifica se a conta está ativa
            elseif ($usuario['ativo'] != 1) {
                $erros[] = "Sua conta está inativa. Entre em contato com o administrador.";
                $login_sucesso = false;
            }
            // Verifica a senha usando password_verify()
            // Esta função compara a senha digitada com o hash armazenado
            elseif (!password_verify($senha, $usuario['senha'])) {
                $erros[] = "E-mail ou senha incorretos.";
                $login_sucesso = false;
            }
            // Login bem-sucedido!
            else {
                $login_sucesso = true;
                
                // =====================================================
                // CRIA A SESSÃO DO USUÁRIO
                // =====================================================
                
                // Regenera o ID da sessão para prevenir session fixation
                session_regenerate_id(true);
                
                // Armazena dados do usuário na sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_foto'] = $usuario['foto_perfil'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                // Atualiza o timestamp do último login no banco de dados
                $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
                query($sql, [$usuario['id']]);
                
                // Se marcou "Lembrar-me", define cookie de longa duração
                if ($lembrar) {
                    // Cookie expira em 30 dias
                    $expire = time() + (30 * 24 * 60 * 60);
                    
                    // Gera um token único para o cookie
                    $token = bin2hex(random_bytes(32));
                    
                    // Armazena o token no cookie
                    setcookie('remember_token', $token, $expire, '/', '', false, true);
                    
                    // Aqui você poderia armazenar o token no banco para validação futura
                    // (implementação mais avançada)
                }
                
                // Registra log de sucesso
                if (DEV_MODE) {
                    error_log("Login bem-sucedido: " . $usuario['email']);
                }
                
                // Redireciona para a página solicitada ou dashboard
                $redirect_to = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirect_to);
            }
            
            // =====================================================
            // REGISTRA A TENTATIVA DE LOGIN
            // =====================================================
            
            try {
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $sql = "INSERT INTO tentativas_login (email, ip_address, sucesso) VALUES (?, ?, ?)";
                query($sql, [$email, $ip_address, $login_sucesso ? 1 : 0]);
            } catch (PDOException $e) {
                error_log("Erro ao registrar tentativa de login: " . $e->getMessage());
            }
            
        } catch (PDOException $e) {
            // Captura erros de banco de dados
            if (DEV_MODE) {
                $erros[] = "Erro ao processar login: " . $e->getMessage();
            } else {
                $erros[] = "Erro ao processar login. Tente novamente mais tarde.";
            }
            error_log("Erro no login: " . $e->getMessage());
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
    <title>Login - Sistema de Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <!-- Cabeçalho do formulário -->
        <div class="auth-header">
            <h1>🔐 Bem-vindo</h1>
            <p>Faça login para acessar o sistema</p>
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
            
            <!-- Formulário de login -->
            <form method="POST" action="" id="formLogin">
                
                <!-- Campo: Email -->
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="seu@email.com"
                        value="<?php echo htmlspecialchars($email); ?>"
                        autofocus
                    >
                </div>
                
                <!-- Campo: Senha -->
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            required 
                            placeholder="Digite sua senha"
                        >
                        <span class="password-toggle" onclick="togglePassword('senha')">
                            👁️
                        </span>
                    </div>
                </div>
                
                <!-- Checkbox: Lembrar-me -->
                <div class="form-check">
                    <input 
                        type="checkbox" 
                        id="lembrar" 
                        name="lembrar"
                    >
                    <label for="lembrar">Lembrar-me neste dispositivo</label>
                </div>
                
                <!-- Botão de login -->
                <button type="submit" class="btn btn-primary">
                    Entrar
                </button>
            </form>
        </div>
        
        <!-- Rodapé com link para cadastro -->
        <div class="auth-footer">
            <p>Ainda não possui uma conta?</p>
            <a href="cadastro.php">Criar Conta Grátis</a>
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
     * Remove mensagens de erro automaticamente após 5 segundos
     */
    setTimeout(function() {
        const alert = document.querySelector('.alert-error');
        if (alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 5000);
    
    /**
     * Validação básica no lado do cliente
     */
    document.getElementById('formLogin').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;
        
        if (!email || !senha) {
            e.preventDefault();
            alert('Por favor, preencha todos os campos!');
            return false;
        }
        
        // Validação simples de formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Por favor, digite um e-mail válido!');
            return false;
        }
    });
    </script>
</body>
</html>
