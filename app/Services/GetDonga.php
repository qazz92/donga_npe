<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GetDonga {
    private $user_id;

    private $user_pw;

    public static $getCookie;

    public function getUserInfo(Request $request){
        $this->user_id = $request->input('stuId');
        $this->user_pw = $request->input('stuPw');

        return $this;
    }

    public function getDongaPage(){
        $loginPage = 'https://student.donga.ac.kr/Login.aspx';
        try {
            $client = new \Goutte\Client();
            $guzzleClient = new \GuzzleHttp\Client(array(
                'timeout' => 90,
                'verify' => false,
            ));
            $client->setClient($guzzleClient);
            $crawlerLogin = $client->request('GET', $loginPage);
            $form = $crawlerLogin->selectButton('ibtnLogin')->form();
            $crawler = $client->submit($form, array('txtStudentCd' => $this->user_id, 'txtPasswd' => $this->user_pw));
            $cookies = $client->getCookieJar()->all();
            $client->getCookieJar()->set($cookies[0]);
            return array('result_code'=>1,'client'=>$client,'user_id'=>$this->user_id);
            } catch (\Exception $e){
                return array('result_code'=>500);
            }
    }
    public function getGrade($crawlerTable,$chunk){
        $label = $crawlerTable->filter('td.td6')->eq(2);
        $getAllGrade = $label->filter('span#lblCdtPass')->text();
        $getAvgGrade = $label->filter('span#lblGpa')->text();
        $arr = array();
        $label->filter('table')->eq(2)->filter('tr')->filter('td')->each(function ($node,$i) use (&$arr){
            if (strcmp($node->text() , "")){
                $arr[] = trim($node->text());
            } else {
                $arr[] = '';
            }
        });
        $chArr = array_chunk($arr,$chunk);
        $result = array('result_code'=>1,'result_body'=>array('allGrade'=>$getAllGrade,'avgGrade'=>$getAvgGrade,'detail'=>$chArr));
        return $result;
    }
}