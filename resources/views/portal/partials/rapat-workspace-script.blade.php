<script>
window.__senatMeetings = @json($meetings ?? []);
window.__senatUserId = {{ (int) ($idUser ?? 2) }};
window.__senatBase = '{{ url('/') }}';
document.addEventListener('alpine:init', () => {
    Alpine.data('memberAttendance', () => ({
        /* ---------- Gerbang: rapat terpilih ---------- */
        meetings: [],
        selectedMeetingId: null,
        idUser: 2,
        apiOnline: false,
        menyimpan: false,

        /* ---------- Catatan & notulensi ---------- */
        noteText: '',
        noteDirty: false,
        savedNote: false,
        noteError: '',
        attachments: [],

        /* ---------- Berbagi dokumen & tautan ---------- */
        sharedItems: [],
        shareUrl: '',
        shareTitle: '',
        shareError: '',

        /* ---------- E-voting ---------- */
        activeVote: null,
        myVote: null,
        voteSubmitted: false,
        isNumericTitle: false,

        editorTools: [
            { cmd: 'bold', label: 'B', title: 'Tebal (Ctrl+B)' },
            { cmd: 'italic', label: 'I', title: 'Miring (Ctrl+I)' },
            { cmd: 'underline', label: 'U', title: 'Garis bawah (Ctrl+U)' },
            { cmd: 'insertUnorderedList', label: '• Daftar', title: 'Daftar butir' },
            { cmd: 'insertOrderedList', label: '1. Daftar', title: 'Daftar bernomor' },
            { cmd: 'formatBlock', value: 'h3', label: 'H', title: 'Subjudul' },
            { cmd: 'formatBlock', value: 'blockquote', label: '❝', title: 'Kutipan' },
            { cmd: 'formatBlock', value: 'p', label: '¶', title: 'Paragraf normal' },
            { cmd: 'removeFormat', label: '✕ fmt', title: 'Hapus format' }
        ],

        get selectedMeeting() {
            if (!this.selectedMeetingId) return null;
            return this.meetings.find(m => m.id === this.selectedMeetingId) || null;
        },

        get wordCount() {
            const t = (this.noteText || '').trim();
            return t ? t.split(/\s+/).length : 0;
        },

        init() {
            this.meetings = window.__senatMeetings || [];
            this.idUser = window.__senatUserId || 2;
            this.loadVoteState();
            window.addEventListener('storage', () => this.loadVoteState());

            const saved = parseInt(localStorage.getItem('polines_senat_meeting_selected') || '', 10);
            const found = this.meetings.find(m => m.id === saved);
            if (found) this.selectMeeting(found);
        },

        loadVoteState() {
            const stored = localStorage.getItem('polines_senat_voting');
            if (stored) {
                this.activeVote = JSON.parse(stored);
            } else {
                this.activeVote = {
                    isOpen: true,
                    id: 'VOTE-2026-001',
                    title: 'Persetujuan Pengesahan Perubahan Kurikulum MBKM Vokasi 2026/2027',
                    description: 'Apakah Sidang Pleno menyetujui draf revisi kurikulum vokasi berbasis industri untuk disahkan menjadi Peraturan Senat Akademik?',
                    options: [
                        { id: 'setuju', label: 'Setuju / Mufakat', count: 22 },
                        { id: 'tolak', label: 'Menolak / Keberatan', count: 2 },
                        { id: 'abstain', label: 'Abstain / Pikir-pikir', count: 4 }
                    ]
                };
                localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
            }
            this.computeNumericTitle();

            const savedMyVote = localStorage.getItem('polines_member_my_vote');
            if (savedMyVote) {
                this.myVote = savedMyVote;
                this.voteSubmitted = true;
            }
        },

        castVote(optionId) {
            if (!this.selectedMeetingId) return;
            if (this.voteSubmitted || !this.activeVote || !this.activeVote.isOpen) return;
            this.myVote = optionId;
            this.voteSubmitted = true;
            localStorage.setItem('polines_member_my_vote', optionId);

            const opt = this.activeVote.options.find(o => o.id === optionId);
            if (opt) opt.count++;
            localStorage.setItem('polines_senat_voting', JSON.stringify(this.activeVote));
        },

        computeNumericTitle() {
            const t = (this.activeVote && this.activeVote.title ? this.activeVote.title : '').trim();
            this.isNumericTitle = t.length > 0 && t.length <= 12 && /^[\d\s.\-\/]+$/.test(t);
        },

        /* ================= PEMILIH RAPAT ================= */
        selectMeeting(m) {
            if (!m) return;
            this.selectedMeetingId = m.id;
            localStorage.setItem('polines_senat_meeting_selected', String(m.id));

            this.noteDirty = false;
            this.savedNote = false;
            this.shareError = '';

            // Tampilkan cache lokal dulu agar UI tidak kosong saat menunggu jaringan
            this.loadNote();
            this.loadAttachments();
            this.loadShared();

            this.muatWorkspace();
        },

        storageKey(kind) {
            return 'polines_senat_' + kind + '_' + this.selectedMeetingId;
        },

        baseUrl() {
            return (window.__senatBase || '') + '/portal/attendance/' + this.selectedMeetingId;
        },

        csrf() {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        },

        /** Ambil catatan + lampiran + berbagi dari backend (via proxy Laravel). */
        async muatWorkspace() {
            const idRapat = this.selectedMeetingId;
            if (!idRapat) return;

            try {
                const res = await fetch(this.baseUrl() + '/workspace', {
                    headers: { 'Accept': 'application/json' },
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (this.selectedMeetingId !== idRapat) return; // rapat berganti saat menunggu

                this.apiOnline = true;

                if (data.catatan && typeof data.catatan.isi === 'string') {
                    const html = data.catatan.isi;
                    this.$nextTick(() => {
                        if (this.$refs.editor) this.$refs.editor.innerHTML = html;
                        this.noteText = this.htmlToText(html);
                    });
                    localStorage.setItem(this.storageKey('note'), html);
                } else {
                    this.$nextTick(() => { if (this.$refs.editor) this.$refs.editor.innerHTML = ''; this.noteText = ''; });
                }

                this.attachments = (data.lampiran || []).map(r => this.mapLampiran(r));
                this.sharedItems = (data.berbagi || []).map(r => this.mapBerbagi(r));
                this.persistAttachments();
                this.persistShared();
            } catch (err) {
                console.warn('Workspace API tidak tersedia, memakai penyimpanan lokal.', err);
                this.apiOnline = false;
            }
        },

        mapLampiran(r) {
            return {
                id: r.id_lampiran,
                name: r.nama,
                size: r.ukuran,
                url: r.url,
                mime: r.mime,
                by: r.user ? r.user.nama : 'Anggota',
                mine: Number(r.id_user) === Number(this.idUser),
                time: this.timeOf(r.created_at),
            };
        },

        mapBerbagi(r) {
            return {
                id: r.id_berbagi,
                kind: r.jenis === 'link' ? 'link' : 'file',
                name: r.nama,
                url: r.url,
                size: r.ukuran,
                by: r.user ? r.user.nama : 'Anggota',
                mine: Number(r.id_user) === Number(this.idUser),
                time: this.timeOf(r.created_at),
            };
        },

        timeOf(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            if (isNaN(d.getTime())) return '';
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        },

        /* ================= CATATAN (TEKS EDITOR) ================= */
        format(cmd, value) {
            if (!this.$refs.editor) return;
            this.$refs.editor.focus();
            document.execCommand(cmd, false, value || null);
            this.syncNote();
        },

        onPaste(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
            this.syncNote();
        },

        syncNote() {
            if (!this.$refs.editor) return;
            this.noteText = this.$refs.editor.innerText || '';
            this.noteDirty = true;
            this.savedNote = false;
        },

        loadNote() {
            const html = localStorage.getItem(this.storageKey('note')) || '';
            this.noteText = this.htmlToText(html);
            this.$nextTick(() => {
                if (this.$refs.editor) this.$refs.editor.innerHTML = html;
            });
        },

        async saveNote() {
            if (!this.selectedMeetingId || !this.$refs.editor) return;

            const html = this.$refs.editor.innerHTML;
            this.noteError = '';
            this.menyimpan = true;

            // Cache lokal selalu ditulis agar tidak ada catatan yang hilang
            localStorage.setItem(this.storageKey('note'), html);

            if (this.apiOnline) {
                try {
                    const res = await fetch(this.baseUrl() + '/catatan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                        },
                        body: JSON.stringify({ isi: html }),
                    });

                    if (!res.ok) {
                        const msg = await res.json().catch(() => ({}));
                        throw new Error(msg.message || ('HTTP ' + res.status));
                    }
                } catch (err) {
                    this.menyimpan = false;
                    this.noteError = 'Gagal menyimpan ke server: ' + err.message + ' (tersimpan lokal)';
                    return;
                }
            }

            this.menyimpan = false;
            this.noteText = this.$refs.editor.innerText || '';
            this.noteDirty = false;
            this.savedNote = true;
            setTimeout(() => { this.savedNote = false; }, 4000);
        },

        /* ================= LAMPIRAN NOTULENSI (UNGGAH BERKAS) ================= */
        async addAttachments(e) {
            const files = Array.from(e.target.files || []);
            const max = 1024 * 1024 * 1024;
            this.noteError = '';

            for (const file of files) {
                if (file.size > max) {
                    alert('Berkas "' + file.name + '" melebihi 1 GB dan dilewati.');
                    continue;
                }

                if (this.apiOnline) {
                    try {
                        const form = new FormData();
                        form.append('file', file);

                        const res = await fetch(this.baseUrl() + '/lampiran', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                            body: form,
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));

                        this.attachments.unshift(this.mapLampiran(data.lampiran));
                        this.persistAttachments();
                        continue;
                    } catch (err) {
                        this.noteError = 'Unggah gagal: ' + err.message + ' — disimpan lokal.';
                    }
                }

                // Fallback lokal
                await new Promise(resolve => {
                    const item = {
                        id: 'att-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
                        name: file.name,
                        size: file.size,
                        time: this.nowLabel()
                    };
                    const reader = new FileReader();
                    reader.onload = () => {
                        item.dataUrl = reader.result;
                        this.attachments.unshift(item);
                        resolve();
                    };
                    reader.onerror = resolve;
                    reader.readAsDataURL(file);
                });
                this.persistAttachments();
            }

            e.target.value = '';
        },

        loadAttachments() {
            try {
                const raw = localStorage.getItem(this.storageKey('attach'));
                this.attachments = raw ? JSON.parse(raw) : [];
            } catch (err) {
                this.attachments = [];
            }
        },

        persistAttachments() {
            try {
                // Saat online, isi berkas ada di server — cukup simpan metadata
                const payload = this.apiOnline
                    ? this.attachments.map(({ dataUrl, ...rest }) => rest)
                    : this.attachments;
                localStorage.setItem(this.storageKey('attach'), JSON.stringify(payload));
            } catch (err) {
                console.warn('Lampiran terlalu besar untuk penyimpanan lokal; tetap tersedia di sesi ini.', err);
            }
        },

        async removeAttachment(i) {
            const f = this.attachments[i];
            if (!f) return;

            if (this.apiOnline && typeof f.id === 'number') {
                try {
                    const res = await fetch(this.baseUrl() + '/lampiran/' + f.id, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                } catch (err) {
                    this.noteError = 'Gagal menghapus lampiran: ' + err.message;
                    return;
                }
            }

            this.attachments.splice(i, 1);
            this.persistAttachments();
        },

        downloadAttachment(i) {
            const f = this.attachments[i];
            if (!f) return;
            if (f.url) { window.open(f.url, '_blank', 'noopener'); return; }
            if (!f.dataUrl) { alert('Isi berkas tidak tersedia pada sesi ini.'); return; }
            this.downloadFile(f.name, null, null, f.dataUrl);
        },

        /* ================= BERBAGI DOKUMEN & TAUTAN ================= */
        loadShared() {
            try {
                const raw = localStorage.getItem(this.storageKey('share'));
                this.sharedItems = raw ? JSON.parse(raw) : [];
            } catch (err) {
                this.sharedItems = [];
            }
        },

        persistShared() {
            try { localStorage.setItem(this.storageKey('share'), JSON.stringify(this.sharedItems)); } catch (err) {}
        },

        async shareFiles(e) {
            const files = Array.from(e.target.files || []);
            this.shareError = '';

            for (const file of files) {
                if (file.size > 1024 * 1024 * 1024) {
                    alert('Berkas "' + file.name + '" melebihi 1 GB dan dilewati.');
                    continue;
                }

                if (this.apiOnline) {
                    try {
                        const form = new FormData();
                        form.append('file', file);

                        const res = await fetch(this.baseUrl() + '/berbagi', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                            body: form,
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));

                        this.sharedItems.unshift(this.mapBerbagi(data.berbagi));
                        this.persistShared();
                        continue;
                    } catch (err) {
                        this.shareError = 'Gagal membagikan ' + file.name + ': ' + err.message;
                    }
                }

                await new Promise(resolve => {
                    const item = {
                        id: 'shr-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
                        kind: 'file',
                        name: file.name,
                        size: file.size,
                        by: 'Anda',
                        time: this.nowLabel(),
                        mine: true
                    };
                    const reader = new FileReader();
                    reader.onload = () => {
                        item.dataUrl = reader.result;
                        this.sharedItems.unshift(item);
                        resolve();
                    };
                    reader.onerror = resolve;
                    reader.readAsDataURL(file);
                });
                this.persistShared();
            }

            e.target.value = '';
        },

        async addLink() {
            this.shareError = '';
            const url = (this.shareUrl || '').trim();

            if (!/^https?:\/\/[^\s.]+\.[^\s]+/i.test(url)) {
                this.shareError = 'Tautan harus diawali http:// atau https://';
                return;
            }

            const nama = (this.shareTitle || '').trim() || this.domainOf(url);

            if (this.apiOnline) {
                try {
                    const res = await fetch(this.baseUrl() + '/berbagi', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                        },
                        body: JSON.stringify({ url: url, nama: nama, jenis: 'link' }),
                    });

                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(data.message || ('HTTP ' + res.status));

                    this.sharedItems.unshift(this.mapBerbagi(data.berbagi));
                    this.shareUrl = '';
                    this.shareTitle = '';
                    this.persistShared();
                    return;
                } catch (err) {
                    this.shareError = err.message;
                    return;
                }
            }

            this.sharedItems.unshift({
                id: 'lnk-' + Date.now(),
                kind: 'link',
                name: nama,
                url: url,
                by: 'Anda',
                time: this.nowLabel(),
                mine: true
            });

            this.shareUrl = '';
            this.shareTitle = '';
            this.persistShared();
        },

        async removeShared(i) {
            const item = this.sharedItems[i];
            if (!item) return;

            if (this.apiOnline && typeof item.id === 'number') {
                try {
                    const res = await fetch(this.baseUrl() + '/berbagi/' + item.id, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                } catch (err) {
                    this.shareError = 'Gagal menghapus: ' + err.message;
                    return;
                }
            }

            this.sharedItems.splice(i, 1);
            this.persistShared();
        },

        openShared(item) {
            if (item.url) window.open(item.url, '_blank', 'noopener');
        },

        downloadShared(item) {
            if (item.url) { window.open(item.url, '_blank', 'noopener'); return; }
            if (!item.dataUrl) {
                alert('Berkas ini tidak memiliki tautan unduhan.');
                return;
            }
            this.downloadFile(item.name, null, null, item.dataUrl);
        },

        /* ================= EXPORT NOTULENSI ================= */
        buildDocument() {
            const m = this.selectedMeeting || {};
            const note = (this.noteText || '').trim();
            const doc = [];

            doc.push('# Notulensi Rapat — ' + (m.type || 'Rapat Senat Akademik'));
            doc.push('');
            doc.push(m.title || '-');
            doc.push('');
            doc.push('- Tanggal: ' + (m.date || '-'));
            doc.push('- Waktu: ' + (m.time || '-'));
            doc.push('- Ruang: ' + (m.room || '-'));
            doc.push('- Status: ' + (m.status || '-'));
            if (m.session_code) doc.push('- Kode Sesi: ' + m.session_code);
            doc.push('');
            doc.push('## Catatan Anggota');
            doc.push('');
            doc.push(note || '_Belum ada catatan._');
            doc.push('');
            doc.push('## Lampiran Notulensi');
            doc.push('');

            if (this.attachments.length) {
                this.attachments.forEach(f => doc.push('- ' + f.name + ' (' + this.formatBytes(f.size) + ')'));
            } else {
                doc.push('_Tidak ada lampiran._');
            }

            doc.push('');
            doc.push('## Dokumen & Tautan Dibagikan');
            doc.push('');

            if (this.sharedItems.length) {
                this.sharedItems.forEach(it => {
                    doc.push(it.kind === 'link'
                        ? '- [' + it.name + '](' + it.url + ') — ' + it.by
                        : '- ' + it.name + ' (' + this.formatBytes(it.size) + ') — ' + it.by);
                });
            } else {
                doc.push('_Tidak ada._');
            }

            doc.push('');
            doc.push('---');
            doc.push('Diekspor ' + new Date().toLocaleString('id-ID') + ' · Portal Anggota Senat Akademik Polines');

            return doc.join('\n');
        },

        exportNote(fmt) {
            if (!this.selectedMeetingId) return;
            this.syncNote();

            let body = this.buildDocument();
            if (fmt === 'txt') {
                body = body.replace(/^#{1,3}\s+/gm, '').replace(/\*\*/g, '').replace(/_([^_]+)_/g, '$1');
            }

            const title = this.selectedMeeting ? this.selectedMeeting.title : 'rapat';
            const name = 'notulensi-' + this.slug(title) + '.' + fmt;
            const mime = fmt === 'md' ? 'text/markdown;charset=utf-8' : 'text/plain;charset=utf-8';

            this.downloadFile(name, body, mime);
        },

        printNote() {
            if (!this.selectedMeetingId) return;
            this.syncNote();

            const m = this.selectedMeeting || {};
            const win = window.open('', '_blank');
            if (!win) { alert('Popup diblokir browser. Izinkan popup untuk mencetak / simpan PDF.'); return; }

            const notesHtml = this.$refs.editor ? this.$refs.editor.innerHTML : '';
            const files = this.attachments
                .map(f => '<li>' + this.escapeHtml(f.name) + ' <span>(' + this.formatBytes(f.size) + ')</span></li>')
                .join('');

            win.document.write(
                '<!doctype html><html lang="id"><head><meta charset="utf-8">' +
                '<title>Notulensi — ' + this.escapeHtml(m.title || 'Rapat') + '</title>' +
                '<style>' +
                'body{font-family:Georgia,serif;color:#172133;margin:36px;line-height:1.6}' +
                'h1{font-size:19px;margin:0 0 4px}' +
                '.meta{font-size:12px;color:#52637d;margin-bottom:18px}' +
                'h2{font-size:13px;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e2e8ee;padding-bottom:4px;margin-top:24px}' +
                '.note{border:1px solid #e2e8ee;padding:14px;min-height:80px}' +
                'ul{padding-left:20px} li span{color:#637082;font-size:11px}' +
                'footer{margin-top:28px;font-size:10px;color:#637082;border-top:1px solid #e2e8ee;padding-top:8px}' +
                '</style></head><body>' +
                '<h1>Notulensi ' + this.escapeHtml(m.type || 'Rapat') + '</h1>' +
                '<div class="meta">' + this.escapeHtml(m.title || '') + '<br>' +
                this.escapeHtml(m.date || '') + ' · ' + this.escapeHtml(m.time || '') + ' · ' + this.escapeHtml(m.room || '') + '</div>' +
                '<h2>Catatan Anggota</h2><div class="note">' + (notesHtml || '<em>Belum ada catatan.</em>') + '</div>' +
                '<h2>Lampiran Notulensi</h2>' + (files ? '<ul>' + files + '</ul>' : '<p><em>Tidak ada lampiran.</em></p>') +
                '<footer>Diekspor ' + new Date().toLocaleString('id-ID') + ' · Portal Anggota Senat Akademik Polines</footer>' +
                '</body></html>'
            );

            win.document.close();
            win.focus();
            setTimeout(() => win.print(), 300);
        },

        /* ================= UTIL ================= */
        downloadFile(name, content, mime, href) {
            const link = document.createElement('a');
            link.download = name;

            if (href) {
                link.href = href;
            } else {
                const blob = new Blob([content], { type: mime || 'application/octet-stream' });
                link.href = URL.createObjectURL(blob);
                setTimeout(() => URL.revokeObjectURL(link.href), 2000);
            }

            document.body.appendChild(link);
            link.click();
            link.remove();
        },

        htmlToText(html) {
            if (!html) return '';
            const d = document.createElement('div');
            d.innerHTML = html;
            return (d.innerText || d.textContent || '').trim();
        },

        formatBytes(n) {
            if (n === null || n === undefined) return '-';
            if (n < 1024) return n + ' B';
            if (n < 1048576) return (n / 1024).toFixed(1).replace('.0', '') + ' KB';
            return (n / 1048576).toFixed(1).replace('.0', '') + ' MB';
        },

        extOf(name) {
            const parts = String(name || '').split('.');
            return parts.length > 1 ? parts.pop().slice(0, 4).toUpperCase() : 'FILE';
        },

        slug(s) {
            return String(s || 'rapat')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '')
                .slice(0, 60);
        },

        domainOf(url) {
            try { return new URL(url).hostname.replace(/^www\./, ''); }
            catch (e) { return url; }
        },

        escapeHtml(s) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, c => map[c]);
        },

        nowLabel() {
            const d = new Date();
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        },

        voteLabel(id) {
            const map = { setuju: 'Setuju', tolak: 'Tolak', abstain: 'Abstain' };
            return map[id] || id;
        }
    }));
});
</script>
