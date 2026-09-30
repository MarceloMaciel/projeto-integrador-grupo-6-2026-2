# Sistema de Geração de Notas Fiscais para Restaurante

Projeto Integrador em Computação II — UNIVESP, Polo Mogi das Cruzes.

Aplicação web para organizar as vendas de um restaurante e gerar as notas fiscais a partir dos pedidos.

## Estado atual

Esta é a **estrutura inicial** do projeto. O que já está pronto:

- Projeto Laravel configurado no padrão MVC
- Acesso ao sistema: login, recuperação de senha e edição de perfil
- Interface em português, com identidade do sistema
- Painel inicial listando os módulos previstos
- Banco de dados local em SQLite

O sistema é de **uso interno**: não existe cadastro público de usuários. O acesso é
criado pelo *seeder* (ver credenciais no passo 3).

Ainda **não** implementado: cadastro de produtos, lançamento de pedidos e geração da nota fiscal.

## Tecnologias

| Camada | Ferramenta |
| --- | --- |
| Linguagem | PHP 8.3 ou superior |
| Framework | Laravel 13 |
| Telas | Blade + Tailwind CSS |
| Interatividade | JavaScript |
| Build de assets | Vite (Node.js) |
| Banco de dados | SQLite (local, nesta etapa) |
| Ambiente de desenvolvimento | Docker + Docker Compose (Nginx + PHP-FPM + Node) |

---

## 1. Instalar o pré-requisito

O jeito oficial de rodar o projeto localmente é via **Docker** — é só isso que precisa estar instalado.

### Linux

Instale o Docker Engine + o plugin do Compose (o script oficial detecta a distro e faz tudo):

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
```

Depois **saia e entre de novo na sessão** (ou rode `newgrp docker`) para usar `docker` sem `sudo`.

### macOS e Windows

Instale o **[Docker Desktop](https://www.docker.com/products/docker-desktop/)**. No Windows, use o backend **WSL2** (o próprio instalador já sugere isso).

### Em qualquer sistema, confira se está tudo no lugar

```bash
docker --version
docker compose version
```

> Prefere não usar Docker? Existe um caminho alternativo (PHP/Composer/Node instalados na máquina) na seção [Alternativa sem Docker](#alternativa-sem-docker), mas o suportado e testado pelo grupo é o Docker.

## 2. Clonar e subir o projeto

```bash
git clone https://github.com/MarceloMaciel/projeto-integrador-grupo-6-2026-2.git
cd projeto-integrador-grupo-6-2026-2
docker compose up -d
```

Na primeira vez isso builda as imagens, instala as dependências do PHP e do Node, e o
container `app` roda automaticamente (via `docker/entrypoint.sh`): cria o `.env` a partir
do `.env.example`, gera a chave da aplicação, cria o banco SQLite, aplica as migrations e
cria o usuário de acesso ao sistema (seeder — só na primeira vez). Acompanhe pelo log:

```bash
docker compose logs -f app
```

Espere aparecer `Setup complete. Starting php-fpm...` antes de acessar a aplicação.

---

## 3. Acessar a aplicação

Acesse **<http://localhost:8000>**.

Entre com as credenciais criadas pelo seeder:

| Campo | Valor |
| --- | --- |
| E-mail | `proprietaria@restaurante.test` |
| Senha | `senha1234` |

São credenciais **apenas para uso local**, definidas no `.env` (`OWNER_EMAIL` e
`OWNER_PASSWORD`). Se quiser outras, altere o `.env` e rode
`docker compose exec app php artisan db:seed`.

### Editando CSS ou JavaScript

Não precisa rodar nada à parte: o `docker compose up` já sobe um container `node` com o
Vite em modo de desenvolvimento (<http://localhost:5173>), recompilando automaticamente a
cada arquivo salvo.

---

## Comandos úteis (Docker)

| Comando | O que faz |
| --- | --- |
| `docker compose up -d` | Sobe (ou religa) os containers em segundo plano |
| `docker compose down` | Para e remove os containers (o código e o banco continuam no disco) |
| `docker compose logs -f app` | Acompanha o log do container da aplicação |
| `docker compose exec app php artisan migrate` | Aplica as migrations pendentes no banco |
| `docker compose exec app php artisan db:seed` | Recria o usuário de acesso ao sistema |
| `docker compose exec app php artisan test` | Roda os testes automatizados |
| `docker compose exec app php artisan route:list` | Lista todas as rotas da aplicação |
| `docker compose exec app php artisan [comando]` | Roda qualquer comando Artisan dentro do container |
| `docker compose build` | Reconstrói a imagem (depois de mudar o `Dockerfile` ou `composer.json`) |

---

## Problemas comuns

**Docker Desktop não está rodando / erro de conexão com o daemon**
Abra o Docker Desktop e espere o ícone indicar que ele está pronto antes de rodar `docker compose up`.

**Porta 8000, 5173 ou 9000 já em uso**
Outro processo (ex.: um `php artisan serve` esquecido rodando) está usando a porta. Pare o processo local ou ajuste a porta no `docker-compose.yml`.

**Arquivos criados pelo container aparecem com outro dono/permissão no host (comum no Linux/WSL; raro no Docker Desktop para Mac)**
O `Dockerfile` aceita `HOST_UID`/`HOST_GID` como variáveis de ambiente para casar o usuário `www-data` do container com o seu usuário no host. Defina-as antes do build se precisar:

```bash
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose up -d --build
```

**`Vite manifest not found` ou tela sem estilo**
Confirme que o container `node` está de pé (`docker compose ps`) e rodando o Vite (`docker compose logs node`).

**Erro de banco de dados ou tabela inexistente**
Aplique as migrations manualmente:

```bash
docker compose exec app php artisan migrate
```

---

## Observações

- O arquivo **`.env` não é versionado** — cada pessoa tem o seu, criado automaticamente pelo `docker/entrypoint.sh` na primeira subida. Ele guarda configurações locais e a chave da aplicação.
- O banco **`database/database.sqlite` também não é versionado**: cada um tem o seu banco local, com os próprios dados de teste.
- Nesta etapa o banco é local (dentro do container/bind mount). Em uma etapa seguinte do projeto ele passará a ser hospedado na nuvem.

---

## Alternativa sem Docker

<details>
<summary>Rodar o projeto nativamente, sem Docker (PHP + Composer + Node na máquina)</summary>

É preciso ter **PHP 8.3+**, **Composer** e **Node.js 20+**.

### Linux

Instale o PHP + Composer com o script oficial (detecta a distro):

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

Depois instale o Node.js 20+ — a versão empacotada pela distro costuma estar desatualizada, então é melhor usar o [nvm](https://github.com/nvm-sh/nvm):

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
\. "$HOME/.nvm/nvm.sh"
nvm install 20
```

**Abra um terminal novo** (ou rode `source ~/.bashrc`/`source ~/.zshrc`) antes de continuar.

### macOS

Instale o PHP + Composer com o script oficial:

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Depois instale o Node.js 20+, com [Homebrew](https://brew.sh/) ou [nvm](https://github.com/nvm-sh/nvm):

```bash
brew install node@20
```

### Windows

Abra o **PowerShell** e rode:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Depois instale o Node.js:

```powershell
winget install OpenJS.NodeJS.LTS
```

**Feche e abra o terminal novamente** — os instaladores alteram o PATH, e os comandos só funcionam em um terminal novo.

### Em qualquer sistema, confira se está tudo no lugar

```bash
php -v
composer -V
node -v
```

<details>
<summary>Alternativa no Windows: instalar o PHP pelo winget (exige um ajuste manual)</summary>

O PHP distribuído pelo winget vem **sem arquivo de configuração**, e por isso as extensões que o Laravel precisa ficam desligadas. Se optar por esse caminho:

```powershell
winget install PHP.PHP.8.4
winget install OpenJS.NodeJS.LTS
```

Em seguida, vá até a pasta de instalação do PHP (algo como
`C:\Users\SEU_USUARIO\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_...`),
copie o arquivo `php.ini-development` para `php.ini` e, editando esse `php.ini`,
descomente (remova o `;` do começo da linha) as seguintes linhas:

```ini
extension_dir = "ext"
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=pdo_pgsql
extension=zip
extension=intl
```

O Composer precisa ser instalado à parte, seguindo as instruções em <https://getcomposer.org/download/>.

</details>

Clone e configure:

```bash
git clone https://github.com/MarceloMaciel/projeto-integrador-grupo-6-2026-2.git
cd projeto-integrador-grupo-6-2026-2
composer run setup
```

Esse único comando faz toda a configuração:

1. instala as dependências do PHP (`composer install`);
2. cria o arquivo `.env` a partir do `.env.example`;
3. gera a chave de criptografia da aplicação;
4. cria o banco SQLite e aplica as migrations (tabelas);
5. cria o usuário de acesso ao sistema;
6. instala as dependências do Node e compila o CSS/JS.

Depois, para rodar:

```bash
php artisan serve
```

Acesse <http://localhost:8000> com as mesmas credenciais da seção 3. Se for editar CSS ou
JavaScript, deixe o Vite rodando em um segundo terminal (`npm run dev`).

**Problemas comuns deste caminho:**

- **`php`, `composer` ou `npm` não é reconhecido como comando** — feche o terminal e abra um novo (os instaladores alteram o PATH).
- **`could not find driver` ou erro de extensão ausente** — falta habilitar uma extensão do PHP; veja a seção do winget acima.
- **`Vite manifest not found`** — rode `npm run build`.

</details>
