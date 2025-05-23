<div class="card-body people-info">
    <!-- Blok Adı ve Daire No -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="blockName" class="fw-semibold">Blok Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-building"></i></div>
                <select class="form-select select2 w-100" id="blockName" required>
                    <option value="">Blok Adı Seçiniz</option>
                    <!-- Add options here -->
                </select>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="apartmentNo" class="fw-semibold">Daire No:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-door-closed"></i></div>
                <select class="form-select select2 w-100" id="apartmentNo" required>
                    <option value="">Daire No Seçiniz</option>
                    <!-- Add options here -->
                </select>
            </div>
        </div>
    </div>

    <!-- TC Kimlik No / Pasaport No ve Konut Sakini Türü -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="tcPassportNo" class="fw-semibold">TC Kimlik No / Pasaport No:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                <input type="text" class="form-control" id="tcPassportNo" placeholder="TC Kimlik No veya Pasaport No Giriniz" maxlength="11" required>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="residentType" class="fw-semibold">Konut Sakini Türü:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-user"></i></div>
                <select class="form-select select2 w-100" id="residentType" required>
                    <option value="">Seçiniz</option>
                    <option value="owner">Kat Maliki</option>
                    <option value="tenant">Kiracı</option>
                    <option value="employee">Çalışan</option>
                    <option value="guest">Misafir</option>
                    <option value="empty">Boş</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Doğum Bilgileri -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="birthDate" class="fw-semibold">Doğum Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                <input type="date" class="form-control" id="birthDate" required>
            </div>
        </div>
        <div class="col-lg-2">
            <label for="gender" class="fw-semibold">Cinsiyet:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-venus-mars"></i></div>
                <select class="form-select select2 w-100" id="gender" required>
                    <option value="">Cinsiyet Seçiniz</option>
                    <option value="male">Erkek</option>
                    <option value="female">Kadın</option>
                </select>
            </div>
        </div>
    </div>

    <!-- İletişim Bilgileri -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="phoneNumber" class="fw-semibold">Telefon Numarası:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-phone"></i></div>
                <input type="text" class="form-control" id="phoneNumber" placeholder="Telefon Numarası Giriniz" required>
            </div>
        </div>
        <div class="col-lg-2">
            <label for="email" class="fw-semibold">E-Posta Adresi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                <input type="email" class="form-control" id="email" placeholder="E-posta Adresi Giriniz">
            </div>
        </div>
    </div>

    <!-- Giriş Tarihi / Satın Alma Tarihi ve Çıkış Tarihi -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2"> 
            <label for="entryDate" class="fw-semibold">Giriş Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-check"></i></div>
                <input type="date" class="form-control" id="entryDate" required>
            </div>
        </div>
   
        <div class="col-lg-2">
            <label for="exitDate" class="fw-semibold">Çıkış Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-times"></i></div>
                <input type="date" class="form-control" id="exitDate">
            </div>
        </div>
    </div>
     <!-- Satın Alma Tarihi -->
     <div class="row mb-4 align-items-center">
        <div class="col-lg-2"> 
            <label for="buyDate" class="fw-semibold">Satın Alma Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-shopping-cart"></i></div>
                <input type="date" class="form-control" id="buyDate" required>
            </div>
        </div> 
    </div>
</div>
