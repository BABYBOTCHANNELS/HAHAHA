<?php
error_reporting(0);

const ua = ["Ookbee-Auth-Rest-Api-Key: ATm8JzJZ3GvN6xhe1dg0Oezlv1a3MuCKl3eZg7bfgcOaAEpPWUxBREFfMjAy",
            "Ookbee-Appcode: JOYLADA_202",
            "Ookbee-Appversion: 60018001",
            "User-Agent: JOYLADA/6.18.1/Dalvik/2.1.0 (Linux; U; Android 10; Infinix X680B Build/QP1A.190711.020)",
            "Ookbee-Country: id",
            "Accept-Language: id",
            "Content-Type: application/json; charset=UTF-8"
           ];
function put($url,$data,$ua){
   $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $ua);
		return curl_exec($ch);      		
   }
function get($url, $ua){return curl($url, null, $ua)[1];}
function post($url,$data, $ua){return curl($url, $data, $ua)[1];}
function curl($url, $post = 0, $httpheader = 0, $proxy = 0){
    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_COOKIE,TRUE);
        #curl_setopt($ch, CURLOPT_COOKIEFILE,"cookie.txt");
       #curl_setopt($ch, CURLOPT_COOKIEJAR,"cookie.txt");
        if($post){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if($httpheader){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        }
        if($proxy){
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            // curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch);
        if(!$httpcode) return "Curl Error : ".curl_error($ch); else{
            $header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
            $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
            curl_close($ch);
            return array($header, $body);
        }
    }
       
function register_manual(){
    $em = readline(" Masukan Email : ");
    $us = readline(" Masukan Username : ");
    $pw = readline(" Masukan Password : ");
    $usr = randomuser();
    $post = '{"emailAddress":"'.$em.'","firstName":"'.$us.'","password":"'.$pw.'","platform":"InfinixInfinix X680B","appCode":"JOYLADA_202","deviceId":"deva/'.$usr[3].'"}';
    $r = json_decode(post("https://accounts.obapi.io/register", $post, array_merge(ua,["Host: accounts.obapi.io"])));
    if($r->data->ookbeeId){
        $post = '{"ookbeeId":"'.$em.'","password":"'.$pw.'","platform":"InfinixInfinix X680B","appCode":"JOYLADA_202","deviceId":"deva/'.$usr[3].'"}';
        $r =  json_decode(post('https://accounts.obapi.io/auth', $post, array_merge(ua,["Host: accounts.obapi.io"])));
        $token = $r->data->accessToken;
        $id = $r->data->ookbeeNumericId;
        $ua = array_merge(["Authorization: Bearer ".$token, "Host: user.id.joylada.io"], ua);
        $post = json_encode(["accessToken" => $token]);
            if(json_decode(post("https://user.id.joylada.io/api/member/createUser", $post, $ua))->data->id){
               # $candy = json_decode(get("https://member.id.joylada.io/app/JOYLADA_202/user/".$id."/key/balance", array_merge(["Host: member.id.joylada.io", "Authorization: Bearer ".$token], ua)))->data->spendableBalance;
                echo "succes register and aktivasi account! ".$us."\n";
                $save = fopen("real.txt", "a");
                         fwrite($save, implode("|",$usr)."\n");
                         fclose($save);
                post("https://accountverify.ookbee.com/email/verify/request", "ookbeeNumericId=".$id, array());
                echo "url verifikasi succes terkirim!\n";
                $verif = readline(" Masukan Url Verif : ");
                if(preg_match('/Email address verify succesfully/',get($verif, array()))){
                    echo "Email address verify succesfully\n";
                }
            }
                 
    }        
}

function randomuser(){
    $r = file_get_contents("https://randomuser.me/api/");
    $a = explode("@",json_decode($r)->results[0]->email)[0]."@gmail.com";
    $b = json_decode($r)->results[0]->login->username;
    $c = json_decode($r)->results[0]->login->password;
    $d = json_decode($r)->results[0]->login->uuid;
     return [$a, $b, $c, $d];
}

function follow($data, $idusr){
    $usr = explode("|",$data);
    $post = '{"ookbeeId":"'.$usr[0].'","password":"'.$usr[2].'","platform":"InfinixInfinix X680B","appCode":"JOYLADA_202","deviceId":"deva/'.$usr[3].'"}';
    $r =  json_decode(post('https://accounts.obapi.io/auth', $post, array_merge(ua,["Host: accounts.obapi.io"])));
    $token = $r->data->accessToken;
    $id = $r->data->ookbeeNumericId;
    $ua = array_merge(["Authorization: Bearer ".$token, "Host: user.id.joylada.io", "deviceid: ".$usr[3], "Account-Id: ".$id], ua);
    put("https://user.id.joylada.io/api/member/".$id."/follows/writer/".$idusr, 0, $ua);
  }
        
function register(){
    $usr = randomuser();
    $post = '{"emailAddress":"'.$usr[0].'","firstName":"'.$usr[1].'","password":"'.$usr[2].'","platform":"InfinixInfinix X680B","appCode":"JOYLADA_202","deviceId":"deva/'.$usr[3].'"}';
    $r = json_decode(post("https://accounts.obapi.io/register", $post, array_merge(ua,["Host: accounts.obapi.io"])));
    if($r->data->registrationDate){
        $post = '{"ookbeeId":"'.$usr[0].'","password":"'.$usr[2].'","platform":"InfinixInfinix X680B","appCode":"JOYLADA_202","deviceId":"deva/'.$usr[3].'"}';
        $r =  json_decode(post('https://accounts.obapi.io/auth', $post, array_merge(ua,["Host: accounts.obapi.io"])));
        $token = $r->data->accessToken;
        $id = $r->data->ookbeeNumericId;
        $ua = array_merge(["Authorization: Bearer ".$token, "Host: user.id.joylada.io"], ua);
        $post = json_encode(["accessToken" => $token]);
            if(json_decode(post("https://user.id.joylada.io/api/member/createUser", $post, $ua))->data->id){
               # $candy = json_decode(get("https://member.id.joylada.io/app/JOYLADA_202/user/".$id."/key/balance", array_merge(["Host: member.id.joylada.io", "Authorization: Bearer ".$token], ua)))->data->spendableBalance;
                echo "succes register and aktivasi account! ".$usr[1]."\n";
                $save = fopen("BABYBOTRUN100K", "a");
                         fwrite($save, implode("|",$usr)."\n");
                         fclose($save);
            }
                 
    }        
}
function register_custom($us){
    $str = [" "," "," "];
    $username = $us.$str[rand(0,2)];
    $usr = randomuser();
    $post = '{"emailAddress":"'.$usr[0].'","firstName":"'.$username.'","password":"'.$usr[2].'","platform":"InfinixInfinix X680B","appCode":"JOYLADA_202","deviceId":"deva/'.$usr[3].'"}';
    $r = json_decode(post("https://accounts.obapi.io/register", $post, array_merge(ua,["Host: accounts.obapi.io"])));
    if($r->data->registrationDate){
        $post = '{"ookbeeId":"'.$usr[0].'","password":"'.$usr[2].'","platform":"InfinixInfinix X680B","appCode":"JOYLADA_202","deviceId":"deva/'.$usr[3].'"}';
        $r =  json_decode(post('https://accounts.obapi.io/auth', $post, array_merge(ua,["Host: accounts.obapi.io"])));
        $token = $r->data->accessToken;
        $id = $r->data->ookbeeNumericId;
        $ua = array_merge(["Authorization: Bearer ".$token, "Host: user.id.joylada.io"], ua);
        $post = json_encode(["accessToken" => $token]);
            if(json_decode(post("https://user.id.joylada.io/api/member/createUser", $post, $ua))->data->id){
               # $candy = json_decode(get("https://member.id.joylada.io/app/JOYLADA_202/user/".$id."/key/balance", array_merge(["Host: member.id.joylada.io", "Authorization: Bearer ".$token], ua)))->data->spendableBalance;              
                echo "succes register and aktivasi account! ".$username."\n";                               
                $save = fopen("BABYBOTRUN100K", "a");
                         fwrite($save, implode("|",$usr)."\n");
                         fclose($save);
            }
                 
    }        
}

#register();
#exit;
echo "1. Create account [".count(file("BABYBOTRUN100K"))."]\n";
echo "2. Follow Account \n";
echo "3. Register manual with verif account\n";
$p = readline(" input : ");
switch($p){
    case 1:
        echo "1. with random username \n";
        echo "2. with custom username \n";
        $pil = readline(" input : ");
        if($pil == 1){
            while(true){register();}}else{ $us = readline(" input username : "); while(true){  register_custom($us); }}
    break;
    case 2:
        $id = explode("https://www.id.joylada.com/app/profile/", readline(" Masukan url account : "))[1];
        if(is_numeric($id)){
            $file = file("BABYBOTRUN100K");
            $x = 0;
            foreach($file as $data){
                follow(trim($data), $id);
                echo "Follower sent ".$x++."  \r";
            }
        }else{
            echo "url profil salah! \n";
        }
    break;
    case 3:
        register_manual();
    break;
}