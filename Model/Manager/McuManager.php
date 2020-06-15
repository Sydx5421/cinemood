<?php


namespace App\Model\Manager;

use App\Model\Entity\MCUConnection;

class McuManager extends AbstractManager
{

    public function createMovieConnection(MCUConnection $newMCUConnection){
        $db = $this->dbConnect();

        $req = $db->prepare("INSERT INTO mcu_connection (movie_id, category_id, user_id, justification_comment) VALUES(:movie_id, :category_id, :user_id, :justification_comment)");

        $req->bindValue(':movie_id', $newMCUConnection->getMovieId());
        $req->bindValue(':category_id', $newMCUConnection->getCategoryId());
        $req->bindValue(':user_id', $newMCUConnection->getUserId());
        $req->bindValue(':justification_comment', $newMCUConnection->getJustificationComment());

        return $req->execute();

    }

    public function doesMcuExistsAlready(MCUConnection $newMCUConnection){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT * FROM mcu_connection WHERE movie_id= ? AND category_id = ? AND  user_id = ? ");
        $req->execute(array($newMCUConnection->getMovieId(), $newMCUConnection->getCategoryId(),
            $newMCUConnection->getUserId()));
        return $req->rowCount();
    }

    public function getAllCommentsForMC($movieId, $categoryId){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT mcu.id, mcu.user_id, mcu.justification_comment, mcu.creation_date, u.pseudo
                                        FROM mcu_connection mcu
                                        INNER JOIN users u
                                        ON mcu.user_id = u.id
                                        WHERE movie_id = ? AND category_id = ? ");
        $req->execute(array($movieId, $categoryId));

        $mcuList = [];

        while($mcuElt = $req->fetchObject('App\Model\Entity\MCUConnection')){
            $mcuList[] = $mcuElt;
        }
        $req->closeCursor();

        if($mcuList == null){
            return "Aucun commentaire justifiant le classement de ce film dans cette catégorie.";
        }
        return $mcuList;

    }

    public function deleteMcuConnection($mcuId){
        $db = $this->dbConnect();
        $req = $db->prepare('DELETE FROM mcu_connection WHERE id = ?');
        $req->execute(array($mcuId));

        return $req;
    }
    public function deleteComment($mcuId){
        $db = $this->dbConnect();
        $req = $db->prepare('UPDATE  mcu_connection set justification_comment = NULL WHERE id = ?');
        $req->execute(array($mcuId));

        return $req;
    }

    public function updateComment($mcuId, $newComment){
        $db = $this->dbConnect();
        $req = $db->prepare('UPDATE  mcu_connection set justification_comment = ? WHERE id = ?');
        $reqExec = $req->execute(array($newComment, $mcuId));

        return $reqExec;
    }

    public function getComment($mcuId){
        $db = $this->dbConnect();
        $req = $db->prepare('SELECT justification_comment FROM mcu_connection WHERE id = ?');
        $req->execute(array( $mcuId));

        $comment = $req->fetch();

        return $comment["justification_comment"];
    }

    public function getMcuFromMcuId($mcuId){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT * FROM mcu_connection WHERE id = ? ");
        $req->execute(array($mcuId));

        $mcuConnection = $req->fetchObject('App\Model\Entity\MCUConnection');
        $req->closeCursor();

        return $mcuConnection;
    }

    public function getMcuFromUser($userId){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT mcu.id, mcu.movie_id, mcu.category_id, mcu.user_id,  mcu.justification_comment, mcu.creation_date, cat.nom, cat.font_color, cat.background_color, m.title, m.poster_path
                                        FROM (mcu_connection mcu
                                        INNER JOIN movies m
                                        ON (mcu.movie_id = m.id))
                                        INNER JOIN categories cat
                                        ON (mcu.category_id = cat.id)
                                        WHERE mcu.user_id = ?  ");
        $req->execute(array($userId));

        $mcuList = [];

        while($mcuElt = $req->fetchObject('App\Model\Entity\MCUConnection')){
            $mcuList[] = $mcuElt;
        }
        $req->closeCursor();

        if($mcuList == null){
            return "Vous n'avez établie aucune connexion entre films et catégories pour l'instant.";
        }
        return $mcuList;
    }

    public function getRandomMcu(){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT mcu.id, mcu.movie_id, mcu.category_id, mcu.user_id,  mcu.justification_comment, mcu.creation_date, cat.nom, cat.font_color, cat.background_color, m.title, m.poster_path
                                        FROM (mcu_connection mcu
                                        INNER JOIN movies m
                                        ON (mcu.movie_id = m.id))
                                        INNER JOIN categories cat
                                        ON (mcu.category_id = cat.id)
                                        ORDER BY RAND() LIMIT 3");
        $req->execute();

        $mcuList = [];

        while($mcuElt = $req->fetchObject('App\Model\Entity\MCUConnection')){
            $mcuList[] = $mcuElt;
        }
        $req->closeCursor();

        if($mcuList == null){
            return "Aucune connections trouvées";
        }
        return $mcuList;
    }

}