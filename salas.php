<?php
/**
 * Salas de Jogos Espirituais
 * Regras CORE:
 * - Chats de mensagens só serão permitidos no premium
 * - Salas de jogos com mais de 3 pessoas só serão permitidas no premium
 * - Criação de roletas personalizadas, serão permitidas em salas com mais de 4 pessoas
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/auth_check.php';

$usuario = obterUsuarioLogado($pdo);
$salaId = !empty($_GET['sala_id']) ? (int)$_GET['sala_id'] : null;
$salaAtual = null;

if ($salaId) {
    $stmt = $pdo->prepare("
        SELECT s.*, u.`nome` AS `criador_nome`, u.`is_premium` AS `criador_premium` 
        FROM `salas` s 
        JOIN `usuarios` u ON u.`id` = s.`criador_id` 
        WHERE s.`id` = :id LIMIT 1
    ");
    $stmt->execute([':id' => $salaId]);
    $salaAtual = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $salaAtual ? htmlspecialchars($salaAtual['nome']) : 'Círculos e Salas Espirituais'; ?> | Rodada Espiritual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/horror.css">
</head>
<body class="bg-ritual-pattern min-h-screen flex flex-col justify-between selection:bg-red-900 selection:text-white">

    <!-- CABEÇALHO -->
    <header class="w-full border-b border-red-950/70 bg-black/70 backdrop-blur-md sticky top-0 z-40 px-4 py-3">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="index.php" class="text-xl sm:text-2xl font-horror text-red-600 tracking-wider hover:text-red-500 transition-colors flex items-center space-x-1.5">
                    <span>🩸</span>
                    <span>RODADA ESPIRITUAL</span>
                </a>
                <span class="text-stone-600">/</span>
                <span class="text-xs text-stone-400 font-ritual hidden xs:inline">
                    <?php echo $salaAtual ? 'Círculo Ativo' : 'Lobby de Seitas'; ?>
                </span>
            </div>

            <div class="flex items-center space-x-3">
                <a href="index.php" class="text-xs text-stone-300 hover:text-red-400 font-ritual flex items-center space-x-1">
                    <span>↺</span>
                    <span>Voltar à Roda</span>
                </a>
                <button class="btn-abrir-premium text-xs text-amber-300 hover:text-amber-200 bg-amber-950/50 border border-amber-700/60 rounded-lg px-2.5 py-1 font-ritual">
                    ★ Premium
                </button>
            </div>
        </div>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-6">

        <?php if (!$salaAtual): ?>
            <!-- ================= LOBBY DE SALAS ================= -->
            <div class="space-y-6">
                <!-- Banner do Lobby -->
                <div class="bg-gradient-to-r from-red-950/80 to-stone-950 border border-red-900/60 rounded-2xl p-6 shadow-[0_0_30px_rgba(139,0,0,0.3)] flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="text-xs text-red-400 font-ritual uppercase tracking-wider mb-1">Comunhão Sobrenatural</div>
                        <h2 class="text-2xl sm:text-3xl font-bold font-horror text-red-500">CÍRCULOS DE INVOCAÇÃO</h2>
                        <p class="text-xs text-stone-300 font-medieval max-w-md mt-1">
                            Una sua alma a outros jogadores em salas com Charlie Charlie. Salas com mais de 3 pessoas exigem o Selo Premium.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <button id="btn-criar-sala-modal" class="bg-red-800 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg border border-red-500/50 text-xs font-ritual tracking-wider transition-all flex items-center justify-center space-x-1.5">
                            <span>🕯️</span>
                            <span>CRIAR NOVO CÍRCULO</span>
                        </button>
                    </div>
                </div>

                <!-- Entrada rápida por Código de Ritual -->
                <div class="bg-stone-900/60 border border-stone-800 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <span class="text-xs text-stone-400 font-ritual">Possui um código de invocação recebido de um amigo?</span>
                    <form onsubmit="event.preventDefault(); gerenciadorSalas.entrarSala(this.codigo.value.trim());" class="flex gap-2 w-full sm:w-auto">
                        <input type="text" name="codigo" placeholder="Ex: RITUAL-A1B2" class="bg-stone-950 border border-stone-700 rounded-lg px-3 py-1.5 text-xs text-stone-200 uppercase font-mono outline-none focus:border-red-500 flex-1 sm:w-44">
                        <button type="submit" class="bg-stone-800 hover:bg-stone-700 text-stone-200 px-3 py-1.5 rounded-lg text-xs font-ritual transition-colors">
                            Entrar
                        </button>
                    </form>
                </div>

                <!-- Lista de Salas Ativas -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-stone-300 font-ritual">Círculos Ativos no Além:</h3>
                        <button onclick="gerenciadorSalas.carregarSalasLobby()" class="text-xs text-stone-500 hover:text-stone-300 font-mono">
                            ↻ Atualizar
                        </button>
                    </div>
                    <div id="lista-salas-lobby" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="col-span-full text-center py-12 text-stone-500 font-medieval">
                            <span class="text-2xl block mb-2 animate-spin">🕯️</span>
                            Sondando o plano espiritual em busca de salas ativas...
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ================= SALA ATIVA ================= -->
            <div class="space-y-6">
                <!-- Barra de Status da Sala -->
                <div class="bg-stone-950 border border-red-900/70 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-[0_0_25px_rgba(220,38,38,0.2)]">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-red-950/80 text-red-300 border border-red-800">
                                Código: <?php echo htmlspecialchars($salaAtual['codigo']); ?>
                            </span>
                            <span class="text-xs text-stone-400">
                                Capacidade: <strong class="text-stone-200"><?php echo (int)$salaAtual['max_jogadores']; ?> almas</strong>
                                <?php if ((int)$salaAtual['max_jogadores'] > 3): ?>
                                    <span class="text-amber-400 font-bold text-[10px] ml-1 bg-amber-950/60 px-1 rounded border border-amber-800">★ SALA PREMIUM</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <h2 class="text-2xl font-bold font-ritual text-red-500 mt-1">
                            <?php echo htmlspecialchars($salaAtual['nome']); ?>
                        </h2>
                        <p class="text-xs text-stone-400">
                            Mestre Invocador: <span class="text-stone-200 font-medium"><?php echo htmlspecialchars($salaAtual['criador_nome']); ?></span>
                        </p>
                    </div>

                    <div class="flex items-center space-x-3 w-full md:w-auto justify-between md:justify-end">
                        <button onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($salaAtual['codigo']); ?>'); mostrarToast('Código copiado para o livro de sombras!', 'sucesso');" class="bg-stone-900 hover:bg-stone-800 text-stone-300 px-3 py-2 rounded-xl border border-stone-800 text-xs font-ritual flex items-center space-x-1.5 transition-colors">
                            <span>📋</span>
                            <span>Copiar Código</span>
                        </button>
                        <a href="salas.php" class="bg-red-950/50 hover:bg-red-900/80 text-red-300 px-3 py-2 rounded-xl border border-red-900/60 text-xs font-ritual transition-colors">
                            Sair do Círculo
                        </a>
                    </div>
                </div>

                <!-- Grade Principal: Roleta da Sala + Jogadores & Chat -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Coluna Esquerda: A Roda de Charlie Charlie da Sala -->
                    <div class="lg:col-span-2 bg-stone-950 border border-red-950 rounded-2xl p-6 flex flex-col items-center justify-center relative overflow-hidden shadow-2xl">
                        
                        <!-- Header da Roleta na Sala -->
                        <div class="w-full flex items-center justify-between mb-4">
                            <span class="text-xs font-ritual text-stone-400">Rito em Andamento</span>
                            <!-- Botão de Invocação de Roleta Personalizada (Regra CORE: > 4 pessoas) -->
                            <div>
                                <button id="btn-abrir-roleta-personalizada" class="text-xs font-ritual font-bold px-3 py-1.5 rounded-lg bg-red-900/40 hover:bg-red-800 border border-red-600/60 text-red-200 transition-all flex items-center space-x-1">
                                    <span>🔮</span>
                                    <span>Roleta Personalizada</span>
                                </button>
                            </div>
                        </div>

                        <!-- Aviso caso não tenha mais de 4 pessoas -->
                        <div id="aviso-roleta-bloqueada" class="w-full text-center text-[11px] text-stone-500 font-medieval mb-3 bg-stone-900/40 py-1.5 px-3 rounded-lg border border-stone-900">
                            🔒 Roletas personalizadas exigem mais de 4 pessoas na sala.
                        </div>

                        <!-- Tabuleiro Charlie Charlie Compartilhado -->
                        <div id="tabuleiro-charlie" class="relative w-[280px] h-[280px] sm:w-[340px] sm:h-[340px] bg-stone-900/90 rounded-2xl border-4 border-red-950 p-4 shadow-[0_0_40px_rgba(139,0,0,0.5)] flex flex-col justify-between overflow-hidden my-3">
                            <div class="absolute inset-x-0 top-1/2 h-[2px] bg-red-800/40 -translate-y-1/2 pointer-events-none"></div>
                            <div class="absolute inset-y-0 left-1/2 w-[2px] bg-red-800/40 -translate-x-1/2 pointer-events-none"></div>

                            <div class="flex justify-between items-start h-1/2">
                                <div id="quadrante-0" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-3">
                                    <span class="text-3xl font-horror text-red-500">SIM</span>
                                </div>
                                <div id="quadrante-1" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-3">
                                    <span class="text-3xl font-horror text-stone-400">NÃO</span>
                                </div>
                            </div>

                            <div class="flex justify-between items-end h-1/2">
                                <div id="quadrante-3" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-3">
                                    <span class="text-3xl font-horror text-stone-400">NÃO</span>
                                </div>
                                <div id="quadrante-2" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-3">
                                    <span class="text-3xl font-horror text-red-500">SIM</span>
                                </div>
                            </div>

                            <!-- Lápis Base -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="relative w-[82%] h-4 flex items-center opacity-85">
                                    <div class="pencil-lead"></div>
                                    <div class="flex-1 h-3.5 pencil-body"></div>
                                    <div class="w-5 h-3.5 pencil-eraser"></div>
                                </div>
                            </div>

                            <!-- Lápis Giratório -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-20">
                                <div id="lapis-giratorio" class="relative w-[82%] h-4 flex items-center drop-shadow-[0_10px_12px_rgba(0,0,0,0.9)]" style="transform: rotate(0deg);">
                                    <div class="pencil-lead"></div>
                                    <div class="flex-1 h-3.5 pencil-body border-t border-amber-300/40"></div>
                                    <div class="w-5 h-3.5 pencil-eraser"></div>
                                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-red-600 shadow-[0_0_8px_#dc2626]"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo de Pergunta e Ação da Sala -->
                        <div class="w-full max-w-md mt-4 space-y-2">
                            <input 
                                type="text" 
                                id="input-pergunta" 
                                placeholder="Invoque sua pergunta ao círculo espiritual..." 
                                class="w-full bg-stone-900 border border-red-900/70 rounded-xl px-4 py-2.5 text-stone-200 text-sm outline-none focus:border-red-500"
                            />
                            <button 
                                id="btn-girar-roda" 
                                class="w-full bg-gradient-to-r from-red-900 to-red-700 hover:from-red-800 hover:to-red-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg border border-red-500/50 font-ritual text-sm tracking-wider flex items-center justify-center space-x-2"
                            >
                                <span>☠️</span>
                                <span>GIRAR RODA NO CÍRCULO</span>
                            </button>
                        </div>

                    </div>

                    <!-- Coluna Direita: Participantes & Chat Exclusivo Premium -->
                    <div class="flex flex-col space-y-4">

                        <!-- Almas Conectadas -->
                        <div class="bg-stone-950 border border-stone-800 rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-3 text-xs font-ritual">
                                <span class="text-stone-300 font-bold">Almas no Círculo:</span>
                                <span class="px-2 py-0.5 rounded bg-stone-900 text-stone-400 font-mono">
                                    <span id="contagem-jogadores-sala">1</span> / <?php echo (int)$salaAtual['max_jogadores']; ?>
                                </span>
                            </div>
                            <div id="lista-jogadores-sala" class="space-y-1.5 max-h-36 overflow-y-auto">
                                <div class="text-stone-500 text-xs italic font-medieval">Carregando almas...</div>
                            </div>
                        </div>

                        <!-- Chat da Sala (Regra CORE: Permitido apenas no Premium) -->
                        <div class="bg-stone-950 border border-red-950/80 rounded-2xl p-4 flex-1 flex flex-col justify-between shadow-lg">
                            <div class="flex items-center justify-between pb-2 border-b border-stone-900 mb-3">
                                <span class="text-xs font-ritual text-red-400 font-bold flex items-center space-x-1">
                                    <span>💬</span>
                                    <span>Sussurros do Além (Chat)</span>
                                </span>
                                <span class="text-[10px] text-amber-400 font-bold bg-amber-950/50 border border-amber-800/60 px-1.5 py-0.5 rounded">
                                    ★ EXCLUSIVO PREMIUM
                                </span>
                            </div>

                            <!-- Mensagens do Chat -->
                            <div id="chat-mensagens-container" class="space-y-2 overflow-y-auto max-h-64 h-52 pr-1 mb-3">
                                <div class="text-center py-6 text-stone-600 text-xs italic font-medieval">
                                    Os sussurros do além ecoarão nesta tumba...
                                </div>
                            </div>

                            <!-- Formulário de Envio / Bloqueio Premium -->
                            <?php if ($usuario && (int)$usuario['is_premium'] === 1): ?>
                                <!-- Usuário Premium: Chat Desbloqueado -->
                                <form id="form-envio-chat" class="flex gap-2">
                                    <input 
                                        type="text" 
                                        id="input-mensagem-chat" 
                                        placeholder="Sussurre ao além..." 
                                        maxlength="400"
                                        class="flex-1 bg-stone-900 border border-stone-800 rounded-xl px-3 py-2 text-xs text-stone-200 outline-none focus:border-red-500"
                                    />
                                    <button type="submit" class="bg-red-800 hover:bg-red-700 text-white font-bold px-3 py-2 rounded-xl text-xs font-ritual transition-colors">
                                        Enviar
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Usuário Não-Premium: Chat Bloqueado com Aviso Sinistro -->
                                <div class="bg-stone-900/90 border border-amber-900/60 rounded-xl p-3 text-center space-y-2">
                                    <p class="text-[11px] text-amber-300/90 font-medieval leading-tight">
                                        🔒 <strong>Voz Silenciada:</strong> O chat de mensagens é restrito exclusivamente para almas com o Plano Premium.
                                    </p>
                                    <button onclick="gerenciadorSalas.abrirModalPremium()" class="bg-gradient-to-r from-amber-700 to-amber-600 hover:from-amber-600 hover:to-amber-500 text-stone-950 font-bold px-4 py-1.5 rounded-lg text-xs font-ritual tracking-wider transition-all">
                                        Desbloquear com Premium
                                    </button>
                                </div>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- MODAL DE CRIAR SALA (COM VALIDAÇÃO: >3 PESSOAS EXCLUSIVO PREMIUM) -->
    <div id="modal-criar-sala" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-stone-950 border-2 border-red-800 rounded-2xl max-w-sm w-full p-6 shadow-[0_0_40px_rgba(220,38,38,0.35)] relative">
            <button onclick="gerenciadorSalas.fecharModalCriarSala()" class="absolute top-4 right-4 text-stone-500 hover:text-white text-xl font-bold">&times;</button>
            
            <div class="text-center mb-4">
                <span class="text-3xl block mb-1">🕯️</span>
                <h3 class="text-lg font-bold text-red-500 font-ritual">FUNDAR CÍRCULO ESPIRITUAL</h3>
                <p class="text-xs text-stone-400">Invoque uma sala para reunir sua seita.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Nome do Círculo</label>
                    <input type="text" id="input-nome-sala" placeholder="Ex: Círculo dos Condenados" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs text-stone-400 mb-1">Capacidade de Almas (Jogadores)</label>
                    <select id="input-max-jogadores" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                        <option value="2">2 Almas (Livre)</option>
                        <option value="3" selected>3 Almas (Livre - Limite Padrão)</option>
                        <option value="4">4 Almas (★ Requer Premium)</option>
                        <option value="6">6 Almas (★ Requer Premium)</option>
                        <option value="8">8 Almas (★ Requer Premium)</option>
                        <option value="12">12 Almas (★ Requer Premium)</option>
                    </select>
                    <p class="text-[10px] text-amber-500/80 mt-1 font-medieval">
                        ⚠️ Regra do Além: Salas com mais de 3 pessoas são exclusivas para o Plano Premium.
                    </p>
                </div>

                <button onclick="gerenciadorSalas.confirmarCriarSala()" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 rounded-lg text-xs font-ritual tracking-wider transition-colors">
                    INVOCAR E ABRIR PORTAL
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE ROLETA PERSONALIZADA (REGRA CORE: > 4 PESSOAS) -->
    <div id="modal-roleta-personalizada" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-stone-950 border-2 border-red-800 rounded-2xl max-w-md w-full p-6 shadow-[0_0_40px_rgba(220,38,38,0.35)] relative">
            <button onclick="gerenciadorSalas.fecharModalRoletaPersonalizada()" class="absolute top-4 right-4 text-stone-500 hover:text-white text-xl font-bold">&times;</button>
            
            <div class="text-center mb-4">
                <span class="text-3xl block mb-1">🔮</span>
                <h3 class="text-lg font-bold text-red-500 font-ritual">CRIAR ROLETA PERSONALIZADA</h3>
                <p class="text-xs text-stone-400">Liberado pelo rito de mais de 4 almas presentes na sala!</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Título do Ritual / Pergunta</label>
                    <input type="text" id="input-titulo-roleta" placeholder="Ex: Quem será a próxima vítima?" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>

                <div>
                    <label class="block text-xs text-stone-400 mb-1">Opções da Roleta (uma por linha)</label>
                    <textarea id="input-opcoes-roleta" rows="4" placeholder="SIM&#10;NÃO&#10;TALVEZ NO ALÉM&#10;CORRA IMEDIATAMENTE" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none font-mono"></textarea>
                    <p class="text-[10px] text-stone-500 mt-1">Defina ao menos duas sentenças para os espíritos apontarem.</p>
                </div>

                <button onclick="gerenciadorSalas.salvarRoletaPersonalizada()" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 rounded-lg text-xs font-ritual tracking-wider transition-colors">
                    CONSAGRAR ROLETA PERSONALIZADA
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE AUTENTICAÇÃO -->
    <div id="modal-autenticacao" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-stone-950 border-2 border-red-800 rounded-2xl max-w-sm w-full p-6 shadow-[0_0_40px_rgba(220,38,38,0.35)] relative">
            <button onclick="gerenciadorSalas.fecharModalAuth()" class="absolute top-4 right-4 text-stone-500 hover:text-white text-xl font-bold">&times;</button>
            
            <div class="text-center mb-4">
                <span class="text-3xl block mb-1">🕯️</span>
                <h3 class="text-lg font-bold text-red-500 font-ritual">PORTAL DE ACESSO</h3>
                <p class="text-xs text-stone-400">Identifique sua alma.</p>
            </div>

            <div class="flex border-b border-stone-800 mb-4">
                <button id="tab-btn-login" onclick="gerenciadorSalas.alternarAbaAuth('login')" class="flex-1 py-2 font-ritual text-sm border-b-2 border-red-500 text-red-400 font-bold">
                    Entrar
                </button>
                <button id="tab-btn-cadastro" onclick="gerenciadorSalas.alternarAbaAuth('cadastro')" class="flex-1 py-2 font-ritual text-sm border-b border-stone-800 text-stone-500 hover:text-stone-300">
                    Novo Pacto
                </button>
            </div>

            <form id="form-login" onsubmit="event.preventDefault(); gerenciadorSalas.executarLogin(this.email.value, this.senha.value);" class="space-y-3">
                <div>
                    <label class="block text-xs text-stone-400 mb-1">E-mail</label>
                    <input type="email" name="email" required placeholder="seu@email.com" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Senha</label>
                    <input type="password" name="senha" required placeholder="••••••••" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 rounded-lg text-xs font-ritual tracking-wider transition-colors">
                    ACESSAR
                </button>
            </form>

            <form id="form-cadastro" onsubmit="event.preventDefault(); gerenciadorSalas.executarCadastro(this.nome.value, this.email.value, this.senha.value);" class="hidden space-y-3">
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Nome de Invocador</label>
                    <input type="text" name="nome" required placeholder="Ex: Mestre Arcano" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-stone-400 mb-1">E-mail</label>
                    <input type="email" name="email" required placeholder="seu@email.com" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Senha (Bcrypt)</label>
                    <input type="password" name="senha" required placeholder="Mínimo 6 caracteres" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 rounded-lg text-xs font-ritual tracking-wider transition-colors">
                    SELA PACTO
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL PREMIUM -->
    <div id="modal-plano-premium" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-stone-950 border-2 border-amber-600/80 rounded-2xl max-w-md w-full p-6 shadow-[0_0_50px_rgba(245,158,11,0.25)] relative">
            <button onclick="gerenciadorSalas.fecharModalPremium()" class="absolute top-4 right-4 text-stone-500 hover:text-white text-xl font-bold">&times;</button>
            
            <div class="text-center mb-4">
                <span class="text-4xl block mb-1">👑</span>
                <h3 class="text-xl font-bold text-amber-400 font-ritual">PACTO PREMIUM DAS TREVAS</h3>
                <p class="text-xs text-stone-400 mt-1">Salas com +3 pessoas, Chats e Criação de Roletas.</p>
            </div>

            <div class="space-y-3 my-5 text-xs text-stone-300">
                <div class="p-2.5 rounded-lg bg-stone-900/60 border border-amber-950 flex items-center space-x-2">
                    <span>💬</span>
                    <span><strong>Chat Liberado:</strong> Converse em qualquer sala ativa.</span>
                </div>
                <div class="p-2.5 rounded-lg bg-stone-900/60 border border-amber-950 flex items-center space-x-2">
                    <span>👥</span>
                    <span><strong>Salas Grandes:</strong> Até 12 pessoas no mesmo rito.</span>
                </div>
                <div class="p-2.5 rounded-lg bg-stone-900/60 border border-amber-950 flex items-center space-x-2">
                    <span>🔮</span>
                    <span><strong>Roleta Customizada:</strong> Permitida em salas com 5+ almas.</span>
                </div>
            </div>

            <button onclick="gerenciadorSalas.alternarStatusPremium(1)" class="w-full bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-extrabold py-3 rounded-xl font-ritual tracking-wider text-xs uppercase transition-all shadow-lg">
                🩸 SELAR PACTO PREMIUM AGORA
            </button>
            <button onclick="gerenciadorSalas.alternarStatusPremium(0)" class="w-full text-stone-500 hover:text-stone-400 text-[11px] py-1 text-center font-medieval mt-2">
                (Cancelar ou Revogar Selo para testes)
            </button>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="assets/js/audio.js"></script>
    <script src="assets/js/toast-modal.js"></script>
    <script src="assets/js/roleta.js"></script>
    <script src="assets/js/salas.js"></script>

    <?php if ($salaAtual): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.roletaEspiritual) {
                window.roletaEspiritual.definirSala(<?php echo (int)$salaAtual['id']; ?>);
            }
            if (window.gerenciadorSalas) {
                window.gerenciadorSalas.iniciarSincronizacaoSala(<?php echo (int)$salaAtual['id']; ?>);
            }
        });
    </script>
    <?php else: ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.gerenciadorSalas) {
                window.gerenciadorSalas.carregarSalasLobby();
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>
