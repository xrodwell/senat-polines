import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import test from 'node:test';

const root = fileURLToPath(new URL('../', import.meta.url));
const renderCard = committee => execFileSync('php', ['-r', String.raw`
require 'vendor/autoload.php';
$container = new \Illuminate\Container\Container;
\Illuminate\Container\Container::setInstance($container);
$routes = new \Illuminate\Routing\RouteCollection;
$routes->add((new \Illuminate\Routing\Route('GET', '/direktori-komisi', fn () => null))->name('public.directory'));
$container->instance('url', new \Illuminate\Routing\UrlGenerator($routes, \Illuminate\Http\Request::create('https://senat.test')));
$files = new \Illuminate\Filesystem\Filesystem;
$compiler = new \Illuminate\View\Compilers\BladeCompiler($files, sys_get_temp_dir());
$__env = new \Illuminate\View\Factory(new \Illuminate\View\Engines\EngineResolver, new \Illuminate\View\FileViewFinder($files, []), new \Illuminate\Events\Dispatcher($container));
$source = file_get_contents('resources/views/public/home.blade.php');
$start = strpos($source, '@forelse ($committees as $committee)');
$end = strpos($source, '@endforelse', $start) + strlen('@endforelse');
$committees = [json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR)];
ob_start();
eval('?>'.$compiler->compileString(substr($source, $start, $end - $start)));
echo ob_get_clean();
`, JSON.stringify(committee)], { cwd: root, encoding: 'utf8' });

const text = markup => markup.replace(/<[^>]*>/g, '').trim();
const heading = html => [...html.matchAll(/<h3\b[^>]*>([\s\S]*?)<\/h3>/g)].map(match => text(match[1]));
const assertCardDetails = (html, code, badge, eyebrow, focus) => {
    assert.equal((html.match(/<a\b/g) ?? []).length, 1, 'card remains a single link');
    assert.match(html, new RegExp(`href="https://senat\\.test/direktori-komisi\\?komisi=${code}"`), 'committee link targets matching directory tab');
    assert.equal(text(html.match(/<span\b[^>]*>([\s\S]*?)<\/span>/)[1]), badge, 'badge is retained');
    const paragraphs = [...html.matchAll(/<p\b[^>]*>([\s\S]*?)<\/p>/g)].map(match => text(match[1]));
    assert.deepEqual(paragraphs, [eyebrow, focus], 'eyebrow and original focus remain intact without invented content');
};

for (const letter of ['A', 'B', 'C']) {
    test(`generic Komisi ${letter} card omits its duplicate letter heading but retains badge, eyebrow, focus and link`, () => {
        const focus = `Fokus asli komisi ${letter}.`;
        const html = renderCard({ code: `KOM-${letter}`, name: `Komisi ${letter}`, focus });
        assertCardDetails(html, `KOM-${letter}`, letter, `KOMISI ${letter}`, focus);
        assert.deepEqual(heading(html), [], 'generic committee names must not render a redundant heading');
    });
}

for (const [name, expected] of [
    ['Komisi Akademik dan Pendidikan', 'Akademik dan Pendidikan'],
    ['Komisi A Bidang Akademik', 'A Bidang Akademik'],
    ['Penelitian dan Pengabdian', 'Penelitian dan Pengabdian'],
]) {
    test(`descriptive committee name "${name}" retains its meaningful heading and original card details`, () => {
        const focus = 'Fokus deskriptif tetap tersedia.';
        const html = renderCard({ code: 'KOM-A', name, focus });
        assertCardDetails(html, 'KOM-A', 'A', 'KOMISI A', focus);
        assert.deepEqual(heading(html), [expected]);
    });
}

test('BP retains its special heading, eyebrow, badge styling, focus and existing link', () => {
    const focus = 'Koordinasi Badan Pekerja.';
    const html = renderCard({ code: 'BP', name: 'Badan Pekerja', focus });
    assertCardDetails(html, 'BP', 'BP', 'BADAN PEKERJA', focus);
    assert.deepEqual(heading(html), ['BP (Badan Pekerja)']);
    assert.match(html, /border border-\[#d6c7a6\] bg-\[#ecdfc3\] text-\[#302d45\]/);
});

test('generic committee with empty focus does not invent replacement content', () => {
    const html = renderCard({ code: 'KOM-A', name: 'Komisi A', focus: '' });
    assertCardDetails(html, 'KOM-A', 'A', 'KOMISI A', '');
    assert.deepEqual(heading(html), []);
});
