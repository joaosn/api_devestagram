<?php
namespace Models;

use \Core\Model;

class Jwt extends Model {
	
	public function create($data) {
        global $config;


		$header = json_encode(array("typ"=>"JWT", "alg"=>"HS256"));

		$payload = json_encode($data);
        
        $Hbase = $this->base64url_encode($header);
        $Pbase = $this->base64url_encode($payload);


        $signature = hash_hmac("sha256", $Hbase.".".$Pbase, $config['jwt_secret_key'], true);
        $bsig = $this->base64url_encode($signature);

        $jwt = $Hbase.".".$Pbase.".".$bsig;

        return $jwt;
	}
    
    //para validar token jwt 
    //1* verificar se token tem 3 partes 
    //2* verificar a chave e bater asinatura com dados.
	public function validate($token) { 
	    global $config;  
        $array = array();

        $jwt_split = explode('.', $token);

        if(count($jwt_split) == 3) { 
           $signature = hash_hmac("sha256", $jwt_split[0].".".$jwt_split[1], $config['jwt_secret_key'], true);
           $bsig = $this->base64url_encode($signature);

	           if($bsig == $jwt_split[2]) {
	                 
                   $array = json_decode($this->base64url_decode($jwt_split[1]));
                   return $array;

	           } else {
	           	  return false;
	           }

        } else {
        	return false;
        }
	}


	private function base64url_encode( $data ){
	  return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');
	}

	private function base64url_decode( $data ){
	  return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
	}
}