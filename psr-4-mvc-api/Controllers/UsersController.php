<?php
namespace Controllers;

use \Core\Controller;
use \Models\Users;
use \Models\Photos;

class UsersController extends Controller {

	public function index() {}

	public function login() {
        $array = array('erro' => '');

        $method = $this->getMethod();
        $data = $this->getRequestData();

        if($method == 'POST') {
            if(!empty($data['email']) && !empty($data['pass'])) {
                $users = new Users();

                if($users->checkCredentials($data['email'],$data['pass'])) {
                	   //gerar JWT 
                	$array['jwt'] = $users->createJWT();
                }else {
                	$array['erro'] = "acesso negado!!";
                }

            }else {
            	$array['erro'] = "email ou senha não preenchidos!!";
            }
        }else {
        	$array['erro'] = "metodo de requisição incompativel!!!!";
        }

        $this->returnJson($array);
	}

	public function new_record() {
		$array = array('erro' => '');

		$method = $this->getMethod();
        $data = $this->getRequestData();

        if($method == 'POST') {
           if(!empty($data['name']) && !empty($data['email']) && !empty($data['pass'])) {
              if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                  $users = new Users();

                  if($users->create($data['name'],$data['email'],$data['pass'])) {
                      $array['jwt'] = $users->createJWT();
                  }else {
                  	$array['erro'] = "E-mail ja existe!!!";
                  }
              }else {
                 $array['erro'] = "E-mail invalido";
              }  
           }else{
           	$array['erro'] = "préncha todos campos";
           }
        }else {
        	$array['erro'] = "Método de requisição invalido!!";
        }

		$this->returnJson($array);
	}

	public function view($id) {
		$array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;

            $array['is_me'] = false;
            if($id == $users->getId()) {
            	$array['is_me'] = true;
            }

            switch($method) {
            	case 'GET':
            		$array['data'] = $users->getInfo($id);

                if(count($array['data']) === 0) {
                    $array['erro'] = 'usuario não existe!!!';
                }
            		break;
            	case 'PUT':
            		$array['erro'] = $users->editInfo($id,$data);
            		break;
            	case 'DELETE':
                $array['erro'] = $users->delete($id);
            	    break;
            	default:
                   $array['erro'] = 'Metodo '.$method.' não disponivel';
            	    break;  	
            }

		}else {
			$array['erro'] = 'Acesso negado';
		}

        $this->returnJson($array);
		return true;
	}

    public function feed() {
		$array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;
             
            if($method == 'GET') {
               $offset = 0;
               if(!empty($data['offset'])) {
               	  $offset = intval($data['offset']);
               }

               $per_page = 10;
               if(!empty($data['per_page'])) {
               	 $per_page = intval($data['per_page']); 
               }

               $array['data'] = $users->getFeed($offset,$per_page);
            } else {
            	$array['erro'] = 'Metodo '.$method.' não disponivel';
            }
        } else {
        	$array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);
    }

    public function photos($id_user) {
       $array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$p = new Photos();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;

            $array['is_me'] = false;
            if($id_user == $users->getId()) {
            	$array['is_me'] = true;
            }
             
            if($method == 'GET') {
               $offset = 0;
               if(!empty($data['offset'])) {
               	  $offset = intval($data['offset']);
               }

               $per_page = 10;
               if(!empty($data['per_page'])) {
               	 $per_page = intval($data['per_page']); 
               }
                
                $array['data'] = $p->getPhotosFromUser($id_user,$offset,$per_page);
            } else {
            	$array['erro'] = 'Metodo '.$method.' não disponivel';
            }
        } else {
        	$array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);
    }

    public function follow($id_user) {
           $array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$p = new Photos();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;
            
            switch ($method) {
            	case 'POST':
            		$users->follow($id_user);
            		break;
            	case 'DELETE':
            		$users->unFollow($id_user);
            	    break;
            	default:
            		$array['erro'] = 'Metodo '.$method.' não disponivel!!!';
            		break;
            }
           
        } else {
        	$array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);

    }
}