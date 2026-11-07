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

- Cardápio do restaurante no banco, com consulta, cadastro de produtos e edição de preços por porção
- Base da nota fiscal: geração do XML de uma **NFC-e simulada** (modelo 65), assinado com
  um certificado de teste e validado contra o XSD oficial. O documento **nunca é enviado à
  SEFAZ** e não tem validade fiscal.

- Pedidos com fechamento, pagamento e CPF opcional do consumidor
- Geração da NFC-e simulada a partir do pedido, histórico, download do XML e comprovante para impressão ou salvamento em PDF pelo navegador

Ainda **não** implementado: emissão real com autorização da SEFAZ e PDF gerado no servidor.

### Gerar uma nota a partir do pedido

1. Acesse **Novo pedido**, escolha os itens, informe o pagamento e o CPF na nota, se solicitado, e feche o pedido.
2. Na tela do pedido, clique em **Gerar NFC-e simulada**.
3. Use **Baixar XML** ou **Imprimir / salvar PDF**. Para PDF, selecione **Salvar como PDF** na janela de impressão do navegador.
4. Para consultar novamente, acesse **Pedidos e notas** no menu e abra o pedido.

A nota usa os preços e dados fiscais copiados no fechamento do pedido. O XML e os dados do comprovante são preservados no banco; repetir a geração devolve a mesma nota. A numeração da simulação usa o ID do documento, com uma nota por pedido.

Após atualizar o projeto, rode `docker compose exec app php artisan migrate`.
O certificado de teste deve existir; se necessário, gere com `docker compose exec app php artisan fiscal:test-certificate`.

O comprovante simulado segue a organização do **Manual de Padrões Técnicos do DANFE-NFC-e e QR Code, versão 6.0 (março/2025)**, seções 3.1 a 3.4: [manual oficial](https://www.nfe.fazenda.gov.br/portal/exibirArquivo.aspx?conteudo=k%2FIuuaW4YiY%3D).
O layout usa cupom de 80 mm, margens laterais de 2 mm, emitente, código e descrição dos itens, quantidades, unidades, preços, totais, pagamento, troco, chave em blocos de quatro dígitos, consumidor, identificação da nota e QR Code centralizado de 40 mm incluindo margem branca. Os dados e o conteúdo do QR Code são extraídos do **XML salvo**, inclusive a descrição de homologação do primeiro item.

Para imprimir, use escala de **100%**, papel de 80 mm (ou A4) e desative cabeçalhos e rodapés do navegador. O tamanho físico depende dessas opções e da impressora.
O QR Code é gerado localmente; aponta para a URL de homologação gravada no XML, mas a nota simulada **não está registrada na SEFAZ**. O protocolo de autorização é indicado como inexistente, e o aviso de homologação permanece destacado. O layout é uma demonstração acadêmica, não um DANFE autorizado.

Após atualizar as dependências, execute `docker compose exec app composer install` para disponibilizar a biblioteca de QR Code.

### Dados do restaurante no emitente

Configure no `.env` local as variáveis `FISCAL_CNPJ`, `FISCAL_LEGAL_NAME`, `FISCAL_TRADE_NAME`, `FISCAL_PHONE` e `FISCAL_ADDRESS_*`. Use o código **IBGE** de sete dígitos em `FISCAL_ADDRESS_CITY_CODE` (não o código de município da Receita Federal). Após alterar, rode `docker compose exec app php artisan config:clear`.

Consultas públicas de CNPJ podem fornecer razão social, nome fantasia, endereço e telefone, mas não confirmam necessariamente a **inscrição estadual** nem toda a configuração tributária. Confirme `FISCAL_IE` e `FISCAL_TAX_REGIME` antes de qualquer integração externa. Na simulação local, uma IE fictícia deve ser identificada como tal na configuração.

O cadastro municipal pode ser registrado separadamente em `FISCAL_MUNICIPAL_REGISTRATION`, `FISCAL_MUNICIPAL_CNAE`, `FISCAL_BUSINESS_SIZE` e `FISCAL_LEGAL_NATURE`. O CNAE municipal não substitui automaticamente a atividade principal do cadastro federal. Use `FISCAL_TAX_REGIME=1` para o enquadramento Simples Nacional adotado nesta simulação. `FISCAL_ISS_APPLICABLE=false` registra a informação de não aplicação de ISS fornecida pelo estabelecimento; este campo é apenas cadastral e não é um motor de tributação. A geração atual não inclui serviços nem grupos ISSQN no XML. Inscrição municipal não deve ser preenchida em `FISCAL_IE`.

As alterações do emitente valem para as próximas notas: XML e DANFE já gerados preservam os dados da emissão original. O certificado autoassinado da simulação pode ser recriado para o emitente configurado com `docker compose exec app php artisan fiscal:test-certificate --force`; isso não cria um certificado ICP-Brasil.

**Nota Fiscal Paulista** é o programa de São Paulo, e o documento utilizado neste fluxo é a **NFC-e (modelo 65)**, com CPF quando informado. Esta implementação é acadêmica: não transmite o XML à SEFAZ, não tem autorização de uso, não substitui o documento fiscal de uma venda real e **não gera créditos na Nota Fiscal Paulista**. A emissão real exige uma integração própria com a SEFAZ e as credenciais fiscais do estabelecimento.

### Gerenciar o cardápio

Após entrar no sistema, acesse **Cardápio**:

- Para cadastrar, clique em **Novo produto**, selecione a categoria e preencha o nome, as porções e os preços.
- Para atualizar preços, clique em **Editar produto e preços** no produto, altere os valores em **Porções e preços** e clique em **Salvar**.
- Os preços aceitam vírgula ou ponto nos centavos (por exemplo, `38,50` ou `38.50`), sem separador de milhar, e devem ser maiores que zero.
- Use **Adicionar variação** para cadastrar outras porções. Para um produto sem tamanhos, use uma opção como **Unidade**.
- Selecione o tipo do produto para preencher os dados fiscais. Para retirar um item de venda, desmarque **Produto ativo**; ele pode ser encontrado pelo filtro **Mostrar inativos**.

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
| `docker compose up -d --build` | Reconstrói a imagem e religa (depois de mudar o `Dockerfile` ou o `docker/entrypoint.sh`) |
| `docker compose exec app php artisan fiscal:test-certificate` | Gera o certificado de teste que assina a NFC-e simulada (o container já faz isso ao subir) |

---

## Testes no navegador (Cypress)

Dois testes de ponta a ponta rodam no navegador, contra o sistema já no ar (`docker compose up`):

- **Funcional** (`cypress/e2e/pedido-nota.cy.js`): monta um pedido, gera a NFC-e simulada e confere o comprovante com a tarja de homologação e o histórico.
- **Não funcional** (`cypress/e2e/acessibilidade.cy.js`): verificação automática de acessibilidade (WCAG 2.1 A e AA, com `cypress-axe`) nas telas de pedido, nota, comprovante, histórico e login; reprova violações graves e críticas.

Rode na máquina, fora do Docker (precisa do Node):

```bash
npm install --ignore-scripts
npx cypress install
npm run test:e2e -- --browser edge
```

No Windows, o navegador padrão do Cypress (Electron) pode fechar sozinho; por isso o `--browser edge` (ou `chrome`). Para testar o site publicado: `CYPRESS_BASE_URL=https://... npm run test:e2e`. Os testes criam pedidos e notas no banco do endereço testado.

---

## Publicar no Render (site + Postgres)

O deploy usa só o Render, no plano gratuito: o `render.yaml` (Blueprint) cria o site, construído pelo `Dockerfile.render`, e um banco Postgres do próprio Render. O `docker/render-start.sh` aplica as migrations, popula o usuário e o cardápio só no banco vazio, gera o certificado de teste e sobe o servidor.

1. No [painel do Render](https://dashboard.render.com), clique em **New → Blueprint** e escolha este repositório (branch `main`).
2. Preencha as variáveis pedidas: `APP_KEY` (gere com `docker compose exec app php artisan key:generate --show`) e `OWNER_PASSWORD` (senha forte, diferente da do seeder).
3. Opcional: para o emitente real na nota, cadastre em **Environment** do serviço as variáveis `FISCAL_*` do seu `.env` local. Sem elas, a nota sai com o emitente fictício. Nunca as coloque no `render.yaml`.
4. Cada push na `main` publica de novo.

Limites do plano gratuito: o site dorme após uns 15 minutos sem acesso (o primeiro acesso depois disso demora cerca de 1 minuto), e **o Postgres gratuito expira 30 dias depois de criado**. Para tirar do ar, apague o serviço e o banco no painel.

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
- Localmente o banco é SQLite (dentro do container/bind mount). No site publicado, é o Postgres do Render (ver "Publicar no Render").

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
