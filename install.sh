#!/usr/bin/env bash
# ==============================================================================
#  ⚡ Zorvex Enterprise Installer - Next-Gen Telegram VPN Platform
#  Supported OS: Ubuntu 22.04 LTS / Ubuntu 24.04 LTS
# ==============================================================================

set -e

# Color Palette
CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color
BOLD='\033[1m'

clear

echo -e "${PURPLE}${BOLD}"
cat << "EOF"
  ______ _____  ______      ________ _  __
 |___  // __ \|  _ \ \    / /  ____| |/ /
    / /| |  | | |_) \ \  / /| |__  | ' / 
   / / | |  | |  _ < \ \/ / |  __| |  <  
  / /__| |__| | |_) | \  /  | |____| . \ 
 /_____\____/|____/   \/   |______|_|\_\
                                         
   Enterprise VPN Sales & Management Platform
EOF
echo -e "${NC}"
echo -e "${CYAN}================================================================${NC}"
echo -e "${GREEN}  خوش‌آمدید به اسکریپت نصب و راه‌اندازی خودکار پلتفرم Zorvex${NC}"
echo -e "${CYAN}================================================================${NC}\n"

# Check root privilege
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ لطفاً این اسکریپت را با دسترسی root اجرا فرمایید.${NC}"
    exit 1
fi

# Gather User Input
echo -e "${YELLOW}📝 لطفاً اطلاعات مورد نیاز زیر را وارد فرمایید:${NC}\n"

read -p "🌐 دامنه یا ساب‌دامنه متصل به سرور (مثال: bot.yourdomain.com): " DOMAIN
read -p "🤖 توکن ربات تلگرام (از @BotFather): " BOT_TOKEN
read -p "👤 آیدی عددی تلگرام ادمین (از @userinfobot): " ADMIN_ID
read -p "📧 ایمیل شما (جهت صدور گواهی SSL رایگان): " SSL_EMAIL

# Generate secure random DB credentials
DB_NAME="zorvex_db"
DB_USER="zorvex_user"
DB_PASS=$(openssl rand -hex 16)
WEBHOOK_SECRET=$(openssl rand -hex 20)

echo -e "\n${CYAN}📦 1/5 در حال بروزرسانی مخازن و نصب پیش‌نیازها...${NC}"
apt-get update -y
apt-get install -y software-properties-common curl git unzip nginx certbot python3-certbot-nginx mysql-server

# Install PHP 8.3 & extensions
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip

echo -e "\n${CYAN}🗄️ 2/5 در حال راه‌اندازی دیتابیس MySQL...${NC}"
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Target installation directory
APP_DIR="/var/www/zorvex"
if [ ! -f "database/schema.sql" ]; then
    echo -e "${CYAN}📥 در حال دریافت مستقیم فایل‌های پروژه از گیت‌هاب...${NC}"
    rm -rf "${APP_DIR}"
    git clone https://github.com/T3chHash/zorvex.git "${APP_DIR}"
else
    mkdir -p "${APP_DIR}"
    cp -r ./* "${APP_DIR}/" || true
fi

# Import Schema
mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${APP_DIR}/database/schema.sql"

echo -e "\n${CYAN}⚙️ 3/5 در حال تنظیم پیکربندی ربات و دیتابیس...${NC}"

# Update config/database.php
cat > "${APP_DIR}/config/database.php" << EOF
<?php
declare(strict_types=1);

return [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => '${DB_NAME}',
    'username' => '${DB_USER}',
    'password' => '${DB_PASS}',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
EOF

# Update config/telegram.php
cat > "${APP_DIR}/config/telegram.php" << EOF
<?php
declare(strict_types=1);

return [
    'token' => '${BOT_TOKEN}',
    'bot_username' => 'zorvex_bot',
    'webhook_secret' => '${WEBHOOK_SECRET}',
    'admins' => ['${ADMIN_ID}'],
    'log_channel' => '',
    'report_topics' => [],
    'force_channel' => '',
];
EOF

# Update config/app.php
cat > "${APP_DIR}/config/app.php" << EOF
<?php
declare(strict_types=1);

return [
    'name' => 'Zorvex',
    'version' => '1.0.0 Pro',
    'timezone' => 'Asia/Tehran',
    'locale' => 'fa',
    'debug' => false,
    'url' => 'https://${DOMAIN}',
    'admin_path' => '/admin',
];
EOF

# Permissions
chown -R www-data:www-data "${APP_DIR}"
chmod -R 755 "${APP_DIR}"
chmod -R 775 "${APP_DIR}/storage"

echo -e "\n${CYAN}🌐 4/5 در حال پیکربندی Nginx و گواهی SSL رایگان...${NC}"

NGINX_CONF="/etc/nginx/sites-available/zorvex"
cat > "${NGINX_CONF}" << EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;
    index index.php index.html;

    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

ln -sf "${NGINX_CONF}" /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# Issue Let's Encrypt SSL
if [ -n "${SSL_EMAIL}" ]; then
    certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${SSL_EMAIL}" --redirect || true
fi

echo -e "\n${CYAN}🤖 5/5 در حال ست کردن وب‌هوک تلگرام و کرون‌جاب...${NC}"

WEBHOOK_URL="https://${DOMAIN}/index.php"
curl -s "https://api.telegram.org/bot${BOT_TOKEN}/setWebhook?url=${WEBHOOK_URL}&secret_token=${WEBHOOK_SECRET}" > /dev/null

# Setup Cron Job
CRON_JOB="* * * * * php ${APP_DIR}/cron/scheduler.php >> /var/log/zorvex_cron.log 2>&1"
(crontab -l 2>/dev/null | grep -Fv "zorvex"; echo "${CRON_JOB}") | crontab -

echo -e "\n${GREEN}================================================================${NC}"
echo -e "${GREEN}🎉 تبریک! ربات و پلتفرم Zorvex با موفقیت نصب و راه‌اندازی شد!${NC}"
echo -e "${CYAN}================================================================${NC}"
echo -e "▫️ 🌐 آدرس وب‌سایت و وب‌هوک: ${BOLD}https://${DOMAIN}${NC}"
echo -e "▫️ 📊 داشبورد وب مدیریت: ${BOLD}https://${DOMAIN}/admin/index.php${NC}"
echo -e "▫️ 📱 مینی‌اپلیکیشن تلگرام: ${BOLD}https://${DOMAIN}/miniapp/index.php${NC}"
echo -e "▫️ 👤 آیدی ادمین: ${BOLD}${ADMIN_ID}${NC}"
echo -e "▫️ 💾 مسیر پروژه: ${BOLD}${APP_DIR}${NC}"
echo -e "${CYAN}================================================================${NC}\n"
EOF
