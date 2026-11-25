import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';
import test from 'node:test';

const root = fileURLToPath(new URL('../', import.meta.url));
const read = path => readFileSync(`${root}${path}`, 'utf8');
const view = path => read(`resources/views/${path}.blade.php`);
const php = code => execFileSync('php', ['-r', code], { cwd: root, encoding: 'utf8' });

test('landing has three KPIs and a balanced responsive grid', () => {
    const home = view('public/home');
    assert.ok(!home.includes('TAHUN PERIODE'));
    assert.match(home, /grid-cols-1[^"\n]*sm:grid-cols-3/);
    assert.ok(!home.includes('$i % 2'));
});

test('landing KPI spacing is compact and spans the content width', () => {
    const home = view('public/home');
    const stats = home.match(/<dl class="([^"]+)"/)[1];
    assert.match(stats, /\bmt-12\b/);
    assert.match(stats, /\bw-full\b/);
    assert.doesNotMatch(stats, /max-w-\[760px\]/);
    assert.doesNotMatch(stats, /mt-\[122px\]/);
});

test('public menu and chat close each other on opening', () => {
    const header = view('components/senat/header');
    const chat = view('components/senat/chat-widget');
    const headerRoot = header.match(/<header\s[^>]+>/)[0];
    const chatRoot = chat.match(/<div x-data="senatChatWidget\(\)"[^>]+>/)[0];
    const handler = (markup, name) => {
        const match = markup.match(new RegExp(`${name}="([^"]+)"`));
        assert.ok(match, `Missing ${name}`);
        return match[1];
    };
    const menu = { open: false };
    const context = { ...menu };
    vm.createContext(context);
    vm.runInContext(chat.match(/<script>([\s\S]*?)<\/script>/)[1], context);
    const widget = context.senatChatWidget();
    widget.$nextTick = () => {};
    const menuClose = handler(headerRoot, '@public-chat-open.window');
    const chatClose = handler(chatRoot, '@public-menu-open.window');
    widget.$dispatch = name => {
        assert.equal(name, 'public-chat-open');
        vm.runInNewContext(menuClose, menu);
    };
    const openMenu = handler(header.match(/<button type="button" @click="[^"]+" :aria-expanded="open.toString\(\)"/)[0], '@click');
    menu.$dispatch = name => {
        assert.equal(name, 'public-menu-open');
        vm.runInNewContext(chatClose, widget);
    };
    widget.toggleChat();
    assert.equal(widget.isOpen, true);
    vm.runInNewContext(openMenu, menu);
    assert.equal(menu.open, true);
    assert.equal(widget.isOpen, false);
    widget.toggleChat();
    assert.equal(menu.open, false);
    assert.equal(widget.isOpen, true);
    widget.toggleChat();
    assert.equal(widget.isOpen, false);
});

test('login helpers remain legible and stack on narrow screens', () => {
    const login = view('portal/login');
    assert.match(login, /flex-col items-start[^"\n]*sm:flex-row sm:items-center/);
    assert.match(login, /class="text-xs leading-relaxed text-slate-600">\s*Sesi aktif/);
    assert.match(login, /class="mt-2.5 text-center text-xs leading-relaxed text-slate-600"/);
    assert.ok(login.includes('@click="showResetNotice = true"'));
    assert.ok(login.includes("route('portal.login.google')"));
});

test('member and secretariat bottom navigation use their own routes', () => {
    const source = view('layouts/portal').match(/@php([\s\S]*?)@endphp/)[1];
    const result = JSON.parse(php(`foreach (['anggota', 'sekretariat'] as $sidebar) { ${source} $result[$sidebar] = array_column($bottomNav, 'route', 'label'); } echo json_encode($result);`));
    for (const [role, prefix] of [['anggota', 'portal'], ['sekretariat', 'portal.secretariat']]) {
        assert.equal(result[role].Voting, `${prefix}.voting`);
        assert.equal(result[role].Aspirasi, `${prefix}.aspirations`);
    }
});

test('inner attendance exports are removed and header exports retained', () => {
    for (const tab of ['kuorum-kehadiran', 'presensi-qr']) {
        assert.doesNotMatch(view(`livewire/admin/tabs/${tab}`), /wire:click="export(?:Excel|Csv)"/);
    }
    const header = view('livewire/admin-preview');
    assert.match(header, /wire:click="exportExcel"/);
    assert.match(header, /wire:click="exportCsv"/);
});

test('portal does not advertise a nonexistent notification feature', () => {
    assert.doesNotMatch(view('layouts/portal'), /aria-label="Notifikasi"|name="bell"/);
});

test('documents keep live search without the inert Cari button', () => {
    const documents = view('portal/documents');
    assert.doesNotMatch(documents, />Cari<\/x-senat.button>/);
    assert.ok(documents.includes('x-model="q"'));
    assert.match(documents, /x-show="[^"\n]*includes\(q.toLowerCase\(\)\)/);
});

test('placeholder action explicitly reports unavailable instead of success', () => {
    const output = php('require "app/Livewire/Admin/Traits/HasPimpinanAndStruktur.php"; $component = new class { use \\App\\Livewire\\Admin\\Traits\\HasPimpinanAndStruktur; public string $flashMessage = ""; }; $component->saveAction("Upload Arsip"); echo $component->flashMessage;');
    assert.match(output, /belum tersedia/i);
    assert.doesNotMatch(output, /berhasil/i);
});

test('all six placeholder controls disclose unavailability before clicking', () => {
    let count = 0;
    for (const tab of ['profil-struktur', 'jadwal-agenda', 'kuorum-kehadiran', 'evoting', 'repositori-arsip']) {
        const buttons = view(`livewire/admin/tabs/${tab}`).match(/<(?:button|x-senat.button)\b[^>]*wire:click="saveAction\([^]*?<\/(?:button|x-senat.button)>/g) ?? [];
        for (const button of buttons) {
            count++;
            assert.match(button, /Belum tersedia/);
        }
    }
    assert.equal(count, 6);
});
