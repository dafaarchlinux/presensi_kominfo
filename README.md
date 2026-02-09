# Presensi Kominfo (Laravel 12 + Filament + Face Recognition)

Aplikasi presensi berbasis **Laravel + Filament** dengan fitur **pengenalan/pengambilan wajah (kamera)** menggunakan **face-api.js** di sisi browser.

Repo: https://github.com/dafaarchlinux/presensi_kominfo

---

## Fitur Utama
- Admin panel (Filament)
- Presensi berbasis wajah (kamera browser)
- Pendaftaran wajah (ambil beberapa frame untuk akurasi)
- Model AI face-api disediakan di folder publik

---

# Penting: Kamera di HP (Wajib HTTPS)
Untuk dipakai di banyak HP / akses lewat IP server:
- `http://IP-server` ❌ biasanya kamera *tidak diizinkan*
- `http://domain` ❌ biasanya kamera *tidak diizinkan*
- `https://domain-kamu.com` ✅ **wajib** untuk production
- `http://localhost` ✅ boleh untuk local dev

Jadi agar kamera berfungsi di HP saat production: **server harus HTTPS**.

---

# Struktur Model Face-API
Model AI face-api ada di:
- `public/face-api/models/*`

Aplikasi ini juga menyediakan alias `/models/*`:
- `public/models` adalah symlink ke `public/face-api/models`

Tujuan: banyak contoh implementasi face-api memuat model dari `/models`.

### Cek model endpoint
Harus `200 OK`:
```bash
curl -sI http://127.0.0.1:8000/models/tiny_face_detector_model-weights_manifest.json | head -n 1
curl -sI http://127.0.0.1:8000/face-api/models/tiny_face_detector_model-weights_manifest.json | head -n 1
