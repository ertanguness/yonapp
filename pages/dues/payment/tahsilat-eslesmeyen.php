<?php 

use App\Helper\Form;
use App\Helper\Date;
use App\Helper\Helper;
use Model\TahsilatHavuzuModel;
use Model\DairelerModel;

$DaireModel = new DairelerModel();
$HavuzModel = new TahsilatHavuzuModel();

$tahsilat_havuzu = $HavuzModel->TahsilatHavuzu($site_id);

$daireler  = $DaireModel->SitedekiDaireler($site_id);
// Daire seçiniz diye option ekle
$daireler = array_merge([['id' => '', 'daire_kodu' => 'Daire Seçiniz']], $daireler);
$optionsForSelect = array_column($daireler, 'daire_kodu', 'id');



?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tahsilat Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Tahsilatlar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>

                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <a href="index?p=dues/payment/list" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </a>

            </div>
        </div>
    </div>
</div>
<div class="main-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5>Eşleşmeyen Tahsilat Yüklemeleri</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive ">
                        <div id="projectList_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-hover datatables no-footer" id="projectList"
                                        aria-describedby="projectList_info">
                                        <thead>
                                      

                                            <tr>
                                                <th>İşlem Tarihi</th>
                                                <th>Açıklama</th>
                                                <th class="text-end">Tahsilat Tutarı  </th>
                                                <th>Daire Kodu</th>
                                                <th>Daire No</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($tahsilat_havuzu as $havuz) {?>
                                            <tr class="single-item odd">
                                                <td class="sorting_1">
                                                    <?php echo Date::Ymd($havuz->islem_tarihi); ?>
                                                </td>
                                                <td class="project-name-td" data-bs-toggle="tooltip" data-bs-title="<?php echo $havuz->ham_aciklama; ?>">
                                                    <div class="hstack gap-4">
                                                      
                                                        <div>
                                                            <a href="projects-view.html"
                                                                class="text-truncate-1-line">
                                                            <?php echo $havuz->ham_aciklama; ?>
                                                            </a>
                                                            <p
                                                                class="fs-12 text-muted mt-2 text-truncate-1-line project-list-desc">
                                                                <?php echo $havuz->referans_no; ?>
                                                                .</p>
                                                            <div
                                                                class="project-list-action fs-12 d-flex align-items-center gap-3 mt-2">
                                                                <a href="javascript:void(0);">Esleşen Havuza Gönder</a>
                                                                <span class="vr text-muted"></span>
                                                               
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                             
                                                <td class="text-end">
                                                    <?php echo Helper::formattedMoney($havuz->tahsilat_tutari) ; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    echo Form::Select2(
                                                        'daire_kodu', // name
                                                        'daire_kodu' . uniqid(), // id
                                                        $optionsForSelect, // options
                                                        '', // selected value
                                                        'form-select select2 w-100 daire_kodu'  // class                                                    
                                                    )
                                                   
                                                   ?>
                                                </td>
                                                <td>
                                                    <select class="form-control select2 daire_kisi"
                                                        id="daire_kisi_<?php echo uniqid(); ?>" name="daire_kisi">
                                                      
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2 justify-content-end">
                                                        <a href="projects-view.html" class="avatar-text avatar-md">
                                                            <i class="feather feather-eye"></i>
                                                        </a>
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0)" class="avatar-text avatar-md"
                                                                data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                                <i class="feather feather-more-horizontal"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-edit-3 me-3"></i>
                                                                        <span>Edit</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item printBTN"
                                                                        href="javascript:void(0)">
                                                                        <i class="feather feather-printer me-3"></i>
                                                                        <span>Print</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-clock me-3"></i>
                                                                        <span>Remind</span>
                                                                    </a>
                                                                </li>
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-archive me-3"></i>
                                                                        <span>Archive</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i
                                                                            class="feather feather-alert-octagon me-3"></i>
                                                                        <span>Report Spam</span>
                                                                    </a>
                                                                </li>
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-trash-2 me-3"></i>
                                                                        <span>Delete</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } ?>


                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="projectList_info" role="status" aria-live="polite">
                                        Showing 1 to 10 of 10 entries</div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" id="projectList_paginate">
                                        <ul class="pagination">
                                            <li class="paginate_button page-item previous disabled"
                                                id="projectList_previous"><a href="#" aria-controls="projectList"
                                                    data-dt-idx="previous" tabindex="0" class="page-link">Previous</a>
                                            </li>
                                            <li class="paginate_button page-item active"><a href="#"
                                                    aria-controls="projectList" data-dt-idx="0" tabindex="0"
                                                    class="page-link">1</a></li>
                                            <li class="paginate_button page-item next disabled" id="projectList_next"><a
                                                    href="#" aria-controls="projectList" data-dt-idx="next" tabindex="0"
                                                    class="page-link">Next</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var url = 'pages/dues/payment/api.php'; // Define the URL for the AJAX request

$(document).ready(function() {
    $('.daire_kodu').select2(); // Initialize select2 for .daire_kodu elements

    $(document).on('change', '.daire_kodu', function() {
        var $this = $(this);
        var daire_kisisi = $this.closest('tr').find('.daire_kisi');

        var formData = new FormData();
        formData.append('daire_id', $this.val());
        formData.append('action', 'get_daire_kisileri'); // Specify the action for the API



        for (var pair of formData.entries()) {
            console.log(pair[0] + ', ' + pair[1]);
        }

        fetch(url, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.status === 'success') {
                // Clear existing options
                daire_kisisi.empty();
                
                // Add the new options
                $.each(data.kisiler, function(index, kisi) {
                    daire_kisisi.append(new Option(kisi.uyelik_tipi + " | " + kisi.adi_soyadi, kisi.id));
                });

                // Reinitialize select2 for the updated element
                daire_kisisi.select2();
            } else {
                console.error('Error fetching daire kisileri:', data.message);
            }
            
        })

    });
});
</script>