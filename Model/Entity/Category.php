<?php


namespace App\Model\Entity;


class Category extends AbstractEntity
{
    protected $id;
    protected $user_id;
    protected $nom;
    protected $description;
    protected $status;
    protected $up_votes;
    protected $down_votes;
    protected $font_color;
    protected $background_color;

    public function __construct($data = null)
    {
        if($data != null){
            $this->hydrate($data);
        }
    }


    /**
     * @return bool|string
     */
    public function isValid(){
        $error = '';

        if(isset($this->userId) && is_numeric($this->userId)){
            if(strlen($this->nom)>=1 && strlen($this->nom)<=250){
                if(strlen($this->description)>=20){
                    return true;
                }else{
                    $error="Décrivez votre categorie en 20 caractères minimum";
                }
            }else{
                $error="Le nom de votre categorie doit comprendre entre 1 et 250 caractères.";
            }
        }else{
            $error="Vous devez être identifié afin de pouvoir créer un catégorie. Connectez-vous ou contactez nous si le problème persiste.";
        }

        return $error;

    }


    // Getters & Setters
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Category
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $user_id
     * @return Category
     */
    public function setUserId($user_id)
    {
        $this->userId = $user_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param mixed $nom
     * @return Category
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Category
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpVotes()
    {
        return $this->up_votes;
    }

    /**
     * @param mixed $up_votes
     * @return Category
     */
    public function setUpVotes($up_votes)
    {
        $this->up_votes = $up_votes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDownVotes()
    {
        return $this->down_votes;
    }

    /**
     * @param mixed $down_votes
     * @return Category
     */
    public function setDownVotes($down_votes)
    {
        $this->down_votes = $down_votes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFontColor()
    {
        return $this->font_color;
    }

    /**
     * @param mixed $font_color
     * @return Category
     */
    public function setFontColor($font_color)
    {
        $this->font_color = $font_color;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundColor()
    {
        return $this->background_color;
    }

    /**
     * @param mixed $background_color
     * @return Category
     */
    public function setBackgroundColor($background_color)
    {
        $this->background_color = $background_color;
        return $this;
    }



}