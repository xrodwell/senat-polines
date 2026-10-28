<?php

namespace Tests\Feature;

use App\Livewire\AdminPreview;
use App\Contracts\PostRepositoryInterface;
use App\Repositories\Api\ApiPostRepository;
use App\Repositories\Mock\JsonPostRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class NewsManagementTest extends TestCase
{
    public function test_admin_can_view_news_screen(): void
    {
        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->assertSet('active', 'Manajemen Berita & Siaran Pers')
            ->assertSee('Publikasi Warta & Berita Senat', false)
            ->assertSee('Antrean Unggah Media', false)
            ->assertSee('Tulis Berita', false)
            ->assertSee('Pilih Berkas', false);
    }

    public function test_admin_can_create_new_news(): void
    {
        $uniqueTitle = 'Uji Coba Berita Baru ' . uniqid();

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('openCreatePostModal')
            ->assertSet('showPostModal', true)
            ->set('postTitle', $uniqueTitle)
            ->set('postCategory', 'Warta Senat')
            ->set('postBody', 'Isi berita lengkap untuk pengujian publikasi warta senat polines.')
            ->call('savePost', true)
            ->assertSet('showPostModal', false)
            ->assertSee($uniqueTitle, false);
    }

    public function test_admin_can_save_news_as_a_draft(): void
    {
        $repo = Mockery::mock(PostRepositoryInterface::class);
        $repo->shouldReceive('all')->andReturn([]);
        $repo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $payload): bool => $payload['status'] === 'Draf'))
            ->andReturn(['id' => 1]);
        $this->app->instance(PostRepositoryInterface::class, $repo);

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('openCreatePostModal')
            ->set('postTitle', 'Berita Draf Pengujian')
            ->set('postBody', 'Isi berita draf untuk pengujian.')
            ->call('savePost', false)
            ->assertSet('showPostModal', false);
    }

    public function test_admin_can_edit_existing_news(): void
    {
        $repo = app(PostRepositoryInterface::class);
        $posts = $repo->all();
        $this->assertNotEmpty($posts);
        $targetId = $posts[0]['id'];

        $updatedTitle = 'Judul Hasil Edit ' . uniqid();

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('openEditPostModal', $targetId)
            ->assertSet('showPostModal', true)
            ->assertSet('editingPostId', (int)$targetId)
            ->set('postTitle', $updatedTitle)
            ->call('savePost', true)
            ->assertSet('showPostModal', false)
            ->assertSee($updatedTitle, false);
    }

    public function test_saving_an_existing_draft_keeps_it_as_a_draft(): void
    {
        $draft = [
            'id' => 45,
            'title' => 'Draf yang sudah ada',
            'category' => 'Warta Senat',
            'date' => '2026-10-28',
            'status' => 'Draf',
            'excerpt' => '',
            'thumbnail' => '',
            'author' => 'Sekretariat Senat Polines',
            'body' => ['Isi draf berita.'],
        ];
        $repo = Mockery::mock(PostRepositoryInterface::class);
        $repo->shouldReceive('all')->andReturn([$draft]);
        $repo->shouldReceive('update')
            ->once()
            ->with(45, Mockery::on(fn (array $payload): bool => $payload['status'] === 'Draf'))
            ->andReturn(true);
        $this->app->instance(PostRepositoryInterface::class, $repo);

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('openEditPostModal', 45)
            ->set('postTitle', 'Draf yang sudah diperbarui')
            ->call('savePost', true)
            ->assertSet('showPostModal', false);
    }

    public function test_api_update_succeeds_when_the_post_exists_only_in_the_backend(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $fallback = Mockery::mock(JsonPostRepository::class);
        $fallback->shouldReceive('update')->once()->andReturn(false);

        $updated = (new ApiPostRepository($fallback))->update(123, ['title' => 'Berita backend']);

        $this->assertTrue($updated);
    }

    public function test_admin_can_toggle_publish_news(): void
    {
        $repo = app(PostRepositoryInterface::class);
        $posts = $repo->all();
        $this->assertNotEmpty($posts);
        $targetId = $posts[0]['id'];

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('togglePublishPost', $targetId);

        $refreshed = collect($repo->all())->firstWhere('id', (int)$targetId);
        $this->assertNotNull($refreshed);
    }

    public function test_admin_sees_an_error_when_publishing_fails(): void
    {
        $repo = Mockery::mock(PostRepositoryInterface::class);
        $repo->shouldReceive('all')->andReturn([[
            'id' => 46,
            'title' => 'Berita untuk gagal dipublikasikan',
            'category' => 'Warta Senat',
            'date' => '2026-10-28',
            'status' => 'Draf',
            'excerpt' => 'Ringkasan berita.',
            'thumbnail' => '',
            'author' => 'Sekretariat Senat Polines',
        ]]);
        $repo->shouldReceive('update')->once()->andReturn(false);
        $this->app->instance(PostRepositoryInterface::class, $repo);

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('togglePublishPost', 46)
            ->assertSet('flashMessage', 'Gagal memperbarui status berita. Silakan coba kembali.');
    }

    public function test_admin_can_pick_and_save_media_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('siaran-pers-senat.pdf', 512, 'application/pdf');

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->set('mediaFile', $file)
            ->call('saveMediaFile')
            ->assertSet('mediaFile', null)
            ->assertSee('berhasil disimpan', false);
    }

    public function test_media_upload_rejects_unsupported_types_and_files_over_10mb(): void
    {
        Storage::fake('public');

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->set('mediaFile', UploadedFile::fake()->create('script.php', 20, 'application/x-php'))
            ->call('saveMediaFile')
            ->assertHasErrors(['mediaFile' => 'mimes']);

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->set('mediaFile', UploadedFile::fake()->create('dokumentasi.pdf', 10241, 'application/pdf'))
            ->call('saveMediaFile')
            ->assertHasErrors(['mediaFile' => 'max']);
    }
}
