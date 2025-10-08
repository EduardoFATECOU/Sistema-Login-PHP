# Guia de Instalação - Sistema de Login PHP 8 PDO MariaDB

Este guia fornece instruções passo a passo para instalar e configurar o sistema de login em diferentes ambientes.

## 📋 Requisitos do Sistema

### Requisitos Mínimos

- **PHP**: Versão 8.0 ou superior
- **Banco de Dados**: MariaDB 10.3+ ou MySQL 5.7+
- **Servidor Web**: Apache 2.4+ ou Nginx 1.18+
- **Espaço em Disco**: 20 MB
- **Memória RAM**: 512 MB (recomendado 1 GB)

### Extensões PHP Necessárias

As seguintes extensões devem estar habilitadas:

- `pdo` - PHP Data Objects
- `pdo_mysql` - Driver PDO para MySQL/MariaDB
- `mbstring` - Manipulação de strings multibyte
- `json` - Suporte a JSON
- `session` - Gerenciamento de sessões

### Verificar Extensões

Crie um arquivo `info.php`:

```php
<?php phpinfo(); ?>
```

Acesse no navegador e procure pelas extensões listadas.

---

## 🪟 Instalação no Windows (XAMPP)

### Passo 1: Instalar o XAMPP

1. Baixe o XAMPP em: https://www.apachefriends.org/
2. Execute o instalador
3. Selecione os componentes: Apache, MySQL, PHP
4. Instale no diretório padrão: `C:\xampp`
5. Conclua a instalação

### Passo 2: Iniciar os Serviços

1. Abra o **XAMPP Control Panel**
2. Clique em **Start** ao lado de **Apache**
3. Clique em **Start** ao lado de **MySQL**
4. Aguarde os serviços ficarem verdes

### Passo 3: Copiar os Arquivos

1. Extraia o arquivo `sistema-login.zip`
2. Copie a pasta `sistema-login` para: `C:\xampp\htdocs\`
3. O caminho completo será: `C:\xampp\htdocs\sistema-login\`

### Passo 4: Criar o Banco de Dados

**Opção A: Via phpMyAdmin**

1. Abra o navegador e acesse: `http://localhost/phpmyadmin`
2. Clique em **Novo** na barra lateral
3. Digite o nome: `sistema_login`
4. Selecione collation: `utf8mb4_unicode_ci`
5. Clique em **Criar**
6. Clique na aba **SQL**
7. Abra o arquivo `database.sql` em um editor de texto
8. Copie todo o conteúdo
9. Cole na área de texto do phpMyAdmin
10. Clique em **Executar**

**Opção B: Via Linha de Comando**

```bash
# Abra o prompt de comando
cd C:\xampp\mysql\bin

# Execute o MySQL
mysql.exe -u root -p

# Dentro do MySQL
CREATE DATABASE sistema_login CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_login;
SOURCE C:/xampp/htdocs/sistema-login/database.sql;
EXIT;
```

### Passo 5: Configurar a Conexão

1. Abra o arquivo `config.php` em um editor de texto
2. Verifique as configurações:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_login');
define('DB_USER', 'root');
define('DB_PASS', '');  // Senha vazia no XAMPP por padrão
```

3. Ajuste `BASE_URL` se necessário:

```php
define('BASE_URL', 'http://localhost/sistema-login/');
```

### Passo 6: Acessar o Sistema

Abra o navegador e acesse:
```
http://localhost/sistema-login/
```

---

## 🪟 Instalação no Windows (WAMP)

### Passo 1: Instalar o WAMP

1. Baixe o WAMP em: https://www.wampserver.com/
2. Execute o instalador
3. Instale no diretório padrão: `C:\wamp64`
4. Conclua a instalação

### Passo 2: Iniciar o WAMP

1. Inicie o **WAMP Server** pelo menu Iniciar
2. Aguarde o ícone na bandeja ficar **verde**
3. Se ficar laranja ou vermelho, verifique as portas 80 e 3306

### Passo 3: Copiar os Arquivos

1. Copie a pasta `sistema-login` para: `C:\wamp64\www\`
2. O caminho completo será: `C:\wamp64\www\sistema-login\`

### Passos 4, 5 e 6

Siga os mesmos passos do XAMPP (criar banco, configurar e acessar).

---

## 🐧 Instalação no Linux (Ubuntu/Debian)

### Passo 1: Atualizar o Sistema

```bash
sudo apt update
sudo apt upgrade -y
```

### Passo 2: Instalar o LAMP Stack

```bash
# Instalar Apache
sudo apt install apache2 -y

# Instalar MariaDB
sudo apt install mariadb-server -y

# Instalar PHP 8 e extensões
sudo apt install php8.1 libapache2-mod-php8.1 php8.1-mysql php8.1-mbstring php8.1-json -y

# Verificar instalação
php -v
```

### Passo 3: Configurar o MariaDB

```bash
# Executar script de segurança
sudo mysql_secure_installation

# Responda as perguntas:
# - Enter current password: [pressione Enter]
# - Switch to unix_socket authentication: N
# - Change the root password: Y (digite uma senha segura)
# - Remove anonymous users: Y
# - Disallow root login remotely: Y
# - Remove test database: Y
# - Reload privilege tables: Y
```

### Passo 4: Criar o Banco de Dados

```bash
# Acessar o MariaDB
sudo mysql -u root -p

# Dentro do MySQL, execute:
CREATE DATABASE sistema_login CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar o arquivo SQL
sudo mysql -u root -p sistema_login < /caminho/para/database.sql
```

### Passo 5: Copiar os Arquivos

```bash
# Copiar arquivos para o diretório do Apache
sudo cp -r sistema-login /var/www/html/

# Ajustar permissões
sudo chown -R www-data:www-data /var/www/html/sistema-login
sudo chmod -R 755 /var/www/html/sistema-login

# Criar diretório de uploads
sudo mkdir /var/www/html/sistema-login/uploads
sudo chmod 775 /var/www/html/sistema-login/uploads
```

### Passo 6: Configurar o Apache

```bash
# Habilitar mod_rewrite (se necessário)
sudo a2enmod rewrite

# Reiniciar Apache
sudo systemctl restart apache2
```

### Passo 7: Configurar a Conexão

Edite o arquivo `config.php`:

```bash
sudo nano /var/www/html/sistema-login/config.php
```

Ajuste as credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_login');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha_aqui');
```

### Passo 8: Acessar o Sistema

Abra o navegador e acesse:
```
http://localhost/sistema-login/
```

---

## 🍎 Instalação no macOS

### Passo 1: Instalar o Homebrew

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### Passo 2: Instalar o LAMP Stack

```bash
# Instalar Apache (já vem no macOS, mas pode atualizar)
brew install httpd

# Instalar PHP 8
brew install php@8.1

# Instalar MariaDB
brew install mariadb

# Iniciar os serviços
brew services start httpd
brew services start mariadb
```

### Passo 3: Configurar o Apache

```bash
# Editar configuração do Apache
sudo nano /usr/local/etc/httpd/httpd.conf

# Encontre e descomente (remova o #):
LoadModule php_module /usr/local/opt/php@8.1/lib/httpd/modules/libphp.so

# Adicione no final do arquivo:
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>

# Reiniciar Apache
brew services restart httpd
```

### Passo 4: Criar o Banco de Dados

```bash
# Acessar MariaDB
mysql -u root

# Criar banco e importar SQL
CREATE DATABASE sistema_login CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_login;
SOURCE /caminho/para/database.sql;
EXIT;
```

### Passo 5: Copiar os Arquivos

```bash
# Copiar para o diretório do Apache
sudo cp -r sistema-login /usr/local/var/www/htdocs/

# Ajustar permissões
sudo chown -R _www:_www /usr/local/var/www/htdocs/sistema-login
```

### Passo 6: Acessar o Sistema

```
http://localhost/sistema-login/
```

---

## 🐳 Instalação com Docker (Avançado)

### Criar docker-compose.yml

```yaml
version: '3.8'

services:
  web:
    image: php:8.1-apache
    ports:
      - "8080:80"
    volumes:
      - ./sistema-login:/var/www/html
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=sistema_login
      - DB_USER=root
      - DB_PASS=senha123

  db:
    image: mariadb:10.6
    environment:
      - MYSQL_ROOT_PASSWORD=senha123
      - MYSQL_DATABASE=sistema_login
    volumes:
      - db_data:/var/lib/mysql
      - ./database.sql:/docker-entrypoint-initdb.d/database.sql

volumes:
  db_data:
```

### Executar

```bash
docker-compose up -d
```

Acesse: `http://localhost:8080`

---

## ✅ Verificação da Instalação

### Checklist

- [ ] Apache/Nginx está rodando
- [ ] MySQL/MariaDB está rodando
- [ ] PHP 8+ está instalado
- [ ] Extensões PDO estão habilitadas
- [ ] Banco `sistema_login` foi criado
- [ ] Tabelas `usuarios` e `tentativas_login` existem
- [ ] Arquivo `config.php` tem credenciais corretas
- [ ] Permissões dos arquivos estão corretas
- [ ] Diretório `uploads` existe e tem permissão de escrita

### Teste de Conexão

Crie `teste-conexao.php`:

```php
<?php
require_once 'conexao.php';

try {
    $pdo = getDB();
    echo "✅ Conexão estabelecida com sucesso!<br>";
    echo "Versão do PHP: " . phpversion() . "<br>";
    echo "Driver PDO: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

Acesse: `http://localhost/sistema-login/teste-conexao.php`

---

## 🔧 Solução de Problemas Comuns

### Erro: "Access denied for user 'root'@'localhost'"

**Causa**: Senha do banco incorreta.

**Solução**:
1. Verifique a senha em `config.php`
2. No XAMPP/WAMP, a senha padrão é vazia
3. No Linux/macOS, use a senha definida na instalação

### Erro: "Could not find driver"

**Causa**: Extensão PDO não habilitada.

**Solução**:
```bash
# Edite php.ini
sudo nano /etc/php/8.1/apache2/php.ini

# Descomente (remova o ;):
extension=pdo_mysql

# Reinicie Apache
sudo systemctl restart apache2
```

### Erro: "Table 'sistema_login.usuarios' doesn't exist"

**Causa**: Banco não foi criado corretamente.

**Solução**: Execute novamente o `database.sql`.

### Erro: "Connection refused"

**Causa**: MySQL/MariaDB não está rodando.

**Solução**:
```bash
# Linux
sudo systemctl start mariadb

# macOS
brew services start mariadb

# Windows
Inicie via XAMPP/WAMP Control Panel
```

### Página em branco

**Causa**: Erro de sintaxe no PHP.

**Solução**:
1. Habilite exibição de erros em `config.php`:
```php
define('DEV_MODE', true);
```
2. Verifique os logs do Apache
3. Teste a sintaxe: `php -l arquivo.php`

### CSS não carrega

**Causa**: Caminho incorreto ou cache.

**Solução**:
1. Limpe o cache do navegador (Ctrl+F5)
2. Verifique o caminho em `<link rel="stylesheet">`
3. Verifique permissões do arquivo CSS

### Sessão não persiste

**Causa**: Diretório de sessões sem permissão.

**Solução**:
```bash
# Linux
sudo chmod 755 /var/lib/php/sessions

# Ou configure um diretório personalizado
mkdir /var/www/html/sistema-login/sessions
chmod 755 /var/www/html/sistema-login/sessions
```

---

## 🔐 Configurações de Segurança Pós-Instalação

### 1. Alterar Senhas Padrão

```sql
-- Altere as senhas dos usuários de teste
UPDATE usuarios SET senha = PASSWORD_HASH('nova_senha_forte', PASSWORD_DEFAULT) WHERE email = 'admin@sistema.com';
```

### 2. Desabilitar Modo de Desenvolvimento

Em `config.php`:
```php
define('DEV_MODE', false);
```

### 3. Configurar HTTPS

Se tiver certificado SSL:
```php
ini_set('session.cookie_secure', 1);
define('BASE_URL', 'https://seudominio.com/sistema-login/');
```

### 4. Proteger Arquivos Sensíveis

Crie `.htaccess`:
```apache
<FilesMatch "^(config\.php|conexao\.php)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### 5. Configurar Backup Automático

```bash
# Criar script de backup
#!/bin/bash
mysqldump -u root -p sistema_login > backup_$(date +%Y%m%d).sql

# Adicionar ao cron
crontab -e
0 2 * * * /caminho/para/backup.sh
```

---

## 📚 Próximos Passos

Após a instalação:

1. **Teste todas as funcionalidades**
   - Cadastro de usuário
   - Login e logout
   - Edição de perfil
   - Listagem de usuários

2. **Personalize o sistema**
   - Altere cores e logo
   - Adicione campos personalizados
   - Implemente funcionalidades extras

3. **Configure backups**
   - Backup do banco de dados
   - Backup dos arquivos

4. **Monitore o sistema**
   - Verifique logs regularmente
   - Monitore tentativas de login
   - Acompanhe o desempenho

---

## 📞 Suporte

Se encontrar problemas:

1. Consulte este guia de instalação
2. Verifique os logs de erro do PHP e Apache
3. Teste a conexão com o banco separadamente
4. Verifique permissões de arquivos e diretórios
5. Consulte a documentação do PHP e MariaDB

---

**Boa instalação! 🚀**
