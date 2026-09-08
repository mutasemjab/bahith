<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ \App\Models\SiteSetting::val('hero_badge') ?: 'أكاديمية الباحث' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --primary: #1e40af;
    --primary-light: #3b82f6;
    --accent: #ef4444;
    --bg: #0f172a;
    --card: #1e293b;
    --text: #f1f5f9;
    --muted: #94a3b8;
    --border: rgba(255,255,255,.08);
  }
  body {
    font-family: 'Cairo', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    direction: rtl;
  }

  /* ── Navbar ── */
  .lp-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border);
  }
  .lp-brand {
    display: flex;
    align-items: center;
    gap: .75rem;
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--text);
    text-decoration: none;
  }
  .lp-brand .logo-circle {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
  }

  /* ── Hero ── */
  .lp-hero {
    text-align: center;
    padding: 6rem 1.5rem 4rem;
    position: relative;
    overflow: hidden;
  }
  .lp-hero::before {
    content: '';
    position: absolute;
    top: -200px; left: 50%; transform: translateX(-50%);
    width: 700px; height: 700px;
    background: radial-gradient(circle, rgba(59,130,246,.15) 0%, transparent 70%);
    pointer-events: none;
  }
  .lp-badge {
    display: inline-block;
    background: rgba(59,130,246,.15);
    color: var(--primary-light);
    border: 1px solid rgba(59,130,246,.3);
    border-radius: 50px;
    padding: .4rem 1.2rem;
    font-size: .85rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
  }
  .lp-hero h1 {
    font-size: clamp(2rem, 6vw, 3.5rem);
    font-weight: 900;
    line-height: 1.2;
    margin-bottom: 1.2rem;
  }
  .lp-hero h1 .accent { color: var(--primary-light); }
  .lp-hero p {
    font-size: 1.1rem;
    color: var(--muted);
    max-width: 560px;
    margin: 0 auto 2.5rem;
    line-height: 1.8;
  }
  .lp-hero-img {
    width: 100%; max-width: 700px;
    border-radius: 20px;
    margin: 0 auto 0;
    display: block;
    box-shadow: 0 30px 80px rgba(0,0,0,.5);
    object-fit: cover;
    max-height: 420px;
  }

  /* ── App Download Button ── */
  .lp-cta {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    background: #000;
    color: #fff;
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 14px;
    padding: .85rem 2rem;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 700;
    transition: all .25s;
    margin-bottom: 2.5rem;
  }
  .lp-cta:hover {
    background: #111;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0,0,0,.4);
    color: #fff;
  }
  .lp-cta .cta-icon { font-size: 1.6rem; }
  .lp-cta .cta-text { text-align: right; }
  .lp-cta .cta-sub { display: block; font-size: .7rem; font-weight: 400; opacity: .7; }
  .lp-cta .cta-main { display: block; font-size: 1rem; }

  /* ── About ── */
  .lp-about {
    padding: 5rem 1.5rem;
    max-width: 900px;
    margin: 0 auto;
    text-align: center;
  }
  .lp-about h2 {
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 1rem;
  }
  .lp-about p {
    font-size: 1rem;
    color: var(--muted);
    line-height: 2;
  }
  .lp-values {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 3rem;
  }
  .lp-value-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.75rem 1.25rem;
    text-align: center;
  }
  .lp-value-card .val-icon {
    font-size: 2rem;
    margin-bottom: .75rem;
    display: block;
  }
  .lp-value-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: .4rem; }
  .lp-value-card p { font-size: .85rem; color: var(--muted); line-height: 1.7; }

  /* ── Footer ── */
  .lp-footer {
    text-align: center;
    padding: 2rem 1.5rem;
    border-top: 1px solid var(--border);
    color: var(--muted);
    font-size: .85rem;
  }
</style>
</head>
<body>

{{-- Navbar --}}
<nav class="lp-nav">
  <a class="lp-brand" href="#">
    <div class="logo-circle">🎓</div>
    <span>{{ \App\Models\SiteSetting::val('hero_badge') ?: 'أكاديمية الباحث' }}</span>
  </a>
</nav>

{{-- Hero --}}
<section class="lp-hero">
  <div class="lp-badge">🚀 {{ app()->getLocale() === 'ar' ? 'التطبيق متاح الآن' : 'App Now Available' }}</div>

  <h1>
    {{ app()->getLocale() === 'ar' ? 'تعلّم بلا حدود مع' : 'Learn Without Limits with' }}
    <span class="accent">{{ app()->getLocale() === 'ar' ? 'الباحث' : 'Al-Bahith' }}</span>
  </h1>

  <p>{{ \App\Models\SiteSetting::val('hero_subtitle') ?: (app()->getLocale() === 'ar' ? 'حمّل التطبيق الآن واستمتع بتجربة تعليمية متكاملة' : 'Download the app and enjoy a complete learning experience') }}</p>

  {{-- App Store Button --}}
  @php $appStore = \App\Models\SiteSetting::raw('app_store'); @endphp
  @if($appStore)
  <a href="{{ $appStore }}" target="_blank" class="lp-cta">
    <span class="cta-icon"><i class="bi bi-apple"></i></span>
    <span class="cta-text">
      <span class="cta-sub">{{ app()->getLocale() === 'ar' ? 'حمّل من' : 'Download on the' }}</span>
      <span class="cta-main">App Store</span>
    </span>
  </a>
  @else
  <div style="color:var(--muted);margin-bottom:2.5rem">
    <i class="bi bi-apple" style="font-size:2rem"></i><br>
    <small>{{ app()->getLocale() === 'ar' ? 'قريباً في App Store' : 'Coming soon on App Store' }}</small>
  </div>
  @endif

  {{-- Hero image --}}
  @php $heroImg = \App\Models\SiteSetting::raw('hero_image'); @endphp
  @if($heroImg)
  <img class="lp-hero-img" src="{{ asset('assets/uploads/site/'.$heroImg) }}" alt="Bahith Academy">
  @endif
</section>

{{-- About --}}
@php
  $aboutTitle = \App\Models\SiteSetting::val('about_title');
  $aboutDesc  = \App\Models\SiteSetting::val('about_description');
  $v1t = \App\Models\SiteSetting::val('about_value1_title');
  $v1d = \App\Models\SiteSetting::val('about_value1_desc');
  $v2t = \App\Models\SiteSetting::val('about_value2_title');
  $v2d = \App\Models\SiteSetting::val('about_value2_desc');
  $v3t = \App\Models\SiteSetting::val('about_value3_title');
  $v3d = \App\Models\SiteSetting::val('about_value3_desc');
@endphp
@if($aboutTitle || $aboutDesc)
<section class="lp-about" id="about">
  <h2>{{ $aboutTitle ?: (app()->getLocale() === 'ar' ? 'من نحن' : 'About Us') }}</h2>
  @if($aboutDesc)
  <p>{{ $aboutDesc }}</p>
  @endif

  @if($v1t || $v2t || $v3t)
  <div class="lp-values">
    @if($v1t)
    <div class="lp-value-card">
      <span class="val-icon">✨</span>
      <h3>{{ $v1t }}</h3>
      @if($v1d)<p>{{ $v1d }}</p>@endif
    </div>
    @endif
    @if($v2t)
    <div class="lp-value-card">
      <span class="val-icon">🎯</span>
      <h3>{{ $v2t }}</h3>
      @if($v2d)<p>{{ $v2d }}</p>@endif
    </div>
    @endif
    @if($v3t)
    <div class="lp-value-card">
      <span class="val-icon">🏆</span>
      <h3>{{ $v3t }}</h3>
      @if($v3d)<p>{{ $v3d }}</p>@endif
    </div>
    @endif
  </div>
  @endif
</section>
@endif

{{-- Footer --}}
<footer class="lp-footer">
  <p>© {{ date('Y') }} {{ app()->getLocale() === 'ar' ? 'أكاديمية ومدارس الباحث — جميع الحقوق محفوظة' : 'Bahith Academy — All rights reserved' }}</p>
</footer>

</body>
</html>
