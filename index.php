<?php
/**
 * Rodada Espiritual - Página Principal
 * Regra CORE: Abrir automaticamente na roda, o acesso só será aberto caso o usuário o abra,
 * seja para acessar o seu plano premium, seus chats de conversas e suas salas de jogos.
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/auth_check.php';

$usuario = obterUsuarioLogado($pdo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodada Espiritual | O Tabuleiro de Charlie Charlie</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/horror.css">
</head>
<body class="bg-ritual-pattern min-h-screen flex flex-col justify-between selection:bg-red-900 selection:text-white">

    <!-- BARRA SUPERIOR (DISCRETA E IMERSIVA) -->
    <header class="w-full border-b border-red-950/70 bg-black/60 backdrop-blur-md sticky top-0 z-40 px-4 py-3">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            
            <!-- Logo / Título Macabro -->
            <a href="index.php" class="flex items-center space-x-2 group">
                <span class="text-2xl filter drop-shadow-[0_0_8px_#dc2626] group-hover:rotate-12 transition-transform">🩸</span>
                <span class="text-xl sm:text-2xl font-horror text-red-600 tracking-wider group-hover:text-red-500 transition-colors">
                    RODADA ESPIRITUAL
                </span>
            </a>

            <!-- Ações e Acessos Rápidos -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- Controle de Áudio -->
                <button id="btn-toggle-audio" onclick="window.audioEspectral.alternarAudio(); this.classList.toggle('opacity-50');" title="Alternar Sussurros e Efeitos de Áudio" class="p-2 rounded-lg bg-stone-900/80 border border-red-900/50 hover:border-red-500 text-stone-300 text-sm transition-all">
                    <span>🔊</span>
                </button>

                <!-- Botão de Salas Sobrenaturais -->
                <a href="salas.php" class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-stone-900/90 border border-red-900/60 hover:border-red-500 text-red-200 text-xs font-ritual font-bold tracking-wider transition-all hover:shadow-[0_0_15px_rgba(220,38,38,0.3)]">
                    <span>👥</span>
                    <span>SALAS</span>
                </a>

                <!-- Botão do Grimório Premium -->
                <button class="btn-abrir-premium inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-red-950 to-stone-900 border border-amber-600/50 hover:border-amber-400 text-amber-300 text-xs font-ritual font-bold tracking-wider transition-all hover:shadow-[0_0_15px_rgba(245,158,11,0.25)]">
                    <span>★</span>
                    <span class="hidden xs:inline">PREMIUM</span>
                </button>

                <!-- Área de Usuário: Logado ou Deslogado -->
                <div id="area-usuario-logado" class="<?php echo $usuario ? '' : 'hidden'; ?> flex items-center space-x-2">
                    <span id="badge-usuario-premium" class="<?php echo ($usuario && $usuario['is_premium']) ? '' : 'hidden'; ?> text-xs text-amber-400 font-bold px-2 py-0.5 rounded bg-amber-950/70 border border-amber-700/60">
                        ★ PREMIUM
                    </span>
                    <span id="nome-usuario-topo" class="text-xs text-stone-300 font-ritual max-w-[100px] truncate">
                        <?php echo htmlspecialchars($usuario['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <button id="btn-logout" title="Retornar ao mundo dos vivos" class="text-xs text-stone-500 hover:text-red-400 p-1">
                        🚪
                    </button>
                </div>

                <div id="area-usuario-deslogado" class="<?php echo $usuario ? 'hidden' : ''; ?>">
                    <button class="btn-abrir-auth px-3 py-1.5 rounded-lg bg-red-900/40 hover:bg-red-800/80 border border-red-700/70 text-red-200 text-xs font-ritual font-bold tracking-wider transition-all">
                        ENTRAR / PACTO
                    </button>
                </div>
            </div>

        </div>
    </header>

    <!-- ÁREA PRINCIPAL: FOCO ABSOLUTO NA RODA ESPIRITUAL (REGRA CORE) -->
    <main class="flex-1 flex flex-col items-center justify-center px-4 py-6 sm:py-10 max-w-4xl mx-auto w-full relative">
        
        <!-- Velas e Elementos Místicos Decorativos -->
        <div class="w-full flex justify-between items-center px-4 max-w-xl pointer-events-none mb-2">
            <!-- Vela Esquerda -->
            <div class="flex flex-col items-center">
                <div class="w-3.5 h-6 bg-gradient-to-t from-amber-500 via-amber-300 to-white rounded-full candle-flame"></div>
                <div class="w-5 h-10 bg-stone-800 border-x border-stone-700 rounded-t-sm shadow-inner"></div>
            </div>

            <!-- Título Ritualístico Central -->
            <div class="text-center">
                <div class="text-xs tracking-widest text-red-500 uppercase font-ritual font-bold">Invocação Paranormal</div>
                <h1 class="text-3xl sm:text-5xl font-horror text-red-600 drop-shadow-[0_0_15px_rgba(220,38,38,0.7)] glitch-text">
                    CHARLIE CHARLIE
                </h1>
                <p class="text-[11px] sm:text-xs text-stone-400 font-medieval mt-1">
                    Faça sua pergunta e deixe as forças sobrenaturais decidirem seu destino.
                </p>
            </div>

            <!-- Vela Direita -->
            <div class="flex flex-col items-center">
                <div class="w-3.5 h-6 bg-gradient-to-t from-amber-500 via-amber-300 to-white rounded-full candle-flame"></div>
                <div class="w-5 h-10 bg-stone-800 border-x border-stone-700 rounded-t-sm shadow-inner"></div>
            </div>
        </div>

        <!-- Alternador discreto de estilo de roleta -->
        <div class="mb-4">
            <button id="btn-alternar-visual" class="text-[11px] text-stone-500 hover:text-stone-300 flex items-center space-x-1 border border-stone-800 hover:border-red-900 rounded-full px-3 py-1 transition-colors">
                <span>🔄</span>
                <span>Alternar Visual: Lápis Tradicional / Roleta Circular</span>
            </button>
        </div>

        <!-- TABULEIRO CHARLIE CHARLIE (2 SIM'S E 2 NÃO'S ALTERNADOS) -->
        <div id="tabuleiro-charlie" class="relative w-[300px] h-[300px] sm:w-[380px] sm:h-[380px] bg-stone-900/90 rounded-2xl border-4 border-red-950 p-4 shadow-[0_0_50px_rgba(139,0,0,0.5)] flex flex-col justify-between overflow-hidden">
            
            <!-- Linhas de Giz Cruzadas -->
            <div class="absolute inset-x-0 top-1/2 h-[2px] bg-red-800/40 -translate-y-1/2 pointer-events-none"></div>
            <div class="absolute inset-y-0 left-1/2 w-[2px] bg-red-800/40 -translate-x-1/2 pointer-events-none"></div>

            <!-- Quadrantes Alternados: SIM, NÃO, SIM, NÃO -->
            <!-- Quadrante 0: Superior-Esquerdo (SIM) -->
            <div class="flex justify-between items-start h-1/2">
                <div id="quadrante-0" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-4 rounded-tl-xl transition-all duration-300">
                    <span class="text-3xl sm:text-4xl font-horror text-red-500 tracking-widest drop-shadow-[0_0_8px_rgba(220,38,38,0.8)]">
                        SIM
                    </span>
                </div>

                <!-- Quadrante 1: Superior-Direito (NÃO) -->
                <div id="quadrante-1" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-4 rounded-tr-xl transition-all duration-300">
                    <span class="text-3xl sm:text-4xl font-horror text-stone-400 tracking-widest drop-shadow-[0_0_6px_rgba(150,150,150,0.5)]">
                        NÃO
                    </span>
                </div>
            </div>

            <!-- Quadrante 3: Inferior-Esquerdo (NÃO) e Quadrante 2: Inferior-Direito (SIM) -->
            <div class="flex justify-between items-end h-1/2">
                <div id="quadrante-3" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-4 rounded-bl-xl transition-all duration-300">
                    <span class="text-3xl sm:text-4xl font-horror text-stone-400 tracking-widest drop-shadow-[0_0_6px_rgba(150,150,150,0.5)]">
                        NÃO
                    </span>
                </div>

                <div id="quadrante-2" class="quadrante-charlie w-1/2 h-full flex items-center justify-center p-4 rounded-br-xl transition-all duration-300">
                    <span class="text-3xl sm:text-4xl font-horror text-red-500 tracking-widest drop-shadow-[0_0_8px_rgba(220,38,38,0.8)]">
                        SIM
                    </span>
                </div>
            </div>

            <!-- LÁPIS DA BASE (HORIZONTAL FIXO) -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-[82%] h-4 flex items-center opacity-85">
                    <div class="pencil-lead"></div>
                    <div class="flex-1 h-3.5 pencil-body"></div>
                    <div class="w-5 h-3.5 pencil-eraser"></div>
                </div>
            </div>

            <!-- LÁPIS GIRATÓRIO (SUPERIOR EQUILIBRADO NO CENTRO) -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-20">
                <div id="lapis-giratorio" class="relative w-[82%] h-4 flex items-center drop-shadow-[0_10px_12px_rgba(0,0,0,0.9)]" style="transform: rotate(0deg);">
                    <div class="pencil-lead"></div>
                    <div class="flex-1 h-3.5 pencil-body border-t border-amber-300/40"></div>
                    <div class="w-5 h-3.5 pencil-eraser"></div>
                    <!-- Ponto de contato central em equilíbrio -->
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-red-600 shadow-[0_0_8px_#dc2626]"></div>
                </div>
            </div>

            <!-- Círculo Central Oculto -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
                <div class="w-10 h-10 rounded-full border border-red-900/60 bg-black/60 backdrop-blur-sm flex items-center justify-center text-[10px] text-red-500 font-mono">
                    ☠
                </div>
            </div>
        </div>

        <!-- MODO VISUAL ALTERNATIVO: ROLETA CIRCULAR DE SANGUE (4 FATIAS ALTERNADAS SIM / NÃO) -->
        <div id="container-roleta-disco" class="hidden relative w-[300px] h-[300px] sm:w-[380px] sm:h-[380px] flex items-center justify-center">
            <!-- Ponteiro de Adaga/Caveira no Topo -->
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-30 filter drop-shadow-[0_0_10px_#dc2626] text-3xl">
                🗡️
            </div>

            <!-- Disco Giratório com 4 fatias com 2 SIM e 2 NÃO alternados -->
            <div id="roleta-disco" class="w-full h-full rounded-full border-4 border-red-800 bg-stone-950 relative overflow-hidden shadow-[0_0_40px_rgba(220,38,38,0.4)]">
                <!-- Fatia 0: SIM (Topo-Dir) -->
                <div class="absolute inset-0 [clip-path:polygon(50%_50%,_50%_0%,_100%_0%,_100%_50%)] bg-red-950/70 border-b border-red-800 flex items-center justify-center">
                    <span class="text-xl sm:text-2xl font-horror text-red-400 font-bold translate-x-10 -translate-y-10 rotate-45">SIM</span>
                </div>
                <!-- Fatia 1: NÃO (Baixo-Dir) -->
                <div class="absolute inset-0 [clip-path:polygon(50%_50%,_100%_50%,_100%_100%,_50%_100%)] bg-stone-900 border-l border-red-800 flex items-center justify-center">
                    <span class="text-xl sm:text-2xl font-horror text-stone-300 font-bold translate-x-10 translate-y-10 rotate-45">NÃO</span>
                </div>
                <!-- Fatia 2: SIM (Baixo-Esq) -->
                <div class="absolute inset-0 [clip-path:polygon(50%_50%,_50%_100%,_0%_100%,_0%_50%)] bg-red-950/70 border-t border-red-800 flex items-center justify-center">
                    <span class="text-xl sm:text-2xl font-horror text-red-400 font-bold -translate-x-10 translate-y-10 rotate-45">SIM</span>
                </div>
                <!-- Fatia 3: NÃO (Topo-Esq) -->
                <div class="absolute inset-0 [clip-path:polygon(50%_50%,_0%_50%,_0%_0%,_50%_0%)] bg-stone-900 border-r border-red-800 flex items-center justify-center">
                    <span class="text-xl sm:text-2xl font-horror text-stone-300 font-bold -translate-x-10 -translate-y-10 rotate-45">NÃO</span>
                </div>
                <!-- Centro do Disco -->
                <div class="absolute inset-0 m-auto w-14 h-14 rounded-full bg-stone-950 border-2 border-red-600 flex items-center justify-center text-red-500 font-horror text-lg z-20 shadow-[0_0_12px_#dc2626]">
                    👁️
                </div>
            </div>
        </div>

        <!-- FORMULÁRIO DE PERGUNTA E BOTÃO DE INVOCAÇÃO -->
        <div class="w-full max-w-md mt-6 sm:mt-8 space-y-3">
            <div class="relative">
                <input 
                    type="text" 
                    id="input-pergunta" 
                    placeholder="Espírito, você está presente entre nós?" 
                    class="w-full bg-stone-950/90 border-2 border-red-900/70 focus:border-red-500 focus:ring-2 focus:ring-red-600/30 rounded-xl px-4 py-3.5 text-stone-200 placeholder-stone-600 text-sm sm:text-base outline-none transition-all shadow-inner font-sans"
                    autocomplete="off"
                />
                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-600 pointer-events-none text-base">
                    📜
                </span>
            </div>

            <!-- Botão Principal de Giro -->
            <button 
                id="btn-girar-roda" 
                class="w-full bg-gradient-to-r from-red-900 via-red-700 to-red-900 hover:from-red-800 hover:via-red-600 hover:to-red-800 text-white font-bold py-4 px-6 rounded-xl shadow-[0_0_30px_rgba(220,38,38,0.4)] border border-red-500/50 transition-all transform active:scale-98 font-ritual tracking-widest text-base sm:text-lg flex items-center justify-center space-x-2"
            >
                <span class="text-xl">☠️</span>
                <span>GIRAR A RODA DO ALÉM</span>
            </button>

            <!-- Sugestões Rápidas de Perguntas Macabras -->
            <div class="pt-2">
                <div class="text-[11px] uppercase tracking-wider text-stone-500 font-ritual text-center mb-1.5">
                    Invocadores costumam perguntar:
                </div>
                <div class="flex flex-wrap gap-1.5 justify-center">
                    <button class="btn-sugestao-pergunta text-[11px] bg-stone-950/80 hover:bg-stone-900 text-stone-400 hover:text-red-300 border border-stone-800 rounded-md px-2.5 py-1 transition-colors" data-pergunta="Charlie, Charlie, você está aí?">
                        "Charlie, você está aí?"
                    </button>
                    <button class="btn-sugestao-pergunta text-[11px] bg-stone-950/80 hover:bg-stone-900 text-stone-400 hover:text-red-300 border border-stone-800 rounded-md px-2.5 py-1 transition-colors" data-pergunta="Alguém vai morrer nesta sala hoje?">
                        "Alguém vai morrer hoje?"
                    </button>
                    <button class="btn-sugestao-pergunta text-[11px] bg-stone-950/80 hover:bg-stone-900 text-stone-400 hover:text-red-300 border border-stone-800 rounded-md px-2.5 py-1 transition-colors" data-pergunta="Devo continuar jogando este ritual?">
                        "Devo continuar o ritual?"
                    </button>
                </div>
            </div>

            <!-- Histórico Recente de Respostas do Além -->
            <div class="pt-4 border-t border-stone-900 mt-4">
                <div class="flex items-center justify-between text-xs text-stone-500 font-ritual mb-2">
                    <span>Eco das Últimas Respostas:</span>
                    <span class="text-[10px] text-stone-600">Registro Sobrenatural</span>
                </div>
                <div id="historico-giros-lista" class="space-y-1.5">
                    <div class="text-center py-2 text-stone-600 text-xs italic font-medieval">
                        As respostas dos espíritos aparecerão gravadas aqui...
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- RODAPÉ SUTIL -->
    <footer class="w-full border-t border-stone-900 bg-black/80 px-4 py-4 text-center text-xs text-stone-500">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                Rodada Espiritual &copy; <?php echo date('Y'); ?> — Inspirado no folclore de Charlie Charlie.
            </div>
            <div class="flex items-center space-x-4">
                <a href="salas.php" class="hover:text-red-400 transition-colors">Salas de Seita</a>
                <button class="btn-abrir-premium hover:text-amber-400 transition-colors">Plano Premium</button>
            </div>
        </div>
    </footer>

    <!-- MODAL DE AUTENTICAÇÃO (LOGIN / CADASTRO COM BCRYPT) -->
    <div id="modal-autenticacao" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-stone-950 border-2 border-red-800 rounded-2xl max-w-sm w-full p-6 shadow-[0_0_40px_rgba(220,38,38,0.35)] relative">
            <button onclick="gerenciadorSalas.fecharModalAuth()" class="absolute top-4 right-4 text-stone-500 hover:text-white text-xl font-bold">&times;</button>
            
            <div class="text-center mb-4">
                <span class="text-3xl block mb-1">🕯️</span>
                <h3 class="text-lg font-bold text-red-500 font-ritual">PORTAL DOS VIVOS E MORTOS</h3>
                <p class="text-xs text-stone-400">Identifique sua alma para acessar as salas e rituais.</p>
            </div>

            <!-- Abas Login / Cadastro -->
            <div class="flex border-b border-stone-800 mb-4">
                <button id="tab-btn-login" onclick="gerenciadorSalas.alternarAbaAuth('login')" class="flex-1 py-2 font-ritual text-sm border-b-2 border-red-500 text-red-400 font-bold">
                    Entrar
                </button>
                <button id="tab-btn-cadastro" onclick="gerenciadorSalas.alternarAbaAuth('cadastro')" class="flex-1 py-2 font-ritual text-sm border-b border-stone-800 text-stone-500 hover:text-stone-300">
                    Novo Pacto
                </button>
            </div>

            <!-- Form Login -->
            <form id="form-login" onsubmit="event.preventDefault(); gerenciadorSalas.executarLogin(this.email.value, this.senha.value);" class="space-y-3">
                <div>
                    <label class="block text-xs text-stone-400 mb-1">E-mail da Alma</label>
                    <input type="email" name="email" required placeholder="seu@email.com" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Palavra de Proteção (Senha)</label>
                    <input type="password" name="senha" required placeholder="••••••••" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 rounded-lg text-xs font-ritual tracking-wider transition-colors">
                    ACESSAR PORTAL
                </button>
            </form>

            <!-- Form Cadastro -->
            <form id="form-cadastro" onsubmit="event.preventDefault(); gerenciadorSalas.executarCadastro(this.nome.value, this.email.value, this.senha.value);" class="hidden space-y-3">
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Nome ou Pseudônimo Oculto</label>
                    <input type="text" name="nome" required placeholder="Ex: Bruxo da Noite" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-stone-400 mb-1">E-mail para o Pacto</label>
                    <input type="email" name="email" required placeholder="seu@email.com" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-stone-400 mb-1">Senha (Criptografada em Bcrypt)</label>
                    <input type="password" name="senha" required placeholder="No mínimo 6 caracteres" class="w-full bg-stone-900 border border-stone-800 rounded-lg px-3 py-2 text-xs text-white focus:border-red-600 outline-none">
                </div>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 rounded-lg text-xs font-ritual tracking-wider transition-colors">
                    CONSIGNAR ALMA E CADASTRAR
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL DO PLANO PREMIUM -->
    <div id="modal-plano-premium" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-stone-950 border-2 border-amber-600/80 rounded-2xl max-w-md w-full p-6 shadow-[0_0_50px_rgba(245,158,11,0.25)] relative">
            <button onclick="gerenciadorSalas.fecharModalPremium()" class="absolute top-4 right-4 text-stone-500 hover:text-white text-xl font-bold">&times;</button>
            
            <div class="text-center mb-4">
                <span class="text-4xl block mb-1">👑</span>
                <h3 class="text-xl font-bold text-amber-400 font-ritual">PACTO PREMIUM DAS TREVAS</h3>
                <p class="text-xs text-stone-400 mt-1">Domine os mistérios sobrenaturais sem quaisquer restrições.</p>
            </div>

            <div class="space-y-3 my-5 text-xs text-stone-300">
                <div class="flex items-start space-x-2.5 p-2 rounded-lg bg-stone-900/60 border border-amber-950">
                    <span class="text-amber-400 text-sm">💬</span>
                    <div>
                        <strong class="text-amber-300">Chats de Mensagens Desbloqueados:</strong>
                        <p class="text-stone-400 text-[11px]">Converse e ouça os sussurros do além nos chats de todas as salas.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-2.5 p-2 rounded-lg bg-stone-900/60 border border-amber-950">
                    <span class="text-amber-400 text-sm">👥</span>
                    <div>
                        <strong class="text-amber-300">Salas com Mais de 3 Pessoas:</strong>
                        <p class="text-stone-400 text-[11px]">Reúna multidões de até 12 pessoas em seus círculos de invocação.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-2.5 p-2 rounded-lg bg-stone-900/60 border border-amber-950">
                    <span class="text-amber-400 text-sm">🔮</span>
                    <div>
                        <strong class="text-amber-300">Roletas Personalizadas em Grupo:</strong>
                        <p class="text-stone-400 text-[11px]">Crie perguntas e opções personalizadas em salas com mais de 4 pessoas.</p>
                    </div>
                </div>
            </div>

            <!-- Botão de Ativação do Pacto -->
            <div class="space-y-2">
                <button onclick="gerenciadorSalas.alternarStatusPremium(1)" class="w-full bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-extrabold py-3 rounded-xl shadow-lg border border-amber-300/60 font-ritual tracking-wider text-xs sm:text-sm uppercase transition-all">
                    🩸 SELAR PACTO PREMIUM AGORA
                </button>
                <button onclick="gerenciadorSalas.alternarStatusPremium(0)" class="w-full text-stone-500 hover:text-stone-400 text-[11px] py-1 text-center font-medieval">
                    (Cancelar ou Revogar Selo Premium para testes)
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="assets/js/audio.js"></script>
    <script src="assets/js/toast-modal.js"></script>
    <script src="assets/js/roleta.js"></script>
    <script src="assets/js/salas.js"></script>
</body>
</html>
