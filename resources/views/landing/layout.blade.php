<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'StreamForge')</title>
  <meta name="description" content="@yield('description', 'Best clips from the biggest streamers. Reactions, fails, clutch moments — the live, condensed.')" />
  @hasSection('og')
    @yield('og')
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/landing.css') }}" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
</head>
<body>

  <!-- Navigation -->
  <nav class="landing-nav">
    <div class="landing-container">
      <div class="nav-inner">
        <a href="{{ route('home') }}" class="nav-logo">StreamForge</a>
        <a href="https://www.tiktok.com/@streamforge" class="nav-tiktok" target="_blank" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.77 1.52V6.72a4.85 4.85 0 0 1-1-.03z"/>
          </svg>
          <span>Follow on TikTok</span>
        </a>
      </div>
    </div>
  </nav>

  @yield('content')

  <!-- Footer -->
  <footer class="landing-footer">
    <div class="landing-container">
      <div class="footer-inner">
        <a href="{{ route('home') }}" class="footer-logo">StreamForge</a>
        <nav class="footer-links">
          <a href="{{ route('home') }}">Home</a>
          <a href="{{ route('terms') }}">Terms of Service</a>
          <a href="{{ route('privacy') }}">Privacy Policy</a>
        </nav>
        <p class="footer-copy">&copy; {{ date('Y') }} StreamForge. All rights reserved.</p>
        <p class="footer-disclaimer">Not affiliated with TikTok, Twitch, Kick, YouTube, or any individual streamer.</p>
      </div>
    </div>
  </footer>

</body>
</html>
