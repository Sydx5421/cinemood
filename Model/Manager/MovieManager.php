<?php


namespace App\Model\Manager;


use App\Model\Entity\Movie;

class MovieManager extends AbstractManager
{
    public function doesMovieExist(Movie $movie){
        $db = $this->dbConnect();
        $req_movie = $db->prepare("SELECT 'id' FROM movies WHERE id = ?");
        $req_movie->execute(array($movie->getId()));
        // return boolean
        if($req_movie->rowCount() == 0){
            return false;
        }elseif($req_movie->rowCount() > 0){
            return true;
        }else{
            return "error " . $req_movie->errorInfo();
        }
    }

    public function createMovie(Movie $movie){
        $db = $this->dbConnect();
        //vérifier le nombre de lignes insérées > 0 pour être sur que l'insertion a bien marché et ne pas allé plus loin si ce n'est pas le cas
        $insert_movie = $db->prepare('INSERT INTO movies(id, title, poster_path ) VALUES(:id, :title, :poster_path)');

        $insert_movie->bindValue(':id', $movie->getId());
        $insert_movie->bindValue(':title', $movie->getTitle());
        $insert_movie->bindValue(':poster_path', $movie->getPosterPath());

        $insert_movie_exec = $insert_movie->execute();
        if($insert_movie_exec == 1){
            return true;
        }else{
            $error_info = $insert_movie->errorInfo();
            return "erreur dans la création du film en base : " . $error_info[2];
        }
    }

}