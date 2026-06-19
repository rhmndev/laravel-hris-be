<?php

namespace Database\Seeders;

use App\Models\LandingSetting;
use Illuminate\Database\Seeder;

class LandingSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Hero Section ──────────────────────────────────────────────
            [
                'key' => 'app_name',
                'value' => 'HRIS ESS',
                'type' => 'text',
                'group' => 'hero',
                'label' => 'Application Name',
                'sort_order' => 1,
            ],
            [
                'key' => 'hero_title',
                'value' => 'HRIS Employee Self-Service',
                'type' => 'text',
                'group' => 'hero',
                'label' => 'Hero Title',
                'sort_order' => 2,
            ],
            [
                'key' => 'hero_tagline',
                'value' => 'Kelola absensi, pengajuan cuti, dan slip gaji langsung dari genggaman tangan Anda.',
                'type' => 'textarea',
                'group' => 'hero',
                'label' => 'Hero Tagline',
                'sort_order' => 3,
            ],
            [
                'key' => 'hero_description',
                'value' => 'Aplikasi mobile HRIS ESS memudahkan karyawan untuk melakukan check-in/out, melihat jadwal shift, mengajukan cuti, dan mengakses slip gaji kapan saja dan di mana saja.',
                'type' => 'textarea',
                'group' => 'hero',
                'label' => 'Hero Description',
                'sort_order' => 4,
            ],
            [
                'key' => 'hero_badge',
                'value' => 'Android App',
                'type' => 'text',
                'group' => 'hero',
                'label' => 'Hero Badge Text',
                'sort_order' => 5,
            ],

            // ── Features Section ──────────────────────────────────────────
            [
                'key' => 'feature_1_icon',
                'value' => 'fingerprint',
                'type' => 'text',
                'group' => 'features',
                'label' => 'Feature 1 - Icon Name',
                'sort_order' => 1,
            ],
            [
                'key' => 'feature_1_title',
                'value' => 'Absensi Wajah',
                'type' => 'text',
                'group' => 'features',
                'label' => 'Feature 1 - Title',
                'sort_order' => 2,
            ],
            [
                'key' => 'feature_1_description',
                'value' => 'Check-in dan check-out dengan pengenalan wajah AI dan verifikasi lokasi GPS untuk akurasi maksimal.',
                'type' => 'textarea',
                'group' => 'features',
                'label' => 'Feature 1 - Description',
                'sort_order' => 3,
            ],
            [
                'key' => 'feature_2_icon',
                'value' => 'calendar',
                'type' => 'text',
                'group' => 'features',
                'label' => 'Feature 2 - Icon Name',
                'sort_order' => 4,
            ],
            [
                'key' => 'feature_2_title',
                'value' => 'Pengajuan Cuti',
                'type' => 'text',
                'group' => 'features',
                'label' => 'Feature 2 - Title',
                'sort_order' => 5,
            ],
            [
                'key' => 'feature_2_description',
                'value' => 'Ajukan cuti langsung dari aplikasi dengan alur approval otomatis ke atasan dan HRD.',
                'type' => 'textarea',
                'group' => 'features',
                'label' => 'Feature 2 - Description',
                'sort_order' => 6,
            ],
            [
                'key' => 'feature_3_icon',
                'value' => 'receipt',
                'type' => 'text',
                'group' => 'features',
                'label' => 'Feature 3 - Icon Name',
                'sort_order' => 7,
            ],
            [
                'key' => 'feature_3_title',
                'value' => 'Slip Gaji Digital',
                'type' => 'text',
                'group' => 'features',
                'label' => 'Feature 3 - Title',
                'sort_order' => 8,
            ],
            [
                'key' => 'feature_3_description',
                'value' => 'Akses slip gaji bulanan, download PDF, dan lihat riwayat penggajian dengan aman.',
                'type' => 'textarea',
                'group' => 'features',
                'label' => 'Feature 3 - Description',
                'sort_order' => 9,
            ],

            // ── About Section ─────────────────────────────────────────────
            [
                'key' => 'about_title',
                'value' => 'Tentang Aplikasi',
                'type' => 'text',
                'group' => 'about',
                'label' => 'About Section Title',
                'sort_order' => 1,
            ],
            [
                'key' => 'about_description',
                'value' => 'HRIS ESS adalah aplikasi Employee Self-Service yang dirancang untuk memudahkan pengelolaan sumber daya manusia. Dengan fitur absensi berbasis wajah, pengajuan cuti online, dan akses slip gaji digital, aplikasi ini menjadi solusi lengkap untuk perusahaan modern.',
                'type' => 'textarea',
                'group' => 'about',
                'label' => 'About Section Description',
                'sort_order' => 2,
            ],

            // ── Footer Section ────────────────────────────────────────────
            [
                'key' => 'footer_company',
                'value' => 'PT Dharma Control Cable Indonesia',
                'type' => 'text',
                'group' => 'footer',
                'label' => 'Footer - Company Name',
                'sort_order' => 1,
            ],
            [
                'key' => 'footer_copyright',
                'value' => '© 2026 All rights reserved.',
                'type' => 'text',
                'group' => 'footer',
                'label' => 'Footer - Copyright Text',
                'sort_order' => 2,
            ],
        ];

        foreach ($settings as $setting) {
            LandingSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
