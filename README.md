# ⚡ Zorvex Bot Pro

<div align="center">

### پلتفرم نسل جدید فروش و مدیریت اشتراک‌های هوشمند VPN در تلگرام
**معماری ماژولار، طراحی فوق‌العاده مدرن (Glassmorphism)، مینی‌اپ تلگرام و داشبورد وب**

[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](#)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](#)
[![Telegram](https://img.shields.io/badge/Telegram-Bot-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white)](#)

</div>

---

## 🌟 مقایسه Zorvex در برابر Mirza Bot

| قابلیت | Mirza Bot 🤖 | Zorvex Pro ⚡ |
| :--- | :---: | :---: |
| **معماری کد** | رویه‌ای درشت و فشرده (`admin.php` بیش از ۶۰۰ کیلوبایت در یک فایل) | **کاملاً شی‌گرا (OOP)، ساختار MVC، روتینگ تمیز و ماژولار** |
| **پشتیبانی از پنل‌ها** | توابع تو در تو و بدون اینترفیس واحد | **درایورهای استاندارد (`PanelInterface`) برای Marzban, X-UI, Hiddify, Marzneshin** |
| **طراحی پیام‌های تلگرام** | متون طولانی با ایموجی‌های ساده | **سیستم دیزاین کارت‌های یونیکد، پروگرس‌بار گرافیکی حجم (`▰▰▰▰▱▱ 70%`) و تایپوگرافی شکیل** |
| **صفحه وب اشتراک کاربر** | ندارد یا ساده | **داشبورد اختصاصی با دکمه‌های اتصال مستقیم به Streisand, v2rayNG, Sing-box و QR Code** |
| **مینی‌اپلیکیشن (Mini App)** | باندل کامپایل‌شده بدون دسترسی تغییر | **طراحی نئومورفیسم و دارک با Tailwind، اتصال کامل به WebApp SDK تلگرام** |
| **داشبورد وب مدیریت** | جداول قدیمی بدون آمار زنده | **کنترل‌پنل ریسپانسیو دارک با قابلیت تأیید آنی فیش‌ها و مانیتورینگ آنلاین پنل‌ها** |
| **امنیت دیتابیس** | ترکیب کوئری‌های خام و بایندرها | **PDO تماماً امن با Prepared Statements و مایگریشن خودکار** |

---

## 🧩 پنل‌های پشتیبانی‌شده
- 🟢 **Marzban (مرزبان)** - احراز هویت توکن، ساخت کاربر، تمدید، ریست ترافیک و ساب‌لینک
- 🟢 **Marzneshin (مرزنشین)** - پشتیبانی از API مرزنشین
- 🟢 **3x-ui / Sanaei / Alireza (ایکس‌یو‌آی)** - ورود سشن کوکی، درج کاربر در اینباندها
- 🟢 **Hiddify Manager (هیدیفای)** - ارتباط REST v2

## 💳 درگاه‌های پرداخت
1. 🏦 **کارت به کارت هوشمند**: چرخش شماره کارت‌ها، دریافت فیش و دکمه‌های تأیید/رد آنی در کانال ادمین
2. 🇮🇷 **زرین‌پال (ZarinPal)**: درگاه مستقیم شتابی آنلاین
3. 🪙 **کریپتو NowPayments**: پشتیبانی از تتر (USDT)، ترون (TRX)، بیت‌کوین و تون
4. 💳 **کیف پول داخلی**: خرید آنی با یک کلیک از موجودی حساب کاربر

---

## 🚀 راهنمای نصب سریع (یک کلیک روی اوبونتو ۲۲.۰۴ / ۲۴.۰۴)

روی سرور ابری لینوکس خود با دسترسی `root` دستور زیر را اجرا نمایید:

```bash
curl -sSL https://raw.githubusercontent.com/your-username/zorvex/main/install.sh | bash
```

یا اگر فایل‌ها را کلون کرده‌اید:
```bash
git clone https://github.com/your-username/zorvex.git /var/www/zorvex
cd /var/www/zorvex
chmod +x install.sh
./install.sh
```

اسکریپت به صورت خودکار:
1. پکیج‌های PHP 8.3، وب‌سرور Nginx، دیتابیس MySQL و Certbot SSL را نصب می‌کند.
2. دیتابیس را ساخته و جداول را وارد می‌نماید.
3. گواهی رایگان HTTPS از Let's Encrypt صادر می‌کند.
4. وب‌هوک ربات تلگرام را ست کرده و کرون‌جاب یادآوری انقضا را فعال می‌کند.

---

## 📂 ساختار ماژولار پروژه

```
zorvex/
├── app/
│   ├── autoload.php          # لودر مستقل بدون نیاز اجباری به کامپوزر
│   ├── Core/                 # هسته، روتینگ، ریکوئست، رسپانس و قالب تم
│   │   ├── Database.php
│   │   ├── Request.php
│   │   ├── Router.php
│   │   ├── TelegramBot.php
│   │   └── Theme.php
│   ├── Controllers/          # کنترلرهای عملیاتی بات
│   │   ├── StartController.php
│   │   ├── ShopController.php
│   │   ├── ServiceController.php
│   │   ├── TestAccountController.php
│   │   ├── WalletController.php
│   │   ├── AffiliateController.php
│   │   ├── LotteryController.php
│   │   ├── SupportController.php
│   │   └── AdminController.php
│   ├── Drivers/
│   │   ├── Panels/           # درایورهای پنل‌های مختلف
│   │   │   ├── PanelInterface.php
│   │   │   ├── MarzbanDriver.php
│   │   │   ├── XuiDriver.php
│   │   │   ├── HiddifyDriver.php
│   │   │   └── PanelFactory.php
│   │   └── Payments/         # درایورهای درگاه‌های بانکی و کریپتو
│   │       ├── PaymentInterface.php
│   │       ├── CardToCardGateway.php
│   │       ├── ZarinpalGateway.php
│   │       ├── NowpaymentsGateway.php
│   │       └── WalletPaymentGateway.php
│   └── Helpers/              # تاریخ جلالی و توابع کمکی
├── config/                   # تنظیمات بات، دیتابیس و برنامه
├── database/                 # ساختار اسکیما و مایگریشن‌ها
├── public/                   # ریشه وب‌سرور Nginx
│   ├── index.php             # دریافت وب‌هوک‌های تلگرام
│   ├── sub.php               # اندپوینت فوق سریع اشتراک
│   ├── sub_page.php          # صفحه گرافیکی اشتراک برای کاربر
│   ├── admin/                # کنترل‌پنل وب مدیریت
│   └── miniapp/              # مینی‌اپلیکیشن تلگرام
├── cron/                     # جاب‌های زمان‌بندی‌شده (یادآوری حجم، انقضا، بک‌آپ)
└── install.sh                # نصاب خودکار با لوگوی اختصاصی
```

---

## 📄 لایسنس
توسعه یافته تحت مجوز MIT.
