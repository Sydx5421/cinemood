<?php


namespace App\Model\Manager;

use App\Model\Entity\Category;

class CategoryManager extends AbstractManager
{
    public function create(Category $category){
        $db = $this->dbConnect();

        // checking that the category does not already exist :
        $req_category = $db->prepare("SELECT 'nom' FROM categories WHERE nom = ?");
        $reqExec_category = $req_category->execute(array($category->getNom()));
        if($reqExec_category === true && $req_category->rowCount()){
            return"Cette categorie existe déjà";
        }

        // checking that the background and the font color are not the same !
        if($category->getFontColor() == $category->getBackgroundColor()){
            return"Choisissez une couleur de fond différente de celle de la police pour que le nom de votre catégorie soit lisible.";
        }

        // Enregistrement de  la catégorie
        $req = $db->prepare('INSERT INTO categories(user_id, nom, description, font_color, background_color) VALUES(:user_id, :nom, :description, :font_color, :background_color)');

        $req->bindValue(':user_id', $category->getUserId());
        $req->bindValue(':nom', $category->getNom());
        $req->bindValue(':description', $category->getDescription());
        $req->bindValue(':font_color', $category->getFontColor());
        $req->bindValue(':background_color', $category->getBackgroundColor());
        $reqExec = $req->execute();

        return $reqExec;

    }

    public function getCategories($movieId = null){
        $db = $this->dbConnect();

        if($movieId != null){
            $req = $db->prepare(
                "SELECT cat.id, count(*) as nbOfTimesCatIsUsed, count(mcu.justification_comment) as nbComments, cat.nom, cat.font_color, cat.background_color 
                        FROM `categories` cat
                        INNER JOIN `mcu_connection` mcu
                        ON mcu.category_id = cat.id 
                        WHERE mcu.movie_id = ?
                        GROUP BY mcu.category_id
                        ORDER BY nbOfTimesCatIsUsed DESC" );
            $req->execute(array($movieId));
        }else{
            $req = $db->query("SELECT * FROM categories ORDER BY up_votes, nom");
        }

        $categories = [];

        while($category = $req->fetchObject('App\Model\Entity\Category')){
            $categories[] = $category;
        }
        $req->closeCursor();

        if($categories == null){
            return "Aucunes catégories.";
        }
        return $categories;

    }

    public function getCategory($categoryId){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $req->execute(array($categoryId));

        if($req !== false){
            $category = $req->fetchObject('App\Model\Entity\Category');

            $req->closeCursor();
            return $category;
        }else {
            $error = $db->errorInfo()[2];
            return $error;
        }
    }

    public function getCategoryMovieList($categoryId){
        $db = $this->dbConnect();
        $req = $db->prepare(
            "SELECT m.id, m.title, m.poster_path 
                        FROM `movies` m
                        INNER JOIN `mcu_connection` mcu
                        ON mcu.movie_id = m.id 
                        WHERE mcu.category_id = ?
                        GROUP BY m.id" );
        $req->execute(array($categoryId));

        $movieIdList = [];

        while($movieId = $req->fetchObject('App\Model\Entity\Movie')){
            $movieIdList[] = $movieId;
        }
        $req->closeCursor();

        if($movieIdList == null){
            return "Aucun films classés dans cette catégorie.";
        }
        return $movieIdList;
    }

    public function getCategoriesOfUser(){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT * FROM categories WHERE user_id = ?");
        $req->execute();
    }

    public function getRandomCategories(){
        $db = $this->dbConnect();
        $req = $db->prepare("SELECT * FROM categories ORDER BY RAND() LIMIT 3");
        $req->execute();

        $categoryList = [];

        while($category = $req->fetchObject('App\Model\Entity\Category')){
            $categoryList[] = $category;
        }
        $req->closeCursor();

        if($categoryList == null){
            return "Aucune catégorie trouvée.";
        }
        return $categoryList;
    }

}