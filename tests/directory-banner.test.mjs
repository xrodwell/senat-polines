import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';

const source = readFileSync(new URL('../resources/views/public/directory.blade.php', import.meta.url), 'utf8');
const hero = source.match(/{{-- Hero --}}([\s\S]*?)<\/section>/)[1];
const classes = markup => markup.match(/class="([^"]+)"/)[1].split(/\s+/);

test('banner information panel is a padded grid child on mobile and desktop, not a viewport overlay', () => {
    const stack = [];
    let panel;
    for (const match of hero.matchAll(/<div\b[^>]*>|<\/div>/g)) {
        if (match[0] === '</div>') {
            stack.pop();
            continue;
        }
        const current = classes(match[0]);
        if (current.includes('lg:border-l')) panel = { current, parent: stack.at(-1) };
        stack.push(current);
    }
    assert.ok(panel?.parent?.includes('grid'), 'dark information panel must be a direct grid child');
    for (const utility of ['grid-cols-1', 'lg:grid-cols-[minmax(0,1fr)_minmax(0,330px)]']) {
        assert.ok(panel.parent.includes(utility), `responsive grid needs ${utility}`);
    }
    for (const utility of ['min-w-0', 'lg:border-l', 'border-white/20', 'lg:pl-10']) {
        assert.ok(panel.current.includes(utility), `information panel needs ${utility}`);
    }
    assert.ok(!panel.current.includes('hidden'), 'information panel must remain visible on mobile');
    assert.doesNotMatch(hero, /absolute|w-\[32%\]|lg:w-\[330px\]/, 'background and width must follow grid geometry');
});

test('banner content can shrink and wrap long dynamic values without clipping', () => {
    const grid = hero.match(/<div[^>]*class="[^"]*\bgrid\b[^>]*>/)?.[0];
    assert.ok(grid, 'banner needs a grid');
    assert.ok(classes(grid).includes('[overflow-wrap:anywhere]'), 'long unbroken values must wrap throughout the banner');
    const titleColumn = hero.match(/<div[^>]*>\s*<p[^>]*>Direktori Anggota Senat/)[0];
    assert.ok(classes(titleColumn).includes('min-w-0'), 'title column must shrink below its content width');
    assert.match(source, /<section class="relative overflow-hidden bg-senat-hero">/);
    assert.match(hero, /\{\{ \$period \}\}/);
});

test('Alpine banner bindings retain selected committee content and handle an empty directory', () => {
    const bindings = [...hero.matchAll(/x-text="([^"]+)"/g)].map(match => match[1]);
    assert.equal(bindings.length, 3);
    const selected = { title: 'Komisi B · Akademik', focus: 'Fokus asli', badge: '12 Anggota Komisi' };
    for (const [index, property] of ['title', 'focus', 'badge'].entries()) {
        assert.equal(vm.runInNewContext(bindings[index], { panels: [{}, selected], active: 1 }), selected[property]);
        const fallback = vm.runInNewContext(bindings[index], { panels: [], active: 0, $el: { textContent: '2025–2029' } });
        assert.equal(fallback, ['Direktori Komisi', '2025–2029', '0 Anggota Komisi'][index]);
    }
    assert.match(source, /active: \{\{ \$initialActive \}\}/);
    assert.match(source, /panels: @js\(\$heroPanels\)/);
    assert.match(source, /@click="active = \{\{ \$i \}\}"/);
});
