document.addEventListener("DOMContentLoaded", function () {
  // 1. VALIDASI FORM LOGIN (EMAIL & PASSWORD) di index.php
  const formLogin = document.getElementById("formLogin");
  if (formLogin) {
    formLogin.addEventListener("submit", function (e) {
      const email = document.getElementById("loginEmail").value.trim();
      const password = document.getElementById("loginPassword").value.trim();
      const polaEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (email === "" || password === "") {
        e.preventDefault();
        alert("Email dan password wajib diisi!");
        return;
      }

      if (!polaEmail.test(email)) {
        e.preventDefault();
        alert("Format email tidak valid! Contoh: nama@email.com");
      }
    });
  }

  // 2. VALIDASI FORM SIGN IN di signin.php
  const formSignIn = document.getElementById("formSignIn");
  if (formSignIn) {
    formSignIn.addEventListener("submit", function (e) {
      const nama = document.getElementById("signinNama").value.trim();
      const hp = document.getElementById("signinHp").value.trim();
      const alamat = document.getElementById("signinAlamat").value.trim();
      const email = document.getElementById("signinEmail").value.trim();
      const password = document.getElementById("signinPassword").value.trim();
      const polaEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (
        nama === "" ||
        hp === "" ||
        alamat === "" ||
        email === "" ||
        password === ""
      ) {
        e.preventDefault();
        alert("Semua kolom wajib diisi!");
        return;
      }

      if (!polaEmail.test(email)) {
        e.preventDefault();
        alert("Format email tidak valid! Masukkan email dengan benar.");
        return;
      }

      if (password.length < 6) {
        e.preventDefault();
        alert("Password minimal harus 6 karakter!");
      }
    });
  }

  // 3. LOGIKA PILIH MULTIPLE SERVICES DARI DROPDOWN & HITUNG DP 50%
  const selectService = document.getElementById("selectService");
  const listServiceDipilih = document.getElementById("listServiceDipilih");
  const inputHargaService = document.getElementById("inputHargaService");
  const labelHargaService = document.getElementById("labelHargaService");
  const labelMinimalDp = document.getElementById("labelMinimalDp");
  const inputJumlahDp = document.getElementById("inputJumlahDp");
  const inputBuktiBayar = document.getElementById("inputBuktiBayar");
  const previewBuktiBox = document.getElementById("previewBuktiBox");
  const previewBuktiImg = document.getElementById("previewBuktiImg");
  const previewBuktiNama = document.getElementById("previewBuktiNama");
  const formBooking = document.getElementById("formBooking");

  let daftarServiceTerpilih = [];

  function formatRupiah(angka) {
    return "Rp " + Number(angka).toLocaleString("id-ID");
  }

  function updateTampilanService() {
    if (!listServiceDipilih) return;

    listServiceDipilih.innerHTML = "";
    let totalHarga = 0;

    daftarServiceTerpilih.forEach(function (item, index) {
      totalHarga += item.harga;

      const kartu = document.createElement("div");
      kartu.className =
        "retro-selected-tag d-flex justify-content-between align-items-center px-3 py-2";
      kartu.innerHTML =
        "<div>" +
        '<strong class="text-cream">' +
        (index + 1) +
        ". " +
        item.nama +
        "</strong> " +
        '<span class="text-mustard fw-bold">(' +
        formatRupiah(item.harga) +
        ")</span>" +
        '<input type="hidden" name="nama_service[]" value="' +
        item.nama +
        '">' +
        "</div>" +
        '<button type="button" class="btn btn-sm retro-tag-remove" data-index="' +
        index +
        '" title="Hapus layanan">✕</button>';

      listServiceDipilih.appendChild(kartu);
    });

    const minimalDp = totalHarga * 0.5;

    if (inputHargaService) inputHargaService.value = totalHarga;
    if (labelHargaService)
      labelHargaService.textContent = formatRupiah(totalHarga);
    if (labelMinimalDp) labelMinimalDp.textContent = formatRupiah(minimalDp);

    if (inputJumlahDp) {
      inputJumlahDp.value = minimalDp > 0 ? minimalDp : "";
    }
  }

  if (selectService) {
    selectService.addEventListener("change", function () {
      const opsi = selectService.options[selectService.selectedIndex];
      const namaService = opsi.value;
      const hargaService = parseInt(opsi.getAttribute("data-harga"), 10) || 0;

      if (namaService === "" || hargaService <= 0) {
        return;
      }

      const sudahAda = daftarServiceTerpilih.some(function (s) {
        return s.nama === namaService;
      });

      if (sudahAda) {
        alert(
          'Layanan "' +
            namaService +
            '" sudah dipilih! Silakan pilih layanan lain jika ingin menambah.',
        );
        selectService.selectedIndex = 0;
        return;
      }

      daftarServiceTerpilih.push({
        nama: namaService,
        harga: hargaService,
      });

      // Kembalikan dropdown ke posisi awal agar siap dipilih lagi untuk layanan ke-2, ke-3, dst.
      selectService.selectedIndex = 0;
      updateTampilanService();
    });
  }

  if (listServiceDipilih) {
    listServiceDipilih.addEventListener("click", function (e) {
      if (e.target && e.target.classList.contains("retro-tag-remove")) {
        const idx = parseInt(e.target.getAttribute("data-index"), 10);
        daftarServiceTerpilih.splice(idx, 1);
        updateTampilanService();
      }
    });
  }

  // Validasi khusus file foto saat customer memilih file bukti pembayaran (Tanpa Preview)
  if (inputBuktiBayar) {
    inputBuktiBayar.addEventListener("change", function () {
      const file = inputBuktiBayar.files[0];
      if (!file) {
        return;
      }

      if (!file.type.startsWith("image/")) {
        alert("Hanya bisa upload file foto/gambar (JPG, JPEG, PNG, WEBP)!");
        inputBuktiBayar.value = "";
      }
    });
  }

  // Validasi sebelum kirim form booking
  if (formBooking && inputHargaService && inputJumlahDp) {
    formBooking.addEventListener("submit", function (e) {
      const harga = parseInt(inputHargaService.value, 10) || 0;
      const dp = parseInt(inputJumlahDp.value, 10) || 0;
      const minimalDp = harga * 0.5;

      if (daftarServiceTerpilih.length === 0 || harga <= 0) {
        e.preventDefault();
        alert(
          "Silakan pilih minimal satu layanan (Services) dari dropdown terlebih dahulu!",
        );
        if (selectService) selectService.focus();
        return;
      }

      if (dp <= 0 || dp < minimalDp) {
        e.preventDefault();
        alert(
          "Booking GAGAL! Pembayaran QRIS wajib DP minimal 50% (" +
            formatRupiah(minimalDp) +
            ") dari total harga service.",
        );
        inputJumlahDp.focus();
        return;
      }

      if (inputBuktiBayar) {
        const file = inputBuktiBayar.files[0];
        if (!file) {
          e.preventDefault();
          alert(
            "Wajib upload foto screenshot bukti pembayaran terlebih dahulu!",
          );
          return;
        }
        if (!file.type.startsWith("image/")) {
          e.preventDefault();
          alert(
            "File tidak valid! Hanya bisa upload foto (JPG, JPEG, PNG, WEBP).",
          );
          inputBuktiBayar.value = "";
        }
      }
    });
  }

  // 4. LIVE POLLING STATUS ANTRIAN CUSTOMER (Langsung hilang jika Owner klik Selesai)
  const containerAntrian = document.getElementById("containerAntrianCustomer");
  let sudahAlertPanggil = false;

  if (containerAntrian) {
    setInterval(function () {
      fetch("booking.php?ajax_antrian=1")
        .then(function (res) {
          return res.text();
        })
        .then(function (html) {
          containerAntrian.innerHTML = html;
          const adaPanggilan =
            containerAntrian.querySelector('[data-panggil="1"]');
          if (adaPanggilan && !sudahAlertPanggil) {
            sudahAlertPanggil = true;
            alert("Anda antrian berikutnya, pastikan sudah berada di tempat!");
          }
        })
        .catch(function (err) {
          console.error("Gagal memuat status antrian:", err);
        });
    }, 4000);
  }

  // 5. MODAL PREVIEW BUKTI PEMBAYARAN di dashboard_owner.php
  const tombolBuktiList = document.querySelectorAll(".btn-lihat-bukti");
  const modalBuktiEl = document.getElementById("modalBuktiBayar");

  if (tombolBuktiList.length > 0 && modalBuktiEl) {
    const modalBukti = new bootstrap.Modal(modalBuktiEl);
    const modalJudul = document.getElementById("modalBuktiJudul");
    const modalImg = document.getElementById("modalBuktiImg");
    const modalNama = document.getElementById("modalBuktiNama");
    const modalService = document.getElementById("modalBuktiService");
    const modalDp = document.getElementById("modalBuktiDp");

    tombolBuktiList.forEach(function (btn) {
      btn.addEventListener("click", function () {
        modalJudul.textContent =
          "BUKTI PEMBAYARAN ANTRIAN " + btn.getAttribute("data-antrian");
        modalImg.src = btn.getAttribute("data-foto");
        modalNama.textContent = btn.getAttribute("data-nama");
        modalService.textContent = btn.getAttribute("data-service");
        modalDp.textContent = btn.getAttribute("data-dp");
        modalBukti.show();
      });
    });
  }
});
