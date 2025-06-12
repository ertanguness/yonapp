<?php

namespace Model;

use Model\BloklarModel;
use Model\KisilerModel;
use Model\DairelerModel;
use Model\Model;
use PDO;



class AraclarModel extends Model
{
    protected $table = 'araclar';

    public function __construct()
    {
        parent::__construct($this->table);
    }
    
    public function aracEkleTableRow($id)
    {
        $Bloklar = new BloklarModel();
        $Kisiler= new KisilerModel();
        $Daireler = new DairelerModel();

        $data = $this->find($id);
        $enc_id = \App\Helper\Security::encrypt($data->id);
        
        $kisi = $Kisiler->getPersonById($data->kisi_id);
        $daire = $Daireler->DaireAdi($kisi->daire_id ?? null);
        $blok = $Bloklar->Blok($kisi->blok_id ?? null);

        return '<tr id="islem_' . $data->id . '" data-id="' . $enc_id . '">
            <td>' . $data->id . '</td>
            <td>' . ($blok->blok_adi ?: '-') . '</td>        
            <td>' . (is_object($daire) && isset($daire->daire_no) ? $daire->daire_no : '-') . '</td>
            <td>' . ($kisi->adi_soyadi ?: '-') . '</td>
            <td>' . ($kisi->telefon ?: '-') . '</td>
            <td>' . ($data->plaka ?: '-') . '</td>
            <td>' . ($data->marka_model ?: '-') . '</td>
            <td class="text-center" style="width:5%">
                <div class="flex-shrink-0">
                    <div class="dropdown align-self-start">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded font-size-24 text-dark"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item uye-islem-duzenle" href="#" data-id="' . $enc_id . '">
                                <span class="mdi mdi-account-edit font-size-18"></span> Düzenle
                            </a>
                            <a class="dropdown-item delete-car" href="#" data-id="' . $enc_id . '">
                                <span class="mdi mdi-delete font-size-18"></span> Sil
                            </a>
                        </div>
                    </div>
                </div>
            </td>
        </tr>';
    }
   
}
