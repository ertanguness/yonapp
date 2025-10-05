 <?php
    require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

    use Model\KisilerModel;

    $KisiModel = new KisilerModel();

    $id = $_GET['id'] ?? 0;
    $kisi = $KisiModel->find($id);

    ?>

 <div class="modal-header">
     <h5 class="modal-title" id="modalTitleId">Mesaj Gönder</h5>
     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 </div>
 <div class="modal-body">
     <form id="messageForm">
         <div class="mb-3">
             <label for="recipient" class="form-label">Alıcı</label>
             <input type="text" class="form-control" id="recipient" name="recipient"
                 value="<?php echo $kisi->adi_soyadi; ?>" >
         </div>

         <label for="message" class="form-label">Telefon numarası</label>
         <div class="mb-3">
             <input type="text" class="form-control" id="recipient" name="recipient"
                 value="<?php echo $kisi->telefon; ?>" >
         </div>

         <div class="mb-3">
             <label for="message" class="form-label">Mesaj</label>
             <textarea class="form-control" id="message" name="message" rows="12"
                 placeholder="Mesajınızı buraya yazın"></textarea>
         </div>
     </form>
 </div>

 <div class="modal-footer">
     <button class="btn btn-secondary" data-bs-dismiss="modal">Geri Dön</button>
     <button type="button" class="btn btn-primary" id="sendMessageBtn">Gönder</button>
 </div>