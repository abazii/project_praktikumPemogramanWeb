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

  // 2. VALIDASI FORM SIGN IN (NAMA, HP, ALAMAT, EMAIL & PASSWORD) di signin.php
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

  // 3. PERHITUNGAN DP 50% QRIS & VALIDASI KHUSUS UPLOAD FOTO di booking.php
  const selectService = document.getElementById("selectService");
  const inputHargaService = document.getElementById("inputHargaService");
  const labelHargaService = document.getElementById("labelHargaService");
  const labelMinimalDp = document.getElementById("labelMinimalDp");
  const inputJumlahDp = document.getElementById("inputJumlahDp");
  const inputBuktiBayar = document.getElementById("inputBuktiBayar");
  const previewBuktiBox = document.getElementById("previewBuktiBox");
  const previewBuktiImg = document.getElementById("previewBuktiImg");
  const previewBuktiNama = document.getElementById("previewBuktiNama");
  const formBooking = document.getElementById("formBooking");

  function formatRupiah(angka) {
    return "Rp " + Number(angka).toLocaleString("id-ID");
  }

  if (
    selectService &&
    inputHargaService &&
    labelHargaService &&
    labelMinimalDp
  ) {
    selectService.addEventListener("change", function () {
      const opsiTerpilih = selectService.options[selectService.selectedIndex];
      const harga = parseInt(opsiTerpilih.getAttribute("data-harga")) || 0;
      const minimalDp = harga * 0.5;

      inputHargaService.value = harga;
      labelHargaService.textContent = formatRupiah(harga);
      labelMinimalDp.textContent = formatRupiah(minimalDp);

      if (inputJumlahDp && minimalDp > 0) {
        inputJumlahDp.value = minimalDp;
      }
    });
  }

  // Validasi & preview langsung saat customer memilih file foto bukti pembayaran
  if (inputBuktiBayar) {
    inputBuktiBayar.addEventListener("change", function () {
      const file = inputBuktiBayar.files[0];
      if (!file) {
        if (previewBuktiBox) previewBuktiBox.style.display = "none";
        return;
      }

      if (!file.type.startsWith("image/")) {
        alert("Hanya bisa upload file foto/gambar (JPG, JPEG, PNG, WEBP)!");
        inputBuktiBayar.value = "";
        if (previewBuktiBox) previewBuktiBox.style.display = "none";
        return;
      }

      if (previewBuktiBox && previewBuktiImg && previewBuktiNama) {
        previewBuktiImg.src = URL.createObjectURL(file);
        previewBuktiNama.textContent = file.name;
        previewBuktiBox.style.display = "block";
      }
    });
  }

  if (formBooking && inputHargaService && inputJumlahDp) {
    formBooking.addEventListener("submit", function (e) {
      const harga = parseInt(inputHargaService.value) || 0;
      const dp = parseInt(inputJumlahDp.value) || 0;
      const minimalDp = harga * 0.5;

      if (harga <= 0) {
        e.preventDefault();
        alert("Silakan pilih layanan (Services) terlebih dahulu!");
        return;
      }

      if (dp <= 0 || dp < minimalDp) {
        e.preventDefault();
        alert(
          "Booking GAGAL! Pembayaran QRIS wajib DP minimal 50% (" +
            formatRupiah(minimalDp) +
            ") dari harga service.",
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
