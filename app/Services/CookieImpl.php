<?php
namespace App\Services;

class CookieImpl extends CookieService{
    protected $cookie;

    public function setCookie($cookie){
        $this->cookie = $cookie;
    }

    public function getCookie(){
        return $this->cookie;
    }
}