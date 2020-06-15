<?php

namespace App\Model\Manager;

use App\Model\Entity\User;
use App\Model\Manager\AbstractManager;

require_once('AbstractManager.php');

class UserManager extends AbstractManager
{
    public function register(User $user){

        $db = $this->dbConnect();

        // checking that the email is not already used :
        $req_email = $db->prepare("SELECT 'id' FROM users WHERE email = ?");
        $reqExec_email = $req_email->execute(array($user->getEmail()));
//        vd($reqExec_email, $req_email, $req_email->rowCount());
        if($reqExec_email === true && $req_email->rowCount()){
            return "Cet email est déjà utilisé par un autre utilisateur, veuillez en choisir un autre.";
        }

        // checking that the pseudo is not already taken :
        $req_pseudo = $db->prepare("SELECT 'id' FROM users WHERE pseudo = ?");
        $reqExec_pseudo = $req_pseudo->execute(array($user->getPseudo()));
        if($reqExec_pseudo === true && $req_pseudo->rowCount()){
            return "Ce pseudo est déjà utilisé par un autre utilisateur, veuillez en choisir un autre.";
        }

        // Now that we checked the unicity of the pseudo and email, we can proceed with the registration.

        $req = $db->prepare('INSERT INTO users(pseudo, password, email) VALUES(:pseudo, :password, :email)');

        $req->bindValue(':pseudo', $user->getPseudo());
        $req->bindValue(':password', $user->getPassword());
        $req->bindValue(':email', $user->getEmail());
        $reqExec = $req->execute();

        return $reqExec;

    }

    public function login($login, $password){
        $db = $this->dbConnect();
        // connexion attempt via pseudo
        $req_pseudo = $db->prepare("SELECT * FROM users WHERE pseudo = ? AND password = ?");
        $reqExec_pseudo = $req_pseudo->execute(array($login, $password));

        if($req_pseudo->rowCount() == 1){
            return $req_pseudo->fetchObject();
        }else{
            // connexion attempt via email
            $req_email = $db->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
            $reqExec_email = $req_email->execute(array($login, $password));

            if($req_email->rowCount() == 1) {
                return $req_email->fetchObject();
            }else{
                return "Login ou mot de passe incorrect";
            }
        }

    }

}