// Filter kartu project berdasarkan tab status, perusahaan & PIC (tanpa reload)
function applyFilters() {
    const company = document.getElementById('filter-company').value;
    const pic = document.getElementById('filter-pic').value;
    const status = document.querySelector('.sgg-tab.is-active')?.dataset.status ?? '';
    let visible = 0;

    document.querySelectorAll('.sgg-project-card').forEach(card => {
        const show = (!company || card.dataset.company === company)
            && (!pic || card.dataset.pic === pic)
            && (!status || card.dataset.status === status);
        card.hidden = !show;
        if (show) visible++;
    });

    document.getElementById('filter-empty').hidden = visible > 0;
}

// Pasang listener hanya jika filter ada (tidak ada saat belum ada project)
['filter-company', 'filter-pic'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyFilters);
});

// Tab status: satu tab aktif, lalu terapkan ulang filter
document.querySelectorAll('button.sgg-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('button.sgg-tab').forEach(t => {
            const active = t === tab;
            t.classList.toggle('is-active', active);
            t.setAttribute('aria-selected', active);
        });
        applyFilters();
    });
});

// Auto refresh: ambil ulang halaman tiap 10 detik, ganti kartu hanya jika data berubah
const REFRESH_MS = 10000;

async function refreshProjects() {
    if (document.hidden) return; // Hemat request saat tab tidak aktif
    try {
        const res = await fetch(location.pathname, { headers: { 'Accept': 'text/html' }, cache: 'no-store' });
        if (!res.ok) return;
        const doc = new DOMParser().parseFromString(await res.text(), 'text/html');

        // Filter muncul/hilang (project pertama ditambah/terakhir dihapus): muat ulang penuh
        if (!!doc.getElementById('filter-company') !== !!document.getElementById('filter-company')) {
            location.reload();
            return;
        }

        // Perbarui opsi dropdown, pilihan pengguna dipertahankan
        ['filter-company', 'filter-pic'].forEach(id => {
            const cur = document.getElementById(id), next = doc.getElementById(id);
            if (cur && next && cur.innerHTML !== next.innerHTML) {
                const value = cur.value;
                cur.innerHTML = next.innerHTML;
                cur.value = value;
                if (cur.value !== value) cur.value = ''; // Opsi terpilih sudah dihapus
            }
        });

        // Perbarui angka pada tab ringkasan
        doc.querySelectorAll('.sgg-tab-count').forEach(next => {
            const cur = document.querySelector(`.sgg-tab-count[data-count="${next.dataset.count}"]`);
            if (cur) cur.textContent = next.textContent;
        });

        const list = document.getElementById('project-list');
        const nextList = doc.getElementById('project-list');
        if (nextList && list.innerHTML !== nextList.innerHTML) {
            // Simpan lebar progress lama per project agar batang bisa meluncur ke nilai baru
            const cardId = card => JSON.parse(card.dataset.project).id;
            const oldWidths = {};
            list.querySelectorAll('.sgg-project-card').forEach(card => {
                oldWidths[cardId(card)] = card.querySelector('.sgg-progress-fill').style.getPropertyValue('--percent');
            });

            list.classList.add('is-refreshed'); // Matikan animasi masuk saat refresh
            list.innerHTML = nextList.innerHTML;

            // Mulai dari lebar lama, lalu set lebar baru di frame berikutnya (CSS transition yang menganimasikan)
            list.querySelectorAll('.sgg-project-card').forEach(card => {
                const fill = card.querySelector('.sgg-progress-fill');
                const old = oldWidths[cardId(card)];
                if (old === undefined || old === fill.style.getPropertyValue('--percent')) return;
                const target = fill.style.getPropertyValue('--percent');
                fill.style.setProperty('--percent', old);
                fill.getBoundingClientRect(); // Paksa reflow agar lebar lama tercatat
                fill.style.setProperty('--percent', target);
            });
        }

        if (document.getElementById('filter-company')) applyFilters();
    } catch (e) {
        // Jaringan putus: coba lagi di siklus berikutnya
    }
}

setInterval(refreshProjects, REFRESH_MS);
document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshProjects(); });

// Ganti tema terang/gelap; pilihan tersimpan di localStorage, default mengikuti sistem
const themeBtn = document.getElementById('theme-toggle');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

function currentTheme() {
    return document.documentElement.dataset.theme || (prefersDark.matches ? 'dark' : 'light');
}

function syncThemeButton() {
    // Ikon menunjukkan mode tujuan (CSS membaca data-current untuk memilih bulan atau matahari)
    if (!themeBtn) return;
    const dark = currentTheme() === 'dark';
    const label = dark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap';
    themeBtn.dataset.current = dark ? 'dark' : 'light';
    themeBtn.setAttribute('aria-label', label);
    themeBtn.title = label;
}

themeBtn?.addEventListener('click', () => {
    const next = currentTheme() === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    try { localStorage.setItem('sgg-theme', next); } catch (e) { /* mode privat: tema tidak disimpan */ }
    syncThemeButton();
});
prefersDark.addEventListener('change', syncThemeButton);
syncThemeButton();

// Modal daftar PIC: buka lewat tombol, tutup lewat tombol, klik area gelap, atau Esc
const picOverlay = document.getElementById('pic-overlay');

function setPicModal(open) {
    picOverlay.classList.toggle('active', open);
    document.body.style.overflow = open ? 'hidden' : '';
}

document.getElementById('pic-open')?.addEventListener('click', () => setPicModal(true));
picOverlay?.addEventListener('click', (e) => {
    if (e.target === picOverlay || e.target.closest('[data-close-pic]')) setPicModal(false);
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && picOverlay?.classList.contains('active')) setPicModal(false);
});

// Login PIC (modal) dan logout; sesi memakai cookie Laravel, CSRF dari meta tag
const loginOverlay = document.getElementById('login-overlay');
const loginForm = document.getElementById('login-form');
const loginError = document.getElementById('login-error');
const pageBase = (document.body.dataset.base || '').replace(/\/$/, '');
const pageCsrf = document.querySelector('meta[name="csrf-token"]')?.content;

async function postSession(path, body) {
    return fetch(pageBase + path, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': pageCsrf },
        body: body ? JSON.stringify(body) : undefined,
    });
}

function setLoginModal(open) {
    loginOverlay.classList.toggle('active', open);
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) setTimeout(() => document.getElementById('login-email')?.focus(), 50);
}

document.getElementById('login-open')?.addEventListener('click', () => setLoginModal(true));
loginOverlay?.addEventListener('click', (e) => {
    if (e.target === loginOverlay || e.target.closest('[data-close-login]')) setLoginModal(false);
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && loginOverlay?.classList.contains('active')) setLoginModal(false);
});

loginForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submit = document.getElementById('login-submit');
    const data = Object.fromEntries(new FormData(loginForm));
    loginError.hidden = true;

    if (!data.email || !data.password) {
        loginError.textContent = 'Email dan password wajib diisi.';
        loginError.hidden = false;
        return;
    }

    submit.disabled = true;
    try {
        const res = await postSession('/masuk', data);
        if (res.ok) {
            location.reload();
            return;
        }
        const body = await res.json().catch(() => ({}));
        loginError.textContent = res.status === 429
            ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
            : (body.message || 'Gagal masuk. Coba lagi.');
        loginError.hidden = false;
    } catch (err) {
        loginError.textContent = 'Tidak dapat terhubung ke server.';
        loginError.hidden = false;
    } finally {
        submit.disabled = false;
    }
});

document.getElementById('logout-btn')?.addEventListener('click', async () => {
    try { await postSession('/keluar'); } finally { location.reload(); }
});
