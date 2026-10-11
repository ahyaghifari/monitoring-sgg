// Modal detail project: progress di atas, daftar tugas checklist di bawah.
// PIC yang login (project miliknya) bisa menambah, mencentang, dan menghapus tugas di sini.

// Pemetaan nilai urgensi ke kelas badge dan label Bahasa Indonesia
const URGENCY_MAP = {
    'Low':    { cls: 'sgg-badge--low',    label: 'Rendah'  },
    'Medium': { cls: 'sgg-badge--medium', label: 'Sedang'  },
    'High':   { cls: 'sgg-badge--high',   label: 'Tinggi'  },
    'Urgent': { cls: 'sgg-badge--urgent', label: 'Darurat' },
};

const overlay = document.getElementById('detail-overlay');
const checklistBox = document.getElementById('detail-checklist-container');
const taskError = document.getElementById('task-error');
const baseUrl = (document.body.dataset.base || '').replace(/\/$/, '');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// Project yang sedang dibuka di modal (data terbaru dari server setelah tiap perubahan)
let currentProject = null;

const CHECK_ICON = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
const CLOSE_ICON = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

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

// Kirim JSON ke server; lempar Error berpesan bila gagal
async function sendJson(url, method, body) {
    const res = await fetch(baseUrl + url, {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        if (res.status === 401) throw new Error('Sesi berakhir. Muat ulang halaman lalu masuk lagi.');
        if (res.status === 403) throw new Error('Anda tidak berhak mengubah project ini.');
        throw new Error(data.message || 'Terjadi kesalahan. Coba lagi.');
    }
    return data;
}

// Daftar tugas checklist; PIC pemilik project mendapat tombol centang, hapus, dan form tambah
function renderChecklist(p) {
    const canEdit = !!p.can_edit;

    const items = p.checklists.length === 0
        ? '<p class="sgg-modal-empty">Belum ada tugas pada project ini.</p>'
        : p.checklists.map(c => {
            const box = canEdit
                ? `<button type="button" class="sgg-checklist-checkbox sgg-checklist-checkbox--btn" data-task-toggle="${c.id}" aria-pressed="${c.is_completed}" aria-label="Ubah status tugas ${escapeHtml(c.title)}">${CHECK_ICON}</button>`
                : `<div class="sgg-checklist-checkbox" aria-hidden="true">${CHECK_ICON}</div>`;
            const del = canEdit
                ? `<button type="button" class="sgg-task-del" data-task-del="${c.id}" aria-label="Hapus tugas ${escapeHtml(c.title)}">${CLOSE_ICON}</button>`
                : '';

            return `
        <div class="sgg-checklist-item ${c.is_completed ? 'checked' : ''}">
            ${box}
            <span class="sgg-checklist-label">${escapeHtml(c.title)}</span>
            <span class="sgg-checklist-state">${c.is_completed ? 'Selesai' : 'Belum Selesai'}</span>
            ${del}
        </div>`;
        }).join('');

    const form = canEdit ? `
        <form class="sgg-task-add" id="task-add-form" autocomplete="off">
            <label class="sgg-task-add-label" for="task-add-input">Tambah tugas</label>
            <div class="sgg-task-add-row">
                <input type="text" id="task-add-input" maxlength="255" required placeholder="Nama tugas baru">
                <button type="submit" class="sgg-modal-close-btn">Tambah</button>
            </div>
        </form>` : '';

    return items + form;
}

// Isi seluruh modal dari data project
function fillModal(p) {
    const u = URGENCY_MAP[p.urgency] || URGENCY_MAP['Low'];
    const done = p.status === 'Completed';

    // Warna khusus project untuk aksen header modal (kosong = tanpa aksen)
    overlay.querySelector('.sgg-modal').style.setProperty('--project-color', p.color || '');

    document.getElementById('detail-company').textContent = p.company_name;
    document.getElementById('detail-title').textContent = p.title;
    document.getElementById('detail-badges').innerHTML =
        badge(u.cls, u.label) +
        badge(done ? 'sgg-badge--completed' : 'sgg-badge--progress', done ? 'Selesai' : 'Dalam Proses');
    document.getElementById('detail-progress-fill').style.setProperty('--percent', p.progress_percent + '%');
    document.getElementById('detail-progress-pct').textContent = p.progress_percent + '%';
    checklistBox.innerHTML = renderChecklist(p);
}

// Sinkronkan kartu di daftar dengan data terbaru (progress, status) tanpa reload
function applyProjectToCard(p) {
    const card = [...document.querySelectorAll('.sgg-project-card')]
        .find(c => JSON.parse(c.dataset.project).id === p.id);
    if (!card) return;

    const done = p.status === 'Completed';
    card.dataset.project = JSON.stringify(p);
    card.dataset.status = p.status;
    card.querySelector('.sgg-progress-fill')?.style.setProperty('--percent', p.progress_percent + '%');
    const pct = card.querySelector('.sgg-progress-text');
    if (pct) pct.textContent = p.progress_percent + '%';

    const statusBadge = card.querySelectorAll('.sgg-card-badges .sgg-badge')[1];
    if (statusBadge) {
        statusBadge.className = 'sgg-badge ' + (done ? 'sgg-badge--completed' : 'sgg-badge--progress');
        statusBadge.innerHTML = '<span class="sgg-badge-dot"></span>' + (done ? 'Selesai' : 'Dalam Proses');
    }
}

function showTaskError(msg) {
    taskError.textContent = msg || '';
    taskError.hidden = !msg;
}

// Jalankan perubahan tugas, lalu perbarui modal dan kartu dengan data dari server
async function mutateTasks(request) {
    showTaskError('');
    checklistBox.classList.add('is-busy');
    try {
        const { project } = await request();
        currentProject = project;
        fillModal(project);
        applyProjectToCard(project);
    } catch (err) {
        showTaskError(err.message);
    } finally {
        checklistBox.classList.remove('is-busy');
    }
}

// Menampilkan modal detail saat kartu project diklik
function openDetailModal(cardEl) {
    currentProject = JSON.parse(cardEl.dataset.project);
    showTaskError('');
    fillModal(currentProject);

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Menutup modal detail dan mengembalikan scroll halaman
function closeDetailModal() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    currentProject = null;
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

// Centang dan hapus tugas (delegasi karena daftar dirender ulang setelah tiap perubahan)
checklistBox.addEventListener('click', (e) => {
    if (!currentProject || checklistBox.classList.contains('is-busy')) return;

    const toggle = e.target.closest('[data-task-toggle]');
    if (toggle) {
        const id = Number(toggle.dataset.taskToggle);
        const task = currentProject.checklists.find(c => c.id === id);
        if (task) mutateTasks(() => sendJson(`/tasks/${id}`, 'PATCH', { is_completed: !task.is_completed }));
        return;
    }

    const del = e.target.closest('[data-task-del]');
    if (del && confirm('Hapus tugas ini?')) {
        mutateTasks(() => sendJson(`/tasks/${del.dataset.taskDel}`, 'DELETE'));
    }
});

// Tambah tugas baru
checklistBox.addEventListener('submit', async (e) => {
    if (e.target.id !== 'task-add-form') return;
    e.preventDefault();
    if (!currentProject || checklistBox.classList.contains('is-busy')) return;

    const title = e.target.querySelector('input').value.trim();
    if (!title) return;

    await mutateTasks(() => sendJson(`/projects/${currentProject.id}/tasks`, 'POST', { title }));
    document.getElementById('task-add-input')?.focus();
});

// Tutup lewat tombol, klik area gelap, atau Esc
overlay.addEventListener('click', (e) => {
    if (e.target === overlay || e.target.closest('[data-close-modal]')) closeDetailModal();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDetailModal();
});
