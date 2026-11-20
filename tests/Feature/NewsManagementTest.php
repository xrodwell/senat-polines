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

    public function test_news_form_has_a_viewport_bounded_modal_and_media_picker(): void
    {
        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('openCreatePostModal')
            ->assertSee('max-h-[calc(100dvh-1rem)]', false)
            ->assertSee('Galeri Gambar Berita', false)
            ->call('openMediaPicker')
            ->assertSee('Pilih Media untuk Berita', false);
    }

    public function test_media_upload_queue_accepts_drag_and_drop_and_office_documents(): void
    {
        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->assertSee('@drop.prevent.stop', false)
            ->assertSee("\$wire.upload('mediaFile'", false)
            ->assertSee('.docx', false)
            ->assertSee('.xlsx', false)
            ->assertSee('.pptx', false);
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

    public function test_admin_can_save_multiple_images_and_attachments_with_news(): void
    {
        $imageOne = 'https://example.test/news/meeting-one.jpg';
        $imageTwo = 'https://example.test/news/meeting-two.jpg';
        $attachment = ['name' => 'Risalah sidang.pdf', 'url' => 'https://example.test/files/minutes.pdf'];
        $repo = Mockery::mock(PostRepositoryInterface::class);
        $repo->shouldReceive('all')->andReturn([]);
        $repo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $payload): bool => $payload['thumbnail'] === $imageOne
                && $payload['images'] === [$imageOne, $imageTwo]
                && $payload['attachments'] === [$attachment]))
            ->andReturn(['id' => 1]);
        $this->app->instance(PostRepositoryInterface::class, $repo);

        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('openCreatePostModal')
            ->set('postTitle', 'Berita Dengan Dokumentasi Lengkap')
            ->set('postBody', 'Isi berita untuk menguji galeri dan lampiran.')
            ->set('postThumbnail', $imageOne)
            ->set('postImages', [$imageOne, $imageTwo])
            ->set('postAttachments', [$attachment])
            ->call('savePost', true)
            ->assertSet('showPostModal', false);
    }

    public function test_admin_can_add_an_image_and_attachment_from_urls(): void
    {
        Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class)
            ->call('openCreatePostModal')
            ->set('postImageUrl', 'https://example.test/images/press.jpg')
            ->call('addPostImageFromUrl')
            ->assertSet('postImages', ['https://example.test/images/press.jpg'])
            ->set('postAttachmentName', 'Rilis Pers')
            ->set('postAttachmentUrl', 'https://example.test/files/press-release.pdf')
            ->call('addPostAttachmentFromUrl')
            ->assertSet('postAttachments', [[
                'name' => 'Rilis Pers',
                'url' => 'https://example.test/files/press-release.pdf',
            ]]);
    }

    public function test_saved_media_preview_can_be_used_for_a_news_gallery(): void
    {
        $component = Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class);
        $originalMedia = $component->instance()->getMediaList();
        $media = [
            [
                'id' => 'gallery-image-one',
                'name' => 'meeting-one.jpg',
                'original_name' => 'Rapat pertama.jpg',
                'url' => 'https://example.test/meeting-one.jpg',
                'type' => 'image',
            ],
            [
                'id' => 'gallery-image-two',
                'name' => 'meeting-two.jpg',
                'original_name' => 'Rapat kedua.jpg',
                'url' => 'https://example.test/meeting-two.jpg',
                'type' => 'image',
            ],
        ];

        try {
            $component->call('saveMediaList', $media)
                ->assertSee('Pratinjau berkas', false)
                ->call('openMediaPreview', 'gallery-image-one')
                ->assertSee('Gunakan di Berita', false)
                ->call('addMediaToPost', 'gallery-image-one')
                ->assertSet('postImages', ['https://example.test/meeting-one.jpg'])
                ->call('openMediaPicker')
                ->call('toggleMediaInPost', 'gallery-image-two')
                ->assertSet('postImages', [
                    'https://example.test/meeting-one.jpg',
                    'https://example.test/meeting-two.jpg',
                ]);
        } finally {
            $component->call('saveMediaList', $originalMedia);
        }
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

    public function test_api_repository_sends_gallery_and_attachments_to_backend(): void
    {
        Http::fake(['*' => Http::response(['id_berita' => 123], 201)]);

        $fallback = Mockery::mock(JsonPostRepository::class);
        $fallback->shouldReceive('create')
            ->once()
            ->andReturn(['id' => 123]);
        $repo = new ApiPostRepository($fallback);

        $repo->create([
            'title' => 'Berita dengan galeri',
            'body_text' => 'Isi berita',
            'thumbnail' => 'https://example.test/one.jpg',
            'images' => ['https://example.test/one.jpg', 'https://example.test/two.jpg'],
            'attachments' => [['name' => 'Risalah', 'url' => 'https://example.test/risalah.pdf']],
        ]);

        Http::assertSent(fn ($request) => $request->url() === $repo->resolveBackendUrl() . '/berita'
            && $request['galeri'] === ['https://example.test/one.jpg', 'https://example.test/two.jpg']
            && $request['lampiran'] === [['name' => 'Risalah', 'url' => 'https://example.test/risalah.pdf']]);
    }

    public function test_api_repository_updates_gallery_and_attachments_in_backend(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $fallback = Mockery::mock(JsonPostRepository::class);
        $fallback->shouldReceive('update')->once()->andReturn(false);
        $repo = new ApiPostRepository($fallback);

        $updated = $repo->update(123, [
            'title' => 'Berita dengan galeri yang diperbarui',
            'thumbnail' => 'https://example.test/one.jpg',
            'images' => ['https://example.test/one.jpg', 'https://example.test/two.jpg'],
            'attachments' => [['name' => 'Risalah', 'url' => 'https://example.test/risalah.pdf']],
        ]);

        $this->assertTrue($updated);
        Http::assertSent(fn ($request) => $request->method() === 'PUT'
            && $request->url() === $repo->resolveBackendUrl() . '/berita/123'
            && $request['galeri'] === ['https://example.test/one.jpg', 'https://example.test/two.jpg']
            && $request['lampiran'] === [['name' => 'Risalah', 'url' => 'https://example.test/risalah.pdf']]);
    }

    public function test_api_repository_keeps_gallery_and_attachments_when_reading_backend_news(): void
    {
        Http::fake(['*' => Http::response(['data' => [[
            'id_berita' => 123,
            'judul' => 'Berita Galeri',
            'isi' => 'Isi berita',
            'gambar' => 'https://example.test/one.jpg',
            'tanggal_publish' => '2026-10-28 10:00:00',
        ]]], 200)]);

        $fallback = Mockery::mock(JsonPostRepository::class);
        $fallback->shouldReceive('all')->andReturn([[
            'id' => 123,
            'title' => 'Berita Galeri',
            'slug' => 'berita-galeri',
            'date' => '2026-10-28',
            'thumbnail' => 'https://example.test/one.jpg',
            'images' => ['https://example.test/one.jpg', 'https://example.test/two.jpg'],
            'attachments' => [['name' => 'Risalah', 'url' => 'https://example.test/risalah.pdf']],
        ]]);

        $post = (new ApiPostRepository($fallback))->all()[0];

        $this->assertSame(
            ['https://example.test/one.jpg', 'https://example.test/two.jpg'],
            $post['images']
        );
        $this->assertSame(
            [['name' => 'Risalah', 'url' => 'https://example.test/risalah.pdf']],
            $post['attachments']
        );
    }

    public function test_api_repository_merges_backend_and_local_media_without_duplicates(): void
    {
        Http::fake(['*' => Http::response(['data' => [[
            'id_berita' => 123,
            'judul' => 'Berita Galeri',
            'isi' => 'Isi berita',
            'gambar' => 'https://example.test/one.jpg',
            'galeri' => [
                'https://example.test/one.jpg',
                'https://example.test/backend.jpg',
            ],
            'lampiran' => [
                ['name' => 'Risalah terbaru', 'url' => 'https://example.test/risalah.pdf'],
            ],
            'tanggal_publish' => '2026-10-28 10:00:00',
        ]]], 200)]);

        $fallback = Mockery::mock(JsonPostRepository::class);
        $fallback->shouldReceive('all')->andReturn([[
            'id' => 123,
            'title' => 'Berita Galeri',
            'slug' => 'berita-galeri',
            'date' => '2026-10-28',
            'thumbnail' => 'https://example.test/one.jpg',
            'images' => ['https://example.test/one.jpg', 'https://example.test/local.jpg'],
            'attachments' => [
                ['name' => 'Risalah lama', 'url' => 'https://example.test/risalah.pdf'],
                ['name' => 'Lampiran lokal', 'url' => 'https://example.test/local.pdf'],
            ],
        ]]);

        $post = (new ApiPostRepository($fallback))->all()[0];

        $this->assertSame(
            [
                'https://example.test/one.jpg',
                'https://example.test/backend.jpg',
                'https://example.test/local.jpg',
            ],
            $post['images']
        );
        $this->assertSame([
            ['name' => 'Risalah terbaru', 'url' => 'https://example.test/risalah.pdf'],
            ['name' => 'Lampiran lokal', 'url' => 'https://example.test/local.pdf'],
        ], $post['attachments']);
    }

    public function test_api_repository_orders_newest_news_first(): void
    {
        Http::fake(['*' => Http::response(['data' => [
            [
                'id_berita' => 99,
                'judul' => 'Berita Lama pada Hari yang Sama',
                'isi' => 'Isi berita lama',
                'tanggal_publish' => '2026-10-28 09:00:00',
            ],
            [
                'id_berita' => 1,
                'judul' => 'Berita Terbaru pada Hari yang Sama',
                'isi' => 'Isi berita terbaru',
                'tanggal_publish' => '2026-10-28 10:00:00',
            ],
        ]], 200)]);

        $fallback = Mockery::mock(JsonPostRepository::class);
        $fallback->shouldReceive('all')->andReturn([]);

        $posts = (new ApiPostRepository($fallback))->all();

        $this->assertSame(
            ['Berita Terbaru pada Hari yang Sama', 'Berita Lama pada Hari yang Sama'],
            array_column($posts, 'title')
        );
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
        $component = Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class);
        $originalMedia = $component->instance()->getMediaList();

        try {
            $component->set('mediaFile', $file)
                ->call('saveMediaFile')
                ->assertSet('mediaFile', null)
                ->assertSee('berhasil disimpan', false);
        } finally {
            $component->call('saveMediaList', $originalMedia);
        }
    }

    public function test_admin_can_upload_office_documents_for_news_attachments(): void
    {
        Storage::fake('public');

        $component = Livewire::withQueryParams(['tab' => 'Manajemen Berita & Siaran Pers'])
            ->test(AdminPreview::class);
        $originalMedia = $component->instance()->getMediaList();

        try {
            $component->set('mediaFile', UploadedFile::fake()->create(
                'risalah-sidang.docx',
                512,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ))
                ->call('saveMediaFile')
                ->assertSet('mediaFile', null);

            $uploadedMedia = $component->instance()->getMediaList()[0];
            $this->assertSame('risalah-sidang.docx', $uploadedMedia['original_name']);
            $this->assertStringEndsWith('.docx', $uploadedMedia['name']);
            $this->assertSame('document', $uploadedMedia['type']);
        } finally {
            $component->call('saveMediaList', $originalMedia);
        }
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
