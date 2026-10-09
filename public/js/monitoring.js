// Modal detail project (read-only): progress aktif di atas, daftar tugas checklist di bawah

// Pemetaan nilai urgensi ke kelas badge dan label Bahasa Indonesia
const URGENCY_MAP = {
    'Low':    { cls: 'sgg-badge--low',    label: 'Rendah'  },
    'Medium': { cls: 'sgg-badge--medium', label: 'Sedang'  },
    'High':   { cls: 'sgg-badge--high',   label: 'Tinggi'  },
    'Urgent': { cls: 'sgg-badge--urgent', label: 'Darurat' },
};

const overlay = document.getElementById('detail-overlay');

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

// Daftar tugas checklist dan status centangnya
function renderChecklist(checklists) {
    if (checklists.length === 0) return '<p class="sgg-modal-empty">Belum ada tugas pada project ini.</p>';

    return checklists.map(c => `
        <div class="sgg-checklist-item ${c.is_completed ? 'checked' : ''}">
            <div class="sgg-checklist-checkbox" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="sgg-checklist-label">${escapeHtml(c.title)}</span>
            <span class="sgg-checklist-state">${c.is_completed ? 'Selesai' : 'Belum Selesai'}</span>
        </div>`).join('');
}

// Menampilkan modal detail saat kartu project diklik
function openDetailModal(cardEl) {
    const p = JSON.parse(cardEl.dataset.project);
    const u = URGENCY_MAP[p.urgency] || URGENCY_MAP['Low'];
    const done = p.status === 'Completed';

    document.getElementById('detail-company').textContent = p.company_name;
    document.getElementById('detail-title').textContent = p.title;
    document.getElementById('detail-badges').innerHTML =
        badge(u.cls, u.label) +
        badge(done ? 'sgg-badge--completed' : 'sgg-badge--progress', done ? 'Selesai' : 'Dalam Proses');
    document.getElementById('detail-progress-fill').style.setProperty('--percent', p.progress_percent + '%');
    document.getElementById('detail-progress-pct').textContent = p.progress_percent + '%';
    document.getElementById('detail-checklist-container').innerHTML = renderChecklist(p.checklists);

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Menutup modal detail dan mengembalikan scroll halaman
function closeDetailModal() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
}

// Klik / Enter / Spasi pada kartu membuka modal (delegasi: tetap jalan setelah auto refresh)
const projectList = document.getElementById('project-list');
projectList.addEventListener('click', (e) => {
    const card = e.target.closest('.sgg-project-card');
    if (card) openDetailModal(card);
});
projectList.addEventListener('keydown', (e) => {
    const card = e.target.closest('.sgg-project-card');
    if (card && (e.key === 'Enter' || e.key === ' ')) {
        e.preventDefault();
        openDetailModal(card);
    }
});

// Tutup lewat tombol, klik area gelap, atau Esc
overlay.addEventListener('click', (e) => {
    if (e.target === overlay || e.target.closest('[data-close-modal]')) closeDetailModal();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDetailModal();
});
