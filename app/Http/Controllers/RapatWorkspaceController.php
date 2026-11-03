<?php

namespace App\Http\Controllers;

use App\Contracts\RapatWorkspaceRepositoryInterface;
use Illuminate\Http\Request;
use Throwable;

/**
 * Proxy workspace rapat untuk portal anggota.
 *
 * Browser memanggil endpoint ini (same-origin), lalu controller meneruskan
 * ke Backend-Senat-Polines. Dengan begitu tidak perlu CORS dan tidak ada
 * URL backend yang bocor ke klien.
 */
class RapatWorkspaceController extends Controller
{
    public function __construct(private RapatWorkspaceRepositoryInterface $workspace) {}

    private function idUser(): int
    {
        return (int) (auth()->id() ?: session('auth_user_id', (int) env('PORTAL_DEMO_USER_ID', 2)));
    }

    /** Muat catatan + lampiran + berbagi untuk satu rapat. */
    public function show(int $rapat)
    {
        $idUser = $this->idUser();
        $catatan = $this->workspace->catatan($rapat, $idUser);

        return response()->json([
            'ok' => true,
            'id_user' => $idUser,
            'catatan' => $catatan,
            'lampiran' => $this->workspace->lampiran($rapat),
            'berbagi' => $this->workspace->berbagi($rapat),
        ]);
    }

    public function simpanCatatan(Request $request, int $rapat)
    {
        $data = $request->validate([
            'isi' => ['nullable', 'string', 'max:1000000'],
        ]);

        try {
            $this->workspace->simpanCatatan($rapat, $this->idUser(), (string) ($data['isi'] ?? ''));
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 502);
        }

        return response()->json(['ok' => true, 'disimpan_pada' => now()->toIso8601String()]);
    }

    public function unggahLampiran(Request $request, int $rapat)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        try {
            $row = $this->workspace->unggahLampiran($rapat, $this->idUser(), $request->file('file'));
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 502);
        }

        return response()->json(['ok' => true, 'lampiran' => $row], 201);
    }

    public function hapusLampiran(int $rapat, int $lampiran)
    {
        if (! $this->workspace->hapusLampiran($rapat, $lampiran)) {
            return response()->json(['ok' => false, 'message' => 'Lampiran gagal dihapus.'], 502);
        }

        return response()->json(['ok' => true]);
    }

    public function bagikan(Request $request, int $rapat)
    {
        $data = $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
            'url' => ['nullable', 'string', 'max:2048'],
            'nama' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $request->hasFile('file') && empty($data['url'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Pilih berkas atau isi tautan terlebih dahulu.',
            ], 422);
        }

        try {
            $row = $this->workspace->bagikan($rapat, $this->idUser(), [
                'file' => $request->file('file'),
                'url' => $data['url'] ?? null,
                'nama' => $data['nama'] ?? null,
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 502);
        }

        return response()->json(['ok' => true, 'berbagi' => $row], 201);
    }

    public function hapusBerbagi(int $rapat, int $berbagi)
    {
        if (! $this->workspace->hapusBerbagi($rapat, $berbagi)) {
            return response()->json(['ok' => false, 'message' => 'Item gagal dihapus.'], 502);
        }

        return response()->json(['ok' => true]);
    }
}
