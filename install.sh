#!/usr/bin/env bash
# ==============================================================================
#  ⚡ Zorvex Platform Installer & Management CLI
#  Supported OS: Ubuntu 20.04 / 22.04 / 24.04 LTS
# ==============================================================================

# Checking Root Access
if [[ $EUID -ne 0 ]]; then
    echo -e "\033[31m[ERROR]\033[0m Please run this script as \033[1mroot\033[0m."
    exit 1
fi

export DEBIAN_FRONTEND=noninteractive
INSTALL_LOG="/tmp/zorvex_install.log"
APP_DIR="/var/www/zorvex"
CONFIG_FILE="$APP_DIR/config/telegram.php"
DB_CONFIG_FILE="$APP_DIR/config/database.php"
APP_CONFIG_FILE="$APP_DIR/config/app.php"
GIT_REPO="T3chHash/zorvex"

# ── Color Palette & UI Helpers ──────────────────────────────
C_BORDER=$'\033[1;36m'
C_TITLE=$'\033[1;37m'
C_DIM=$'\033[0;37m'
C_KEY=$'\033[1;33m'
C_TXT=$'\033[0;37m'
C_OK=$'\033[1;32m'
C_BAD=$'\033[1;31m'
C_WARN=$'\033[1;33m'
C_PROMPT=$'\033[1;36m'
CR=$'\033[0m'
UI_W=58

_repeat() { local ch="$1" n="$2" out="" i; for ((i=0;i<n;i++)); do out+="$ch"; done; printf '%s' "$out"; }
_rule()   { printf "  ${C_BORDER}%s${CR}\n" "$(_repeat "─" "$UI_W")"; }
_drule()  { printf "  ${C_BORDER}%s${CR}\n" "$(_repeat "━" "$UI_W")"; }

banner() {
    clear
    echo ""
    _drule
    printf "  ${C_OK}▌${CR} ${C_TITLE}ZORVEX PRO${CR}  ${C_DIM}— Enterprise VPN Sales & Management Platform${CR}\n"
    _drule
}

_mi()     { printf "    ${C_KEY}[%s]${CR}  ${C_TXT}%b${CR}\n" "$1" "$2"; }
_sec()    { printf "\n  ${C_KEY}▌${CR} ${C_TITLE}%s${CR}\n" "$1"; _rule; }
_kv()     { printf "    ${C_DIM}%-20s${CR}${C_BORDER}:${CR} %b${CR}\n" "$1" "$2"; }
_dot() {
    case "$1" in
        ok)   printf "${C_OK}●${CR} "  ;;
        bad)  printf "${C_BAD}●${CR} " ;;
        warn) printf "${C_WARN}●${CR} ";;
        *)    printf "${C_DIM}●${CR} " ;;
    esac
}

# ── Animated Step Runner ─────────────────────────────────────
run_step() {
    local msg="$1"
    local cmd="$2"
    : > "$INSTALL_LOG"
    local start; start=$(date +%s)
    bash -c "$cmd" >> "$INSTALL_LOG" 2>&1 &
    local pid=$!
    local frames=("⠋" "⠙" "⠹" "⠸" "⠼" "⠴" "⠦" "⠧" "⠇" "⠏")
    local n=${#frames[@]}
    local i=0
    tput civis 2>/dev/null

    while kill -0 "$pid" 2>/dev/null; do
        printf "\r  ${C_PROMPT}%s${CR} %-48s" "${frames[i]}" "$msg"
        i=$(( (i + 1) % n ))
        sleep 0.1
    done

    wait "$pid"
    local exit_code=$?
    tput cnorm 2>/dev/null

    if [ "$exit_code" -eq 0 ]; then
        local elapsed=$(( $(date +%s) - start ))
        printf "\r  $(_dot ok) %-48s ${C_DIM}(%ds)${CR}\n" "$msg" "$elapsed"
        return 0
    else
        printf "\r  $(_dot bad) %-48s ${C_BAD}[FAILED]${CR}\n" "$msg"
        echo -e "\n\033[1;31m──────────── Error Details ────────────\033[0m"
        tail -n 20 "$INSTALL_LOG" 2>/dev/null
        echo -e "\033[1;31m───────────────────────────────────────\033[0m\n"
        return "$exit_code"
    fi
}

# ── Status & Info Collectors ─────────────────────────────────
is_installed() {
    [ -d "$APP_DIR" ] && [ -f "$CONFIG_FILE" ] && [ -f "$DB_CONFIG_FILE" ] && [ -f "$APP_CONFIG_FILE" ]
}

get_domain() {
    if [ -f "$APP_CONFIG_FILE" ]; then
        grep -oE "https?://[^']+" "$APP_CONFIG_FILE" 2>/dev/null | head -1 | sed -e 's|^https://||' -e 's|^http://||'
    else
        echo ""
    fi
}

get_bot_token() {
    if [ -f "$CONFIG_FILE" ]; then
        grep "'token'" "$CONFIG_FILE" | head -1 | cut -d"'" -f4
    else
        echo ""
    fi
}

status_dashboard() {
    _sec "System Status"
    if is_installed; then
        _kv "Bot Status" "$(_dot ok)${C_OK}Installed & Active${CR}"
        local dom; dom=$(get_domain)
        [ -z "$dom" ] && dom="Not configured"
        _kv "Server Domain" "${C_KEY}https://${dom}${CR}"

        # SSL status
        if [ "$dom" != "Not configured" ] && [ -f "/etc/letsencrypt/live/$dom/cert.pem" ]; then
            local exp days
            exp=$(openssl x509 -enddate -noout -in "/etc/letsencrypt/live/$dom/cert.pem" 2>/dev/null | cut -d= -f2)
            days=$(( ( $(date -d "$exp" +%s 2>/dev/null || echo 0) - $(date +%s) ) / 86400 ))
            if [ "$days" -gt 10 ]; then
                _kv "SSL Certificate" "$(_dot ok)${C_OK}Valid (${days} days left)${CR}"
            else
                _kv "SSL Certificate" "$(_dot warn)${C_WARN}Near expiry (${days} days left)${CR}"
            fi
        else
            _kv "SSL Certificate" "$(_dot warn)${C_WARN}Not issued or pending${CR}"
        fi

        # Telegram webhook check
        local token; token=$(get_bot_token)
        if [ -n "$token" ]; then
            local wh; wh=$(curl -fsSL --max-time 5 "https://api.telegram.org/bot${token}/getWebhookInfo" 2>/dev/null)
            local url; url=$(echo "$wh" | grep -oE '"url":"[^"]+"' | cut -d'"' -f4)
            if [ -n "$url" ]; then
                _kv "Telegram Webhook" "$(_dot ok)${C_OK}Connected${CR}"
            else
                _kv "Telegram Webhook" "$(_dot bad)${C_BAD}Not connected${CR}"
            fi
        fi
    else
        _kv "Bot Status" "$(_dot bad)${C_BAD}Not installed${CR}"
    fi
}

# ── 1. Install Bot Function ──────────────────────────────────
install_bot() {
    banner
    _sec "Step 1: Installation Parameters"

    if is_installed; then
        echo -e "  ${C_WARN}!${CR} ${C_WARN}Zorvex is already installed on this server.${CR}"
        printf "  ${C_PROMPT}❯${CR} Do you want to reinstall and overwrite? ${C_DIM}[y/N]${CR}: "
        read -r _confirm
        if [[ ! "$_confirm" =~ ^[Yy]$ ]]; then
            show_menu
            return 0
        fi
    fi

    echo -e "  ${C_DIM}Please enter the required information:${CR}\n"

    printf "  ${C_PROMPT}❯${CR} Domain name pointing to this VPS (e.g. bot.yourdomain.com): "
    read -r DOMAIN_INPUT
    DOMAIN_NAME=$(echo "$DOMAIN_INPUT" | sed -e 's|^https://||' -e 's|^http://||' -e 's|/.*$||' | tr -d ' ')

    while [ -z "$DOMAIN_NAME" ]; do
        echo -e "  ${C_BAD}Domain cannot be empty.${CR}"
        printf "  ${C_PROMPT}❯${CR} Domain: "
        read -r DOMAIN_INPUT
        DOMAIN_NAME=$(echo "$DOMAIN_INPUT" | sed -e 's|^https://||' -e 's|^http://||' -e 's|/.*$||' | tr -d ' ')
    done

    printf "  ${C_PROMPT}❯${CR} Telegram Bot Token (from @BotFather): "
    read -r BOT_TOKEN
    while [ -z "$BOT_TOKEN" ]; do
        echo -e "  ${C_BAD}Bot token is required.${CR}"
        printf "  ${C_PROMPT}❯${CR} Bot Token: "
        read -r BOT_TOKEN
    done

    printf "  ${C_PROMPT}❯${CR} Admin Chat ID (numerical ID from @userinfobot): "
    read -r ADMIN_ID
    while ! [[ "$ADMIN_ID" =~ ^[0-9]+$ ]]; do
        echo -e "  ${C_BAD}Admin Chat ID must be a numerical value.${CR}"
        printf "  ${C_PROMPT}❯${CR} Admin Chat ID: "
        read -r ADMIN_ID
    done

    printf "  ${C_PROMPT}❯${CR} Your Email (for free Let's Encrypt SSL): "
    read -r SSL_EMAIL

    DB_NAME="zorvex_db"
    DB_USER="zorvex_user"
    DB_PASS=$(openssl rand -hex 16)
    WEBHOOK_SECRET=$(openssl rand -hex 20)

    echo ""
    _sec "Step 2: Installing Dependencies & Configuring Server"

    run_step "Updating system repositories" "apt-get update -y"
    run_step "Installing base tools (git, curl, nginx, mysql)" "apt-get install -y software-properties-common curl git unzip nginx certbot python3-certbot-nginx mysql-server"
    run_step "Installing PHP 8.3 & modules" "add-apt-repository -y ppa:ondrej/php && apt-get update -y && apt-get install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip"

    # MySQL database & user creation (No backticks inside double quotes to prevent bash command substitution)
    run_step "Configuring MySQL database & user permissions" \
        "mysql -e \"CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}'; ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}'; GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;\"" || { echo -e "  ${C_BAD}Failed to configure MySQL.${CR}"; return 1; }

    # Safe repository download/update without deleting current directory
    if [ -d "${APP_DIR}/.git" ]; then
        cd "${APP_DIR}"
        run_step "Updating Zorvex repository files" "git fetch origin main && git reset --hard origin/main" || { echo -e "  ${C_BAD}Git reset failed.${CR}"; return 1; }
    else
        cd /root
        rm -rf "${APP_DIR}"
        run_step "Downloading Zorvex source files" "git clone https://github.com/${GIT_REPO}.git '${APP_DIR}'" || { echo -e "  ${C_BAD}Git clone failed.${CR}"; return 1; }
        cd "${APP_DIR}"
    fi

    # Ensure directories exist
    mkdir -p "${APP_DIR}/config" "${APP_DIR}/database" "${APP_DIR}/storage/logs" "${APP_DIR}/storage/backups" "${APP_DIR}/storage/cache"

    # Import schema directly as root via socket (100% reliable)
    run_step "Initializing database tables & schema" "mysql ${DB_NAME} < '${APP_DIR}/database/schema.sql'" || { echo -e "  ${C_BAD}Failed to import schema.sql${CR}"; return 1; }

    # Configure app.php
    cat > "${APP_DIR}/config/app.php" << EOF
<?php
declare(strict_types=1);

return [
    'name' => 'Zorvex',
    'version' => '1.0.0 Pro',
    'timezone' => 'Asia/Tehran',
    'locale' => 'fa',
    'debug' => false,
    'url' => 'https://${DOMAIN_NAME}',
    'admin_path' => '/admin',
];
EOF

    # Configure database.php
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

    # Configure telegram.php
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

    chown -R www-data:www-data "${APP_DIR}"
    chmod -R 755 "${APP_DIR}"
    chmod -R 775 "${APP_DIR}/storage" 2>/dev/null || true

    # Nginx configuration
    NGINX_CONF="/etc/nginx/sites-available/zorvex"
    cat > "${NGINX_CONF}" << EOF
server {
    listen 80;
    server_name ${DOMAIN_NAME};
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
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true
    run_step "Configuring Nginx web server" "nginx -t && systemctl reload nginx"

    # SSL Issuance
    if [ -n "$SSL_EMAIL" ]; then
        run_step "Issuing Let's Encrypt SSL certificate" "certbot --nginx -d '${DOMAIN_NAME}' --non-interactive --agree-tos -m '${SSL_EMAIL}' --redirect || true"
    fi

    # Set Webhook
    run_step "Setting Telegram bot webhook" \
        "curl -s \"https://api.telegram.org/bot${BOT_TOKEN}/setWebhook?url=https://${DOMAIN_NAME}/index.php&secret_token=${WEBHOOK_SECRET}\" > /dev/null"

    # Send Welcome message to Admin
    curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/sendMessage" \
        -d chat_id="${ADMIN_ID}" \
        -d parse_mode="HTML" \
        -d text="🎉 <b>Zorvex Bot successfully installed and activated!</b>%0ASend /start to begin." > /dev/null 2>&1 || true

    # Setup cron
    CRON_CMD="* * * * * php ${APP_DIR}/cron/scheduler.php >> /var/log/zorvex_cron.log 2>&1"
    (crontab -l 2>/dev/null | grep -Fv "zorvex"; echo "${CRON_CMD}") | crontab -
    run_step "Configuring cron scheduler" "systemctl enable cron 2>/dev/null; systemctl start cron 2>/dev/null || true"

    # Link zorvex CLI command globally
    chmod +x "${APP_DIR}/install.sh"
    ln -sf "${APP_DIR}/install.sh" /usr/local/bin/zorvex

    echo ""
    _sec "Installation Complete 🎉"
    _kv "Bot URL" "${C_KEY}https://${DOMAIN_NAME}${CR}"
    _kv "Web Admin Dashboard" "${C_KEY}https://${DOMAIN_NAME}/admin/index.php${CR}"
    _kv "Telegram Mini App" "${C_KEY}https://${DOMAIN_NAME}/miniapp/index.php${CR}"
    _kv "Server CLI Command" "${C_OK}zorvex${CR} (type anywhere in terminal)"
    echo ""
    printf "  ${C_PROMPT}❯${CR} Press Enter to return to menu... "
    read -r _
    show_menu
}

# ── 2. Update Bot ────────────────────────────────────────────
update_bot() {
    banner
    _sec "Update Zorvex"

    if ! is_installed; then
        echo -e "  ${C_BAD}●${CR} ${C_BAD}Zorvex is not installed. Please install it first.${CR}"
        sleep 2
        show_menu
        return 1
    fi

    echo -e "  ${C_DIM}Fetching latest version from repository...${CR}\n"
    TEMP_UPD="/tmp/zorvex_upd"
    rm -rf "$TEMP_UPD"

    run_step "Downloading update package" "git clone https://github.com/${GIT_REPO}.git '$TEMP_UPD'"
    run_step "Backing up current configuration" "cp '$CONFIG_FILE' /tmp/tg_cfg.php && cp '$DB_CONFIG_FILE' /tmp/db_cfg.php"

    run_step "Updating application files" "cp -r '$TEMP_UPD'/* '$APP_DIR'/"
    run_step "Restoring configuration files" "cp /tmp/tg_cfg.php '$CONFIG_FILE' && cp /tmp/db_cfg.php '$DB_CONFIG_FILE'"
    run_step "Running database migrations" "curl -s \"https://$(get_domain)/table.php\" > /dev/null 2>&1 || true"

    rm -rf "$TEMP_UPD" /tmp/tg_cfg.php /tmp/db_cfg.php

    echo ""
    echo -e "  $(_dot ok) ${C_OK}Zorvex has been successfully updated to the latest version!${CR}\n"
    printf "  ${C_PROMPT}❯${CR} Press Enter to return to menu... "
    read -r _
    show_menu
}

# ── 3. Remove Bot ────────────────────────────────────────────
remove_bot() {
    banner
    _sec "Remove Zorvex"

    if ! is_installed; then
        echo -e "  ${C_BAD}●${CR} ${C_BAD}Zorvex is not installed.${CR}"
        sleep 2
        show_menu
        return 1
    fi

    printf "  ${C_BAD}WARNING:${CR} Are you sure you want to completely remove Zorvex, its database, and configs? ${C_DIM}[y/N]${CR}: "
    read -r confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        show_menu
        return 0
    fi

    run_step "Removing cron jobs" "crontab -l 2>/dev/null | grep -Fv 'zorvex' | crontab - || true"
    run_step "Removing Nginx site configuration" "rm -f /etc/nginx/sites-enabled/zorvex /etc/nginx/sites-available/zorvex && systemctl reload nginx 2>/dev/null || true"
    run_step "Removing application files (/var/www/zorvex)" "rm -rf '$APP_DIR'"
    run_step "Removing MySQL database & user" "mysql -e 'DROP DATABASE IF EXISTS zorvex_db; DROP USER IF EXISTS \"zorvex_user\"@\"localhost\"; FLUSH PRIVILEGES;' 2>/dev/null || true"
    rm -f /usr/local/bin/zorvex

    echo ""
    echo -e "  $(_dot ok) ${C_OK}Zorvex has been completely removed from this server.${CR}\n"
    exit 0
}

# ── 4. Renew SSL ─────────────────────────────────────────────
renew_ssl() {
    banner
    _sec "Renew SSL Certificate"
    local dom; dom=$(get_domain)
    if [ -z "$dom" ] || [ "$dom" = "Not configured" ]; then
        echo -e "  ${C_BAD}●${CR} ${C_BAD}No domain configured in settings.${CR}"
        sleep 2; show_menu; return 1
    fi

    run_step "Renewing SSL certificate for $dom" "certbot certonly --nginx --force-renewal -d '$dom' --non-interactive"
    run_step "Reloading Nginx web server" "systemctl reload nginx"

    echo ""
    echo -e "  $(_dot ok) ${C_OK}SSL certificate renewed successfully.${CR}\n"
    printf "  ${C_PROMPT}❯${CR} Press Enter to return to menu... "
    read -r _
    show_menu
}

# ── 5. Backup Database ───────────────────────────────────────
backup_db() {
    banner
    _sec "Backup Database"
    if ! is_installed; then
        echo -e "  ${C_BAD}●${CR} ${C_BAD}Zorvex is not installed.${CR}"
        sleep 2; show_menu; return 1
    fi

    local backup_file="/root/zorvex_backup_$(date +%Y%m%d_%H%M%S).sql"
    run_step "Exporting MySQL database" "mysqldump zorvex_db > '$backup_file'"

    _kv "Backup File" "${C_KEY}${backup_file}${CR}"
    local token; token=$(get_bot_token)
    local admin_id; admin_id=$(grep "'admins'" "$CONFIG_FILE" | cut -d"'" -f4)

    if [ -n "$token" ] && [ -n "$admin_id" ]; then
        run_step "Sending backup file to Telegram admin" \
            "curl -s -F chat_id='${admin_id}' -F document=@'${backup_file}' -F caption='📦 Zorvex Database Backup' 'https://api.telegram.org/bot${token}/sendDocument' > /dev/null"
    fi

    echo ""
    printf "  ${C_PROMPT}❯${CR} Press Enter to return to menu... "
    read -r _
    show_menu
}

# ── 6. Change Settings ───────────────────────────────────────
change_settings() {
    banner
    _sec "Edit Bot Configuration"
    if ! is_installed; then
        echo -e "  ${C_BAD}●${CR} ${C_BAD}Zorvex is not installed.${CR}"
        sleep 2; show_menu; return 1
    fi

    printf "  ${C_PROMPT}❯${CR} New Bot Token (leave blank to keep current): "
    read -r n_token
    [ -n "$n_token" ] && sed -i "s|'token' => '.*'|'token' => '${n_token}'|" "$CONFIG_FILE"

    printf "  ${C_PROMPT}❯${CR} New Admin Chat ID (leave blank to keep current): "
    read -r n_admin
    [ -n "$n_admin" ] && sed -i "s|'admins' => \['.*'\]|'admins' => \['${n_admin}'\]|" "$CONFIG_FILE"

    # Reset Webhook
    local dom; dom=$(get_domain)
    local active_token; active_token=$(get_bot_token)
    local secret; secret=$(grep "'webhook_secret'" "$CONFIG_FILE" | cut -d"'" -f4)

    run_step "Updating Telegram webhook" \
        "curl -s \"https://api.telegram.org/bot${active_token}/setWebhook?url=https://${dom}/index.php&secret_token=${secret}\" > /dev/null"

    echo ""
    echo -e "  $(_dot ok) ${C_OK}Configuration updated and webhook refreshed.${CR}\n"
    printf "  ${C_PROMPT}❯${CR} Press Enter to return to menu... "
    read -r _
    show_menu
}

# ── Interactive Menu ─────────────────────────────────────────
show_menu() {
    banner
    status_dashboard
    _sec "Menu & Operations"
    _mi "1" "Install Zorvex (Full Automatic Server Setup)"
    _mi "2" "Update Zorvex to Latest Version"
    _mi "3" "Remove Zorvex from Server"
    _mi "4" "Renew Domain SSL Certificate"
    _mi "5" "Backup Database (Send to Telegram)"
    _mi "6" "Edit Bot Token & Admin Chat ID"
    _mi "7" "Restart Services (Nginx, PHP-FPM, MySQL)"
    _mi "0" "Exit"
    _rule
    echo ""
    printf "  ${C_PROMPT}❯${CR} Select an option ${C_DIM}[0-7]${CR}: "
    read -r choice
    case "$choice" in
        1) install_bot ;;
        2) update_bot ;;
        3) remove_bot ;;
        4) renew_ssl ;;
        5) backup_db ;;
        6) change_settings ;;
        7)
            run_step "Restarting core services" "systemctl restart php8.3-fpm nginx mysql"
            echo -e "  $(_dot ok) ${C_OK}Services restarted successfully.${CR}"
            sleep 1; show_menu ;;
        0) echo -e "\n${C_OK}Exiting... Goodbye!${CR}\n"; exit 0 ;;
        *) echo -e "\n${C_BAD}Invalid option. Please try again.${CR}"; sleep 1; show_menu ;;
    esac
}

# Entry point
case "$1" in
    install) install_bot ;;
    update)  update_bot ;;
    remove)  remove_bot ;;
    renew)   renew_ssl ;;
    backup)  backup_db ;;
    menu|*)  show_menu ;;
esac
