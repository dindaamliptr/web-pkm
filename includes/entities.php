<?php
// Definisi konten yang bisa dikelola di admin. Nama tabel & kolom di sini dipakai
// langsung di SQL, jadi hanya boleh berisi nilai tetap (bukan input pengguna).
return [
    'galeri' => [
        'label' => 'Galeri',
        'table' => 'galeri',
        'order' => 'urutan ASC, id DESC',
        'list'  => ['gambar', 'judul', 'urutan'],
        'fields' => [
            'judul'  => ['label' => 'Keterangan foto', 'type' => 'text', 'required' => true, 'max' => 150],
            'gambar' => ['label' => 'Foto', 'type' => 'image', 'required' => true],
            'urutan' => ['label' => 'Urutan (kecil tampil dulu)', 'type' => 'number', 'default' => 0],
        ],
    ],
    'program' => [
        'label' => 'Program',
        'table' => 'program',
        'order' => 'urutan ASC, id ASC',
        'list'  => ['gambar', 'judul', 'kategori', 'beranda', 'urutan'],
        'fields' => [
            'judul'     => ['label' => 'Nama program', 'type' => 'text', 'required' => true, 'max' => 120],
            'kategori'  => ['label' => 'Kategori (mis. Keagamaan, Akademik)', 'type' => 'text', 'max' => 50],
            'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea', 'required' => true, 'max' => 600],
            'gambar'    => ['label' => 'Gambar', 'type' => 'image', 'required' => true],
            'beranda'   => ['label' => 'Tampilkan juga di Beranda', 'type' => 'checkbox'],
            'urutan'    => ['label' => 'Urutan (kecil tampil dulu)', 'type' => 'number', 'default' => 0],
        ],
    ],
    'keunggulan' => [
        'label' => 'Keunggulan',
        'table' => 'keunggulan',
        'order' => 'urutan ASC, id ASC',
        'list'  => ['ikon', 'judul', 'urutan'],
        'fields' => [
            'judul'     => ['label' => 'Judul', 'type' => 'text', 'required' => true, 'max' => 100],
            'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea', 'required' => true, 'max' => 400],
            'ikon'      => ['label' => 'Ikon Bootstrap Icons (mis. bi-book-half)', 'type' => 'icon', 'required' => true,
                            'help' => 'Cari nama ikon di icons.getbootstrap.com, lalu tulis dengan awalan "bi-".'],
            'urutan'    => ['label' => 'Urutan (kecil tampil dulu)', 'type' => 'number', 'default' => 0],
        ],
    ],
    'ekskul' => [
        'label' => 'Ekstrakurikuler',
        'table' => 'ekskul',
        'order' => 'urutan ASC, id ASC',
        'list'  => ['ikon', 'judul', 'urutan'],
        'fields' => [
            'judul'  => ['label' => 'Nama ekstrakurikuler', 'type' => 'text', 'required' => true, 'max' => 80],
            'ikon'   => ['label' => 'Ikon Bootstrap Icons (mis. bi-trophy)', 'type' => 'icon', 'required' => true,
                         'help' => 'Cari nama ikon di icons.getbootstrap.com, lalu tulis dengan awalan "bi-".'],
            'urutan' => ['label' => 'Urutan (kecil tampil dulu)', 'type' => 'number', 'default' => 0],
        ],
    ],
    'testimoni' => [
        'label' => 'Testimoni',
        'table' => 'testimoni',
        'order' => 'urutan ASC, id ASC',
        'list'  => ['nama', 'keterangan', 'urutan'],
        'fields' => [
            'nama'       => ['label' => 'Nama wali murid', 'type' => 'text', 'required' => true, 'max' => 80],
            'keterangan' => ['label' => 'Keterangan (mis. Wali Murid Kelas 3)', 'type' => 'text', 'max' => 80],
            'isi'        => ['label' => 'Isi testimoni', 'type' => 'textarea', 'required' => true, 'max' => 500],
            'urutan'     => ['label' => 'Urutan (kecil tampil dulu)', 'type' => 'number', 'default' => 0],
        ],
    ],
];
