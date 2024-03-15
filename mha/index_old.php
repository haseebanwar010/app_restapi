<?php 

// $webhost        = 'localhost';
// $webusername    = 'u847441908_testing_uvs';
// $webpassword    = 'Uvs_testing@3030';
// $webdbname      = 'u847441908_testing_uvs';
// $webcon         = mysqli_connect($webhost, $webusername, $webpassword, $webdbname);
// if (mysqli_connect_errno())
// {
//     echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
// }



$mobhost        = 'localhost';
$mobusername    = 'u847441908_uvschoollive';
$mobpassword    = 'UVschool@12345';
$mobdbname      = 'u847441908_uvschoollive';
$mobcon         = mysqli_connect($mobhost, $mobusername, $mobpassword, $mobdbname);
if (mysqli_connect_errno())
{
    echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
}

///////////////////////////////////////////////////////////////First Script Starts////////////////////////////////////////////////////////////////////////
// for country
// $record=mysqli_query($mobcon, 'SELECT sh_users.id,sh_users.country, sh_countries.country_code, sh_countries.id as country_tab_id FROM sh_users LEFT JOIN sh_countries ON sh_users.country=sh_countries.id where sh_users.school_id=96');
// $record=mysqli_query($mobcon, 'SELECT sh_users.id,sh_users.country, sh_countries.country_code, sh_countries.id as country_tab_id FROM sh_users LEFT JOIN sh_countries ON sh_users.country=sh_countries.id');

// for nationality
// $record=mysqli_query($mobcon, 'SELECT sh_users.id,sh_users.nationality, sh_countries.country_code, sh_countries.id as country_tab_id FROM sh_users LEFT JOIN sh_countries ON sh_users.nationality=sh_countries.id where sh_users.school_id=96');
// $record=mysqli_query($mobcon, 'SELECT sh_users.id,sh_users.nationality, sh_countries.country_code, sh_countries.id as country_tab_id FROM sh_users LEFT JOIN sh_countries ON sh_users.nationality=sh_countries.id');

// $result=mysqli_fetch_all($record);
// foreach($result as $row)
// {
//     if($row[1]!=0)
//     {
//         $sql="UPDATE sh_users SET parent_phone_code='".$row[2]."' WHERE id=$row[0]";
//         $result=$mobcon->query($sql);
//         echo $result;
//     }
    
// }
///////////////////////////////////////////////////////////////First Script Ends////////////////////////////////////////////////////////////////////////

// $record=mysqli_query($mobcon, 'SELECT id,contact FROM sh_users where school_id=96');
$record=mysqli_query($mobcon, 'SELECT id,contact FROM sh_users');
$result=mysqli_fetch_all($record);

//1st script
// foreach($result as $key => $res)
// {
//     $u_phone_number='';
//     $sl=explode('-', $res[1]);
//     if(sizeof($sl) > 0)
//     {
//         $result[$key][1]=NULL;
//         foreach($sl as $ckey => $csl)
//         {
//             $result[$key][1].=$csl;
//             if($ckey > 0)
//             {
//                 $u_phone_number.=$csl;
//             }
            
//             if(sizeof($sl) == $ckey+1)
//             {
//                 $result[$key][2]=$u_phone_number;
//                 $result[$key][1]=trim( $result[$key][1]);
//             }
//         }
//     }
//     else
//     {
//         $result[$key][2]=$u_phone_number;
//     }
// }

// foreach($result as $key => $res)
// {
//     $u_phone_number='';
//     $sl=explode(' ', $res[1]);
//     if(sizeof($sl) > 0)
//     {
//         $result[$key][1]=NULL;
//         foreach($sl as $ckey => $csl)
//         {
//             $result[$key][1].=$csl;
//             if($ckey > 0)
//             {
//                 $u_phone_number.=$csl;
//             }
            
//             if(sizeof($sl) == $ckey+1)
//             {
//                 $result[$key][2]=$u_phone_number;
//                 $result[$key][1]=trim( $result[$key][1]);
//             }
//         }
//     }
//     else
//     {
//         $result[$key][2]=$u_phone_number;
//     }
// }

// echo '<pre>';
// print_r($result);
// die;

//3ird script
foreach($result as $key => $row)
{
    // if($key==1)
    // {
        $sql="UPDATE sh_users SET mobile_phone='".$row[1]."' WHERE id=$row[0]";
        $result=$mobcon->query($sql);
        echo $result;
    // }
    
}


// $testing_data=mysqli_fetch_all($record);
// foreach($testing_data as $key => $row)
// {
//     $sql="UPDATE sh_users SET email='".$row[1]."' WHERE id=$row[0]";
//     $result=$mobcon->query($sql);
//     echo $result;
// }


?>