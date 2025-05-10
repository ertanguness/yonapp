<div class="card-body task-info">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="taskTitle" class="fw-semibold">Görev Başlığı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-tasks"></i></div>
                <input type="text" class="form-control" id="taskTitle" placeholder="Görev Başlığını Giriniz">
            </div>
        </div>
        <div class="col-lg-2">
            <label for="taskStatus" class="fw-semibold">Görev Durumu:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-check-circle"></i></div>
                <select class="form-control" id="taskStatus">
                    <option value="Atandı">Atandı</option>
                    <option value="Devam Ediyor">Devam Ediyor</option>
                    <option value="Tamamlandı">Tamamlandı</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="taskStartDate" class="fw-semibold">Başlangıç Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                <input type="date" class="form-control" id="taskStartDate">
            </div>
        </div>
        <div class="col-lg-2">
            <label for="taskEndDate" class="fw-semibold">Bitiş Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-check"></i></div>
                <input type="date" class="form-control" id="taskEndDate">
            </div>
        </div>
    </div>
</div>
