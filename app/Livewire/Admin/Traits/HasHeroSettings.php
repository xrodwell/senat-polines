<?php

namespace App\Livewire\Admin\Traits;

use App\Support\HeroApi;
use App\Support\HeroSettings;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Throwable;

trait HasHeroSettings
{
    public array $hero = HeroSettings::DEFAULTS;
    #[Locked]
    public bool $heroLoaded = false;
    #[Locked]
    public bool $heroAttempted = false;
    public bool $heroSaving = false;
    public string $heroError = '';
    public string $heroMessage = '';

    public function reloadHeroSettings(): void
    {
        $this->heroAttempted = true;
        $this->heroError = '';
        $this->heroMessage = '';
        $this->resetValidation('hero');
        try {
            $this->hero = HeroApi::read();
            $this->heroLoaded = true;
        } catch (Throwable) {
            $this->heroLoaded = false;
            $this->heroError = 'Pengaturan hero gagal dimuat. Salin draft sebelum memuat ulang.';
        }
    }

    public function saveHeroSettings(): void
    {
        $this->heroMessage = '';
        $this->heroError = '';
        $this->resetValidation();
        if (!$this->heroLoaded) {
            $this->heroError = 'Muat pengaturan hero dengan berhasil sebelum menyimpan.';
            return;
        }
        try {
            $data = array_intersect_key(HeroSettings::validate($this->hero), array_flip(['eyebrow', 'title', 'description', 'background_image']));
        } catch (ValidationException $error) {
            foreach ($error->errors() as $field => $messages) {
                $this->addError('hero.'.$field, $messages[0]);
            }
            return;
        }
        if (!session('auth_token')) {
            $this->heroError = 'Sesi berakhir. Silakan login ulang.';
            return;
        }
        $this->heroSaving = true;
        try {
            $response = Http::acceptJson()->withToken(session('auth_token'))->timeout(5)->connectTimeout(5)->put(HeroApi::url(), $data);
            if ($response->status() === 422) {
                foreach (array_keys(HeroSettings::DEFAULTS) as $field) {
                    $messages = $response->json('errors.'.$field);
                    if (is_array($messages) && isset($messages[0]) && is_string($messages[0])) {
                        $this->addError('hero.'.$field, $messages[0]);
                    }
                }
                $this->heroError = 'Periksa input pengaturan hero.';
            } elseif (!$response->successful()) {
                $this->heroError = match ($response->status()) {
                    401 => 'Sesi berakhir. Silakan login ulang.',
                    403 => 'Hanya admin yang dapat menyimpan pengaturan hero.',
                    default => 'Pengaturan hero gagal disimpan. Draft tetap tersedia.',
                };
                Log::warning('hero_write_failed', ['category' => 'http', 'status' => $response->status()]);
            } else {
                $saved = HeroApi::payload($response->json());
                $this->hero = $saved;
                $this->heroMessage = 'Pengaturan hero berhasil disimpan.';
            }
        } catch (ConnectionException) {
            $this->heroError = 'Hasil simpan tidak pasti. Jangan kirim ulang otomatis; salin draft dan muat ulang untuk memeriksa.';
            Log::warning('hero_write_failed', ['category' => 'timeout']);
        } catch (Throwable) {
            $this->heroError = 'Respons simpan tidak valid; hasil tidak pasti. Salin draft sebelum memuat ulang.';
            Log::warning('hero_write_failed', ['category' => 'invalid']);
        } finally {
            $this->heroSaving = false;
        }
    }
}
