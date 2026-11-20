<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * Workspace rapat: catatan anggota, lampiran notulensi, dan berbagi dokumen/tautan.
 */
interface RapatWorkspaceRepositoryInterface
{
    /** Catatan milik satu anggota pada satu rapat (null bila belum ada). */
    public function catatan(int $idRapat, int $idUser): ?array;

    /** Simpan / perbarui catatan anggota. */
    public function simpanCatatan(int $idRapat, int $idUser, string $isi): array;

    /** Daftar lampiran notulensi rapat. */
    public function lampiran(int $idRapat): array;

    /** Unggah berkas sebagai lampiran notulensi. */
    public function unggahLampiran(int $idRapat, int $idUser, UploadedFile $file): array;

    public function hapusLampiran(int $idRapat, int $idLampiran): bool;

    /** Daftar dokumen & tautan yang dibagikan pada rapat. */
    public function berbagi(int $idRapat): array;

    /**
     * Bagikan berkas atau tautan.
     *
     * @param  array{nama?:string,url?:string,file?:UploadedFile}  $payload
     */
    public function bagikan(int $idRapat, int $idUser, array $payload): array;

    public function hapusBerbagi(int $idRapat, int $idBerbagi): bool;
}
