@extends('landing.layout')

@section('title', 'StreamForge — The craziest live moments, first on your feed.')
@section('description', 'Best clips from the biggest streamers. Reactions, fails, clutch moments — the live, condensed. Speed, Kai Cenat, xQc and more.')

@section('og')
  <meta property="og:title" content="StreamForge — The craziest live moments, first on your feed." />
  <meta property="og:description" content="Best clips from the biggest streamers. Reactions, fails, clutch moments — the live, condensed." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="{{ url('/') }}" />
@endsection

@section('content')
  <!-- Hero -->
  <section class="hero">
    <div class="landing-container">
      <div class="hero-content">
        <div class="badge-live">
          <span class="badge-live-dot"></span>
          Live Now
        </div>
        <h1 class="hero-title">
          The craziest live moments,<br />
          <span>first on your feed.</span>
        </h1>
        <p class="hero-sub">
          Speed. Kai Cenat. xQc. The biggest streamers, clipped and served to you daily.
          Reactions, fails, clutch moments — the live, condensed.
        </p>
        <a href="https://www.tiktok.com/@streamforge" class="btn-primary" target="_blank" rel="noopener noreferrer">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.77 1.52V6.72a4.85 4.85 0 0 1-1-.03z"/>
          </svg>
          Follow @streamforge
        </a>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section class="features">
    <div class="landing-container">
      <p class="section-label">What we do</p>
      <h2 class="section-title">The best moments. Zero effort on your end.</h2>
      <div class="features-grid">
        <div class="feature-card">
          <span class="feature-icon">🎬</span>
          <h3>Daily Clips</h3>
          <p>Fresh content posted every day. We watch the streams so you don't have to — only the best moments make the cut.</p>
        </div>
        <div class="feature-card">
          <span class="feature-icon">👑</span>
          <h3>Top Streamers</h3>
          <p>Speed, Kai Cenat, xQc, Adin Ross and the biggest names in live streaming. All in one place.</p>
        </div>
        <div class="feature-card">
          <span class="feature-icon">⚡</span>
          <h3>First on Your Feed</h3>
          <p>Viral moments clipped within minutes of happening. If it happened live, you'll see it here first.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Streamers -->
  <section class="streamers">
    <div class="landing-container">
      <p class="section-label">Featured creators</p>
      <h2 class="section-title">We cover the biggest names</h2>
      <div class="streamers-grid">
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> Speed</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> Kai Cenat</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> xQc</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> Adin Ross</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> IShowSpeed</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> N3on</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> Fanum</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> Agent 00</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> Duke Dennis</div>
        <div class="streamer-pill"><span class="streamer-pill-dot"></span> And more...</div>
      </div>
    </div>
  </section>
@endsection
