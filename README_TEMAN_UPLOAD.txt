CARA UPLOAD (GAYA TEMAN):

1) Extract ZIP -> muncul folder "presensi_kominfo"
2) Masuk ke folder webroot hosting (yang dibuka domain) -> bisa "/", "release", "public_html", dll.
3) Upload PAKAI CARA INI:
   - Masuk folder presensi_kominfo
   - Upload SEMUA ISINYA (index.php, .htaccess, build/, models/, face-api/, presensi_app/, dll) ke webroot.
   - Jangan upload foldernya doang sampai jadi webroot/presensi_kominfo/index.php (itu salah).

4) Pastikan setelah upload di webroot ada:
   - index.php
   - presensi_app/ (di folder yang sama dengan index.php)

Kalau error DB "Access denied":
- Edit presensi_app/.env
- Ganti DB_USERNAME (biasanya ada prefix dari hosting).
