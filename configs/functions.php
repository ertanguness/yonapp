<?php 

class DateFormat
{
    public static function dmY($date)
    {
        if (empty($date)) {
            return '';
        }
        return date('d.m.Y', strtotime($date));
    }

    public static function Ymd($date)
    {
        if (empty($date)) {
            return '';
        }
        return date('Y-m-d', strtotime($date));
    }
}
  function alertdanger($message)
{
    echo '<div class="alert alert-danger bg-white text-start font-weight-600" role="alert">
            <div class="d-flex">
                <div>
                    <img src="assets/images/icons/ikaz2.png " alt="ikaz" style="width: 36px; height: 36px;">                    
                </div>
                    <div style="margin-left: 10px;">
                        <h4 class="alert-title">Hata!</h4>
                    <div class="text-secondary">' . $message . '</div>
                </div>
            </div>
        </div>';
}