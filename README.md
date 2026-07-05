# LocAtivo

LocAtivo é uma plataforma de gestão de ativos para empresas de locação de equipamentos, como geradores, compressores e andaimes. Ela acompanha o equipamento do cadastro até o encerramento do contrato, passando por checklist de saída, checklist de retorno e ordens de manutenção.

A ideia surgiu de um caso real: uma empresa de locação de geradores em Chapecó que ainda controla contratos, checklists e manutenções em planilhas e conversas de WhatsApp.

## Sobre esta stack

Este repositório foi desenvolvido em Laravel e PHP, seguindo a stack descrita no relatório técnico parcial do Projeto Integrador.


## Tecnologias utilizadas

| Tecnologia | Papel no projeto |
|---|---|
| Laravel 11 (PHP 8.3) | Framework principal: rotas, Eloquent, filas, validação |
| Livewire 3 | Componentes de tela reativos, sem escrever JavaScript à mão para cada interação |
| Alpine.js | Comportamento client-side pontual (modais, dropdowns, máscaras de campo), embarcado junto com o Livewire |
| TailwindCSS | Estilização, seguindo os tokens definidos em `docs/design-system` |
| MySQL 8 | Banco de dados relacional |
| Laravel Sail | Ambiente de desenvolvimento em Docker, sem precisar instalar PHP ou MySQL na máquina |
| MinIO | Serviço compatível com S3 usado localmente para guardar fotos de ativos e de checklists |
| Sanctum | Autenticação por token para a API |
| Scramble | Geração automática de documentação OpenAPI/Swagger a partir do próprio código |
| PHPUnit | Testes automatizados |

No ecossistema PHP, o Composer cumpre o papel equivalente ao Maven em Java: resolve dependências, define scripts de build e mantém o `composer.lock` como travamento de versões.

## Funcionalidades implementadas

- **Autenticação e perfis de usuário**: login, registro, recuperação de senha, e cinco perfis (Gestor, Operador Logístico, Técnico de Manutenção, Financeiro, Cliente), cada um com acesso próprio dentro do sistema.
- **Gestão de Ativos**: cadastro, edição, listagem com busca e filtro por status, exclusão. O campo "tipo" é texto livre por enquanto, não uma tabela separada. Há upload de foto do equipamento e um campo de horímetro, hoje apenas informativo, sem regra de negócio associada.
- **Gestão de Clientes**: cadastro, edição, listagem com busca, tanto para pessoa física quanto jurídica, com validação real de CPF e CNPJ (dígito verificador, não só contagem de caracteres).
- **Contratos de locação**: criação de contrato com busca por autocomplete tanto de ativo disponível quanto de cliente, encerramento que libera o ativo automaticamente e gera a cobrança correspondente.
- **Checklists de saída e retorno**: registro de checklist com upload de fotos, comparação lado a lado entre as fotos da saída e do retorno, e encerramento automático do contrato ao registrar o retorno.
- **Manutenção**: abertura e fechamento de ordens de serviço, com regra de que só o técnico responsável (ou o Gestor) pode fechar uma ordem, e alertas de manutenção separados entre pendentes e resolvidos.
- **Gerenciamento de usuários**: cadastro, edição de nome e perfil, ativação e desativação lógica (sem excluir o registro do banco).

## Fora do escopo desta versão

Os itens "Painel" (dashboard com indicadores) e "Financeiro" completo estão ocultos do menu por enquanto.

A estrutura de dados de cobrança já existe e é usada no encerramento de contrato, mas a tela de gestão financeira ainda não foi implementada nem ligada ao menu. Quando as rotas e componentes dessas duas áreas existirem, eles continuam acessíveis por URL direta. Só a entrada no menu lateral foi removida.

## Como rodar localmente

Este projeto usa Laravel Sail. Com Docker instalado, siga esta ordem:

```bash
composer install
```

Se o Composer não estiver instalado na máquina, use o container do Sail direto:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

Depois:

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

A aplicação fica disponível em `http://localhost`.

## Documentação da API

A documentação OpenAPI é gerada automaticamente pelo Scramble a partir do código e fica disponível em:

```
http://localhost/docs/api
```

## Como rodar os testes

```bash
./vendor/bin/sail artisan test
```

## Perfis de usuário e acesso

| Perfil | O que acessa |
|---|---|
| Gestor | Acesso completo: ativos, clientes, contratos, checklists, manutenção e usuários |
| Operador Logístico | Ativos (visualização), clientes, contratos e checklists |
| Técnico de Manutenção | Ativos (visualização), manutenção (só fecha ordens atribuídas a ele mesmo, ou se for Gestor) |
| Financeiro | Ativos (visualização) |
| Cliente | Só os próprios contratos, sem acesso a cadastros de outras entidades |
