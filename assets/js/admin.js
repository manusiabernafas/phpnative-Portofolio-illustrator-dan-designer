// Pratinjau gambar sebelum upload + cari logo + nama file terpilih
document.querySelectorAll('input[type=file]').forEach(inp => {
    inp.addEventListener('change', () => {
        const lbl = inp.closest('label');
        if (lbl && inp.files.length) lbl.dataset.file = inp.files.length > 1 ? inp.files.length + ' file dipilih' : inp.files[0].name;
        const prev = inp.dataset.preview && document.getElementById(inp.dataset.preview);
        if (prev && inp.files[0]) prev.src = URL.createObjectURL(inp.files[0]);
    });
});
function filterLogo(q) {
    q = q.trim().toLowerCase();
    document.querySelectorAll('.logo-grid label[data-n]').forEach(l => { l.hidden = q && !l.dataset.n.includes(q); });
}
const toast = document.querySelector('.adm-main > .toast');
if (toast) setTimeout(() => toast.classList.add('hide'), 4500);
