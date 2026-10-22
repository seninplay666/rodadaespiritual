<?php
/**
 * Grimório do Plano Premium
 * Regras: Apresentação e ativação dos poderes exclusivos
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/auth_check.php';

$usuario = obterUsuarioLogado($pdo);
$ehPremium = $usuario && (int)$usuario['is_premium'] === 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacto Premium | Rodada Espiritual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/horror.css">
</head>
<body class="bg-ritual-pattern min-h-screen flex flex-col justify-between selection:bg-amber-900 selection:text-white">

    <header class="w-full border-b border-red-950/70 bg-black/70 backdrop-blur-md sticky top-0 z-40 px-4 py-3">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="index.php" class="text-xl sm:text-2xl font-horror text-red-600 tracking-wider hover:text-red-500 transition-colors flex items-center space-x-1.5">
                <span>🩸</span>
                <span>RODADA ESPIRITUAL</span>
            </a>

            <div class="flex items-center space-x-3">
                <a href="index.php" class="text-xs text-stone-300 hover:text-red-400 font-ritual flex items-center space-x-1">
                    <span>↺</span>
                    <span>Voltar à Roda</span>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-8 sm:py-12 flex flex-col items-center">
        
        <div class="text-center mb-8">
            <span class="text-5xl block mb-2 filter drop-shadow-[0_0_15px_#f59e0b]">👑</span>
            <div class="text-xs uppercase tracking-widest text-amber-500 font-ritual font-bold">O Círculo Superior das Trevas</div>
            <h1 class="text-3xl sm:text-5xl font-horror text-amber-400 drop-shadow-[0_0_20px_rgba(245,158,11,0.5)] mt-1">
                PACTO PREMIUM
            </h1>
            <p class="text-xs sm:text-sm text-stone-400 font-medieval max-w-md mx-auto mt-2">
                Eleve sua alma ao ápice do ocultismo com privilégios restritos aos verdadeiros mestres do além.
            </p>
        </div>

        <!-- Grade de Benefícios -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full mb-10">
            <!-- Benefício 1: Chat -->
            <div class="bg-stone-950/90 border border-amber-900/60 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                <div>
                    <div class="text-3xl mb-3">💬</div>
                    <h3 class="text-lg font-bold text-amber-300 font-ritual mb-2">Voz Desbloqueada</h3>
                    <p class="text-xs text-stone-400 leading-relaxed font-sans">
                        Sua voz não será silenciada pelas trevas. Acesso irrestrito a todos os chats de mensagens em tempo real nas salas.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-stone-900 text-[11px] text-amber-500 font-mono">
                    Regra: Chats exclusivos Premium
                </div>
            </div>

            <!-- Benefício 2: Salas com +3 pessoas -->
            <div class="bg-stone-950/90 border border-amber-900/60 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                <div>
                    <div class="text-3xl mb-3">👥</div>
                    <h3 class="text-lg font-bold text-amber-300 font-ritual mb-2">Grandes Círculos</h3>
                    <p class="text-xs text-stone-400 leading-relaxed font-sans">
                        Reúna assembleias sombrias. Funde salas com 4, 6, 8 ou até 12 pessoas conectadas ao mesmo rito de invocação.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-stone-900 text-[11px] text-amber-500 font-mono">
                    Regra: Salas +3 pessoas exclusivas
                </div>
            </div>

            <!-- Benefício 3: Roletas Personalizadas com > 4 pessoas -->
            <div class="bg-stone-950/90 border border-amber-900/60 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                <div>
                    <div class="text-3xl mb-3">🔮</div>
                    <h3 class="text-lg font-bold text-amber-300 font-ritual mb-2">Roletas Customizadas</h3>
                    <p class="text-xs text-stone-400 leading-relaxed font-sans">
                        Crie suas próprias rodas e perguntas macabras para jogar com sua seita em salas com mais de 4 pessoas conectadas.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-stone-900 text-[11px] text-amber-500 font-mono">
                    Regra: Roletas custom com 5+ pessoas
                </div>
            </div>
        </div>

        <!-- Caixa de Ação do Pacto -->
        <div class="bg-stone-950 border-2 border-amber-600/80 rounded-2xl p-8 max-w-lg w-full text-center shadow-[0_0_50px_rgba(245,158,11,0.2)]">
            <h4 class="text-xl font-bold font-ritual text-amber-400 mb-2">
                Status da sua Alma: 
                <span class="<?php echo $ehPremium ? 'text-amber-300' : 'text-stone-400'; ?>">
                    <?php echo $ehPremium ? '★ CONDENADA AO PREMIUM' : 'Iniciado Comum'; ?>
                </span>
            </h4>
            
            <p class="text-xs text-stone-400 mb-6 font-medieval">
                <?php if ($ehPremium): ?>
                    Você já possui o Selo Supremo ativado! Todos os chats e salas ilimitadas estão ao seu alcance.
                <?php else: ?>
                    Sele seu pacto agora para desbloquear chats, criar salas multidão e invocar roletas personalizadas.
                <?php endif; ?>
            </p>

            <div class="space-y-3">
                <?php if ($usuario): ?>
                    <?php if (!$ehPremium): ?>
                        <button onclick="gerenciadorSalas.alternarStatusPremium(1)" class="w-full bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-400 text-stone-950 font-extrabold py-3.5 rounded-xl font-ritual tracking-widest text-sm uppercase transition-all shadow-xl">
                            🩸 SELAR PACTO PREMIUM
                        </button>
                    <?php else: ?>
                        <button onclick="gerenciadorSalas.alternarStatusPremium(0)" class="w-full bg-stone-900 hover:bg-stone-800 text-stone-400 py-3 rounded-xl border border-stone-800 font-ritual text-xs transition-colors">
                            Revogar Selo Premium (Modo de Testes)
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <button onclick="gerenciadorSalas.abrirModalAuth('login')" class="w-full bg-red-800 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl font-ritual text-xs tracking-wider transition-colors">
                        ENTRE COM SUA CONTA PARA SELAR O PACTO
                    </button>
                <?php endif; ?>

                <a href="index.php" class="inline-block text-xs text-stone-500 hover:text-stone-300 pt-2 font-medieval">
                    ← Retornar à Roda de Charlie Charlie
                </a>
            </div>
        </div>

    </main>

    <!-- SCRIPTS -->
    <script src="assets/js/audio.js"></script>
    <script src="assets/js/toast-modal.js"></script>
    <script src="assets/js/roleta.js"></script>
    <script src="assets/js/salas.js"></script>
</body>
</html>
