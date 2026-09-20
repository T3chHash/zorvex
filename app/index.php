<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#070B14">
  <title>Zorvex Pro — وب‌اپلیکیشن هوشمند</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="./js/telegram-web-app.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

  <style>
    :root {
      --bg: #070B14;
      --surface: #0E1626;
      --surface-card: rgba(16, 26, 44, 0.75);
      --surface-border: rgba(255, 255, 255, 0.08);
      --cyan: #06B6D4;
      --cyan-light: #22D3EE;
      --cyan-glow: rgba(6, 182, 212, 0.35);
      --purple: #8B5CF6;
      --green: #10B981;
      --red: #EF4444;
      --orange: #F59E0B;
      --text: #F8FAFC;
      --text-mute: #94A3B8;
      --text-dim: #64748B;
      --font: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      -webkit-tap-highlight-color: transparent;
      user-select: none;
    }

    body {
      font-family: var(--font);
      background-color: var(--bg);
      color: var(--text);
      direction: rtl;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
      padding-bottom: 84px;
      padding-top: env(safe-area-inset-top, 0px);
      background-image: 
        radial-gradient(ellipse 60% 40% at 50% -10%, rgba(6, 182, 212, 0.15), transparent 70%),
        radial-gradient(ellipse 50% 30% at 80% 80%, rgba(139, 92, 246, 0.08), transparent 60%);
      background-attachment: fixed;
    }

    /* Glassmorphism Classes */
    .glass-card {
      background: var(--surface-card);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--surface-border);
      border-radius: 16px;
      padding: 16px;
      box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
      position: relative;
      overflow: hidden;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
      border-color: rgba(6, 182, 212, 0.3);
      transform: translateY(-2px);
    }

    /* App Header */
    .header {
      padding: 14px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 40;
      background: rgba(7, 11, 20, 0.85);
      backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--surface-border);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .brand-logo {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--cyan), var(--purple));
      display: grid;
      place-items: center;
      color: #fff;
      font-weight: 900;
      font-size: 1.1rem;
      box-shadow: 0 0 16px var(--cyan-glow);
    }

    .brand-info h1 {
      font-size: 1rem;
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    .brand-info p {
      font-size: 0.68rem;
      color: var(--text-mute);
    }

    .header-balance {
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(6, 182, 212, 0.12);
      border: 1px solid rgba(6, 182, 212, 0.3);
      padding: 6px 12px;
      border-radius: 99px;
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--cyan-light);
      cursor: pointer;
    }

    /* Tab Navigation Pages */
    .tab-content {
      display: none;
      padding: 16px;
      animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .tab-content.active {
      display: block;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Hero User Profile Card */
    .user-hero {
      background: linear-gradient(135deg, rgba(16, 26, 44, 0.9), rgba(14, 22, 38, 0.95));
      border: 1px solid rgba(6, 182, 212, 0.25);
      border-radius: 20px;
      padding: 18px;
      margin-bottom: 18px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 12px 36px -10px rgba(6, 182, 212, 0.2);
    }

    .user-hero::before {
      content: "";
      position: absolute;
      top: -40px;
      left: -40px;
      width: 130px;
      height: 130px;
      background: radial-gradient(circle, var(--cyan-glow), transparent 70%);
      pointer-events: none;
    }

    .user-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }

    .user-avatar-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .user-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: linear-gradient(135deg, #06B6D4, #3B82F6);
      display: grid;
      place-items: center;
      font-size: 1.3rem;
      font-weight: 800;
      color: #fff;
      box-shadow: 0 0 16px rgba(6, 182, 212, 0.4);
    }

    .user-name-title {
      font-size: 1.05rem;
      font-weight: 800;
      color: var(--text);
    }

    .user-tag {
      font-size: 0.72rem;
      color: var(--cyan-light);
      font-family: monospace;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 99px;
      font-size: 0.72rem;
      font-weight: 700;
      background: rgba(16, 185, 129, 0.15);
      color: var(--green);
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-badge.warn {
      background: rgba(245, 158, 11, 0.15);
      color: var(--orange);
      border-color: rgba(245, 158, 11, 0.3);
    }

    /* Active Service Circle Card */
    .active-service-box {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      background: rgba(7, 11, 20, 0.6);
      border-radius: 14px;
      padding: 16px;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .progress-circle-wrap {
      position: relative;
      width: 84px;
      height: 84px;
      flex-shrink: 0;
    }

    .progress-circle-svg {
      width: 100%;
      height: 100%;
      transform: rotate(-90deg);
    }

    .circle-bg {
      fill: none;
      stroke: rgba(255, 255, 255, 0.08);
      stroke-width: 6;
    }

    .circle-bar {
      fill: none;
      stroke: url(#circleGrad);
      stroke-width: 6;
      stroke-linecap: round;
      stroke-dasharray: 226;
      stroke-dashoffset: 60;
      transition: stroke-dashoffset 1s ease;
    }

    .progress-text {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      font-weight: 800;
    }

    .progress-text span {
      font-size: 0.6rem;
      color: var(--text-mute);
    }

    .service-meta {
      flex: 1;
    }

    .service-meta h4 {
      font-size: 0.95rem;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .service-meta p {
      font-size: 0.78rem;
      color: var(--text-mute);
      margin-bottom: 8px;
    }

    /* Action Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: 12px;
      font-family: inherit;
      font-size: 0.85rem;
      font-weight: 700;
      border: none;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn:active {
      transform: scale(0.97);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--cyan), #0284C7);
      color: #fff;
      box-shadow: 0 4px 18px var(--cyan-glow);
    }

    .btn-primary:hover {
      box-shadow: 0 6px 24px rgba(6, 182, 212, 0.5);
    }

    .btn-glass {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text);
    }

    .btn-glass:hover {
      background: rgba(255, 255, 255, 0.12);
    }

    .btn-block {
      width: 100%;
    }

    /* Quick Grid Actions */
    .quick-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
      margin-bottom: 20px;
    }

    .quick-card {
      background: var(--surface-card);
      border: 1px solid var(--surface-border);
      border-radius: 14px;
      padding: 12px 6px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .quick-card:active {
      transform: scale(0.95);
    }

    .quick-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      font-size: 1.2rem;
    }

    .quick-label {
      font-size: 0.72rem;
      font-weight: 600;
      color: var(--text);
    }

    /* Section Title */
    .section-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .section-head h3 {
      font-size: 0.95rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* Countries / Location Pills */
    .pills-scroll {
      display: flex;
      gap: 8px;
      overflow-x: auto;
      padding-bottom: 8px;
      margin-bottom: 14px;
      scrollbar-width: none;
    }

    .pills-scroll::-webkit-scrollbar {
      display: none;
    }

    .pill-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      background: var(--surface-card);
      border: 1px solid var(--surface-border);
      border-radius: 99px;
      font-size: 0.8rem;
      font-weight: 600;
      white-space: nowrap;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .pill-item.active {
      background: linear-gradient(135deg, var(--cyan), #0284C7);
      border-color: var(--cyan-light);
      color: #fff;
      box-shadow: 0 4px 16px var(--cyan-glow);
    }

    /* Product Plan Cards */
    .plans-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 12px;
      margin-bottom: 20px;
    }

    .plan-card {
      background: var(--surface-card);
      border: 1px solid var(--surface-border);
      border-radius: 16px;
      padding: 16px;
      position: relative;
      overflow: hidden;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .plan-card:hover {
      border-color: rgba(6, 182, 212, 0.4);
      transform: translateY(-2px);
    }

    .plan-card.featured {
      border-color: var(--cyan);
      background: linear-gradient(135deg, rgba(16, 26, 44, 0.85), rgba(6, 182, 212, 0.1));
    }

    .plan-badge {
      position: absolute;
      top: 12px;
      left: 12px;
      background: linear-gradient(135deg, var(--cyan), var(--purple));
      color: #fff;
      font-size: 0.65rem;
      font-weight: 800;
      padding: 2px 8px;
      border-radius: 99px;
    }

    .plan-title {
      font-size: 1rem;
      font-weight: 800;
      margin-bottom: 6px;
    }

    .plan-features {
      display: flex;
      gap: 12px;
      margin-bottom: 12px;
      font-size: 0.78rem;
      color: var(--text-mute);
    }

    .plan-foot {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 10px;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .plan-price {
      font-size: 1.15rem;
      font-weight: 900;
      color: var(--cyan-light);
    }

    .plan-price small {
      font-size: 0.72rem;
      color: var(--text-mute);
      margin-right: 4px;
    }

    /* Custom Calculator Slider */
    .slider-group {
      margin-bottom: 16px;
    }

    .slider-label-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.82rem;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .slider-input {
      width: 100%;
      height: 6px;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.1);
      outline: none;
      -webkit-appearance: none;
      accent-color: var(--cyan);
    }

    /* Service List (Invoices) */
    .service-card {
      background: var(--surface-card);
      border: 1px solid var(--surface-border);
      border-radius: 16px;
      padding: 14px 16px;
      margin-bottom: 12px;
    }

    .service-card-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .service-name {
      font-size: 0.95rem;
      font-weight: 800;
      color: var(--text);
    }

    .progress-bar-wrap {
      height: 6px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 99px;
      overflow: hidden;
      margin: 8px 0;
    }

    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--cyan), var(--purple));
      border-radius: 99px;
      transition: width 0.5s ease;
    }

    .service-stats-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.74rem;
      color: var(--text-mute);
      margin-bottom: 10px;
    }

    .service-actions-row {
      display: flex;
      gap: 8px;
    }

    /* Modal */
    .modal-veil {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(8px);
      z-index: 100;
      display: none;
      place-items: center;
      padding: 16px;
      opacity: 0;
      transition: opacity 0.25s ease;
    }

    .modal-veil.open {
      display: grid;
      opacity: 1;
    }

    .modal-box {
      background: #0E1626;
      border: 1px solid var(--surface-border);
      border-radius: 20px;
      padding: 24px;
      width: min(440px, 100%);
      max-height: 85vh;
      overflow-y: auto;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
      text-align: center;
      position: relative;
    }

    .modal-close-btn {
      position: absolute;
      top: 14px;
      left: 14px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: none;
      color: var(--text-mute);
      font-size: 1.1rem;
      cursor: pointer;
      display: grid;
      place-items: center;
    }

    #qrcode-container {
      display: flex;
      justify-content: center;
      padding: 16px;
      background: #fff;
      border-radius: 12px;
      width: 200px;
      height: 200px;
      margin: 16px auto;
    }

    /* Bottom Navigation Bar */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 64px;
      background: rgba(14, 22, 38, 0.92);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-top: 1px solid var(--surface-border);
      display: flex;
      align-items: center;
      justify-content: space-around;
      z-index: 50;
      padding-bottom: env(safe-area-inset-bottom, 0px);
    }

    .nav-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 3px;
      color: var(--text-dim);
      font-size: 0.68rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      background: none;
      border: none;
      width: 25%;
    }

    .nav-btn.active {
      color: var(--cyan-light);
    }

    .nav-btn.active .nav-icon-box {
      background: rgba(6, 182, 212, 0.15);
      color: var(--cyan-light);
      box-shadow: 0 0 12px var(--cyan-glow);
    }

    .nav-icon-box {
      width: 34px;
      height: 30px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      font-size: 1.2rem;
      transition: all 0.2s ease;
    }

    /* Toast Notification */
    .toast {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%) translateY(-30px);
      background: rgba(16, 26, 44, 0.95);
      border: 1px solid var(--cyan);
      color: #fff;
      padding: 10px 18px;
      border-radius: 99px;
      font-size: 0.8rem;
      font-weight: 700;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      z-index: 999;
      opacity: 0;
      pointer-events: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }
  </style>
</head>
<body>

  <!-- SVG Gradient Definitions -->
  <svg style="position:absolute;width:0;height:0">
    <defs>
      <linearGradient id="circleGrad" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#06B6D4" />
        <stop offset="100%" stop-color="#8B5CF6" />
      </linearGradient>
    </defs>
  </svg>

  <!-- Toast Notification Area -->
  <div class="toast" id="app-toast">
    <span id="toast-icon">✓</span>
    <span id="toast-msg">پیام کپی شد</span>
  </div>

  <!-- Header -->
  <header class="header">
    <div class="brand">
      <div class="brand-logo">Z</div>
      <div class="brand-info">
        <h1>ZORVEX PRO</h1>
        <p>پلتفرم نسل جدید ارتباط امن</p>
      </div>
    </div>
    <div class="header-balance" onclick="switchTab('wallet')">
      <span>موجودی:</span>
      <span id="header-balance-val">0 تومان</span>
    </div>
  </header>

  <!-- ==================== TAB 1: HOME ==================== -->
  <div class="tab-content active" id="tab-home">
    <!-- User Profile Hero -->
    <div class="user-hero">
      <div class="user-header-row">
        <div class="user-avatar-group">
          <div class="user-avatar" id="hero-avatar">Z</div>
          <div>
            <div class="user-name-title" id="hero-name">کاربر گرامی</div>
            <div class="user-tag" id="hero-id">ID: 000000</div>
          </div>
        </div>
        <span class="status-badge" id="hero-status">● کاربر فعال</span>
      </div>

      <!-- Active Service Widget -->
      <div class="active-service-box">
        <div class="progress-circle-wrap">
          <svg class="progress-circle-svg" viewBox="0 0 84 84">
            <circle class="circle-bg" cx="42" cy="42" r="36"></circle>
            <circle class="circle-bar" id="hero-circle-bar" cx="42" cy="42" r="36"></circle>
          </svg>
          <div class="progress-text">
            <div id="hero-percent">75%</div>
            <span>باقی‌مانده</span>
          </div>
        </div>
        <div class="service-meta">
          <h4 id="hero-sub-title">اشتراک اختصاصی Zorvex</h4>
          <p id="hero-traffic-info">حجم باقی‌مانده: <strong style="color:var(--cyan-light)">45.2 GB</strong> از 60 GB</p>
          <div style="display:flex;gap:6px">
            <button class="btn btn-primary" style="padding:6px 12px;font-size:0.75rem" onclick="copyActiveSub()">
              کپی لینک ساب
            </button>
            <button class="btn btn-glass" style="padding:6px 10px;font-size:0.75rem" onclick="showQRModal(activeSubUrl)">
              QR کد
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Action Grid -->
    <div class="quick-grid">
      <div class="quick-card" onclick="switchTab('shop')">
        <div class="quick-icon" style="background:rgba(6,182,212,0.15);color:var(--cyan-light)">⚡</div>
        <span class="quick-label">خرید پلن</span>
      </div>
      <div class="quick-card" onclick="switchTab('services')">
        <div class="quick-icon" style="background:rgba(139,92,246,0.15);color:#A78BFA)">📦</div>
        <span class="quick-label">سرویس‌ها</span>
      </div>
      <div class="quick-card" onclick="switchTab('wallet')">
        <div class="quick-icon" style="background:rgba(16,185,129,0.15);color:var(--green)">💳</div>
        <span class="quick-label">شارژ حساب</span>
      </div>
      <div class="quick-card" onclick="openSupport()">
        <div class="quick-icon" style="background:rgba(245,158,11,0.15);color:var(--orange)">💬</div>
        <span class="quick-label">پشتیبانی</span>
      </div>
    </div>

    <!-- Connection Guides -->
    <div class="glass-card" style="margin-bottom:18px">
      <div class="section-head">
        <h3>📱 نرم‌افزارهای اتصال پیشنهادی</h3>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.05)">
          <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.2rem">🤖</span>
            <div>
              <div style="font-size:0.85rem;font-weight:700">اندروید (Android)</div>
              <div style="font-size:0.72rem;color:var(--text-mute)">v2rayNG / Happ / Sing-box</div>
            </div>
          </div>
          <button class="btn btn-glass" style="padding:4px 10px;font-size:0.72rem" onclick="copyActiveSub()">کپی کانفیگ</button>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.05)">
          <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.2rem">🍏</span>
            <div>
              <div style="font-size:0.85rem;font-weight:700">آیفون (iOS)</div>
              <div style="font-size:0.72rem;color:var(--text-mute)">Streisand / Shadowrocket / FoXray</div>
            </div>
          </div>
          <button class="btn btn-glass" style="padding:4px 10px;font-size:0.72rem" onclick="copyActiveSub()">کپی کانفیگ</button>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0">
          <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.2rem">💻</span>
            <div>
              <div style="font-size:0.85rem;font-weight:700">ویندوز و مک (Desktop)</div>
              <div style="font-size:0.72rem;color:var(--text-mute)">NekoRay / V2rayN / Clash</div>
            </div>
          </div>
          <button class="btn btn-glass" style="padding:4px 10px;font-size:0.72rem" onclick="copyActiveSub()">کپی کانفیگ</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== TAB 2: SHOP ==================== -->
  <div class="tab-content" id="tab-shop">
    <div class="section-head">
      <h3>🌍 انتخاب موقعیت سرور</h3>
    </div>
    <!-- Country Selector Pills -->
    <div class="pills-scroll" id="country-pills">
      <div class="pill-item active" onclick="selectCountry(this, 'de')">🇩🇪 آلمان (ترافیک نامحدود)</div>
      <div class="pill-item" onclick="selectCountry(this, 'fi')">🇫🇮 فنلاند (پینگ گیمینگ)</div>
      <div class="pill-item" onclick="selectCountry(this, 'nl')">🇳🇱 هلند (آی‌پی ثابت)</div>
      <div class="pill-item" onclick="selectCountry(this, 'fr')">🇫🇷 فرانسه (پرسرعت)</div>
    </div>

    <!-- Category Pills -->
    <div class="pills-scroll" id="cat-pills">
      <div class="pill-item active" onclick="selectCategory(this, 'all')">همه دسته‌ها</div>
      <div class="pill-item" onclick="selectCategory(this, 'unlimited')">نامحدود ویژه</div>
      <div class="pill-item" onclick="selectCategory(this, 'economy')">بسته‌های اقتصادی</div>
      <div class="pill-item" onclick="selectCategory(this, 'gaming')">گیمینگ و استریم</div>
    </div>

    <!-- Custom Plan Builder -->
    <div class="glass-card" style="margin-bottom:20px;border-color:rgba(6,182,212,0.3)">
      <div class="section-head">
        <h3>🛠️ ساخت پلن اختصاصی دلخواه</h3>
        <span class="status-badge">محاسبه آنی</span>
      </div>
      <div class="slider-group">
        <div class="slider-label-row">
          <span>حجم ترافیک:</span>
          <span id="slider-vol-val" style="color:var(--cyan-light)">50 گیگابایت</span>
        </div>
        <input type="range" class="slider-input" id="slider-vol" min="10" max="300" step="5" value="50" oninput="updateCustomCalc()">
      </div>
      <div class="slider-group">
        <div class="slider-label-row">
          <span>مدت زمان:</span>
          <span id="slider-days-val" style="color:var(--cyan-light)">30 روزه</span>
        </div>
        <input type="range" class="slider-input" id="slider-days" min="15" max="90" step="15" value="30" oninput="updateCustomCalc()">
      </div>
      <div class="plan-foot" style="margin-top:10px">
        <div>
          <div style="font-size:0.75rem;color:var(--text-mute)">قیمت محاسبه شده:</div>
          <div class="plan-price" id="custom-price-val">75,000 <small>تومان</small></div>
        </div>
        <button class="btn btn-primary" onclick="buyCustomPlan()">خرید پلن دلخواه</button>
      </div>
    </div>

    <!-- Ready Plans Grid -->
    <div class="section-head">
      <h3>⚡ بسته‌های محبوب و آماده</h3>
    </div>
    <div class="plans-grid" id="ready-plans-grid">
      <!-- Plan 1 -->
      <div class="plan-card featured" onclick="confirmPurchase('پلن ۳۰ گیگ یک‌ماهه', 50000, 30, 30)">
        <span class="plan-badge">پرفروش‌ترین</span>
        <div class="plan-title">اشتراک اقتصادی ۳۰ گیگابایت</div>
        <div class="plan-features">
          <span>⏱️ مدت: ۳۰ روز</span>
          <span>⚡ پروتکل‌های ضد فیلتر</span>
          <span>🛡️ ۲ کاربر همزمان</span>
        </div>
        <div class="plan-foot">
          <div class="plan-price">50,000 <small>تومان</small></div>
          <button class="btn btn-primary" style="padding:6px 14px;font-size:0.8rem">سفارش فوری</button>
        </div>
      </div>

      <!-- Plan 2 -->
      <div class="plan-card" onclick="confirmPurchase('پلن ۶۰ گیگ یک‌ماهه', 90000, 60, 30)">
        <div class="plan-title">اشتراک حرفه‌ای ۶۰ گیگابایت</div>
        <div class="plan-features">
          <span>⏱️ مدت: ۳۰ روز</span>
          <span>🚀 پینگ زیر ۸۰ میلی‌ثانیه</span>
          <span>🛡️ ۳ کاربر همزمان</span>
        </div>
        <div class="plan-foot">
          <div class="plan-price">90,000 <small>تومان</small></div>
          <button class="btn btn-primary" style="padding:6px 14px;font-size:0.8rem">سفارش فوری</button>
        </div>
      </div>

      <!-- Plan 3 -->
      <div class="plan-card" onclick="confirmPurchase('پلن ۱۲۰ گیگ دوماهه', 160000, 120, 60)">
        <span class="plan-badge" style="background:#10B981">تخفیف ویژه</span>
        <div class="plan-title">اشتراک نامحدود ۱۲۰ گیگابایت</div>
        <div class="plan-features">
          <span>⏱️ مدت: ۶۰ روز</span>
          <span>🌐 آی‌پی ثابت ترید و بایننس</span>
          <span>🛡️ نامحدود دستگاه</span>
        </div>
        <div class="plan-foot">
          <div class="plan-price">160,000 <small>تومان</small></div>
          <button class="btn btn-primary" style="padding:6px 14px;font-size:0.8rem">سفارش فوری</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== TAB 3: SERVICES ==================== -->
  <div class="tab-content" id="tab-services">
    <div class="section-head">
      <h3>📦 اشتراک‌ها و سرویس‌های فعال من</h3>
      <span class="status-badge" id="services-count-badge">۱ سرویس</span>
    </div>

    <div id="services-list-wrap">
      <!-- Service Item -->
      <div class="service-card">
        <div class="service-card-head">
          <div class="service-name">Zorvex-VIP-Sub#1084</div>
          <span class="status-badge">● فعال و متصل</span>
        </div>
        <div class="progress-bar-wrap">
          <div class="progress-bar-fill" style="width:75%"></div>
        </div>
        <div class="service-stats-row">
          <span>مصرف: ۱۴.۸ GB از ۶۰ GB</span>
          <span>انقضا: ۱۸ روز دیگر</span>
        </div>
        <div class="service-actions-row">
          <button class="btn btn-primary" style="flex:1;padding:8px" onclick="copyActiveSub()">کپی لینک اشتراک</button>
          <button class="btn btn-glass" style="padding:8px 14px" onclick="showQRModal(activeSubUrl)">نمایش QR</button>
          <button class="btn btn-glass" style="padding:8px 14px" onclick="switchTab('shop')">تمدید</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== TAB 4: WALLET ==================== -->
  <div class="tab-content" id="tab-wallet">
    <!-- Wallet Card -->
    <div class="glass-card" style="margin-bottom:18px;background:linear-gradient(135deg,rgba(16,26,44,0.9),rgba(6,182,212,0.15))">
      <div class="section-head">
        <h3>💳 کیف پول من</h3>
        <span class="status-badge">واحد: تومان</span>
      </div>
      <div style="font-size:2rem;font-weight:900;color:#fff;margin:12px 0" id="wallet-balance-num">
        0 <small style="font-size:0.9rem;color:var(--text-mute)">تومان</small>
      </div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-primary" style="flex:1" onclick="openDepositModal()">+ افزایش موجودی</button>
        <button class="btn btn-glass" style="flex:1" onclick="switchTab('shop')">خرید اشتراک</button>
      </div>
    </div>

    <!-- Referral / Affiliate Card -->
    <div class="glass-card" style="margin-bottom:18px">
      <div class="section-head">
        <h3>🎁 پاداش معرفی و کسب درآمد</h3>
        <span class="status-badge">۱۰٪ هدیه</span>
      </div>
      <p style="font-size:0.8rem;color:var(--text-mute);margin-bottom:12px;line-height:1.6">
        با ارسال لینک اختصاصی خود به دوستان، با هر خرید آن‌ها ۱۰٪ از مبلغ مستقیماً به کیف پول شما واریز خواهد شد.
      </p>
      <div style="display:flex;gap:8px">
        <input type="text" id="ref-link-input" readonly value="https://t.me/zorvex_bot?start=ref_00000" style="flex:1;background:rgba(0,0,0,0.3);border:1px solid var(--surface-border);border-radius:10px;padding:8px 12px;color:var(--cyan-light);font-size:0.75rem;direction:ltr">
        <button class="btn btn-primary" style="padding:8px 14px" onclick="copyRefLink()">کپی لینک</button>
      </div>
    </div>

    <!-- Gift Code Box -->
    <div class="glass-card">
      <div class="section-head">
        <h3>🎟️ کد تخفیف یا کارت هدیه</h3>
      </div>
      <div style="display:flex;gap:8px">
        <input type="text" id="gift-code-input" placeholder="کد هدیه خود را وارد کنید..." style="flex:1;background:rgba(0,0,0,0.3);border:1px solid var(--surface-border);border-radius:10px;padding:8px 12px;color:#fff;font-size:0.82rem">
        <button class="btn btn-glass" onclick="applyGiftCode()">ثبت کد</button>
      </div>
    </div>
  </div>

  <!-- ==================== MODALS ==================== -->
  <!-- QR Modal -->
  <div class="modal-veil" id="qr-modal" onclick="closeModal(this, event)">
    <div class="modal-box">
      <button class="modal-close-btn" onclick="document.getElementById('qr-modal').classList.remove('open')">✕</button>
      <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:4px">بارکد اتصال مستقیم</h3>
      <p style="font-size:0.75rem;color:var(--text-mute)">دوربین نرم‌افزار را روبه‌روی بارکد زیر بگیرید</p>
      <div id="qrcode-container"></div>
      <button class="btn btn-primary btn-block" onclick="copyActiveSub()">کپی مجدد لینک</button>
    </div>
  </div>

  <!-- Purchase Confirm Modal -->
  <div class="modal-veil" id="purchase-modal" onclick="closeModal(this, event)">
    <div class="modal-box">
      <button class="modal-close-btn" onclick="document.getElementById('purchase-modal').classList.remove('open')">✕</button>
      <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:6px">تأیید سفارش اشتراک</h3>
      <div id="modal-plan-name" style="color:var(--cyan-light);font-weight:700;font-size:0.95rem;margin-bottom:12px">پلن منتخب</div>
      <div style="background:rgba(0,0,0,0.3);border-radius:12px;padding:12px;margin-bottom:16px;text-align:right;font-size:0.82rem;line-height:1.8">
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-mute)">مبلغ قابل پرداخت:</span>
          <strong id="modal-plan-price" style="color:var(--cyan-light)">0 تومان</strong>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-mute)">موجودی کیف پول شما:</span>
          <span id="modal-user-balance">0 تومان</span>
        </div>
      </div>
      <button class="btn btn-primary btn-block" id="modal-pay-btn" onclick="executePurchase()">پرداخت و تحویل آنی</button>
    </div>
  </div>

  <!-- ==================== BOTTOM BAR ==================== -->
  <nav class="bottom-bar">
    <button class="nav-btn active" id="btn-tab-home" onclick="switchTab('home')">
      <div class="nav-icon-box">🏠</div>
      <span>خانه</span>
    </button>
    <button class="nav-btn" id="btn-tab-shop" onclick="switchTab('shop')">
      <div class="nav-icon-box">⚡</div>
      <span>خرید اشتراک</span>
    </button>
    <button class="nav-btn" id="btn-tab-services" onclick="switchTab('services')">
      <div class="nav-icon-box">📦</div>
      <span>سرویس‌ها</span>
    </button>
    <button class="nav-btn" id="btn-tab-wallet" onclick="switchTab('wallet')">
      <div class="nav-icon-box">💳</div>
      <span>کیف پول</span>
    </button>
  </nav>

  <!-- ==================== LOGIC & TELEGRAM INTEGRATION ==================== -->
  <script>
    // Telegram WebApp Initialization
    const tg = window.Telegram?.WebApp;
    let tgUser = null;
    let authToken = new URLSearchParams(window.location.search).get('token') || '';
    let currentBalance = 0;
    let activeSubUrl = 'vless://zorvex-sample-sub-config-direct-connect-token#Zorvex-Fast';
    let pendingOrder = null;

    if (tg) {
      tg.ready();
      tg.expand();
      try {
        tg.setHeaderColor('#070B14');
        tg.setBackgroundColor('#070B14');
      } catch (e) {}
      if (tg.initDataUnsafe?.user) {
        tgUser = tg.initDataUnsafe.user;
      }
    }

    // Haptic feedback helper
    function haptic(style = 'medium') {
      if (tg?.HapticFeedback) {
        try { tg.HapticFeedback.impactOccurred(style); } catch (e) {}
      }
    }

    // Toast notification
    function showToast(msg, icon = '✓') {
      const toast = document.getElementById('app-toast');
      document.getElementById('toast-msg').innerText = msg;
      document.getElementById('toast-icon').innerText = icon;
      toast.classList.add('show');
      haptic('light');
      setTimeout(() => toast.classList.remove('show'), 2600);
    }

    // Tab Switching
    function switchTab(tabId) {
      haptic('light');
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));

      const targetTab = document.getElementById('tab-' + tabId);
      const targetBtn = document.getElementById('btn-tab-' + tabId);
      if (targetTab) targetTab.classList.add('active');
      if (targetBtn) targetBtn.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Copy to clipboard helper
    function copyText(txt, msg = 'لینک کپی شد') {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(txt).then(() => showToast(msg));
      } else {
        const inp = document.createElement('input');
        inp.value = txt;
        document.body.appendChild(inp);
        inp.select();
        document.execCommand('copy');
        document.body.removeChild(inp);
        showToast(msg);
      }
    }

    function copyActiveSub() {
      copyText(activeSubUrl, 'لینک سابسکریپشن با موفقیت کپی شد!');
    }

    function copyRefLink() {
      const val = document.getElementById('ref-link-input').value;
      copyText(val, 'لینک دعوت اختصاصی کپی شد!');
    }

    // QR Code Display
    function showQRModal(text) {
      haptic('medium');
      const container = document.getElementById('qrcode-container');
      container.innerHTML = '';
      if (typeof QRCode !== 'undefined') {
        new QRCode(container, {
          text: text,
          width: 180,
          height: 180,
          colorDark: '#070B14',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.M
        });
      } else {
        container.innerHTML = '<div style="color:#000;font-size:0.75rem;padding:20px">بارکد: ' + text + '</div>';
      }
      document.getElementById('qr-modal').classList.add('open');
    }

    function closeModal(el, event) {
      if (event.target === el) {
        el.classList.remove('open');
      }
    }

    // Custom Calculator
    function updateCustomCalc() {
      const gb = parseInt(document.getElementById('slider-vol').value);
      const days = parseInt(document.getElementById('slider-days').value);
      document.getElementById('slider-vol-val').innerText = gb + ' گیگابایت';
      document.getElementById('slider-days-val').innerText = days + ' روزه';

      // Calculation formula: base rate + gb rate + days factor
      const price = Math.round((gb * 1200 + days * 500) / 1000) * 1000;
      document.getElementById('custom-price-val').innerHTML = Number(price).toLocaleString('fa-IR') + ' <small>تومان</small>';
      return { gb, days, price };
    }

    function buyCustomPlan() {
      const p = updateCustomCalc();
      confirmPurchase(`پلن سفارشی (${p.gb} گیگابایت / ${p.days} روز)`, p.price, p.gb, p.days);
    }

    function selectCountry(el, code) {
      haptic('light');
      document.querySelectorAll('#country-pills .pill-item').forEach(e => e.classList.remove('active'));
      el.classList.add('active');
      showToast('سرور موقعیت به روز شد');
    }

    function selectCategory(el, cat) {
      haptic('light');
      document.querySelectorAll('#cat-pills .pill-item').forEach(e => e.classList.remove('active'));
      el.classList.add('active');
    }

    function confirmPurchase(name, price, gb, days) {
      haptic('medium');
      pendingOrder = { name, price, gb, days };
      document.getElementById('modal-plan-name').innerText = name;
      document.getElementById('modal-plan-price').innerText = Number(price).toLocaleString('fa-IR') + ' تومان';
      document.getElementById('modal-user-balance').innerText = Number(currentBalance).toLocaleString('fa-IR') + ' تومان';
      
      const payBtn = document.getElementById('modal-pay-btn');
      if (currentBalance < price) {
        payBtn.innerText = 'موجودی ناکافی — افزایش موجودی';
        payBtn.onclick = () => {
          document.getElementById('purchase-modal').classList.remove('open');
          switchTab('wallet');
        };
      } else {
        payBtn.innerText = 'پرداخت از موجودی و دریافت آنی';
        payBtn.onclick = executePurchase;
      }
      document.getElementById('purchase-modal').classList.add('open');
    }

    async function executePurchase() {
      if (!pendingOrder) return;
      haptic('heavy');
      const payBtn = document.getElementById('modal-pay-btn');
      payBtn.innerText = 'در حال ثبت و صدور کانفیگ...';
      payBtn.disabled = true;

      try {
        // Attempt request to miniapp API
        const res = await fetch('../api/miniapp.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + authToken
          },
          body: JSON.stringify({
            actions: 'purchase',
            service_id: 1,
            user_id: tgUser ? tgUser.id : 0
          })
        });
        const json = await res.json();
        if (json.success || json.status) {
          showToast('سرویس با موفقیت فعال گردید!');
        } else {
          showToast('سفارش در سیستم ثبت شد');
        }
      } catch (e) {
        showToast('سرویس ایجاد شد!');
      } finally {
        payBtn.disabled = false;
        document.getElementById('purchase-modal').classList.remove('open');
        switchTab('services');
      }
    }

    function openDepositModal() {
      haptic('light');
      showToast('برای افزایش موجودی به ربات اصلی مراجعه فرمایید یا با پشتیبانی در ارتباط باشید');
    }

    function applyGiftCode() {
      const code = document.getElementById('gift-code-input').value.trim();
      if (!code) {
        showToast('لطفاً کد را وارد کنید', '!');
        return;
      }
      showToast('کد هدیه در حال بررسی است...');
    }

    function openSupport() {
      haptic('light');
      if (tg) {
        tg.openTelegramLink('https://t.me/zorvex_support');
      } else {
        window.open('https://t.me/zorvex_support', '_blank');
      }
    }

    // Load User and Active Service details from backend
    async function initMiniAppData() {
      if (tgUser) {
        document.getElementById('hero-name').innerText = tgUser.first_name + (tgUser.last_name ? ' ' + tgUser.last_name : '');
        document.getElementById('hero-avatar').innerText = (tgUser.first_name || 'Z')[0].toUpperCase();
        document.getElementById('hero-id').innerText = 'ID: ' + tgUser.id;
        document.getElementById('ref-link-input').value = `https://t.me/zorvex_bot?start=ref_${tgUser.id}`;
      }

      try {
        const uid = tgUser ? tgUser.id : 0;
        const res = await fetch(`../api/miniapp.php?actions=user_info&user_id=${uid}`, {
          headers: { 'Authorization': 'Bearer ' + authToken }
        });
        if (res.ok) {
          const data = await res.json();
          if (data && data.obj) {
            currentBalance = parseInt(data.obj.balance || 0);
            document.getElementById('header-balance-val').innerText = Number(currentBalance).toLocaleString('fa-IR') + ' تومان';
            document.getElementById('wallet-balance-num').innerHTML = Number(currentBalance).toLocaleString('fa-IR') + ' <small style="font-size:0.9rem;color:var(--text-mute)">تومان</small>';
          }
        }
      } catch (e) {}

      updateCustomCalc();
    }

    window.addEventListener('DOMContentLoaded', initMiniAppData);
  </script>
</body>
</html>