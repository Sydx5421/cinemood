<?php


namespace App\Model\Entity;


class MCUConnection extends AbstractEntity
{
    protected $id;
    protected $movie_id;
    protected $category_id;
    protected $user_id;
    protected $justification_comment;
    protected $creation_date;

    public function __construct($data = null)
    {
        if($data != null){
            $this->hydrate($data);
        }
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
     * @return MCUConnection
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getMovieId()
    {
        return $this->movie_id;
    }

    /**
     * @param mixed $movie_id
     * @return MCUConnection
     */
    public function setMovieId($movie_id)
    {
        $this->movie_id = $movie_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * @param mixed $category_id
     * @return MCUConnection
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     * @return MCUConnection
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJustificationComment()
    {
        return $this->justification_comment;
    }

    /**
     * @param mixed $justification_comment
     * @return MCUConnection
     */
    public function setJustificationComment($justification_comment)
    {
        $this->justification_comment = $justification_comment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * @param mixed $creation_date
     * @return MCUConnection
     */
    public function setCreationDate($creation_date)
    {
        $this->creation_date = $creation_date;
        return $this;
    }

}