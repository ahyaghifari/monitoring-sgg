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
