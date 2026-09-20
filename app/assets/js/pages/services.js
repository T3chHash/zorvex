import { call } from '../api.js';
import { escapeHtml, skeletonList, emptyState, toast, fmtNumber } from '../utils.js';

export async function services(view) {
    let page = 1;
    const limit = 10;
    let query = '';
    let totalPages = 1;
    let totalItems = 0;

    view.innerHTML = `
        <article class="card card-window">
            <header class="card-window-bar">
                <span class="dots"><span></span><span></span><span></span></span>
                <span class="window-url">faoxima/services</span>
            </header>
            <div class="card-body">
                <div class="input-row">
                    <label class="input-mono" style="flex:1">
                        <input id="services-search" type="text" placeholder="جستجو در نام سرویس..." inputmode="search" />
                    </label>
                    <button class="btn btn-primary" id="services-search-btn"><span>جستجو</span><span class="arrow"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.00024 13.5V9M9.00024 9H13.5002M9.00024 9L15.0002 14.9999M7.20024 20H16.8002C17.9203 20 18.4804 20 18.9082 19.782C19.2845 19.5903 19.5905 19.2843 19.7823 18.908C20.0002 18.4802 20.0002 17.9201 20.0002 16.8V7.2C20.0002 6.0799 20.0002 5.51984 19.7823 5.09202C19.5905 4.71569 19.2845 4.40973 18.9082 4.21799C18.4804 4 17.9203 4 16.8002 4H7.20024C6.08014 4 5.52009 4 5.09226 4.21799C4.71594 4.40973 4.40998 4.71569 4.21823 5.09202C4.00024 5.51984 4.00024 6.07989 4.00024 7.2V16.8C4.00024 17.9201 4.00024 18.4802 4.21823 18.908C4.40998 19.2843 4.71594 19.5903 5.09226 19.782C5.52009 20 6.08014 20 7.20024 20Z"/></svg></span></button>
                </div>

                <p class="section-title mt-md">اشتراک‌های فعال</p>

                <div id="services-list" class="list mt-sm">
                    ${skeletonList(4)}
                </div>

                <div class="pager hidden" id="services-pager">
                    <button class="btn btn-ghost" id="services-prev">قبلی</button>
                    <span class="pager-info" id="services-pager-info"></span>
                    <button class="btn btn-ghost" id="services-next">بعدی</button>
                </div>
            </div>
        </article>
    `;

    const $list = view.querySelector('#services-list');
    const $pager = view.querySelector('#services-pager');
    const $info = view.querySelector('#services-pager-info');
    const $prev = view.querySelector('#services-prev');
    const $next = view.querySelector('#services-next');
    const $search = view.querySelector('#services-search');
    const $searchBtn = view.querySelector('#services-search-btn');

    async function load() {
        $list.innerHTML = skeletonList(4);
        $pager.classList.add('hidden');

        try {
            const params = { page: String(page), limit: String(limit) };
            if (query) params.q = query;

            const res = await call('invoices', { params });
            const items = Array.isArray(res?.obj) ? res.obj : [];
            const meta = res?.meta || {};
            totalPages = meta.totalPages || 0;
            totalItems = meta.totalItems || 0;

            if (items.length === 0) {
                $list.innerHTML = emptyState('سرویسی یافت نشد', 'هنوز هیچ اشتراک فعالی ثبت نشده است.', '~');
                return;
            }

            $list.innerHTML = items.map(renderItem).join('');

            if (totalPages > 1) {
                $pager.classList.remove('hidden');
                $info.textContent = `صفحه ${fmtNumber(page)} از ${fmtNumber(totalPages)} (${fmtNumber(totalItems)} سرویس)`;
                $prev.disabled = page <= 1;
                $next.disabled = page >= totalPages;
            }
        } catch (err) {
            $list.innerHTML = `
                <div class="empty">
                    <span class="glyph">!</span>
                    <h3>خطا در دریافت لیست</h3>
                    <p class="muted">${escapeHtml(err.message || '')}</p>
                </div>
            `;
        }
    }

    function renderItem(it) {
        const status = String(it.status || it.Status || '').toLowerCase();
        let badge = 'is-active';
        let badgeText = 'فعال';
        if (status === 'end_of_time')   { badge = 'is-warn'; badgeText = 'پایان زمان'; }
        else if (status === 'end_of_volume') { badge = 'is-warn'; badgeText = 'پایان حجم'; }
        else if (status === 'sendedwarn')    { badge = 'is-warn'; badgeText = 'هشدار'; }
        else if (status === 'send_on_hold')  { badge = 'is-danger'; badgeText = 'متوقف'; }

        const username = it.username || '—';
        const productName = it.name_product || '—';
        const location = it.Service_location || '';

        return `
            <a href="#/services/${encodeURIComponent(username)}" class="list-item" data-username="${escapeHtml(username)}">
                <div class="li-main">
                    <div class="li-title">${escapeHtml(productName)}</div>
                    <div class="li-sub">${escapeHtml(username)}${location ? ' · ' + escapeHtml(location) : ''}</div>
                </div>
                <span class="badge ${badge}">${escapeHtml(badgeText)}</span>
                <span class="li-action" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </span>
            </a>
        `;
    }

    $searchBtn.addEventListener('click', () => {
        query = ($search.value || '').trim();
        page = 1;
        load();
    });
    $search.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            $searchBtn.click();
        }
    });
    $prev.addEventListener('click', () => { if (page > 1) { page--; load(); } });
    $next.addEventListener('click', () => { if (page < totalPages) { page++; load(); } });

    await load();
}

