<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $hero['app_name']->value ?? 'HRIS ESS' }} - {{ $hero['hero_title']->value ?? 'Employee Self-Service' }}</title>
    <meta name="description" content="{{ $hero['hero_tagline']->value ?? '' }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" type="image/png" href="/dci.ico">
    <link rel="icon" type="image/png" href="/dci.ico" >
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        .gradient-hero { background: #1a365d; }
        .gradient-card { background: #f0f9ff; }
        .glass { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15); }
        .feature-icon { width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: 14px; }
        .changelog-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .changelog-content.open { max-height: 500px; }
        .fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .float-anim { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="font-sans bg-white text-gray-800 antialiased">

    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="navbar">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="#" class="flex items-center gap-2">
                    <!-- <div class="w-8 h-8 bg-sky-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div> -->
                    <img src="{{ asset('assets/images/dci.png') }}" alt="{{ $hero['app_name']->value }}" class="w-16 h-16" />
                    <span class="font-bold text-xl text-white" id="nav-brand">{{ $hero['app_name']->value ?? 'PT DCCI' }}</span>
                </a>
                <div class="hidden md:flex items-center gap-6">
                    <a href="#features" class="text-sm font-medium text-white/80 hover:text-white transition">Fitur</a>
                    <a href="#screenshots" class="text-sm font-medium text-white/80 hover:text-white transition">Screenshot</a>
                    <a href="#changelog" class="text-sm font-medium text-white/80 hover:text-white transition">Changelog</a>
                    <a href="#about" class="text-sm font-medium text-white/80 hover:text-white transition">Tentang</a>
                    @if($latestRelease)
                        <a href="{{ route('download.latest') }}" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-400 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    @endif
                </div>
                {{-- Mobile menu button --}}
                <button class="md:hidden text-white" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            {{-- Mobile menu --}}
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-2">
                <a href="#features" class="block text-sm text-white/80 hover:text-white py-1">Fitur</a>
                <a href="#screenshots" class="block text-sm text-white/80 hover:text-white py-1">Screenshot</a>
                <a href="#changelog" class="block text-sm text-white/80 hover:text-white py-1">Changelog</a>
                <a href="#about" class="block text-sm text-white/80 hover:text-white py-1">Tentang</a>
                @if($latestRelease)
                    <a href="{{ route('download.latest') }}" class="block bg-sky-500 text-white text-sm font-semibold px-4 py-2 rounded-lg text-center mt-2">Download APK</a>
                @endif
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="gradient-hero min-h-screen flex items-center relative overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute top-20 right-10 w-72 h-72 bg-sky-400/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-10 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    @if($hero['hero_badge'] ?? null)
                        <span class="inline-flex items-center gap-1.5 bg-sky-500/20 text-sky-300 text-xs font-semibold px-3 py-1 rounded-full mb-6 border border-sky-500/30">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            {{ $hero['hero_badge']->value }}
                        </span>
                    @endif

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                        {{ $hero['hero_title']->value ?? 'HRIS Employee Self-Service' }}
                    </h1>

                    @if($hero['hero_tagline'] ?? null)
                        <div class="text-lg sm:text-xl text-sky-100/90 mb-4 leading-relaxed font-medium prose prose-invert max-w-none">
                            {!! $hero['hero_tagline']->value !!}
                        </div>
                    @endif

                    @if($hero['hero_description'] ?? null)
                        <div class="text-base text-sky-200/70 mb-8 leading-relaxed prose prose-invert max-w-none">
                            {!! $hero['hero_description']->value !!}
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-4">
                        @if($latestRelease)
                            <a href="{{ route('download.latest') }}" class="inline-flex items-center gap-2 bg-white text-slate-900 font-bold px-8 py-4 rounded-xl hover:bg-sky-50 transition shadow-lg shadow-black/20 text-lg">
                                <svg class="w-6 h-6 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download APK
                            </a>
                            <div class="flex items-center gap-4 text-sky-200/70 text-sm">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                    v{{ $latestRelease->version }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6"/></svg>
                                    {{ $latestRelease->formatted_file_size }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.523 15.341a1.234 1.234 0 110-2.468 1.234 1.234 0 010 2.468zm-11.046 0a1.234 1.234 0 110-2.468 1.234 1.234 0 010 2.468zm11.47-6.39L19.7 5.47a.41.41 0 00-.149-.559.41.41 0 00-.559.149l-1.773 3.07A12.408 12.408 0 0012 6.818a12.408 12.408 0 00-5.219 1.322L5.008 5.07a.41.41 0 00-.559-.149.41.41 0 00-.149.559l1.753 3.481C3.7 10.293 2.25 12.796 2.25 15.621h19.5c0-2.825-1.45-5.328-3.703-6.67z"/></svg>
                                    Android {{ $latestRelease->min_android_version }}+
                                </span>
                            </div>
                        @else
                            <div class="glass text-white/80 px-6 py-3 rounded-xl text-sm">
                                Belum ada versi yang tersedia saat ini.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Hero illustration --}}
                <div class="hidden lg:flex justify-center fade-in">
                    <div class="relative">
                        <div class="w-64 h-[500px] bg-slate-800 rounded-[2.5rem] border-4 border-slate-700 shadow-2xl overflow-hidden float-anim">
                            <div class="w-full h-full bg-sky-500 flex flex-col items-center justify-center p-6">
                                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-4">
                                    <img src="{{ asset('assets/images/face-detec2.png') }}" alt="{{ $hero['app_name']->value }}" class="w-16 h-16" />
                                </div>
                                <span class="text-white font-bold text-xl">{{ $hero['app_name']->value ?? 'HRIS ESS' }}</span>
                                <span class="text-white/60 text-xs mt-1">Employee Self-Service</span>
                                <div class="mt-8 w-full space-y-3">
                                    <div class="bg-white/15 rounded-xl p-3 flex items-center gap-3">
                                        <div class="w-8 h-8 bg-white/20 rounded-lg">
                                            <img src="{{ asset('assets/images/face-detec.png') }}" alt="{{ $hero['app_name']->value }}" class="w-8 h-8" />
                                        </div>
                                        <div class="flex-1"><div class="h-2 bg-white/30 rounded w-3/4 mb-1"></div><div class="h-1.5 bg-white/20 rounded w-1/2"></div></div>
                                    </div>
                                    <div class="bg-white/15 rounded-xl p-3 flex items-center gap-3">
                                        <div class="w-8 h-8 bg-white/20 rounded-lg">
                                            <img src="{{ asset('assets/images/face-detec.png') }}" alt="{{ $hero['app_name']->value }}" class="w-8 h-8" />
                                        </div>
                                        <div class="flex-1"><div class="h-2 bg-white/30 rounded w-2/3 mb-1"></div><div class="h-1.5 bg-white/20 rounded w-3/5"></div></div>
                                    </div>
                                    <div class="bg-white/15 rounded-xl p-3 flex items-center gap-3">
                                        <div class="w-8 h-8 bg-white/20 rounded-lg">
                                            <img src="{{ asset('assets/images/face-detec.png') }}" alt="{{ $hero['app_name']->value }}" class="w-8 h-8" />
                                        </div>
                                        <div class="flex-1"><div class="h-2 bg-white/30 rounded w-4/5 mb-1"></div><div class="h-1.5 bg-white/20 rounded w-2/5"></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-48 h-4 bg-black/20 rounded-full blur-md"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wave divider --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/></svg>
        </div>
    </section>

    {{-- Features Section --}}
    @php
        $featureIcons = [
            'fingerprint' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>',
            'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
            'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        ];
    @endphp

    <section id="features" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-in">
                <span class="text-sky-500 font-semibold text-sm uppercase tracking-wider">Fitur Unggulan</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mt-2">Semua yang Anda Butuhkan</h2>
                <p class="text-gray-500 mt-3 max-w-xl mx-auto">Fitur lengkap untuk mengelola kebutuhan HR dalam satu aplikasi mobile.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @for ($i = 1; $i <= 3; $i++)
                    @php
                        $icon = $features["feature_{$i}_icon"]->value ?? 'shield';
                        $title = $features["feature_{$i}_title"]->value ?? '';
                        $desc = $features["feature_{$i}_description"]->value ?? '';
                        $svgPath = $featureIcons[$icon] ?? $featureIcons['shield'];
                    @endphp
                    <div class="fade-in gradient-card rounded-2xl p-8 hover:shadow-lg transition-shadow duration-300 border border-sky-100">
                        <div class="feature-icon bg-sky-500 text-white mb-5">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $svgPath !!}</svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $title }}</h3>
                        <p class="text-gray-600 leading-relaxed">{!! nl2br(e($desc)) !!}</p>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- Screenshots Section --}}
    @if($screenshots->isNotEmpty())
    <section id="screenshots" class="py-20 bg-slate-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 fade-in">
                <span class="text-sky-500 font-semibold text-sm uppercase tracking-wider">Preview</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mt-2">Screenshot Aplikasi</h2>
            </div>
            <div class="flex gap-6 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide fade-in">
                @foreach($screenshots as $screenshot)
                    <div class="flex-shrink-0 snap-center">
                        <img src="{{ asset('storage/' . $screenshot->value) }}"
                             alt="{{ $screenshot->label }}"
                             class="w-64 h-auto rounded-2xl shadow-lg border border-gray-200"
                             loading="lazy">
                        @if($screenshot->label)
                            <p class="text-center text-sm text-gray-500 mt-2">{{ $screenshot->label }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Changelog Section --}}
    <section id="changelog" class="py-20 {{ $screenshots->isNotEmpty() ? 'bg-white' : 'bg-slate-50' }}">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 fade-in">
                <span class="text-sky-500 font-semibold text-sm uppercase tracking-wider">Riwayat Versi</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mt-2">Changelog</h2>
            </div>

            @if($allReleases->isEmpty())
                <div class="text-center text-gray-400 py-12 fade-in">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <p>Belum ada riwayat versi.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($allReleases as $idx => $release)
                        <div class="fade-in bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <button class="w-full px-6 py-4 flex items-center justify-between text-left changelog-toggle" onclick="toggleChangelog(this)">
                                <div class="flex items-center gap-3">
                                    <span class="bg-sky-100 text-sky-700 font-bold text-sm px-3 py-1 rounded-full">v{{ $release->version }}</span>
                                    @if($idx === 0)
                                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">Latest</span>
                                    @endif
                                    <span class="text-gray-400 text-sm hidden sm:inline">{{ $release->published_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('download.release', $release) }}" class="text-sky-500 hover:text-sky-600 transition" onclick="event.stopPropagation()">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                    <svg class="w-5 h-5 text-gray-400 transition-transform changelog-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>
                            <div class="changelog-content">
                                <div class="px-6 pb-4 border-t border-gray-100 pt-3">
                                    <div class="text-sm text-gray-400 mb-2 sm:hidden">{{ $release->published_at->format('d M Y') }} &middot; {{ $release->formatted_file_size }} &middot; Android {{ $release->min_android_version }}+</div>
                                    @if($release->release_notes)
                                        <div class="prose prose-sm prose-sky max-w-none text-gray-600">{!! $release->release_notes !!}</div>
                                    @else
                                        <p class="text-gray-400 text-sm italic">Tidak ada catatan rilis.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- About Section --}}
    <section id="about" class="py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="fade-in bg-slate-900 rounded-3xl p-10 sm:p-14 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                        {{ $about['about_title']->value ?? 'Tentang Aplikasi' }}
                    </h2>
                    <div class="text-slate-300 leading-relaxed text-lg max-w-2xl mx-auto prose prose-invert">
                        {!! $about['about_description']->value ?? '' !!}
                    </div>
                    @if($latestRelease)
                        <a href="{{ route('download.latest') }}" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-400 text-white font-bold px-8 py-4 rounded-xl transition mt-8 shadow-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download Sekarang
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-900 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <!-- <div class="w-7 h-7 bg-sky-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div> -->
                    <img src="{{ asset('assets/images/dci.png') }}" alt="{{ $footer['footer_company']->value }}" class="w-16 h-16" />
                    <span class="font-semibold text-sm text-white">{{ $footer['footer_company']->value ?? 'PT Dharma Control Cable Indonesia' }}</span>
                </div>
                <p class="text-slate-500 text-sm">{{ $footer['footer_copyright']->value ?? '© ' . date('Y') . ' All rights reserved.' }}</p>
                <a href="/admin" class="text-slate-500 hover:text-sky-400 text-sm transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Admin Panel
                </a>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('bg-slate-900/95', 'backdrop-blur-md', 'shadow-lg');
            } else {
                navbar.classList.remove('bg-slate-900/95', 'backdrop-blur-md', 'shadow-lg');
            }
        });

        // Changelog toggle
        function toggleChangelog(btn) {
            const content = btn.nextElementSibling;
            const arrow = btn.querySelector('.changelog-arrow');
            content.classList.toggle('open');
            arrow.classList.toggle('rotate-180');
        }

        // Fade-in on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>
