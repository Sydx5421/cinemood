<?php


namespace App\Model\Entity;


class Movie extends AbstractEntity
{
    protected $id;
    protected $title;
    protected $poster_path;
    protected $abstract;

    public function __construct($data = null)
    {
        if($data != null){
            $this->hydrate($data);
        }
    }

    /**
     * @return mixed
     */
    public function getPosterPath()
    {
        return $this->poster_path;
    }

    /**
     * @param mixed $poster_path
     * @return Movie
     */
    public function setPosterPath($poster_path)
    {
        $this->poster_path = $poster_path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * @param mixed $abstract
     * @return Movie
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
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
     * @return Movie
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Movie
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

}