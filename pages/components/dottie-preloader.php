<style>
/* Overlay'i (yükleme ekranını) stilize etme */
/* Bölgesel Overlay (yükleme ekranı) */
#loading-overlay {
    /* Parent'a (upload-card) göre kendini konumlandırır */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /* Görseldeki gibi hafif beyaz bir overlay */
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(2px);
    /* Arka planı hafifçe bulanıklaştırır (isteğe bağlı) */

    display: flex;
    justify-content: center;
    align-items: center;

    z-index: 9999;

    /* Başlangıçta gizli */
    display: none;
}

/* Animasyon ve yazıyı içeren dikey hizalama kutusu */
.loading-content {
    display: flex;
    flex-direction: column;
    /* Öğeleri dikeyde sıralar */
    align-items: center;
    /* Öğeleri yatayda ortalar */
}

/* Yükleme metni */
.loading-text {
    margin-top: 16px;
    /* Animasyon ile arasında boşluk bırakır */
    font-size: 1.2rem;
    font-weight: 500;
    color: #333;
}
</style>
<!-- Yükleme Overlay'i (Artık bu kartın içinde) -->
<div id="loading-overlay">
    <div class="loading-content">
        <!-- 1. Lottie Animasyonu-->
         <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module">
        </script>
        <div class="row text-center">

            <dotlottie-player src="https://lottie.host/89ed090e-17ef-4759-a81f-253d8ac79b03/aG8CcljtbZ.lottie"
                background="transparent" speed="1" style="width: 150px; height: 150px" loop autoplay>
            </dotlottie-player>
        </div> 
      
        <div class="row text-center">

            <p class=" text-white fs-5 mt-3">
                Veriler yükleniyor. Lütfen bekleyiniz...
            </p>
        </div>
    </div>

</div>