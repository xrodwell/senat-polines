<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Penjaga regresi: Livewire menolak morph DOM bila markup tidak seimbang
 * ("Livewire: missing closing tags found"), akibatnya seluruh tombol mati
 * setelah satu update. Tes ini gagal lebih awal kalau ada tag tak tertutup.
 */
class LivewireMarkupIntegrityTest extends TestCase
{
    private const TAGS = ['div', 'section', 'aside', 'main', 'header', 'nav', 'span', 'button', 'table', 'ul', 'li', 'form', 'p'];

    public function test_setiap_tab_menghasilkan_markup_seimbang(): void
    {
        foreach (AdminPreview::MENU_KEYS as $label) {
            $html = Livewire::withQueryParams(['tab' => $label])
                ->test(AdminPreview::class)
                ->html();

            foreach (self::TAGS as $tag) {
                $open = preg_match_all("/<{$tag}\b/i", $html);
                $close = preg_match_all("#</{$tag}>#i", $html);

                $this->assertSame(
                    $open,
                    $close,
                    "Tag <{$tag}> tidak seimbang pada tab '{$label}' (buka {$open}, tutup {$close}) — Livewire akan memblokir morph dan semua tombol mati."
                );
            }
        }
    }

    public function test_tidak_ada_direktif_livewire_yang_tidak_didukung(): void
    {
        $view = file_get_contents(resource_path('views/livewire/admin-preview.blade.php'));

        foreach (['wire:model.defer', 'wire:click.away', 'wire:model.lazy'] as $unsupported) {
            $this->assertStringNotContainsString(
                $unsupported,
                $view,
                "Direktif {$unsupported} tidak didukung Livewire 4 dan mematikan handler."
            );
        }
    }
}
