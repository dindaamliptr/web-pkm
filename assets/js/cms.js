/* Mengisi konten halaman dari API CMS. Jika API tidak tersedia atau data kosong,
   isi HTML bawaan tetap dipakai. */
(function () {
  "use strict";

  var targets = document.querySelectorAll("[data-cms], [data-cms-text], [data-cms-img], [data-cms-count]");
  if (!targets.length) return;

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }
  function icon(s) { return /^bi-[a-z0-9-]+$/.test(s) ? s : "bi-star"; }
  function img(src) { return /^(uploads\/|https?:\/\/)/.test(src) ? src : ""; }

  var R = {
    galeri: function (i) {
      return '<div class="col-md-4"><div class="galeri-item reveal in"><img src="' + esc(img(i.gambar)) +
        '" alt="' + esc(i.judul) + '"></div></div>';
    },
    "program-home": function (i) {
      return '<div class="col-md-4"><div class="card-program shadow-sm reveal in"><img src="' + esc(img(i.gambar)) +
        '" class="w-100" alt="' + esc(i.judul) + '"><div class="card-body text-start"><h5>' + esc(i.judul) +
        '</h5><p class="text-muted mb-0">' + esc(i.deskripsi) + "</p></div></div></div>";
    },
    program: function (i) {
      return '<div class="col-md-6 col-lg-4"><div class="card-program shadow-sm reveal in"><img src="' + esc(img(i.gambar)) +
        '" class="w-100" alt="' + esc(i.judul) + '"><div class="card-body">' +
        (i.kategori ? '<span class="badge badge-program mb-2">' + esc(i.kategori) + "</span>" : "") +
        "<h5>" + esc(i.judul) + '</h5><p class="text-muted mb-0">' + esc(i.deskripsi) + "</p></div></div></div>";
    },
    keunggulan: function (i) {
      return '<div class="col-md-4"><div class="card-keunggulan reveal in"><div class="icon-wrap"><i class="bi ' +
        icon(i.ikon) + '"></i></div><h5>' + esc(i.judul) + '</h5><p class="text-muted mb-0">' + esc(i.deskripsi) + "</p></div></div>";
    },
    ekskul: function (i) {
      return '<div class="col-md-3 col-6"><div class="card-keunggulan reveal in"><div class="icon-wrap"><i class="bi ' +
        icon(i.ikon) + '"></i></div><h6 class="mb-0">' + esc(i.judul) + "</h6></div></div>";
    },
    testimoni: function (i) {
      return '<div class="col-md-4"><div class="card-testimoni text-start reveal in"><div class="kutip">&#8220;</div>' +
        '<p class="text-muted">' + esc(i.isi) + '</p><h6 class="mb-0 fw-bold">' + esc(i.nama) + "</h6>" +
        (i.keterangan ? '<small class="text-muted">' + esc(i.keterangan) + "</small>" : "") + "</div></div>";
    }
  };

  fetch("api/content.php", { headers: { Accept: "application/json" } })
    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(function (d) {
      document.querySelectorAll("[data-cms]").forEach(function (el) {
        var kind = el.getAttribute("data-cms");
        var list = kind === "program-home"
          ? (d.program || []).filter(function (p) { return +p.beranda === 1; })
          : d[kind];
        if (!list || !list.length || !R[kind]) return;
        el.innerHTML = list.map(R[kind]).join("");
      });

      var s = d.pengaturan || {};
      document.querySelectorAll("[data-cms-text]").forEach(function (el) {
        var v = s[el.getAttribute("data-cms-text")];
        if (!v) return;
        el.textContent = el.hasAttribute("data-cms-quote") ? "“" + v + "”" : v;
      });
      document.querySelectorAll("[data-cms-img]").forEach(function (el) {
        var v = img(s[el.getAttribute("data-cms-img")] || "");
        if (v) el.src = v;
      });
      document.querySelectorAll("[data-cms-count]").forEach(function (el) {
        var v = s[el.getAttribute("data-cms-count")];
        if (v == null || isNaN(+v)) return;
        el.dataset.count = v;
        // Jika animasi hitung sudah selesai sebelum data tiba, tampilkan nilai baru langsung.
        if (el.textContent !== "0") el.textContent = v;
      });
    })
    .catch(function () { /* pakai isi HTML bawaan */ });
})();
