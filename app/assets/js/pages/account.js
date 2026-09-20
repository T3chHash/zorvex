import { call } from '../api.js';
import { escapeHtml, fmtPrice, fmtNumber, skeletonList } from '../utils.js';
import { setUser } from '../state.js';

export async function account(view) {
    view.innerHTML = `
        <article class="card card-window">
            <header class="card-window-bar">
                <span class="dots"><span></span><span></span><span></span></span>
                <span class="window-url">faoxima/account</span>
            </header>
            <div class="card-body" id="account-body">
                ${skeletonList(4)}
            </div>
        </article>
    `;

    const $body = view.querySelector('#account-body');

    let info = null;
    try {
        const res = await call('user_info');
        info = res?.obj;
        if (info) setUser(info);
    } catch (err) {
        $body.innerHTML = `
            <div class="empty">
                <span class="glyph">!</span>
                <h3>خطا در دریافت اطلاعات</h3>
                <p class="muted">${escapeHtml(err.message || '')}</p>
            </div>
        `;
        return;
    }

    if (!info) {
        $body.innerHTML = `<div class="empty"><span class="glyph">∅</span><h3>اطلاعاتی یافت نشد</h3></div>`;
        return;
    }

    $body.innerHTML = `
        <p class="section-title">حساب کاربری</p>
        <div class="stat-grid mt-sm">
            <div class="stat">
                <div class="stat-label"><span class="glyph">$</span> موجودی کیف پول</div>
                <div class="stat-value accent">${escapeHtml(fmtPrice(info.balance))}</div>
            </div>
            <div class="stat">
                <div class="stat-label"><span class="glyph">~</span> نوع کاربری</div>
                <div class="stat-value">${escapeHtml(info.group_type || '—')}</div>
            </div>
            <div class="stat">
                <div class="stat-label"><span class="glyph">#</span> سرویس‌های فعال</div>
                <div class="stat-value">${escapeHtml(fmtNumber(info.count_order))}</div>
            </div>
            <div class="stat">
                <div class="stat-label"><span class="glyph">@</span> پرداخت‌ها</div>
                <div class="stat-value">${escapeHtml(fmtNumber(info.count_payment))}</div>
            </div>
        </div>

        <div class="card-section">
            <div class="kv">
                <span class="kv-label"><span class="glyph">/</span> تاریخ عضویت</span>
                <span class="kv-value">${escapeHtml(info.time_join || '—')}</span>
            </div>
            <div class="kv">
                <span class="kv-label"><span class="glyph">:</span> شماره تلفن</span>
                <span class="kv-value">${escapeHtml(info.phone || '—')}</span>
            </div>
        </div>

        <div class="row-spread mt-md gap-sm">
            <a href="#/services" class="btn btn-ghost btn-block">سرویس‌های من</a>
            <a href="#/settings" class="btn btn-ghost btn-block">تنظیمات</a>
        </div>
        <div class="row-spread mt-sm">
            <a href="#/buy" class="btn btn-primary btn-block"><span>خرید سرویس</span><span class="arrow"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.00024 13.5V9M9.00024 9H13.5002M9.00024 9L15.0002 14.9999M7.20024 20H16.8002C17.9203 20 18.4804 20 18.9082 19.782C19.2845 19.5903 19.5905 19.2843 19.7823 18.908C20.0002 18.4802 20.0002 17.9201 20.0002 16.8V7.2C20.0002 6.0799 20.0002 5.51984 19.7823 5.09202C19.5905 4.71569 19.2845 4.40973 18.9082 4.21799C18.4804 4 17.9203 4 16.8002 4H7.20024C6.08014 4 5.52009 4 5.09226 4.21799C4.71594 4.40973 4.40998 4.71569 4.21823 5.09202C4.00024 5.51984 4.00024 6.07989 4.00024 7.2V16.8C4.00024 17.9201 4.00024 18.4802 4.21823 18.908C4.40998 19.2843 4.71594 19.5903 5.09226 19.782C5.52009 20 6.08014 20 7.20024 20Z"/></svg></span></a>
        </div>
    `;
}

