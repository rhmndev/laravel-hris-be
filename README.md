#  Dokumentasi HRIS ESS — Panduan Menjalankan Project di Lokal

<img width="413" height="848" alt="Screenshot 2026-07-18 174153" src="https://github.com/user-attachments/assets/1e61f934-8408-4e55-a10a-f0357410e2d9" />


Panduan singkat cara menjalankan project **HRIS ESS** dari cloning sampai berjalan di komputer lokal, mencakup backend (`laravel-hris-be`) dan mobile app (`hris-mobile-app`).

---

##  Spesifikasi Teknologi

### Backend — `laravel-hris-be`

| Teknologi | Versi | Fungsi |
|---|---|---|
| PHP | ^8.3 | Bahasa pemrograman backend |
| Laravel | ^13.0 | Framework REST API |
| Laravel Sanctum | ^4.0 | Autentikasi Bearer token untuk mobile app |
| Filament | ^4.0 | Admin panel (kelola branch, user, attendance, payroll, dll) |
| Filament Shield + spatie/laravel-permission | ^4.2 / ^7.4 | Role-Based Access Control (RBAC) |
| MySQL / SQLite | - | Database (SQLite default, MySQL untuk produksi) |
| Scramble | ^0.13 | Dokumentasi API otomatis |

### Mobile App — `hris-mobile-app`

| Teknologi | Versi | Fungsi |
|---|---|---|
| Vue.js | ^3.5 | Framework frontend (Composition API) |
| TypeScript | ~6.0 | Type safety |
| Vite | ^8.0 | Build tool & dev server |
| Capacitor | ^8.3 | Hybrid mobile runtime (Android) |
| Tailwind CSS + DaisyUI | ^4.2 / ^5.5 | Styling & UI components |
| Pinia | ^3.0 | State management (auth store) |
| Vue Router | ^4.6 | Navigasi & route guard |
| face-api.js + MediaPipe | ^0.22 / ^0.3 | Face recognition & liveness detection |
| Capacitor Geolocation / Camera / Share | ^8.x | Akses GPS, kamera, share file native |
| jsPDF | ^4.2 | Generasi slip gaji (payslip) PDF |
| Leaflet | ^1.9 | Peta lokasi check-in |

### Kebutuhan Sistem (Prasyarat)

- **PHP >= 8.3** + Composer
- **Node.js >= 20** + npm
- **MySQL** (opsional, default SQLite sudah siap pakai)
- **Android Studio + JDK 17+** (hanya jika ingin build/run di emulator/HP Android)

---

##  1. Clone Repository

```bash
git clone <url-repo-laravel-hris-be> laravel-hris-be
git clone <url-repo-hris-mobile-app> hris-mobile-app
```

---

##  2. Menjalankan Backend (laravel-hris-be)

```bash
cd laravel-hris-be

# Install dependensi PHP
composer install

# Buat file konfigurasi environment
copy .env.example .env          # Windows
# cp .env.example .env          # macOS/Linux

# Generate application key
php artisan key:generate
```

### Konfigurasi Database

**Opsi A — SQLite (paling mudah, tanpa server database):**

Buat file database lalu jalankan migrasi:

```bash
type nul > database/database.sqlite   # Windows
php artisan migrate --seed
```

**Opsi B — MySQL:**

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `hris` di MySQL, lalu:

```bash
php artisan migrate --seed
```

### Jalankan Server

```bash
php artisan serve
```

 API berjalan di **http://127.0.0.1:8000**
 Admin panel Filament di **http://127.0.0.1:8000/admin** (akun dari seeder)

> Alternatif sekali jalan: `composer run dev` (menjalankan server, queue, logs, dan vite bersamaan).

---

##  3. Menjalankan Mobile App (hris-mobile-app)

```bash
cd hris-mobile-app

# Install dependensi
npm install

# Buat file konfigurasi environment
copy .env.example .env          # Windows
```

Edit file `.env` dan arahkan ke backend lokal:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

>  Gunakan `127.0.0.1` (bukan `localhost`) agar mudah diakses dari emulator Android. Untuk perangkat fisik, ganti dengan IP komputer di jaringan yang sama, misal `http://192.168.1.10:8000/api`.

### Jalankan di Browser (Mode Development)

```bash
npm run dev
```

 Aplikasi berjalan di **http://localhost:5173**

### Jalankan di Android (Emulator / HP)

```bash
# Build aset web
npm run build

# Sinkronkan ke project Android
npx cap sync android

# Buka Android Studio / jalankan di emulator
npx cap run android
```

---

##  4. Alur Kerja Development

1. Jalankan backend: `php artisan serve` (terminal 1)
2. Jalankan mobile app: `npm run dev` (terminal 2)
3. Login di mobile app dengan akun hasil seeder
4. Kelola data (branch, user, approval cuti, payroll) lewat admin panel `/admin`

### Catatan Penting

- **Setiap ada perubahan migrasi** (misal kolom baru di branch), jalankan:
  ```bash
  php artisan migrate
  ```
- **Jika menambah/ubah field di Branch**, sesuaikan tipe TypeScript di `src/services/auth/types.ts` dan `src/services/company/types.ts`.
- **Aturan bisnis yang berlaku**:
  - Hari Minggu dalam rentang pengajuan cuti **otomatis dilewati** (tidak dihitung sebagai hari cuti).
  - Check-in/check-out divalidasi terhadap 4 jendela waktu per branch: `min_check_in_time`, `max_check_in_time`, `min_check_out_time`, `max_check_out_time` (dikosongkan = tanpa batas).

---

##  Struktur Singkat

```
developments/
├── laravel-hris-be/              # Backend API + Admin Panel
│   ├── app/Http/Controllers/Api/ # REST API (auth, attendance, leave, payslip, payroll)
│   ├── app/Filament/Resources/   # Halaman admin panel
│   ├── app/Models/               # Eloquent models (User, Branch, Attendance, dll)
│   ├── database/migrations/      # Skema database
│   └── routes/api.php            # Daftar endpoint API
│
└── hris-mobile-app/              # Mobile App (Vue 3 + Capacitor)
    ├── src/views/                # Halaman (Login, Attendance, Leave, PaySlip, Profile)
    ├── src/components/           # Komponen (LivenessDetection, MapPreview, dll)
    ├── src/services/             # API client per domain
    ├── src/stores/               # Pinia auth store
    └── android/                  # Project native Android (Capacitor)
```
