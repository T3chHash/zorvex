<div align="center">

```
  ____   ____  _____ __      __ ______ __   __
 |___  // __ \|  __ \\ \    / /|  ____|\ \ / /
    / /| |  | | |__) |\ \  / / | |__    \ V / 
   / / | |  | |  _  /  \ \/ /  |  __|    > <  
  / /__| |__| | | \ \   \  /   | |____  / . \ 
 /_____\____/|_|  \_\   \/    |______|/_/ \_\
```

### ⚡ Next-Generation Enterprise VPN Sales & Node Management Platform ⚡
**Full-Featured Telegram Mini App • Advanced Analytics Admin Panel • Multi-Node Orchestration**

---

[![PHP Version](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Release](https://img.shields.io/badge/Release-v1.0.3-00f2fe?style=for-the-badge&logo=github)](https://github.com/T3chHash/zorvex/releases)
[![Telegram Mini App](https://img.shields.io/badge/Telegram-Mini%20App%20Ready-26A5E4?style=for-the-badge&logo=telegram&logoColor=white)](https://telegram.org)
[![Supported OS](https://img.shields.io/badge/Ubuntu-20.04%20%7C%2022.04%20%7C%2024.04-E95420?style=for-the-badge&logo=ubuntu&logoColor=white)](https://ubuntu.com)
[![License](https://img.shields.io/badge/License-GPL%20v3-green?style=for-the-badge)](LICENSE)

[English](#-english-overview) • [فارسی](#-معرفی-پلتفرم-زوروکس-پرو) • [Installation](#-quick-installation) • [Mini App Features](#-telegram-mini-app-capabilities) • [Admin Panel](#-command-center-admin-panel)

---

</div>

<a name="-english-overview"></a>
## 🚀 English Overview

**Zorvex Pro** is an all-in-one enterprise ecosystem designed for high-scale VPN providers, cloud administrators, and digital service resellers. By bridging Telegram's viral distribution channels with an ultra-responsive, cyber-dark WebApp Single-Page Application (SPA) and an administrative command center, Zorvex empowers sellers to automate customer acquisition, traffic accounting, node distribution, and payment settlement without technical friction.

### 🌟 Core Architectural Highlights
- **Full Telegram Mini App Integration:** Native mobile-first web app accessible directly inside Telegram with zero authentication overhead, instant subscriptions, and interactive plan builders.
- **Unified Multi-Node Engine:** Effortlessly orchestrate and distribute load across multiple node managers: **Marzban**, **X-UI / 3X-UI**, **Remnawave**, **PasarGuard**, and **Hiddify**.
- **Self-Healing Webhook & Diagnostics:** Zero-downtime terminal monitor with real-time SSL expiry checks, webhook status validation, and automated queue recovery.
- **Autonomous Financial Automation:** Support for card-to-card receipt OCR/manual approvals, instant Iranian payment gateways (ZarinPal, NextPay, Aqayepardakht), and decentralized cryptocurrencies (USDT-TRC20, TON, BTC).

---

<a name="-معرفی-پلتفرم-زوروکس-پرو"></a>
## 💎 معرفی پلتفرم زوروکس پرو (نسخه پایدار 1.0.3)

**پلتفرم زوروکس (Zorvex Pro)** نسل نوینی از سیستم‌های مدیریت، فروش و مانیتورینگ سرویس‌های وی‌پی‌ان بر بستر پیام‌رسان تلگرام است. این پلتفرم با ترکیب رابط کاربری مدرن نئونی، مینی‌اپلیکیشن مستقل تک‌صفحه‌ای (SPA) و پنل مدیریتی غنی، تمامی نیازهای یک سرور و ارائه‌دهنده سرویس اینترنت آزاد را پوشش می‌دهد.

### ✨ امکانات و قابلیت‌های متمایز نسخه 1.0.3:
1. **رفع کامل مشکل ورود و خطای پارامتر دیتابیس (SQLSTATE[HY093] & Auth Fix):**
   - استانداردسازی کامل تمامی کوئری‌های پنل ادمین به فرمت پارامترهای پوزیشنی و حذف باگ ناسازگاری پارامترهای نام‌گذاری شده تکراری.
   - اصلاح اساسی نحوه ذخیره و ارسال کوکی Session (`path=/; samesite=Lax; httponly`) جهت جلوگیری از لوپ ریدایرکت.
   - حذف تاخیرها و توابع مسدودکننده حین فرآیند ورود برای انتقال آنی و بی‌نقص به داشبورد ادمین.
   - قابلیت مشاهده و تغییر آنی رمز عبور ادمین از ترمینال لینوکس با دستور `zorvex pass` یا انتخاب گزینه `[3]` در منوی اصلی.
   - رفع کامل خطاهای Fatal 500 در پاسخ به تیکت‌ها و تست اکانت‌ها با توابع کمکی یکپارچه.

2. **مینی‌اپلیکیشن تلگرام (Telegram Mini App):**
   - تم سایبردارک اختصاصی با قابلیت انتخاب رنگ‌های نئونی پویا (Cyan, Electric Violet, Emerald, Crimson).
   - رینگ گرافیکی و داینامیک مصرف ترافیک لحظه‌ای با محاسبه گر روزهای باقیمانده.
   - کیوآرکد (QR Code) اختصاصی، کپی آنی کانفیگ، و دکمه اتصال مستقیم به کلاینت‌های V2Ray, Clash, Sing-box و Shadowrocket.
   - اسلایدر هوشمند محاسبه قیمت بسته سفارشی (ترافیک دلخواه + مدت زمان دلخواه).
   - کیف پول داخلی کاربر با قابلیت شارژ مستقیم، کارت به کارت و پرداخت ارز دیجیتال (TRC20 / TON).
   - سیستم پشتیبانی تیکتینگ یکپارچه درون مینی‌اپلیکیشن.

3. **داشبورد پیشرفته مدیریت (Zorvex Pro Panel):**
   - نمودارهای فروش و عضویت لحظه‌ای مبتنی بر Chart.js (فیلترهای ۲۴ ساعت، ۷ روز، ۳ ماه و بازه سفارشی).
   - مانیتورینگ سلامت نودها و سرورها به صورت تفکیک‌شده.
   - مدیریت کامل سرویس‌ها (تمدید، قطع موقت، ویرایش حجم، انتقال به کاربر دیگر).
   - سیستم پیشرفته کدهای تخفیف، بازاریابی معرف (افیلیت) و کمیسیون خودکار.
   - پنل تنظیمات ظاهر (`appearance.php`) جهت شخصی‌سازی کامل برند و پالت‌های رنگی.

4. **ترمینال هوشمند لینوکس (CLI Installer & Updater):**
   - فرآیند آپدیت فوق‌العاده سریع و روان تنها با انتخاب گزینه ۲ در منوی `zorvex`.
   - پشتیبان‌گیری خودکار از دیتابیس و فایل‌های پیکربندی در هر آپدیت.
   - دانلود چندلایه و فال‌بک امن برای تضمین موفقیت آپدیت در شرایط اینترنت ناپایدار.
   - تمدید خودکار گواهی امنیتی SSL و عیب‌یابی وب‌هوک تلگرام.

---

<a name="-telegram-mini-app-capabilities"></a>
## 📱 Telegram Mini App Capabilities

The Zorvex Mini App lives inside the `/app` directory and runs as a modern, decoupled client communicating with `/api/miniapp.php`:

```
┌─────────────────────────────────────────────────────────────┐
│                    ZORVEX MINI APP (SPA)                    │
├─────────────────────────────────────────────────────────────┤
│  [Tab 1: Home]                                              │
│  • Circular SVG traffic usage ring (% consumed / left)     │
│  • Expiration countdown & status badge                     │
│  • One-touch Quick Copy & Interactive QR modal             │
│  • Server node selection & latency indicator               │
├─────────────────────────────────────────────────────────────┤
│  [Tab 2: Services]                                          │
│  • Unified subscription card list                           │
│  • Sub-link renewal & protocol switcher                    │
├─────────────────────────────────────────────────────────────┤
│  [Tab 3: Smart Shop]                                        │
│  • Preset categories (High Speed, VIP, Gaming, Unlimited)   │
│  • Interactive Custom Slider (Volume GB × Validity Days)    │
│  • Instant coupon code discount verification               │
├─────────────────────────────────────────────────────────────┤
│  [Tab 4: Account & Wallet]                                  │
│  • Wallet balance & instant top-up                          │
│  • Direct Gateways / Manual Card Upload / Crypto Direct     │
│  • Affiliate referral link with tier-based commission stats│
│  • Integrated Support Ticket Desk                           │
└─────────────────────────────────────────────────────────────┘
```

---

<a name="-command-center-admin-panel"></a>
## 🖥️ Command Center & Admin Panel

Located at `https://your-domain.com/panel`:

| Module | Features & Description |
| :--- | :--- |
| **📈 Live Analytics** | Real-time interactive sales trend curves, order counts, user acquisition charts, and revenue metrics. |
| **🌐 Node Manager** | Seamlessly connect and sync with Marzban, X-UI, Remnawave, and PasarGuard nodes. |
| **👥 Users & CRM** | Search, ban, unban, adjust wallet balances, view transaction trails, and inspect user configurations. |
| **🛍️ Products & Plans** | Define fixed pricing tiers, custom per-GB rate formulas, and multi-protocol packages. |
| **🎫 Ticket System** | Centralized customer support inbox with attachment handling, canned replies, and notification triggers. |
| **🎨 Appearance Studio**| Customize the primary brand identity, icon marks, logo uploads, and neon color palettes directly from the web browser. |
| **🛡️ Security & Backup** | IP-restricted administrative logins, session token rotation, and 1-click database snapshots pushed to Telegram. |

---

<a name="-quick-installation"></a>
## ⚡ Quick Installation

Run the automated one-line setup script on a clean **Ubuntu 20.04 / 22.04 / 24.04** server:

```bash
bash <(curl -Ls https://raw.githubusercontent.com/T3chHash/zorvex/main/install.sh)
```

### CLI Command Options

The installer automatically links the `zorvex` binary to your system PATH:

```bash
# Open interactive terminal management menu
zorvex

# Perform one-line unattended setup
zorvex install --token "YOUR_TELEGRAM_BOT_TOKEN" --admin "ADMIN_CHAT_ID" --domain "bot.yourdomain.com"

# Check and perform automated system update
zorvex update

# Renew or re-issue Let's Encrypt SSL certificate
zorvex renew

# Backup database and dispatch directly to admin Telegram chat
zorvex backup
```

---

## 🛠️ System Requirements & Architecture

- **Operating System:** Ubuntu 20.04 LTS, 22.04 LTS, or 24.04 LTS (x86_64 / ARM64)
- **Web Server:** Nginx or Apache2 with HTTP/2 and SSL/TLS enabled
- **Backend Runtime:** PHP 8.2 or PHP 8.3 (with `pdo_mysql`, `curl`, `mbstring`, `zip`, `gd`, `bcmath`, `xml`)
- **Database:** MySQL 8.0+ or MariaDB 10.6+
- **Inbound Ports:** 80 (HTTP), 443 (HTTPS)

---

## 🔒 Security & Best Practices

1. **Firewall:** Keep only essential ports (22 for SSH, 80 & 443 for web traffic) open via UFW.
2. **Database Hardening:** The installation script generates unique, high-entropy database passwords and grants restricted privileges.
3. **Webhook Isolation:** Telegram webhooks are secured with secret tokens preventing spoofed requests.
4. **IP Login Guard:** Restrict administrative access to your personal static IP through the Admin Panel Security settings.

---

## 🤝 Community & Support

- **Telegram Updates Channel:** [@zorvexpanel](https://t.me/zorvexpanel)
- **Community & Discussion Group:** [@zorvexpanelgroup](https://t.me/zorvexpanelgroup)
- **Bug Reports & Issues:** [GitHub Issues](https://github.com/T3chHash/zorvex/issues)

---

<div align="center">

**ZORVEX PRO** is crafted with dedication for freedom of information and open communications.  
Licensed under the [GPL-3.0 License](LICENSE).

</div>
