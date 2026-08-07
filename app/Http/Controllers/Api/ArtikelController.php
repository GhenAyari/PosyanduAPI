<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Tambahkan ini untuk membuat slug otomatis

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $query = Artikel::with('penulis:id,name,role');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'dipublikasikan');
        }

        return response()->json([
            'status' => 'sukses',
            'data' => $query->latest()->get()
        ]);
    }

    public function show($id)
    {
        $artikel = Artikel::with('penulis:id,name,role')->find($id);

        if (!$artikel) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Artikel tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'sukses', 'data' => $artikel]);
    }

    public function store(Request $request)
    {
        // Sesuaikan validasi dengan nama kolom di Bruno
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string',
            'isi_artikel' => 'required|string', // Sesuai migrasi
            'status' => 'required|in:draf,dipublikasikan', // Sesuai enum
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('images/artikel', 'public');
        }

        // Buat URL ramah SEO (Slug) dari Judul, tambah angka acak agar unik
        $slug = Str::slug($request->judul) . '-' . time();

        $artikel = Artikel::create([
            'penulis_id' => $request->user()->id,
            // 'posyandu_id' => $request->user()->posyandu_id, // Buka komen ini jika user sudah punya relasi ke posyandu
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'slug' => $slug,
            'isi_artikel' => $request->isi_artikel,
            'status' => $request->status,
            'path_foto' => $fotoPath,
            'published_at' => $request->status === 'dipublikasikan' ? now() : null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Artikel berhasil disimpan',
            'data' => $artikel
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $artikel = Artikel::find($id);
        if (!$artikel) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Artikel tidak ditemukan'], 404);
        }

        $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'kategori' => 'sometimes|required|string',
            'isi_artikel' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:draf,dipublikasikan',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Jika ada foto baru
        if ($request->hasFile('foto')) {
            if ($artikel->path_foto && Storage::disk('public')->exists($artikel->path_foto)) {
                Storage::disk('public')->delete($artikel->path_foto);
            }
            $artikel->path_foto = $request->file('foto')->store('images/artikel', 'public');
        }

        // Jika judul berubah, ubah juga slug-nya
        if ($request->has('judul') && $request->judul !== $artikel->judul) {
            $artikel->slug = Str::slug($request->judul) . '-' . time();
        }

        // Update waktu publish jika status berubah jadi dipublikasikan
        if ($request->has('status') && $request->status === 'dipublikasikan' && $artikel->status !== 'dipublikasikan') {
            $artikel->published_at = now();
        }

        $artikel->update($request->only(['judul', 'kategori', 'isi_artikel', 'status']));
        $artikel->save(); // Simpan perubahan tambahan seperti path_foto, slug, published_at

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Artikel berhasil diperbarui',
            'data' => $artikel
        ]);
    }

    public function destroy($id)
    {
        $artikel = Artikel::find($id);
        if (!$artikel) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Artikel tidak ditemukan'], 404);
        }

        if ($artikel->path_foto && Storage::disk('public')->exists($artikel->path_foto)) {
            Storage::disk('public')->delete($artikel->path_foto);
        }

        $artikel->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Artikel berhasil dihapus']);
    }
}
