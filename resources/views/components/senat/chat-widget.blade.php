{{--
  Floating AI Assistant Chat Widget — Senat Polines
  Desain: Impeccable Craft, Tailwind CSS + Alpine.js
  Fitur: Expandable Bubble, Quick Prompt Chips, Typing Indicator, Resilient Error Handling
--}}

<div x-data="senatChatWidget()" class="fixed bottom-6 right-6 z-50 font-sans" @keydown.escape.window="isOpen = false">
    {{-- Launcher Bubble Button --}}
    <button
        type="button"
        @click="toggleChat()"
        :aria-expanded="isOpen"
        aria-label="Buka Asisten Virtual Senat Polines"
        class="group relative flex h-14 w-14 items-center justify-center rounded-full bg-senat-primary text-white shadow-xl ring-4 ring-white/80 transition-all duration-300 ease-out hover:scale-105 hover:bg-senat-primaryHover hover:shadow-2xl focus:outline-none focus-visible:ring-4 focus-visible:ring-senat-orange"
    >
        {{-- Ping pulse indicator online --}}
        <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex h-4 w-4 rounded-full border-2 border-white bg-emerald-500"></span>
        </span>

        {{-- Icon Open / Close — CSS state, bukan x-show (hindari bug x-show+x-transition) --}}
        <div class="relative h-6 w-6">
            {{-- Chat / Bot Icon --}}
            <svg
                class="absolute inset-0 h-6 w-6 transition-all duration-200 ease-out"
                :class="isOpen ? 'rotate-45 scale-75 opacity-0' : 'rotate-0 scale-100 opacity-100'"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>

            {{-- Close (X) Icon --}}
            <svg
                class="absolute inset-0 h-6 w-6 transition-all duration-200 ease-out"
                :class="isOpen ? 'rotate-0 scale-100 opacity-100' : '-rotate-45 scale-75 opacity-0'"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
    </button>

    {{-- Chat Box Window — dikontrol kelas CSS, bukan x-show (deterministik di semua browser) --}}
    <div
        role="dialog"
        aria-label="Asisten Virtual Senat Polines"
        :aria-hidden="isOpen ? 'false' : 'true'"
        :class="isOpen
            ? 'pointer-events-auto visible translate-y-0 scale-100 opacity-100'
            : 'pointer-events-none invisible translate-y-3 scale-95 opacity-0'"
        class="absolute right-0 bottom-20 flex h-[min(520px,calc(100dvh-7rem))] w-[92vw] max-w-[380px] origin-bottom-right flex-col overflow-hidden rounded-2xl border border-senat-line bg-white shadow-2xl transition-all duration-300 ease-out sm:max-w-[400px]"
    >
        {{-- Header Widget --}}
        <div class="flex items-center justify-between border-b border-senat-primaryHover/20 bg-senat-primary px-4 py-3.5 text-white">
            <div class="flex items-center gap-3">
                <div class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 font-bold text-senat-sky shadow-inner">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 01-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                    </svg>
                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border border-senat-primary bg-emerald-400"></span>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-white leading-tight">Asisten Senat Polines</h3>
                    <p class="text-[11px] text-senat-skySoft">Informasi Resmi &amp; Kanal Aspirasi</p>
                </div>
            </div>
            <button
                type="button"
                @click="isOpen = false"
                class="rounded-lg p-1.5 text-white/70 hover:bg-white/10 hover:text-white transition-colors"
                aria-label="Tutup asisten"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Message Stream Body --}}
        <div id="senatChatStream" class="flex-1 overflow-y-auto p-4 space-y-3.5 bg-senat-surface text-xs leading-relaxed">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start items-start gap-2'">
                    {{-- Bot Avatar Thumbnail --}}
                    <template x-if="msg.role !== 'user'">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-senat-primary text-white text-[10px] font-bold">
                            S
                        </div>
                    </template>

                    {{-- Message Bubble --}}
                    <div
                        :class="msg.role === 'user'
                            ? 'max-w-[82%] rounded-2xl rounded-tr-sm bg-senat-primary px-3.5 py-2.5 text-white shadow-sm'
                            : 'max-w-[85%] rounded-2xl rounded-tl-sm border border-senat-line bg-white px-3.5 py-2.5 text-senat-ink shadow-sm'"
                    >
                        <p class="whitespace-pre-line text-xs" x-html="formatMessage(msg.content)"></p>
                    </div>
                </div>
            </template>

            {{-- Typing Indicator Dots --}}
            <div x-show="isLoading" class="flex items-center gap-2" x-cloak>
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-senat-primary text-white text-[10px] font-bold">
                    S
                </div>
                <div class="flex items-center gap-1.5 rounded-2xl rounded-tl-sm border border-senat-line bg-white px-3.5 py-2.5 shadow-sm">
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-senat-muted" style="animation-delay: 0ms"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-senat-muted" style="animation-delay: 150ms"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-senat-muted" style="animation-delay: 300ms"></span>
                </div>
            </div>
        </div>

        {{-- Quick Prompts Chips --}}
        <div class="border-t border-senat-line/60 bg-white px-3 py-2 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button
                type="button"
                @click="sendPrompt('Siapa Ketua Senat Polines?')"
                class="shrink-0 rounded-full border border-senat-line bg-senat-surface px-2.5 py-1 text-[11px] font-medium text-senat-body transition hover:border-senat-primary hover:text-senat-primary"
            >
                👑 Ketua Senat
            </button>
            <button
                type="button"
                @click="sendPrompt('Apa tugas 5 Komisi Senat?')"
                class="shrink-0 rounded-full border border-senat-line bg-senat-surface px-2.5 py-1 text-[11px] font-medium text-senat-body transition hover:border-senat-primary hover:text-senat-primary"
            >
                🏛️ 5 Komisi
            </button>
            <button
                type="button"
                @click="sendPrompt('Bagaimana cara mengirim aspirasi?')"
                class="shrink-0 rounded-full border border-senat-line bg-senat-surface px-2.5 py-1 text-[11px] font-medium text-senat-body transition hover:border-senat-primary hover:text-senat-primary"
            >
                📢 Ajukan Aspirasi
            </button>
        </div>

        {{-- Input Composer Bar --}}
        <form @submit.prevent="submitMessage()" class="border-t border-senat-line bg-white p-2.5">
            <div class="relative flex items-center">
                <input
                    type="text"
                    x-model="inputMessage"
                    :disabled="isLoading"
                    placeholder="Tanya seputar Senat Polines..."
                    class="w-full rounded-xl border border-senat-line bg-senat-surface py-2.5 pl-3.5 pr-11 text-xs text-senat-ink placeholder:text-senat-muted focus:border-senat-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-senat-primary disabled:opacity-60"
                />
                <button
                    type="submit"
                    :disabled="isLoading || !inputMessage.trim()"
                    class="absolute right-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-senat-primary text-white transition-all hover:bg-senat-primaryHover disabled:opacity-40 disabled:hover:bg-senat-primary"
                    aria-label="Kirim pesan"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </button>
            </div>
            <div class="mt-1.5 text-center">
                <span class="text-[10px] text-senat-muted">Asisten AI resmi Politeknik Negeri Semarang</span>
            </div>
        </form>
    </div>
</div>

<script>
function senatChatWidget() {
    return {
        isOpen: false,
        isLoading: false,
        inputMessage: '',
        messages: [
            {
                role: 'assistant',
                content: 'Halo! Saya asisten virtual resmi Senat Polines. Ada yang ingin Anda tanyakan seputar struktur komisi, produk hukum, atau pengajuan aspirasi mahasiswa?'
            }
        ],
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        sendPrompt(text) {
            this.inputMessage = text;
            this.submitMessage();
        },
        async submitMessage() {
            const text = this.inputMessage.trim();
            if (!text || this.isLoading) return;

            // Push User Message
            this.messages.push({ role: 'user', content: text });
            this.inputMessage = '';
            this.isLoading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch('/api/chatbot/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        message: text,
                        history: this.messages.map(m => ({ role: m.role, content: m.content }))
                    })
                });

                const json = await response.json();
                if (json.success && json.data?.reply) {
                    this.messages.push({
                        role: 'assistant',
                        content: json.data.reply
                    });
                } else {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Maaf, terjadi kendala saat memproses jawaban. Silakan coba kembali sesaat lagi.'
                    });
                }
            } catch (err) {
                this.messages.push({
                    role: 'assistant',
                    content: 'Koneksi ke asisten Senat terputus. Pastikan koneksi internet Anda stabil.'
                });
            } finally {
                this.isLoading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        scrollToBottom() {
            const stream = document.getElementById('senatChatStream');
            if (stream) {
                stream.scrollTop = stream.scrollHeight;
            }
        },
        formatMessage(text) {
            if (!text) return '';
            // Escape dulu (model output tidak dipercaya), baru beri markup terbatas
            const escaped = String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
            return escaped
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/`([^`]+)`/g, '<code class="rounded border border-senat-line bg-senat-surface px-1 py-0.5 font-mono text-[11px]">$1</code>');
        }
    };
}
</script>
