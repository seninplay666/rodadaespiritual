# 🩸 Rodada Espiritual (Charlie Charlie)

Um jogo interativo e imersivo de terror que revive a lenda urbana do ritual de **Charlie Charlie**! Tome decisões de "SIM" ou "NÃO" utilizando a roda espiritual influenciada por forças sobrenaturais, com direito a salas multiplayer, chats exclusivos e roletas personalizadas.

---

## 🔮 Stack Tecnológica
- **Backend:** PHP 8 estruturado com sessões nativas (`$_SESSION`, `session_start`).
- **Banco de Dados:** MySQL / MariaDB via **PDO** (com prepared statements para proteção contra SQL Injection e auto-migração de tabelas).
- **Frontend:** HTML5 semântico, Tailwind CSS (Mobile-First), CSS3 com estética de horror gótico.
- **Áudio Paranormal:** Web Audio API sintetizada via JavaScript puro (ruídos de sopro, rangidos do lápis, impacto espectral e badalos de sino).

---

## 📜 Regras de Negócio Implementadas (CORE)

1. **Segurança de Senhas:** Senhas criptografadas e validadas com hash nativo **Bcrypt** (`PASSWORD_BCRYPT`).
2. **Entrada Direta na Roda (Regra de Ouro):** Ao acessar o site (`index.php`), o usuário cai imediatamente na Roda de Charlie Charlie pronta para jogar! O acesso a outras áreas (painel, conta, plano premium, chats e salas) só é aberto se o usuário clicar para abrir.
3. **Alternância Perfeita:** Tabuleiro com 2 SIM's e 2 NÃO's dispostos de forma alternada (SIM, NÃO, SIM, NÃO), fiel à brincadeira original.
4. **Respostas Macabras e Divertidas:**
   - Toda resposta **"SIM"** gera um **Pop-Up** com texto macabro e bem-humorado que estimula um novo jogo imediatamente.
   - Toda resposta **"NÃO"** gera um **Pop-Up** com texto macabro e divertido que desafia o usuário a continuar o ritual.
5. **Chats de Mensagens:** Exclusivos para membros com **Plano Premium** ativado. Usuários comuns visualizam aviso das trevas e opção de upgrade.
6. **Salas de Jogos:**
   - Salas gratuitas suportam até **3 pessoas**.
   - Salas com **mais de 3 pessoas** só são permitidas para usuários **Premium**.
7. **Roletas Personalizadas:** Apenas permitidas em salas com **mais de 4 pessoas** conectadas simultaneamente.
8. **Feedback ao Usuário:** Mensagens de erro e aviso claras em **TOAST** customizado. Caixas nativas do navegador (`alert`, `confirm`) tratadas em **MODAL** com estética sombria.

---

## 📂 Arquitetura e Separação de Arquivos

```
rodadaespiritual/
├── bd.php                  # Conexão PDO segura e migração automática de tabelas
├── auth_check.php          # Gerenciamento de sessões nativas e permissões
├── index.php               # PÁGINA PRINCIPAL: Abre direto na Roda Espiritual
├── salas.php               # Gestor de Salas, Lobby e Sala Ativa multiplayer
├── premium.php             # Página e detalhes do Plano Premium
├── api/
│   ├── auth.php            # Registro (bcrypt), Login, Logout e Upgrade Premium
│   ├── girar.php           # Invocação da Roda (SIM/NÃO alternados e frases macabras)
│   ├── salas.php           # Criação, listagem e entrada em salas
│   ├── chat.php            # Mensagens de chat (restrito a usuários Premium)
│   ├── roleta_custom.php   # Criação de roletas customizadas (> 4 pessoas)
│   └── sincronizar.php     # Sincronização em tempo real das salas
└── assets/
    ├── css/
    │   └── horror.css      # Estilização de horror, chamas, velas e glitch
    └── js/
        ├── audio.js        # Efeitos sonoros sintetizados (Web Audio API)
        ├── toast-modal.js  # Sistema de Toasts e Modais macabros
        ├── roleta.js       # Motor da Roda Charlie Charlie com animação física
        └── salas.js        # Lógica de interface de salas, chat e permissões
```

---

## ⚡ Como Executar o Projeto

### Pré-requisitos:
- PHP 8.x instalado (ou XAMPP / Laragon / WampServer) com extensões `pdo_mysql` e `session` habilitadas.
- MySQL / MariaDB ativo.

### 1. Iniciar o MySQL
Inicie o serviço do MySQL no seu painel (ex: XAMPP Control Panel) ou linha de comando. As credenciais padrão configuradas em `bd.php` são:
- Host: `127.0.0.1`
- Porta: `3306`
- Usuário: `root`
- Senha: *(em branco)*

> **Nota:** O banco de dados `rodada_espiritual` e todas as 6 tabelas são criados automaticamente no primeiro acesso através do `bd.php`.

### 2. Iniciar o Servidor Web PHP
No diretório do projeto, execute no terminal:
```bash
php -S localhost:8000
```
Ou coloque a pasta dentro de `htdocs` do seu XAMPP e acesse `http://localhost/rodadaespiritual/`.

### 3. Acessar no Navegador
Abra:
```
http://localhost:8000/
```

---

## 👥 Contas de Teste Pré-Cadastradas

O sistema já popula automaticamente duas contas de demonstração:

| Tipo | E-mail | Senha | Privilégios |
|---|---|---|---|
| **Mestre Ocultista (Premium)** | `mestre@oculto.com` | `charlie123` | Chat liberado, salas com até 12 pessoas, roletas customizadas |
| **Alma Curiosa (Comum)** | `curioso@oculto.com` | `charlie123` | Salas de até 3 pessoas, modo livre da roda |

*(Você também pode registrar novas contas livremente ou alternar o status Premium a qualquer momento no botão "Grimório Premium")*.
