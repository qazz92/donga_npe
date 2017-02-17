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

    public function getDongaPage($loginPage,$dis)
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
        }
    }
//    public function getDongaPage($loginPage,$dis)
//    {
//        $cacehd = Cache::get('login_'.$dis.'_'.$this->user_id);
//        if ($cacehd == null){
//            try {
//                $client = new \Goutte\Client();
//                $guzzleClient = new \GuzzleHttp\Client(array(
//                    'timeout' => 90,
//                    'verify' => false,
//                ));
//                $client->setClient($guzzleClient);
//                $crawlerLogin = $client->request('GET', $loginPage);
//                $form = $crawlerLogin->selectButton('ibtnLogin')->form();
//                $client->submit($form, array('txtStudentCd' => $this->user_id, 'txtPasswd' => $this->user_pw));
//                $cookie  = $client->getCookieJar()->get('.ASPXAUTH');
//                $expiresAt = Carbon::now()->addMinutes(20);
//                $authCookie = explode(';',$cookie)[0];
//                $domain = explode(';',$cookie)[1];
//                $name = $client->getCookieJar()->get('sName');
//                $nameCookie = explode(';',$name)[0];
//                Log::info(explode('=',$nameCookie)[1]);
//                Cache::put('login_'.$dis.'_'.$this->user_id, array('authCookie'=>explode('=',$authCookie)[1],'sName'=>explode('=',$nameCookie)[1],'domain'=>explode('=',$domain)[1]), $expiresAt);
//                return array('result_code' => 1, 'client' => $client, 'user_id' => $this->user_id);
//            } catch (\Exception $e) {
//                echo $e;
////                return array('result_code' => 500);
//            }
//        }
//        else {
//            Log::info('dongaPage REDIS');
//            $cookieJar1= new \GuzzleHttp\Cookie\CookieJar(true);
//            $cookieJar1->setCookie(new \GuzzleHttp\Cookie\SetCookie(
//                [
//                    'Domain'  => $cacehd["domain"],
//                    'Name'    => 'sName',
//                    'Value'   => $cacehd["sName"],
//                    'Discard' => true
//                ]
//            ));
//            $cookieJar1->setCookie(new \GuzzleHttp\Cookie\SetCookie(
//                [
//                    'Domain'  => $cacehd["domain"],
//                    'Name'    => '.ASPXAUTH',
//                    'Value'   => $cacehd["authCookie"],
//                    'Discard' => true
//                ]
//            ));
//            $client = new \Goutte\Client();
//            $guzzleclient = new \GuzzleHttp\Client([
//                'timeout' => 90,
//                'verify' => false,
//                'cookies' => $cookieJar1
//            ]);
//            $client->setClient($guzzleclient);
//            var_dump($client);
////            return array('result_code' => 1, 'client' => $client, 'user_id' => $this->user_id);
//        }
//    }

    public function getGrade($crawlerTable, $chunk)
    {
        $label = $crawlerTable->filter('td.td6')->eq(2);
        $getAllGrade = $label->filter('span#lblCdtPass')->text();
        $getAvgGrade = $label->filter('span#lblGpa')->text();
        $arr = array();
        $label->filter('table')->eq(2)->filter('tr')->filter('td')->each(function ($node, $i) use (&$arr) {
            if (strcmp($node->text(), "")) {
                $arr[] = trim($node->text());
            } else {
                $arr[] = '';
            }
        });
        $chArr = array_chunk($arr, $chunk);
        $result = array('result_code' => 1, 'result_body' => array('allGrade' => $getAllGrade, 'avgGrade' => $getAvgGrade, 'detail' => $chArr));
        return $result;
    }

    public function getTimetableLoop($day, $crawlerTable)
    {
        $result = array();
        for ($j = 3; $j < 31; $j++) {
            $result[$day][] = $crawlerTable->filter('table#htblTime')->filter('tr')->eq($j)->filter('td')->eq(1)->text() . $j . '. 아마도 ' . $day;
        }
        return $result;
    }
}