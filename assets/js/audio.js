/**
 * Motor de Áudio Espectral (Web Audio API)
 * Gera efeitos sonoros sobrenaturais 100% sintetizados via código (independente de mp3 externos)
 */

class AudioEspectral {
    constructor() {
        this.ctx = null;
        this.ativo = localStorage.getItem('audio_espiritual_ativo') !== 'false';
    }

    obterContexto() {
        if (!this.ctx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.ctx = new AudioContext();
            }
        }
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume();
        }
        return this.ctx;
    }

    alternarAudio() {
        this.ativo = !this.ativo;
        localStorage.setItem('audio_espiritual_ativo', this.ativo ? 'true' : 'false');
        if (this.ativo) {
            this.obterContexto();
            this.tocarBadaloSino(440, 0.4);
        }
        return this.ativo;
    }

    estaAtivo() {
        return this.ativo;
    }

    /**
     * Toca sussurro ou sopro fantasmagórico (ruído rosa filtrado)
     */
    tocarSussurro() {
        if (!this.ativo) return;
        const ctx = this.obterContexto();
        if (!ctx) return;

        const bufferSize = ctx.sampleRate * 2.5;
        const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
        const data = buffer.getChannelData(0);

        let lastOut = 0.0;
        for (let i = 0; i < bufferSize; i++) {
            const white = Math.random() * 2 - 1;
            lastOut = (lastOut + (0.02 * white)) / 1.02;
            data[i] = lastOut * 3.5;
        }

        const noise = ctx.createBufferSource();
        noise.buffer = buffer;

        const filter = ctx.createBiquadFilter();
        filter.type = 'bandpass';
        filter.frequency.setValueAtTime(320, ctx.currentTime);
        filter.frequency.exponentialRampToValueAtTime(120, ctx.currentTime + 2.4);
        filter.Q.value = 4.0;

        const gain = ctx.createGain();
        gain.gain.setValueAtTime(0.001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.35, ctx.currentTime + 0.6);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 2.5);

        noise.connect(filter);
        filter.connect(gain);
        gain.connect(ctx.destination);

        noise.start();
    }

    /**
     * Toca o som de fricção e oscilação do lápis girando
     */
    tocarGiroLapis() {
        if (!this.ativo) return;
        const ctx = this.obterContexto();
        if (!ctx) return;

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(80, ctx.currentTime);
        osc.frequency.linearRampToValueAtTime(140, ctx.currentTime + 1.5);
        osc.frequency.linearRampToValueAtTime(60, ctx.currentTime + 3.5);

        const filter = ctx.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.setValueAtTime(400, ctx.currentTime);

        gain.gain.setValueAtTime(0.01, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(0.12, ctx.currentTime + 0.5);
        gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 3.6);

        osc.connect(filter);
        filter.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + 3.7);
    }

    /**
     * Toca o badalo de sino macabro de igreja abandonada
     */
    tocarBadaloSino(freq = 220, vol = 0.35) {
        if (!this.ativo) return;
        const ctx = this.obterContexto();
        if (!ctx) return;

        const t = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, t);
        osc.frequency.exponentialRampToValueAtTime(freq * 0.98, t + 3.0);

        gain.gain.setValueAtTime(vol, t);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + 3.2);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(t);
        osc.stop(t + 3.3);
    }

    /**
     * Toca impacto macabro ao revelar a resposta do SIM ou NÃO
     */
    tocarImpactoResposta(tipo = 'sim') {
        if (!this.ativo) return;
        const ctx = this.obterContexto();
        if (!ctx) return;

        const t = ctx.currentTime;
        const baseFreq = tipo === 'sim' ? 140 : 85;

        // Oscilador grave de impacto (Boom)
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'triangle';
        osc.frequency.setValueAtTime(baseFreq, t);
        osc.frequency.exponentialRampToValueAtTime(28, t + 1.2);

        gain.gain.setValueAtTime(0.5, t);
        gain.gain.exponentialRampToValueAtTime(0.001, t + 1.5);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(t);
        osc.stop(t + 1.6);

        // Eco dissonante espectral
        const dissonant = ctx.createOscillator();
        const dissGain = ctx.createGain();
        dissonant.type = 'sawtooth';
        dissonant.frequency.setValueAtTime(tipo === 'sim' ? 330 : 277, t);
        dissGain.gain.setValueAtTime(0.15, t);
        dissGain.gain.exponentialRampToValueAtTime(0.001, t + 1.8);

        const filter = ctx.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.value = 500;

        dissonant.connect(filter);
        filter.connect(dissGain);
        dissGain.connect(ctx.destination);

        dissonant.start(t);
        dissonant.stop(t + 1.9);
    }
}

window.audioEspectral = new AudioEspectral();
