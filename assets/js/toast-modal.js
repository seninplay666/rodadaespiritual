/**
 * Sistema de Notificações TOAST e Caixas MODAL do Além
 * Substitui caixas nativas (alert/confirm) e exibe as respostas macabras em Pop-Ups.
 */

// 1. CONTAINER GLOBAL DE TOASTS
(function inicializarContainers() {
    if (!document.getElementById('toast-container-espectral')) {
        const toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container-espectral';
        toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col space-y-3 max-w-sm w-full px-4 pointer-events-none';
        document.body.appendChild(toastContainer);
    }
})();

/**
 * Exibe um TOAST estilizado na interface (erro, aviso ou sucesso)
 * @param {string} mensagem 
 * @param {'erro'|'sucesso'|'aviso'} tipo 
 * @param {number} duracao 
 */
function mostrarToast(mensagem, tipo = 'erro', duracao = 4500) {
    const container = document.getElementById('toast-container-espectral') || document.body;

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto transform transition-all duration-300 ease-out translate-y-3 opacity-0 rounded-lg p-4 shadow-2xl border flex items-start space-x-3 text-sm`;

    let icone = '⚠️';
    let bordaEcor = 'bg-stone-950/95 border-red-700 text-red-200 box-glow-red';

    if (tipo === 'erro') {
        icone = '💀';
        bordaEcor = 'bg-stone-950/95 border-red-600 text-red-100 shadow-[0_0_20px_rgba(220,38,38,0.4)]';
    } else if (tipo === 'sucesso') {
        icone = '🩸';
        bordaEcor = 'bg-stone-950/95 border-emerald-700 text-emerald-200 shadow-[0_0_20px_rgba(16,185,129,0.3)]';
    } else if (tipo === 'aviso') {
        icone = '🕯️';
        bordaEcor = 'bg-stone-950/95 border-amber-600 text-amber-200 shadow-[0_0_20px_rgba(245,158,11,0.3)]';
    }

    toast.className += ` ${bordaEcor}`;
    toast.innerHTML = `
        <div class="text-xl flex-shrink-0 mt-0.5">${icone}</div>
        <div class="flex-1 font-medium leading-relaxed">${mensagem}</div>
        <button class="text-gray-400 hover:text-white transition-colors ml-2 font-bold text-lg">&times;</button>
    `;

    const botaoFechar = toast.querySelector('button');
    botaoFechar.onclick = () => removerToast(toast);

    container.appendChild(toast);

    // Animação de entrada
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-3', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    if (window.audioEspectral && tipo === 'erro') {
        window.audioEspectral.tocarBadaloSino(180, 0.2);
    }

    // Auto-remover
    const timer = setTimeout(() => {
        removerToast(toast);
    }, duracao);

    function removerToast(elemento) {
        clearTimeout(timer);
        elemento.classList.add('opacity-0', 'translate-x-4');
        setTimeout(() => {
            if (elemento.parentNode) {
                elemento.parentNode.removeChild(elemento);
            }
        }, 300);
    }
}

/**
 * Exibe um MODAL estilizado em substituição aos alerts/confirms nativos
 */
function mostrarModal({
    titulo = 'Mensagem das Trevas',
    mensagem = '',
    icone = '👁️',
    textoBotao = 'Entendido',
    onConfirm = null,
    textoCancelar = null,
    onCancel = null
}) {
    // Remove modal anterior se houver
    const anterior = document.getElementById('modal-espectral-backdrop');
    if (anterior) anterior.remove();

    const backdrop = document.createElement('div');
    backdrop.id = 'modal-espectral-backdrop';
    backdrop.className = 'fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300 opacity-0';

    const card = document.createElement('div');
    card.className = 'bg-stone-950 border-2 border-red-800 rounded-xl max-w-md w-full p-6 text-center shadow-[0_0_40px_rgba(220,38,38,0.35)] transform transition-transform duration-300 scale-95 relative overflow-hidden';

    let botoesHtml = `
        <button id="btn-modal-confirm" class="w-full bg-gradient-to-r from-red-800 to-red-600 hover:from-red-700 hover:to-red-500 text-white font-semibold py-2.5 px-5 rounded-lg shadow-lg border border-red-500/50 transition-all uppercase tracking-wider text-sm font-ritual">
            ${textoBotao}
        </button>
    `;

    if (textoCancelar) {
        botoesHtml = `
            <div class="grid grid-cols-2 gap-3 mt-2">
                <button id="btn-modal-cancel" class="bg-stone-800 hover:bg-stone-700 text-gray-300 font-medium py-2.5 px-4 rounded-lg border border-stone-600 transition-all text-sm font-ritual">
                    ${textoCancelar}
                </button>
                <button id="btn-modal-confirm" class="bg-red-700 hover:bg-red-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-lg border border-red-500 transition-all text-sm font-ritual">
                    ${textoBotao}
                </button>
            </div>
        `;
    }

    card.innerHTML = `
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-red-900/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="text-4xl mb-3 animate-pulse">${icone}</div>
        <h3 class="text-xl font-bold text-red-500 font-ritual tracking-wide mb-2">${titulo}</h3>
        <div class="text-gray-300 text-sm leading-relaxed mb-6 font-sans">${mensagem}</div>
        ${botoesHtml}
    `;

    backdrop.appendChild(card);
    document.body.appendChild(backdrop);

    // Animação de abertura
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        card.classList.remove('scale-95');
        card.classList.add('scale-100');
    });

    const fechar = () => {
        backdrop.classList.add('opacity-0');
        card.classList.add('scale-95');
        setTimeout(() => backdrop.remove(), 250);
    };

    const btnConfirm = card.querySelector('#btn-modal-confirm');
    btnConfirm.onclick = () => {
        fechar();
        if (typeof onConfirm === 'function') onConfirm();
    };

    const btnCancel = card.querySelector('#btn-modal-cancel');
    if (btnCancel) {
        btnCancel.onclick = () => {
            fechar();
            if (typeof onCancel === 'function') onCancel();
        };
    }
}

/**
 * Regra CORE: Toda resposta "Sim" e "Não" será respondida por um Pop-Up,
 * com uma resposta "macabra", mas ao mesmo tempo divertida e que incentiva um novo jogo!
 */
function mostrarPopUpResposta({
    resultado,
    respostaMacabra,
    pergunta,
    tipo = 'sim',
    aoJogarNovamente = null
}) {
    const backdropId = 'popup-resposta-espiritual';
    const antigo = document.getElementById(backdropId);
    if (antigo) antigo.remove();

    const backdrop = document.createElement('div');
    backdrop.id = backdropId;
    backdrop.className = 'fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300 opacity-0';

    const isSim = tipo === 'sim';
    const corDestaque = isSim ? 'text-red-500' : 'text-stone-300';
    const bordaCor = isSim ? 'border-red-600 shadow-[0_0_60px_rgba(220,38,38,0.5)]' : 'border-stone-700 shadow-[0_0_60px_rgba(139,0,0,0.4)]';
    const iconeGrande = isSim ? '🩸' : '🕯️';

    const card = document.createElement('div');
    card.className = `bg-stone-950 border-2 ${bordaCor} rounded-2xl max-w-lg w-full p-6 sm:p-8 text-center relative overflow-hidden transform scale-90 transition-transform duration-300`;

    card.innerHTML = `
        <!-- Efeito de névoa e runas no fundo -->
        <div class="absolute inset-0 bg-radial-gradient from-red-950/30 to-transparent pointer-events-none"></div>
        <div class="text-xs uppercase tracking-widest text-red-400/80 font-ritual mb-1">O Além Declarou:</div>
        
        <!-- Pergunta feita -->
        <div class="text-gray-400 text-xs italic mb-4 max-w-xs mx-auto truncate" title="${pergunta}">
            "${pergunta}"
        </div>

        <!-- Grande Resultado: SIM ou NÃO -->
        <div class="my-4">
            <span class="inline-block text-6xl sm:text-7xl font-extrabold ${corDestaque} font-horror tracking-wider drop-shadow-[0_0_20px_rgba(220,38,38,0.8)] animate-pulse">
                ${resultado}
            </span>
        </div>

        <!-- Resposta Macabra e Divertida -->
        <div class="bg-stone-900/80 border border-red-900/60 rounded-xl p-4 my-5 text-gray-200 text-sm sm:text-base leading-relaxed font-medieval shadow-inner">
            <span class="text-xl mr-1">${iconeGrande}</span>
            "${respostaMacabra}"
        </div>

        <!-- Botões de Ação: Incentivando Novo Jogo -->
        <div class="flex flex-col sm:flex-row gap-3 mt-6">
            <button id="btn-jogar-novamente" class="flex-1 bg-gradient-to-r from-red-800 to-red-600 hover:from-red-700 hover:to-red-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg border border-red-400/40 transition-all font-ritual tracking-wider flex items-center justify-center space-x-2 text-sm group">
                <span class="text-lg group-hover:rotate-180 transition-transform duration-500">↺</span>
                <span>DESAFIAR O ALÉM NOVAMENTE</span>
            </button>
            <button id="btn-fechar-popup" class="bg-stone-900 hover:bg-stone-800 text-gray-400 hover:text-white py-3 px-5 rounded-xl border border-stone-800 transition-colors text-sm font-ritual">
                Fechar
            </button>
        </div>
    `;

    backdrop.appendChild(card);
    document.body.appendChild(backdrop);

    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        card.classList.remove('scale-90');
        card.classList.add('scale-100');
    });

    const fechar = () => {
        backdrop.classList.add('opacity-0');
        card.classList.add('scale-90');
        setTimeout(() => backdrop.remove(), 250);
    };

    card.querySelector('#btn-fechar-popup').onclick = fechar;

    card.querySelector('#btn-jogar-novamente').onclick = () => {
        fechar();
        if (typeof aoJogarNovamente === 'function') {
            aoJogarNovamente();
        }
    };
}

// Substituição obrigatória de caixas nativas (alert/confirm)
window.alert = function(msg) {
    mostrarModal({
        titulo: 'Aviso Sobrenatural',
        mensagem: String(msg),
        icone: '⚠️',
        textoBotao: 'Entendido'
    });
};
