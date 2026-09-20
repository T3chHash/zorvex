import * as Telegram from './telegram.js';
import { verify, recheckJoin, submitContact } from './api.js';

function viewEl() {
    return document.getElementById('view');
}

function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
}

function openTelegramLink(url) {
    const u = String(url || '');
    try {
        const w = (window.Telegram && window.Telegram.WebApp) || null;
        if (w && typeof w.openTelegramLink === 'function' && /^https?:\/\/t\.me\//i.test(u)) {
            w.openTelegramLink(u);
            return;
        }
        if (w && typeof w.openLink === 'function') {
            w.openLink(u);
            return;
        }
    } catch (_) {  }
    try { window.open(u, '_blank'); } catch (_) {  }
}

async function probe() {
    try {
        await verify();
        return { ok: true };
    } catch (err) {
        if (err && err.code === 'GATE' && err.data && err.data.gate) {
            return { ok: false, gate: err.data.gate, data: err.data };
        }
        throw err;
    }
}

function renderForceJoin(data, onRecheck) {
    const view = viewEl();
    if (!view) return;
    const channels = (data && Array.isArray(data.channels)) ? data.channels : [];
    const msg = (data && data.msg) || 'برای استفاده، ابتدا در کانال‌ها عضو شوید.';
    const items = channels.map((c, i) =>
        `<button class="btn join-link" data-i="${i}" style="display:block;width:100%;margin-top:8px">عضویت در ${esc(c.title)}</button>`
    ).join('');
    view.innerHTML = `
        <div class="empty">
            <span class="glyph">🔒</span>
            <h3>عضویت در کانال</h3>
            <p class="muted">${esc(msg)}</p>
            <div class="mt-md">${items}</div>
            <button class="btn btn-primary mt-md" id="recheck-btn" style="display:block;width:100%">بررسی مجدد</button>
            <p class="muted mono mt-sm" id="join-note" style="font-size:11px"></p>
        </div>`;
    channels.forEach((c, i) => {
        const b = view.querySelector('.join-link[data-i="' + i + '"]');
        if (b) b.addEventListener('click', () => openTelegramLink(c.link));
    });
    const rb = view.querySelector('#recheck-btn');
    if (rb) rb.addEventListener('click', () => onRecheck(rb));
}

function renderPhone(data, onShare) {
    const view = viewEl();
    if (!view) return;
    const msg = (data && data.msg) || 'برای ادامه، شمارهٔ موبایل خود را تأیید کنید.';
    view.innerHTML = `
        <div class="empty">
            <span class="glyph">📱</span>
            <h3>تأیید شمارهٔ موبایل</h3>
            <p class="muted">${esc(msg)}</p>
            <button class="btn btn-primary mt-md" id="share-phone-btn" style="display:block;width:100%">اشتراک‌گذاری شماره</button>
            <p class="muted mono mt-sm" id="phone-note" style="font-size:11px"></p>
        </div>`;
    const sb = view.querySelector('#share-phone-btn');
    if (sb) sb.addEventListener('click', () => onShare(sb));
}

function resolveForceJoin(data) {
    return new Promise((resolve, reject) => {
        const onRecheck = async (btn) => {
            const note = document.getElementById('join-note');
            const label = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'در حال بررسی…';
            try {
                const r = await recheckJoin();
                if (r.ok) { resolve({ ok: true }); return; }
                if (r.gate && r.gate !== 'force_join') { resolve({ ok: false, gate: r.gate, data: r.data }); return; }
                if (note) note.textContent = 'هنوز عضو همهٔ کانال‌ها نیستید.';
                renderForceJoin(r.data || data, onRecheck);
            } catch (err) {
                if (note) note.textContent = (err && err.message) || 'خطا در بررسی عضویت.';
                btn.disabled = false;
                btn.textContent = label;
            }
        };
        renderForceJoin(data, onRecheck);
    });
}

function resolvePhone(data) {
    return new Promise((resolve, reject) => {
        const onShare = async (btn) => {
            const note = document.getElementById('phone-note');
            const label = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'در انتظار تأیید…';
            try {
                const contact = await Telegram.requestContact();
                const r = await submitContact(contact.response);
                if (r.ok) { resolve({ ok: true }); return; }
                resolve({ ok: false, gate: r.gate, data: r.data });
            } catch (err) {
                const m = (err && err.message) || '';
                if (note) {
                    note.textContent = (m === 'cancelled')
                        ? 'اشتراک‌گذاری شماره لغو شد.'
                        : (m === 'requestContact_unsupported'
                            ? 'نسخهٔ تلگرام شما این قابلیت را پشتیبانی نمی‌کند.'
                            : (m || 'خطا در دریافت شماره.'));
                }
                btn.disabled = false;
                btn.textContent = label;
            }
        };
        renderPhone(data, onShare);
    });
}

function resolveGate(state) {
    if (state.gate === 'force_join') return resolveForceJoin(state.data);
    if (state.gate === 'phone_required') return resolvePhone(state.data);
    const err = new Error((state.data && state.data.msg) || 'Access blocked');
    err.code = 'GATE';
    err.data = state.data;
    return Promise.reject(err);
}

export async function ensureAccess() {
    let state = await probe();
    let guard = 0;
    while (!state.ok) {
        if (++guard > 20) {
            throw new Error('Too many access attempts');
        }
        state = await resolveGate(state);
    }
}
