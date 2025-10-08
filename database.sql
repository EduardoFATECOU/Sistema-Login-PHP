-- =====================================================
-- Script de criação do banco de dados para Sistema de Login
-- Banco de dados: sistema_login
-- Tabela: usuarios
-- =====================================================

-- Cria o banco de dados se não existir
CREATE DATABASE IF NOT EXISTS sistema_login
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco de dados para uso
USE sistema_login;

-- Remove a tabela se já existir (útil para recriar)
DROP TABLE IF EXISTS usuarios;

-- Cria a tabela de usuários para o sistema de autenticação
CREATE TABLE usuarios (
    -- ID auto-incrementável como chave primária
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Nome completo do usuário (obrigatório)
    nome VARCHAR(100) NOT NULL,
    
    -- Endereço de email único para login (obrigatório)
    email VARCHAR(100) NOT NULL UNIQUE,
    
    -- Senha criptografada com password_hash() (obrigatório)
    -- VARCHAR(255) é o tamanho recomendado para armazenar hash bcrypt
    senha VARCHAR(255) NOT NULL,
    
    -- Foto de perfil (opcional) - armazena o caminho do arquivo
    foto_perfil VARCHAR(255) DEFAULT NULL,
    
    -- Status da conta (1 = ativa, 0 = inativa)
    ativo TINYINT(1) DEFAULT 1,
    
    -- Data e hora de criação da conta (preenchido automaticamente)
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Data e hora da última atualização (atualizado automaticamente)
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Data e hora do último login (NULL se nunca fez login)
    ultimo_login TIMESTAMP NULL DEFAULT NULL,
    
    -- Índice para otimizar buscas por email
    INDEX idx_email (email),
    
    -- Índice para otimizar buscas por status
    INDEX idx_ativo (ativo)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insere um usuário de exemplo para teste
-- Senha: 123456 (em produção, use senhas fortes!)
-- O hash abaixo foi gerado com password_hash('123456', PASSWORD_DEFAULT)
INSERT INTO usuarios (nome, email, senha, ativo) VALUES
('Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('João Silva', 'joao@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Maria Santos', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Exibe os dados inseridos
SELECT id, nome, email, ativo, criado_em FROM usuarios;

-- =====================================================
-- Tabela opcional para registrar tentativas de login
-- Útil para implementar proteção contra força bruta
-- =====================================================

DROP TABLE IF EXISTS tentativas_login;

CREATE TABLE tentativas_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    sucesso TINYINT(1) DEFAULT 0,
    data_tentativa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email_data (email, data_tentativa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
