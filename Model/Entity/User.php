<?php


namespace App\Model\Entity;


class User extends AbstractEntity
{
    protected $id;
    protected $pseudo;
    protected $is_admin;
    protected $password;
    protected $confirm_password;
    protected $email;
    protected $inscription_date;
    protected $password_crypted;


    public function __construct($data)
    {
        $this->hydrate($data);
    }

    /**
     * @return mixed
     */
    public function getIsAdmin()
    {
        return $this->is_admin;
    }

    /**
     * @param mixed $is_admin
     * @return User
     */
    public function setIsAdmin($is_admin)
    {
        $this->is_admin = $is_admin;
        return $this;
    }


    /**
     * @param $pseudo
     * @param $password
     * @param $email
     * @param $confirm_password
     * @return bool|string
     */
    public function isValid(){
        $error='';
        if(strlen($this->email)<=225){
            if(filter_var($this->email, FILTER_VALIDATE_EMAIL)){
                if(strlen($this->pseudo)>=1 && strlen($this->pseudo)<=250){
                    if(strlen($this->password)>=8 && strlen($this->password)<=100){
                        if($this->password == $this->confirm_password){
                            if(!isset($this->password_crypted)){
                                $this->password_crypted = sha1($this->password);
                            }
                            return true;
                        }else{
                            $error='Vos mots de passes ne correspondent pas.';
                        }
                    }else{
                        $error='Votre mot de passe doit comprendre entre 8 et 100 caractères.';
                    }
                }else{
                    $error ='Votre pseudo doit comprendre entre 1 et 250 characters.';
                }
            }else{
                $error ='Veuillez entrez une adresse mail valide.';
            }
        }else{
            $error ='Votre email est trop long.';
        }
        return $error;
    }



    // Getters & Setters

    /**
     * @return mixed
     */
    public function getConfirmPassword()
    {
        return $this->confirm_password;
    }

    /**
     * @param mixed $confirm_password
     * @return User
     */
    public function setConfirmPassword($confirm_password)
    {
        $this->confirm_password = $confirm_password;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * @param mixed $pseudo
     * @return User
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInscriptionDate()
    {
        return $this->inscription_date;
    }

    /**
     * @param mixed $inscription_date
     * @return User
     */
    public function setInscriptionDate($inscription_date)
    {
        $this->inscription_date = $inscription_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPasswordCrypted()
    {
        return $this->password_crypted;
    }

    /**
     * @param mixed $password_crypted
     * @return User
     */
    public function setPasswordCrypted($password_crypted)
    {
        $this->password_crypted = $password_crypted;
        return $this;
    }



}