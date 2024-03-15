<?php 

namespace app\Helpers;
use DB;

class CommonHelper
{
    public static function token_authentication()
    {
        $data = DB::table('token_authentication')->select('token')->first();
        return $data->token;
    }
}



?>