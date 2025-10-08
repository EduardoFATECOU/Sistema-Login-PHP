<?php
/**
 * =====================================================
 * Página de Listagem de Usuários
 * =====================================================
 * Exibe todos os usuários cadastrados no sistema
 * Área restrita - requer autenticação
 */

// Inclui os arquivos necessários
require_once 'config.php';
require_once 'conexao.php';

// Protege a página - redireciona para login se não estiver autenticado
requireLogin();

// Obtém o usuário logado
$usuario_logado = getUsuarioLogado();

/**
 * =====================================================
 * BUSCA TODOS OS USUÁRIOS
 * =====================================================
 */
try {
    // Query para buscar todos os usuários ordenados por data de cadastro
    $sql = "SELECT id, nome, email, ativo, criado_em, ultimo_login 
            FROM usuarios 
            ORDER BY criado_em DESC";
    
    $usuarios = fetchAll($sql);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    $usuarios = [];
}

/**
 * Formata data para exibição
 */
function formatarData($data) {
    if (!$data) return 'Nunca';
    return date('d/m/Y H:i', strtotime($data));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema de Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos adicionais para a tabela */
        .usuarios-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 1200px;
            width: 100%;
            overflow: hidden;
        }
        
        .table-container {
            padding: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tbody tr {
            transition: background-color 0.2s ease;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="usuarios-container">
        <div class="dashboard-header">
            <h1>👥 Usuários Cadastrados</h1>
            <div class="user-info">
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($usuario_logado['nome']); ?></h3>
                    <p>Logado como <?php echo htmlspecialchars($usuario_logado['email']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <a href="dashboard.php" class="back-link">← Voltar ao Dashboard</a>
            
            <p style="color: #6c757d; margin-bottom: 20px;">
                Total de usuários cadastrados: <strong><?php echo count($usuarios); ?></strong>
            </p>
            
            <?php if (count($usuarios) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                                <th>Último Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id']; ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td>
                                        <?php if ($usuario['ativo']): ?>
                                            <span class="badge badge-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatarData($usuario['criado_em']); ?></td>
                                    <td><?php echo formatarData($usuario['ultimo_login']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Nenhum usuário cadastrado no sistema.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
