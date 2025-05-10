<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borç Ödeme</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="#">Borçlarım</a></li>
            <li class="breadcrumb-item">Borç Ödeme</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <select class="form-select me-2" id="yearFilter" style="width: auto">
                    <option selected disabled>Yıl Seçiniz &nbsp;&nbsp;</option>
                    <option value="2023">2023</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                </select>
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="dues/user-payment/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-success" id="saveuserPayment">
                    <i class="feather-check-circle me-2"></i>
                    Seçili Borçları Öde
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <form id="paymentForm" action="#" method="POST">
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle datatables">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" class="custom-control-input" id="selectAll">
                                            <label class="custom-control-label" for="selectAll"></label>
                                        </div>
                                    </th>
                                    <th>Başlık</th>
                                    <th>Tutar (₺)</th>
                                    <th>Ceza Tutarı (₺)</th>
                                    <th>Toplam Borç (₺)</th>
                                    <th>Son Tarih</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" name="selected_debts[]" value="1" class="custom-control-input debt-checkbox" id="debt1">
                                            <label class="custom-control-label" for="debt1"></label>
                                        </div>
                                    </td>
                                    <td><i class="fas fa-file-invoice me-1 text-primary"></i> Ocak Aidatı</td>
                                    <td>500.00</td>
                                    <td>50.00</td>
                                    <td>550.00</td>
                                    <td>31.01.2025</td>
                                    <td><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Bekliyor</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" name="selected_debts[]" value="3" class="custom-control-input debt-checkbox" id="debt3">
                                            <label class="custom-control-label" for="debt3"></label>
                                        </div>
                                    </td>
                                    <td><i class="fas fa-wrench me-1 text-danger"></i> Tesisat Gideri</td>
                                    <td>300.00</td>
                                    <td>30.00</td>
                                    <td>330.00</td>
                                    <td>15.03.2025</td>
                                    <td><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Bekliyor</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" name="selected_debts[]" value="5" class="custom-control-input debt-checkbox" id="debt5">
                                            <label class="custom-control-label" for="debt5"></label>
                                        </div>
                                    </td>
                                    <td><i class="fas fa-bolt me-1 text-warning"></i> Elektrik Gideri</td>
                                    <td>220.50</td>
                                    <td>22.05</td>
                                    <td>242.55</td>
                                    <td>05.05.2025</td>
                                    <td><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Bekliyor</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header  text-white rounded-top">
                <h5 class="modal-title" id="paymentModalLabel"><i class="fas fa-wallet me-3 fs-3"></i>Ödeme SAyfası</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Seçilen borçlar listesi -->
                <div class="mb-3">
                    <h6 class="fw-bold mb-2">Seçilen Borçlar</h6>
                    <ul id="selectedDebtsList" class="list-group mb-3"></ul>
                    <div class="text-end fw-bold fs-5">Toplam: <span id="totalAmount">0.00</span> ₺</div>
                </div>

                <!-- Ödeme seçimi -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Ödeme Yöntemi</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="sec">Lütfen Ödeme Yöntemi Seçiniz</option>
                        <option value="havale">EFT / Havale</option>
                        <option value="kredi">Kredi Kartı</option>
                    </select>
                </div>

                <!-- EFT Bilgileri -->
                <div id="eftSection" class="border p-3 rounded-3 bg-light d-none">
                    <h6 class="fw-bold"><i class="fas fa-university me-2"></i>Banka Bilgileri</h6>
                    <p><strong>Banka:</strong> Ziraat Bankası</p>
                    <p class="d-flex align-items-center gap-2">
                        <strong>IBAN:</strong> <span id="ibanText">TR12 0001 0012 3456 7890 1234 56</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyIban()">
                            <i class="fas fa-copy me-1"></i>Kopyala
                        </button>
                    </p>
                </div>

                <!-- Kredi Kartı Alanları -->
                <div id="creditCardSection" class="border p-3 rounded-3 bg-light d-none">
                    <h6 class="fw-bold"><i class="fas fa-credit-card me-2"></i>Kredi Kartı ile Ödeme</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kart Üzerindeki İsim</label>
                            <input type="text" class="form-control" placeholder="Ad Soyad">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kart Numarası</label>
                            <input type="text" class="form-control" placeholder="0000 0000 0000 0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Son Kullanma Tarihi</label>
                            <input type="text" class="form-control" placeholder="MM / YY">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" placeholder="123">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button class="btn btn-success"><i class="fas fa-check-circle me-1"></i>Ödemeyi Tamamla</button>
            </div>
        </div>
    </div>
</div>


<script>
document.getElementById('selectAll').addEventListener('change', function () {
    const isChecked = this.checked;
    document.querySelectorAll('.debt-checkbox').forEach(function (checkbox) {
        checkbox.checked = isChecked;
        checkbox.dispatchEvent(new Event('change')); // istersen başka işlemler tetiklenebilir
    });
});


    document.getElementById('yearFilter').addEventListener('change', function() {
        const selectedYear = this.value;
        document.querySelectorAll('#debtTable tr').forEach(function(row) {
            row.style.display = row.getAttribute('data-year') === selectedYear ? '' : 'none';
        });
    });

    document.getElementById('saveuserPayment').addEventListener('click', function() {
        const selectedDebts = document.querySelectorAll('.debt-checkbox:checked');

        if (selectedDebts.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Borç Seçilmedi!',
                text: 'Lütfen ödeme yapmak için en az bir borç seçin.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-4 shadow'
                }
            });
            return;
        }

        // Listeyi temizle
        const listElement = document.getElementById('selectedDebtsList');
        listElement.innerHTML = '';
        let total = 0;

        selectedDebts.forEach(function(checkbox) {
            const row = checkbox.closest('tr');
            const title = row.cells[1].textContent.trim();
            const amount = parseFloat(row.cells[4].textContent.replace(',', '.')) || 0;

            // Listeye ekle
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `<span>${title}</span><span class="fw-bold">${amount.toFixed(2)} ₺</span>`;
            listElement.appendChild(li);

            total += amount;
        });

        document.getElementById('totalAmount').textContent = total.toFixed(2);

        // Modal göster
        const myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        myModal.show();
    });


    document.getElementById('paymentMethod').addEventListener('change', function() {
        const method = this.value;
        document.getElementById('eftSection').classList.toggle('d-none', method !== 'havale');
        document.getElementById('creditCardSection').classList.toggle('d-none', method !== 'kredi');
    });


    function copyIban() {
        const iban = document.getElementById("ibanText").textContent;
        navigator.clipboard.writeText(iban).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'IBAN Kopyalandı',
                text: iban,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        });
    }
</script>