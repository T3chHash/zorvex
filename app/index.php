<!DOCTYPE html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <meta name="theme-color" content="#0B0F19" />
    <title>Zorvex Mini App</title>
    <script src="./js/telegram-web-app.js"></script>
    <script>
      if (window.Telegram && window.Telegram.WebApp) {
        window.Telegram.WebApp.ready();
        window.Telegram.WebApp.expand();
        window.Telegram.WebApp.setHeaderColor('#0B0F19');
        window.Telegram.WebApp.setBackgroundColor('#0B0F19');
      }
    </script>
    <link rel="stylesheet" href="./assets/index-BoHBsj0Z.css">
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap');
      :root {
        --font-sans: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
      }
      body {
        font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        background: #0B0F19 !important;
        color: #F3F4F6 !important;
        direction: rtl;
        -webkit-font-smoothing: antialiased;
        overflow-x: hidden;
      }
      .bg-card {
        background-color: rgba(17, 24, 39, 0.8) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35) !important;
      }
      .bg-popover {
        background-color: rgba(17, 24, 39, 0.92) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
      }
      .bg-primary {
        background: linear-gradient(135deg, #06B6D4, #3B82F6) !important;
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.3) !important;
      }
      .border-border {
        border-color: rgba(255, 255, 255, 0.08) !important;
      }
      nav {
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        background-color: rgba(11, 15, 25, 0.88) !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
      }
      body {
        padding-top: env(safe-area-inset-top, 0px);
        padding-bottom: env(safe-area-inset-bottom, 0px);
      }
    </style>
    <script type="module" crossorigin src="./assets/index-C-2a0Dur.js"></script>
    <link rel="modulepreload" crossorigin href="./assets/vendor-CIGJ9g2q.js">
  </head>
  <body class="dark">
    <div id="root"></div>
  </body>
</html>