# Task Manager Application

O projeto **Task Manager** é uma aplicação web para gerenciar tarefas de forma eficiente. Este projeto é composto por um frontend desenvolvido com Vue.js 2 e um backend construído com Laravel 8, utilizando PostgreSQL como banco de dados. Este README fornece instruções detalhadas para baixar, configurar e executar o projeto localmente no Windows 11.

## Funcionalidades

Este projeto permite que o usuário:
- Registrar um novo usuário
- Fazer login no sistema
- Criar tarefas
- Enviar um email de confirmação após criar uma tarefa
- Editar tarefas existentes
- Deletar tarefas existentes
- Filtrar tarefas por status e prioridade

## Funcionalidades BÔNUS
- Exportar tarefas em arquivo CSV
- Implementação de filas para envio assíncrono de emails
- Criação de usuário e empresa

## Pré-requisitos

Antes de começar, instale as seguintes ferramentas no seu sistema Windows 11:

- [PHP 8.1+](https://www.php.net/downloads.php) (compatível com Laravel 8)
- [Composer](https://getcomposer.org/download/) (gerenciador de dependências do PHP)
- [Node.js](https://nodejs.org/) e [npm] (versão 14.x ou superior recomendada, incluído com Node.js)
- [PostgreSQL](https://www.postgresql.org/download/windows/) (banco de dados)
- [Git](https://git-scm.com/download/win) (para controle de versão)
- [Visual Studio Code](https://code.visualstudio.com/) (recomendado como editor de código)
- [Insomnia](https://insomnia.rest/download) (para testes de APIs no backend)

Certifique-se de que todas as ferramentas estão configuradas e acessíveis via linha de comando (e.g., `php -v`, `node -v`, `psql --version`).

## Estrutura do Projeto

- `frontend/`: Código do frontend em Vue.js.
- `backend/`: Código do backend em Laravel 8.

## Passo a Passo para Executar o Projeto

### 1. Clone o Repositório
Clone o repositório para o seu computador.

```bash
git clone https://github.com/jonjgc/Task-Manager.git
```

Navegue até o diretório raiz do projeto:

```bash
cd task-manager
```

### 2. Configure o Backend (Laravel)

#### 2.1. Instale as Dependências do Backend
No diretório backend, instale as dependências do Laravel usando Composer:

```bash
cd backend
composer install
```

#### 2.2. Configure o Ambiente
Renomeie o arquivo `.env.example` para `.env` no diretório backend.  
Edite o arquivo `.env` com as configurações do banco de dados PostgreSQL. Exemplo:

```plaintext
APP_URL=http://localhost
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_manager
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Gere a chave de aplicação do Laravel:

```bash
php artisan key:generate
```

#### 2.3. Configure o Banco de Dados
Crie um banco de dados chamado `task_manager` no PostgreSQL usando o comando `psql` ou uma ferramenta como pgAdmin.  
Execute as migrações para criar as tabelas:

```bash
php artisan migrate
```

#### 2.4. Inicie o Servidor do Backend
Inicie o servidor Laravel:

```bash
php artisan serve
```
abra outro prompt para executar a implementação das filas

```bash
php artisan queue:work
```

A API estará disponível em `http://localhost:8000`.

### 3. Configure o Frontend (Vue.js)

#### 3.1. Instale as Dependências do Frontend
Navegue até o diretório frontend e instale as dependências:

```bash
cd ../frontend
npm install
```


#### 3.2. Inicie o Frontend
Inicie o servidor de desenvolvimento:

```bash
npm run serve
```

Abra o navegador em `http://localhost:8080` para acessar a aplicação.

### 4. Teste a Aplicação
- Use o Insomnia para testar os endpoints da API (ex.: `GET /api/tasks`, `POST /api/login`).  
- Faça login na aplicação com credenciais válidas (consulte o backend para usuários de teste).  
- Adicione, edite e delete tarefas para verificar o funcionamento.

## Tecnologias Utilizadas
- **Frontend**: Vue.js, Vue Router, VeeValidate  
- **Backend**: Laravel 8, PHP 8.1+  
- **Banco de Dados**: PostgreSQL  
- **Outros**: Axios (requisições HTTP), CSS puro, Composer, npm  

## Contribuindo
Contribuições são bem-vindas! Abra uma issue ou envie um pull request no repositório.

## Contato
Para dúvidas ou suporte, entre em contato em `jonatasmc04@gmail.com`.
