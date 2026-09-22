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
[![Release](https://img.shields.io/badge/Release-v0.0.1--beta-00f2fe?style=for-the-badge&logo=github)](https://github.com/T3chHash/zorvex/releases)
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
## 💎 معرفی پلتفرم زوروکس پرو (نسخه 0.0.1 بتا - پایدار، سریع و آماده انتشار عمومی)

**پلتفرم زوروکس (Zorvex Pro)** نسل نوینی از سیستم‌های مدیریت، فروش و مانیتورینگ سرویس‌های وی‌پی‌ان بر بستر پیام‌رسان تلگرام است. این پلتفرم با ترکیب رابط کاربری مدرن نئونی، مینی‌اپلیکیشن مستقل تک‌صفحه‌ای (SPA) و پنل مدیریتی غنی، تمامی نیازهای یک سرور و ارائه‌دهنده سرویس اینترنت آزاد را پوشش می‌دهد.

### ✨ امکانات و بهبودهای متمایز نسخه 0.0.1 (Beta):
1. **حل قطعی خطای ۵۰۰ و کرش کامل سرور هنگام افزودن محصول در مینی‌اپ و ربات:**
   - تعریف و تثبیت توابع سیستمی مفقود `panel_feature_enabled`، `panel_creation_limit_reached`، `panel_creation_limit_reached_unlocked`، `panel_limit_lock_acquire`، `panel_limit_lock_release` و `rx_product_allows_agent` در هسته ربات و مینی‌اپلیکیشن.
   - جلوگیری از بروز خطای کشنده PHP Fatal Error (`Call to undefined function`) هنگام فراخوانی اندپوینت‌های `countries`، `categories`، `time_ranges`، `services` و `purchase`.
2. **برطرف‌سازی کرش دکمه خرید اشتراک پس از ثبت محصولات در ربات تلگرام:**
   - ایمن‌سازی کامل تابع `KeyboardProduct` و `keyboardTimeCategory` در برابر متغیرهای تهی، مقادیر قیمت فرمت‌نشده و نبود کلیدهای تنظیمی جدول `shopSetting` در PHP 8.2+.
   - اضافه شدن قابلیت داینامیک پشتیبانی از تمامی بازه‌های زمانی محصولات (اعم از ۱ روزه، ۷ روزه، ۱ ماهه، ۳ ماهه و هر تعداد روز سفارشی دیگر) بدون محو شدن محصولات یا ارسال کیبورد خالی.
   - اصلاح کوئری‌های واکشی محصول با `FIND_IN_SET` برای محصولاتی با موقعیت‌های مکانی چندگانه و گروه‌های کاربری مختلف.
3. **همگام‌سازی کامل زیرربات‌ها (`vpnbot/update` و `vpnbot/Default`) با پارامترهای جدید و ایمن‌سازی قیمت‌ها.**
1. **اورهاول کامل و قطعی فرایند «خرید اشتراک» در ربات اصلی و ربات‌های ساب‌بات:**
   - بازنویسی کامل تابع `check_active_btn` با سیستم تشخیص هوشمند مستعارها و برچسب‌های فارسی (`text_sell`, `خرید اشتراک`, `خرید`, `buy`, `سفارش`)؛ حل ریشه‌ای پیغام خطای نادرست «این دکمه غیرفعال است» پس از تغییر چیدمان کیبورد در پنل.
   - اصلاح ساختار کیبورد شیشه‌ای (اینلاین) در `keyboard.php` و نگاشت خودکار کال‌بک دکمه‌ها همراه با درج متون فارسی و برطرف‌سازی خطای `BUTTON_DATA_INVALID` تلگرام.
   - پوشش کامل تمامی دستورات خرید اعم از کلیک روی دکمه متنی، کیبورد شیشه‌ای، کال‌بک‌های اینلاین (`buy`, `buyback`, `buybacktow`) و دستورات متنی (`/buy`, `خرید`).
   - ایمن‌سازی کامل دیکود جی‌سان و آرایه‌های `customvolume`، `pricecustomvolume`، `mainvolume`، `maxvolume`، `pricecustomtime`، `maintime` و `maxtime` در PHP 8.2+.
   - تعمیم و همگام‌سازی کامل اصلاحات خرید اشتراک روی پوشه‌های `vpnbot/update` و `vpnbot/Default`.

2. **رفع ریشه‌ای باگ ثبت و عدم نمایش محصولات در ردیف‌های پنل و ربات (`product.php` & `index.php`):**
   - اصلاح تطابق پارامترهای بیلد کوئری PDO (`:symbolic_limit_users`) که مانع از اجرای INSERT و ثبت محصول در دیتابیس می‌شد.
   - پاکسازی فرمت قیمت از کاما و نویسه‌های جداکننده به صورت خودکار پیش از ذخیره.
   - اصلاح کوئری‌های واکشی محصولات در ربات تلگرام جهت پشتیبانی کامل از محصولات عمومی (`agent = 'all'`) و چند لوکیشنی (`FIND_IN_SET`).
   - افزودن کارت‌های آمار زنده، سیستم اعلان شیشه‌ای زیبا (Flash Messages) و نمایش صفحه خالی شکیل (Empty State).
1. **اصلاح اساسی و فعال‌سازی «انبار شبکه ملی» (`stock.php` & `nm_stock.php`):**
   - رفع خطای Fatal ۵۰۰ ناشی از فراخوانی فایل ناموجود موتور انبار شبکه ملی.
   - ایجاد کامل موتور مستقل انبار شبکه ملی (`re/rx/function/nm_stock.php`) همراه با توابع رزرو هوشمند، تحویل، برگشت و بررسی موجودی.
   - ایجاد و مایگریشن خودکار جداول `nm_stock_shelves` و `nm_config_stock` در صورت عدم وجود در دیتابیس به صورت ۱۰۰٪ تضمینی.

2. **رفع قطعی و خطاهای «درگاه‌ها و امور مالی» (`finance.php`):**
   - حذف نیازمندی به فایل‌های ناموجود و پیشگیری از خطای داخلی سرور در باز کردن یا ذخیره تنظیمات مالی.
   - ایجاد و مقداردهی اولیه خودکار جدول `PaySetting` و رفع ارورهای ذخیره‌سازی درگاه‌های کارت‌به‌کارت، زرین‌پال، NowPayments و کریپتو.

3. **همگام‌سازی کامل «متن‌های ربات» و «دکمه‌های بات» (`textbot.php` & `keyboard.php`):**
   - بارگذاری و تزریق کلیه عنوان‌های فارسی دکمه‌های اصلی ربات (خرید اشتراک، کیف پول، سرویس‌های من، تمدید، اکانت تست، گردونه شانس و...) در جدول `textbot` و ادیتور کیبورد.
   - رفع باگ خالی بودن لیست دکمه‌ها در پنجره ویرایش دکمه و اضافه شدن گزینه «متن دلخواه (دکمه سفارشی)».
   - حل مشکل نمایش کدهای فنی خام به‌جای نام‌های فارسی در کیبورد.

4. **اصلاح و ارتقای بخش «محصولات» (`product.php` & `productedit.php`):**
   - ایجاد خودکار ستون‌های ساختاری جدول محصول اعم از `position`, `ip_limit`, `hwid_limit`, `symbolic_limit_enabled`, `symbolic_limit_users`, `category`, `hide_panel`.
   - ایجاد خودکار جدول دسته‌بندی‌ها (`category`) جهت جلوگیری از ارور دیتابیس هنگام افزودن یا ویرایش محصول.

3. **ایمن‌سازی ساختار دیتابیس در تمام صفحات پنل (Schema Self-Healing):**
   - ایجاد و تزریق خودکار تمام ستون‌های جدول `setting` شامل ستون‌های تنظیمات تاپیک و لینک اشتراک (`receipt_topic_reporting`, `subscription_link_button`)، کانال اعتماد (`PublicLog_*`) و بنرها (`banner_*`).
   - جلوگیری کامل از خطاهای خطای دیتابیس `Unknown column in field list` در تمام صفحات پیکربندی.

4. **بازگردانی و نمایش تمام بخش‌های پنهان منوی سایدبار (`header.php`):**
   - اضافه شدن لینک مستقیم به بخش‌های «درگاه‌ها و امور مالی» (`finance.php`)، «قابلیت‌های فروشگاه» (`shopsettings.php`)، «تنظیمات کانفیگ X-UI» (`seeting_x_ui.php`) و «ارسال پیام همگانی» (`broadcast.php`) به منوی ناوبری پنل.

5. **پاکسازی و نوسازی آنی کش سیستم:**
   - تخلیه آنی کش پس از افزودن، ویرایش، حذف و جابجایی محصولات و دسته‌بندی‌ها و تنظیمات ظاهر، تا تغییرات بلافاصله در ربات تلگرام منعکس شوند.

4. **پایداری هسته و سیستم ورود ادمین (Core Stability & Auth):**
   - رفع کامل خطاهای پارامتر دیتابیس (SQLSTATE[HY093]) در صفحه ورود و پاسخ به تیکت‌ها.
   - مدیریت حرفه‌ای سشن‌ها جهت جلوگیری از لوپ ریدایرکت لاگین.
   - ابزار ترمینال لینوکس `zorvex pass` و منوی یکپارچه خط فرمان.

5. **مینی‌اپلیکیشن تلگرام (Telegram Mini App):**
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
