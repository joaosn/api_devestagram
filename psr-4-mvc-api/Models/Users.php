<?php
namespace Models;

use \Core\Model;
use \Models\Jwt;
use \Models\Photos;

class Users extends Model {

	private $id_user;

	public function create($name,$email,$pass) {
        if(!$this->emailExists($email)) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (name, email, pass) VALUES (:name, :email, :pass)";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(':name',$name);
            $sql->bindValue(':email',$email);
            $sql->bindValue(':pass',$hash);
            $sql->execute();

            $this->id_user = $this->db->lastInsertId();

            return true;
        } else {
           return false;
        }
	}

	public function checkCredentials($email,$pass) {
       $sql = "SELECT id, pass FROM users WHERE email = :email";
       $sql = $this->db->prepare($sql);
       $sql->bindValue(':email', $email);
       $sql->execute();

       if($sql->rowCount() > 0) {
           $info = $sql->fetch();

           if(password_verify($pass, $info['pass'])) {
              $this->id_user = $info['id'];

              return true;
           }else {
              return false;
           } 
       }else {
       	 return false;
       } 
	}
  
  public function getId() {
     return $this->id_user;
  }

  public function getInfo($id) {
      $array = array();
      
      $sql = "SELECT id,name,email,avatar FROM users WHERE id = :id";
      $sql = $this->db->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      if($sql->rowCount() > 0) {
         $array = $sql->fetch(\PDO::FETCH_ASSOC);

         $photos = new Photos();

         if(!empty($array['avatar'])) {
             $array['avatar'] = BASE_URL.'media/avatar/'.$array['avatar'];
         }else {
            $array['avatar'] = BASE_URL.'media/avatar/default.jpg';
         }
        
        $array['following'] = $this->getFollowingCount($id);
        $array['followers'] = $this->getFollowersCount($id);
        $array['photos_count'] = $photos->getPhotosCount($id);

      }
 
      return $array;
  } 

  public function getFeed($offset = 0,$per_page = 10) {
      $followingUsers = $this->getFollowing($this->getId());
      $p = new Photos();

      return $p->getfeedCollection($followingUsers, $offset, $per_page);

  }

  public function getFollowing($id) {
     $array = array();

      $sql = "SELECT id_user_passive FROM user_following WHERE id_user_active = :id";
      $sql = $this->db->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();
      
      if($sql->rowCount() > 0) {
      	$data = $sql->fetchAll();

	      	foreach($data as $item) {
	      		$array[] = intval($item['id_user_passive']);
	      	}
      }


     return $array; 
  }

  public function getFollowingCount($id_user) {
      $sql = "SELECT COUNT(*) as c FROM user_following WHERE id_user_active = :id";
      $sql = $this->db->prepare($sql);
      $sql->bindValue(':id', $id_user);
      $sql->execute();
      $info = $sql->fetch();

      return $info['c'];
  }

   public function getFollowersCount($id_user) {
      $sql = "SELECT COUNT(*) as c FROM user_following WHERE id_user_passive = :id";
      $sql = $this->db->prepare($sql);
      $sql->bindValue(':id', $id_user);
      $sql->execute();
      $info = $sql->fetch();

      return $info['c'];
  }

	public function createJWT() {
		$jwt = new Jwt();
		return $jwt->create(array('id_user'=>$this->id_user));
	}

  public function validateJWT($token) {
      $jwt = new Jwt();
      $info = $jwt->validate($token);

      if(isset($info->id_user)) {
        $this->id_user = $info->id_user;
        return true;
      }else {
        return false;
      }
  }

    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':email',$email);
        $sql->execute();

        if($sql->rowCount() > 0) {
           return true;
        }else {
        	return false;
        }
    }

    public function editInfo($id,$data) {
       if($id === $this->getId()) {
           $toChange = array();

           if(!empty($data['name'])) {
              $toChange['name'] = $data['name'];
           }
           if(!empty($data['email'])) {
              if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                 if(!$this->emailExists($data['email'])) {
                     $toChange['email'] = $data['email'];
                   }else {
                     return 'E-mail já existente!!';
                   }
                }else {
                    return 'E-mail invalido!!';
                }
           }
           if(!empty($data['pass'])) {
              $toChange = password_hash($data['pass'], PASSWORD_DEFAULT);
           }

           if(count($toChange) > 0) {

                $fildes = array();
                foreach($toChange as $k => $v) {
                  $fildes[] = $k.' = :'.$k;
                }

                $sql = 'UPDATE users SET '.implode(",",$fildes).' WHERE id = :id';
                $sql = $this->db->prepare($sql);
                $sql->bindValue(':id',$id);

                foreach($toChange as $k => $v) {
                  $sql->bindValue(':'.$k,$v);
                }



                $sql->execute();
                return '';
           }else {
              return 'preencha os dados corretamente!!';
           }
       }else {
          return 'Não é permitido editar outro usuário!!!';
       }
    }

    public function delete($id) { //vai ser deletado tudo do usuario fotos...
        if($id === $this->getId()) {
            $p = new Photos();
            $p->deleteAll($id);

            $sql = "DELETE FROM user_following WHERE id_user_active = :id OR id_user_active = :id";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id',$id);
            $sql->execute();

            $sql = "DELETE FROM users WHERE id = :id";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id',$id);
            $sql->execute();

            return '';
        } else {
           return 'não permitido excluir outros usuário';
        }
    }

    public function follow($id_user) {
       $sql = "SELECT * FROM users_following WHERE id_user_active = :id_user_active AND id_user_passive = :id_user_passive";
       $sql = $this->db->prepare($sql);
       $sql->bindValue(':id_user_active',$this->getId());
       $sql->bindValue(':id_user_passive',$id_user);
       $sql->execute();

       if($sql->rowCount() === 0) {
       	  $sql = "INSERT INTO user_following (id_user_active,id_user_passive) VALUES (:id_user_active,:id_user_passive)";
       	  $sql = $this->db->prepare($sql);
       	  $sql->bindValue(':id_user_active',$this->getId());
       	  $sql->bindValue(':id_user_passive',$id_user);
       	  $sql->execute();

       	  return true;
       } else {
       	  return false;
       }

    }

    public function unFollow($id_user) {
    	$sql = "DELETE FROM user_following WHERE id_user_active = :id_user_active AND id_user_passive = :id_user_passive";
    	$sql = $this->db->prepare($sql);
    	$sql->bindValue(':id_user_active',$this->getId());
       	$sql->bindValue(':id_user_passive',$id_user);
        $sql->execute();
    }
}