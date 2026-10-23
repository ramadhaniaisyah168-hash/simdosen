// Konfirmasi hapus: form dengan atribut data-confirm
document.addEventListener('submit', function (e) {
  var f = e.target;
  if (f.dataset && f.dataset.confirm && !window.confirm(f.dataset.confirm)) {
    e.preventDefault();
  }
});
// Tampilkan/sembunyikan password
document.querySelectorAll('[data-toggle-pass]').forEach(function (b) {
  b.addEventListener('click', function () {
    var i = document.getElementById(b.dataset.togglePass);
    i.type = i.type === 'password' ? 'text' : 'password';
  });
});
