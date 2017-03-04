<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;
use Carbon\Carbon;

class GetDonga
{
    private $user_id;

    private $user_pw;

    public static $getCookie;

    public function getUserInfo(Request $request)
    {
        $this->user_id = $request->input('stuId');
        $this->user_pw = $request->input('stuPw');

        return $this;
    }

    public function getDongaPage($loginPage)
    {
        try {
            $client = new \Goutte\Client();
            $guzzleClient = new \GuzzleHttp\Client(array(
                'timeout' => 90,
                'verify' => false,
            ));
            $client->setClient($guzzleClient);
            $crawlerLogin = $client->request('GET', $loginPage);
            $form = $crawlerLogin->selectButton('ibtnLogin')->form();
            $client->submit($form, array('txtStudentCd' => $this->user_id, 'txtPasswd' => $this->user_pw));
            return array('result_code' => 1, 'client' => $client, 'user_id' => $this->user_id);
        } catch (\Exception $e) {
            return array('result_code' => 500);
//            echo $e;
        }
    }
    public function getGrade($crawlerTable, $chunk)
    {
        $label = $crawlerTable->filter('td.td6')->eq(2);
        $getAllGrade = $label->filter('span#lblCdtPass')->text();
        $getAvgGrade = $label->filter('span#lblGpa')->text();
        $arr = array();
        $label->filter('table')->eq(2)->filter('tr')->filter('td')->each(function ($node, $i) use (&$arr) {
                $arr[] = trim($node->text());
        });
        $chArr = array_chunk($arr, $chunk);
        $result = array('result_code' => 1, 'result_body' => array('allGrade' => $getAllGrade, 'avgGrade' => $getAvgGrade, 'detail' => $chArr));
        return $result;
    }

    public function getTimetableLoop($day, $crawlerTable)
    {
        $dayString = '';
        switch ($day){
            case 1:
                $dayString="mon";
                break;
            case 2:
                $dayString="tue";
                break;
            case 3:
                $dayString="wen";
                break;
            case 4:
                $dayString="thu";
                break;
            case 5:
                $dayString="fri";
                break;
        }
        $result = array();
        for ($j = 3; $j < 31; $j++) {
            $dirtyResult = $crawlerTable->filter('table#htblTime')->filter('tr')->eq($j)->filter('td')->eq($day)->html();
            $cleanResult = str_replace('<br>','|',str_replace("\xc2\xa0",' ',$dirtyResult));
            $result[$dayString][] = preg_replace('/\s+/','$1|',$cleanResult);
        }
        return $result;
    }
}