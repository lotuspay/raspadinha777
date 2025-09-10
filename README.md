# Sistema de Raspadinha

Script baixado da internet e adaptado para o gateway Lotuspay.
Fornecido sem qualquer garantia.

Obs.: Utilize na URL /premios como presell. (lp).

## Configuração Inicial

### 1. Configuração do Banco de Dados

#### Importando o banco de dados:
1. Crie um banco de dados MySQL
2. Importe o arquivo `dump-raspadinha777-202509101425.sql`:
   ```bash
   mysql -u seu_usuario -p nome_do_banco < dump-raspadinha777-202509101425.sql
   ```

#### Credenciais de Acesso Padrão:
- **Email:** admin@admin
- **Senha:** raspa@admin123

### 2. Arquivos de Conexão com o MySQL

Atualize as credenciais do banco de dados nos seguintes arquivos:

1. `admin/config.php`
2. `includes/db.php`
3. `jogo/includes/db.php`

### 3. Configuração do Gateway de Pagamento

Atualize o token da Lotuspay no arquivo:
- `includes/lotuspay_api.php`

## Como Alterar a Senha do Administrador

1. Acesse o banco de dados
2. Execute o comando SQL:
   ```sql
   UPDATE users SET password = MD5('nova_senha') WHERE email = 'admin@admin';
   ```
   
   Ou use o painel administrativo após o primeiro login para alterar a senha de forma segura.

## Suporte

Este software é fornecido como está, sem garantias. Use por sua conta e risco.