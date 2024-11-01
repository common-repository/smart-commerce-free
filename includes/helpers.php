<?php

function smt_smart_commerce_checkCode(){
    $key = get_option("smt_smart_commerce_consumer_key");
    $secret = get_option("smt_smart_commerce_consumer_key");
    
    if(!($key && $secret)) return false;
    return true;
}


?>