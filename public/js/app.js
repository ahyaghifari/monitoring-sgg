// Pemetaan nilai urgensi ke kelas badge dan label Bahasa Indonesia
const URGENCY_MAP = {
    'Low':    { cls: 'sgg-badge--low',    label: 'Rendah'  },
    'Medium': { cls: 'sgg-badge--medium', label: 'Sedang'  },
    'High':   { cls: 'sgg-badge--high',   label: 'Tinggi'  },
    'Urgent': { cls: 'sgg-badge--urgent', label: 'Darurat' },
};

// Mengamankan teks dari input pengguna sebelum disisipkan ke HTML
function escapeHtml(t) {
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

// Membuat markup badge berwarna
function badge(cls, label) {
    return `<span class="sgg-badge ${cls}"><span class="sgg-badge-dot"></span>${label}</span>`;
}

// Menampilkan modal detail saat kartu project diklik
function openDetailModal(cardEl) {
    const p = JSON.parse(cardEl.getAttribute('data-project'));
    const u = URGENCY_MAP[p.urgency] || URGENCY_MAP['Low'];
    const done = p.status === 'Completed';

    document.getElementById('detail-company').textContent = p.company_name;
    document.getElementById('detail-title').textContent = p.title;
    document.getElementById('detail-badges').innerHTML =
        badge(u.cls, u.label) +
        badge(done ? 'sgg-badge--completed' : 'sgg-badge--progress', done ? 'Selesai' : 'Dalam Proses');
    document.getElementById('detail-progress-fill').style.width = p.progress_percent + '%';
    document.getElementById('detail-progress-pct').textContent = p.progress_percent + '%';

    // Render daftar tugas (read-only)
    document.getElementById('detail-checklist-container').innerHTML = p.checklists.length === 0
        ? '<p style="color:#9ca3af;font-size:14px;padding:8px 0;">Belum ada tugas pada project ini.</p>'
        : p.checklists.map(c => `
            <div class="sgg-checklist-item ${c.is_completed ? 'checked' : ''}">
                <div class="sgg-checklist-checkbox" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="sgg-checklist-label">${escapeHtml(c.title)}</span>
                <span class="sgg-checklist-state">${c.is_completed ? 'Selesai' : 'Belum'}</span>
            </div>`).join('');

    document.getElementById('detail-overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Menutup modal detail dan mengembalikan scroll halaman
function closeDetailModal() {
    document.getElementById('detail-overlay').classList.remove('active');
    document.body.style.overflow = '';
}

// Filter kartu project berdasarkan perusahaan & PIC (tanpa reload)
function applyFilters() {
    const company = document.getElementById('filter-company').value;
    const pic = document.getElementById('filter-pic').value;
    let visible = 0;

    document.querySelectorAll('.sgg-project-card').forEach(card => {
        const show = (!company || card.dataset.company === company) && (!pic || card.dataset.pic === pic);
        card.hidden = !show;
        if (show) visible++;
    });

    document.getElementById('filter-empty').hidden = visible > 0;
}

// Pasang listener hanya jika filter ada (tidak ada saat belum ada project)
['filter-company', 'filter-pic'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyFilters);
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

        const list = document.getElementById('project-list');
        const nextList = doc.getElementById('project-list');
        if (nextList && list.innerHTML !== nextList.innerHTML) {
            // Simpan lebar progress lama per project agar batang bisa meluncur ke nilai baru
            const cardId = card => JSON.parse(card.dataset.project).id;
            const oldWidths = {};
            list.querySelectorAll('.sgg-project-card').forEach(card => {
                oldWidths[cardId(card)] = card.querySelector('.sgg-progress-fill').style.width;
            });

            list.classList.add('is-refreshed'); // Matikan animasi masuk saat refresh
            list.innerHTML = nextList.innerHTML;

            // Mulai dari lebar lama, lalu set lebar baru di frame berikutnya (CSS transition yang menganimasikan)
            list.querySelectorAll('.sgg-project-card').forEach(card => {
                const fill = card.querySelector('.sgg-progress-fill');
                const old = oldWidths[cardId(card)];
                if (old === undefined || old === fill.style.width) return;
                const target = fill.style.width;
                fill.style.width = old;
                fill.getBoundingClientRect(); // Paksa reflow agar lebar lama tercatat
                fill.style.width = target;
            });
        }

        if (document.getElementById('filter-company')) applyFilters();
    } catch (e) {
        // Jaringan putus: coba lagi di siklus berikutnya
    }
}

setInterval(refreshProjects, REFRESH_MS);
document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshProjects(); });

// Tombol Esc menutup modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDetailModal();
});
