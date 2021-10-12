<?php

session_start();

class JS_Generator {
    public function __construct(){
        $this->localStorage_item = str_shuffle("15#2@18NbVcuIuoPozW0*^");
        $this->js_cookie_val;
        $this->script_list =
            ["https://mc.yandex.ru/metrika/watch.js",
            "https://www.statcounter.com/counter/counter.js", 
            "https://secure.quantserve.com/quant.js", 
            "https://secure.gaug.es/track.js"];
        $this->test_script = 
            $this->script_list[rand(0, count($this->script_list))];
    }
    public function generateNewJSCookieValue(){
        $this->js_cookie_val = str_shuffle("aFtgbzSeTTYPlmzREwzamskZmNcoIjL"); 
        $_SESSION["js_cookie_value"] = $this->js_cookie_val;
    }
    public function getTestJavaScriptCode(){
        $code = "<!DOCTYPE html><head><title>Browser Test</title></head><body>Testing your browser. Please wait.<br><br>Note: Javascript and cookies must be enabled for this test to continue! Tracking blockers may also interfere with this test!<div style='display:none;'><script>tracking_js_loaded = false;</script><script type='text/javascript' src='" . $this->test_script . "' onload='tracking_js_loaded=true'></script></div><script>setTimeout(function(){try { cookies = true; localStorage.setItem('" . $this->localStorage_item . "', 'true');} catch { var cookies = false; document.write('Malicious user suspected. Check to make sure that cookies and JavaScript are enabled, then reload to try again.');} finally { if (cookies === true){if (tracking_js_loaded === true){document.cookie = 'js=" . $this->js_cookie_val . "';setTimeout(function(){window.location = window.location.href;}, 800);}}}}, 1000);</script></body></html>";
        return $code;
    }
}

class Validator extends JS_Generator {
    public function __construct(){
        parent::__construct();
    }
    public function validateJSCookie(){ //Runs during stage 2
        $validated = false;
        if (!isset($_COOKIE["js"])){
            $validated = false;
        } elseif ($_COOKIE["js"] === $_SESSION["js_cookie_value"]){
            $validated = true;
        } elseif ($_COOKIE["js"] != $_SESSION["js_cookie_value"]){
            $validated = false;
        }
        return $validated; //Returns true or false
    }
    public function resetTest(){
        setcookie("js", "0", time() - 3600);
        unset($_SESSION["js_cookie_value"]);
        unset($_SESSION["stage"]);
    }
}

class Stage_Tester extends Validator {
    public function __construct(){
        parent::__construct();
        $this->stage1();
    }
    public function stage1(){
        if (!isset($_SESSION["stage"])){
            $_SESSION["stage"] = 1;
            $this->generateNewJSCookieValue();
            die($this->getTestJavaScriptCode());
        } else {
            $this->stage2();
        }
    }
    public function stage2(){
        if (isset($_SESSION["stage"])){
            if (!$this->validateJSCookie()){
                $this->blockVisitor();
            } elseif ($this->validateJSCookie()) {
                $this->permitEntry();
            }
        }
    }
    public function permitEntry(){
        echo "Permitted";
        $this->resetTest();
    }
    public function blockVisitor(){
        $this->resetTest();
        die("Blocked Malicious user suspected. Please reload or use a different browser to try again.");
    }
}

//$validator = new Validator();
//$validator->resetTest();
$test = new Stage_Tester();