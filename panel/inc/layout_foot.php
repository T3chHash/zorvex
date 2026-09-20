<?php ?>
  </main>
</div>

<nav class="bottom-nav">
  <div class="bottom-nav-row">
    <a href="index.php"   class="bnav-item <?= ($activeNav??'')==='dashboard'?'active':''?>"><?= icon('dashboard',22) ?><span>داشبورد</span></a>
    <a href="users.php"   class="bnav-item <?= ($activeNav??'')==='users'?'active':''?>"><?= icon('users',22) ?><span>کاربران</span></a>
    <a href="invoice.php" class="bnav-item <?= ($activeNav??'')==='invoice'?'active':''?>"><?= icon('invoice',22) ?><span>سفارشات</span></a>
    <a href="payment.php" class="bnav-item <?= ($activeNav??'')==='payment'?'active':''?>"><?= icon('card',22) ?><span>پرداخت</span></a>
    <a href="settings.php"class="bnav-item <?= ($activeNav??'')==='settings'?'active':''?>"><?= icon('settings',22) ?><span>تنظیمات</span></a>
  </div>
</nav>
</div>

<script src="js/app.js"></script>
</body>
</html>
