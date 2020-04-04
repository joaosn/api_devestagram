<?php
namespace Controllers;

use \Core\Controller;
use \Models\Users;
use \Models\Photos;

class PhotosController extends Controller {

	public function index() {}

	public function random() {
         $array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$p = new Photos();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;
             
            if($method == 'GET') {

               $per_page = 10;
               if(!empty($data['per_page'])) {
               	 $per_page = intval($data['per_page']); 
               }
                
                $excludes = array();
                if(!empty($data['excludes'])) {
                	$excludes = explode(',',$data['excludes']);

                } 

                $array['data'] = $p->getRandomPhotos($per_page,$excludes);

            } else {
            	$array['erro'] = 'Metodo '.$method.' não disponivel';
            }
        } else {
        	$array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);
	}

	public function view($id_photo) {
       $array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$p = new Photos();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;
             
            switch ($method) {
            	case 'GET':
            		$array['data'] = $p->getPhoto($id_photo);
            		break;
            	case 'DELETE':
            		$array['erro'] = $p->deletePhoto($id_photo,$users->getId());
            		break;
            	default:
            		$array['erro'] = 'Metodo '.$method.' não disponivel';
            		break;
            }
        } else {
        	$array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);
	}

	public function comment($id_photo) {
		$array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$p = new Photos();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;
             
            switch ($method) {
            	case 'POST':
            	 if(!empty($data['txt'])) {
            	 	$array['erro'] = $p->addComment($id_photo,$users->getId(),$data['txt']);
            	 } else {
            	 	$array['erro'] = "Comentario vazio!!";
            	 }
                    break;
            	default:
            		$array['erro'] = 'Metodo '.$method.' não disponivel';
            		break;
            }
        } else {
        	$array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);
	}

	public function delete_comment($id) {
          $array = array('erro'=>'', 'logged'=>false);

		$method = $this->getMethod();
		$data = $this->getRequestData();

		$users = new Users();
		$p = new Photos();

		if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;
             
            switch ($method) {
            	case 'DELETE':
            	    $array['erro'] = $p->deleteComment($id,$users->getId());
            	   break;
            	default:
            		$array['erro'] = 'Metodo '.$method.' não disponivel';
            		break;
            }
        } else {
        	$array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);
	}

    public function like($id_photo) {
        $array = array('erro'=>'', 'logged'=>false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        $p = new Photos();

        if(!empty($data['jwt']) && $users->validateJWT($data['jwt'])) {  
            $array['logged'] = true;
             
            switch ($method) {
                case 'POST':
                    $array['erro'] = $p->like($id_photo,$users->getId());
                    break;
                case 'DELETE':
                    $array['erro'] = $p->unLike($id_photo,$users->getId());
                    break;
                default:
                    $array['erro'] = 'Metodo '.$method.' não disponivel';
                    break;
            }
        } else {
            $array['erro'] = 'Acesso negado';
        }

        $this->returnJson($array);
    }
}