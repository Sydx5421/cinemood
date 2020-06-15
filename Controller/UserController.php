<?php


namespace App\Controller;

use App\Model\Entity\Category;
use App\Model\Entity\MCUConnection;
use App\Model\Entity\Movie;
use App\Model\Entity\User;
use App\Model\Manager\CategoryManager;
use App\Controller\TmdbRequestsTrait;
use App\Model\Manager\McuManager;
use App\Model\Manager\MovieManager;

class UserController extends AbstractController
{

    public function __construct() {
        parent::__construct();

        if($this->userConnected === false){
            $this->redirectIfNotConnected();
        }
    }

    protected function redirectIfNotConnected() {
        $this->addFlash("Ces pages sont réservées aux utilisateurs connectés, veuillez vous connecter.", "danger",
            true);
        $this->redirect('home');
        die;
    }

    use TmdbRequestsTrait;

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function dashboard($userId){
        $McuManager = new McuManager();
        $mcuList = $McuManager->getMcuFromUser($userId);
//        vd($mcuList);
        echo $this->render('dashboard.twig',  array("mcuList" => $mcuList));
    }

    /**
     *
     */
    public function deconnexion(){
        session_destroy();
        $this->isAdmin = false;
        $this->isAdmin = false;
        $this->redirect('home');
    }



    public function deleteConnection($mcuId){
//        vd("On rentre dans la méthode deleteConnection");
//        echo "connection to delete : " . $mcuId;

        $McuManager = new McuManager();
        $connectedUser = new User($this->session["user"]);
        $mcuConnection  = $McuManager->getMcuFromMcuId($mcuId);
        // On vérifie avant tout que l'utilisateur est bien l'auteur du commentaire :
//        vd($mcuConnection);
        if($mcuConnection->getUserId() === $connectedUser->getId()){
            $req = $McuManager->deleteMcuConnection($mcuId);
            if ( $req->errorInfo()[0] == "00000" ){
                echo "supression réussie";
            }
            die;
        }else{
            vd("Vous n'êtes pas l'auteur de cette connexion !") ;
            die;
        }
    }

    public function editComment($mcuId){
//        vd("On rentre dans l'action");
        $McuManager = new McuManager();
        $previousComment = $McuManager->getComment($mcuId);
        $connectedUser = new User($this->session["user"]);
        $mcuConnection  = $McuManager->getMcuFromMcuId($mcuId);
        // On vérifie avant tout que l'utilisateur est bien l'auteur du commentaire :
        if($mcuConnection->getUserId() === $connectedUser->getId()){
//            vd("On rentre dans le if de vérification de l'utilisateur");
            if($this->isPost()){
//                vd("On rentre dans le if isPost");
                if($_POST["action"] == "edit"){
                    $newComment = trim(htmlspecialchars($_POST["comment"]));
                    $newComment = $McuManager->updateComment($mcuId, $newComment);
                    $newUpdatedComment = $McuManager->getComment($mcuId);
                    echo $newUpdatedComment;
                    die;

                }else if($_POST["action"] == "delete"){
                    $McuManager->deleteComment($mcuId);
                    $newUpdatedComment = $McuManager->getComment($mcuId);
                    echo $newUpdatedComment;
                    die;

                }else if($_POST["action"] == "cancel") {
                    $newUpdatedComment = $McuManager->getComment($mcuId);
                    echo $newUpdatedComment;
                    die;
                }
            }
        }else{
            // l'utilisateur n'a pas le droit de modifier se commentaire, car il n'est pas son auteur
            // on ne fait donc rien et on renvoie le commentaire initiale à l'affichage.
            echo $previousComment;
        }

    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createCategory(){
        $CategoryManager = new CategoryManager();

        if($this->isPost()){
            $newCategoryData = [
                'user_id' => trim(htmlspecialchars($_POST['userId'])),
                'nom' => trim(htmlspecialchars($_POST['nameCategory'])),
                'description' => trim(htmlspecialchars($_POST['descriptionCat'])),
                'background_color' => trim(htmlspecialchars($_POST['backgroundColor'])),
                'font_color' => trim(htmlspecialchars($_POST['fontColor']))
            ];

            $newCategory = new Category($newCategoryData);
//            vd($newCategory);

            if($newCategory->isValid() === true){
                $categoryCreation = $CategoryManager->create($newCategory);
                if($categoryCreation === true){
                    $this->addFlash('Votre catégorie "'  . $newCategory->getNom()
                        .  '" a bien été créé ! Vous pouvez dès à présent vous l\'utiliser.', 'success');
                    $this->redirect('categories');
                }else{
                    // Erreur au niveau de l'enregistrement dans la base (mail ou pseudo déjà utilisés)
                    $this->addFlash('Erreur : ' . $categoryCreation, 'danger');
                }

            }else{
                // On renvoie l'erreur retourner par le manager:
                $this->addFlash('Erreur : ' . $newCategory->isValid(), 'danger');
            }

        }

        $categories =  $CategoryManager->getCategories();

        echo $this->render('categories.twig', array('categories' => $categories));

    }

    /**
     * @param $catId
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function categorySearchNewMovies($catId, $searchQueryGet = null, $pageQueryGet = null){
        $CategoryManager = new CategoryManager();
        $category =  $CategoryManager->getCategory($catId);
        $module = "categorySearch";
        $searchResult = $this->searchMovies();

        if($searchQueryGet != null && $pageQueryGet != null ){
            $searchResult = $this->searchMovies($searchQueryGet, $pageQueryGet);
        }

        echo $this->render('category.twig', array('category' => $category, 'module' => $module, 'moviesSearchResults' => $searchResult["moviesSearchResults"], 'searchQuery' => $searchResult["searchQuery"], 'previousPage' => $searchResult["previousPage"], 'nextPage' => $searchResult["nextPage"]));
    }

    /**
     * @param $catId
     * @param $movieId
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function addMoviesToCategory($catId, $movieId){
        $CategoryManager = new CategoryManager();
        $MovieManager = new MovieManager();
        $McuManager = new McuManager();
        $category =  $CategoryManager->getCategory($catId);
//        vd($category); // vérifier la nature de cette variable, objet?
        $module = "addMovie";
        $searchResult = $this->movie($movieId);

        if($this->isPost()){
//            vd($_POST, $_POST['movieId'], $_POST['movieTitle']);
            $newMovieData = [
                'id' => trim(htmlspecialchars($_POST['movieId'])),
                'title' => trim(htmlspecialchars($_POST['movieTitle'])),
                'poster_path' => trim(htmlspecialchars($_POST['posterPath'])),
            ];

            $newMovie = new Movie($newMovieData);

            $justifyComment = trim(htmlspecialchars($_POST['justificationText']));

            if(trim(htmlspecialchars($_POST['justificationText'])) == null || trim(htmlspecialchars($_POST['justificationText'])) == "" ){
                $justifyComment = null;
            }

            $newMCUConnectionData = [
                'movie_id' => trim(htmlspecialchars($_POST['movieId'])),
                'category_id' => trim(htmlspecialchars($_POST['categoryId'])),
                'user_id' => trim(htmlspecialchars($_POST['userId'])),
                'justification_comment' => $justifyComment,
            ];

            $newMCUConnection = new MCUConnection($newMCUConnectionData);


            if(!$MovieManager->doesMovieExist($newMovie)){
                if($MovieManager->doesMovieExist($newMovie) === false ){
                    // on enregistre le nouveau film
                    $movieCreation = $MovieManager->createMovie($newMovie);
                    if($movieCreation !== true){
                        $this->addFlash('Erreur 1: le classement n\'a pas pu être enregistré car  ' .                         $movieCreation,'danger');
                    echo $this->render('category.twig', array('category' => $category, 'module' => $module));
                    }

                }else{
                    // on gère l'erreur qui a été généré
                    $this->addFlash('Erreur 1: le classement n\'a pas pu être enregistré car  ' .                         $MovieManager->doesMovieExist($newMovie),'danger');
                    echo $this->render('category.twig', array('category' => $category, 'module' => $module));
                }
            }
            // Le film existe déjà dans notre base ou vient d'être créé, on peut donc créer la connection :

            if($McuManager->doesMcuExistsAlready($newMCUConnection) > 0 ){
                $this->addFlash('Vous avez déjà classé ce film dans cette catégorie. Vous pouvez modifier ou supprimer cette connexion depuis votre  dashboard.', 'info');
                $this->redirect($this->basePath . 'category/' . $catId);
                die;
            }

            $MCUConnectionCreation = $McuManager->createMovieConnection($newMCUConnection);

            if($MCUConnectionCreation === true){
                $this->addFlash('Votre classement du film "'  . $newMovie->getTitle() .  '" dans la catégorie ' .
                    $category->getNom() . ' a bien été enregistré ! Merci pour votre contribution !', 'success');

                $this->redirect($this->basePath . 'category/' . $catId);

            }else{
                // Erreur au niveau de l'enregistrement dans la base (mail ou pseudo déjà utilisés)
                $this->addFlash('Erreur 2: le classement n\'a pas pu être enregistré car ' . $MCUConnectionCreation, 'danger');
                $this->redirect($this->basePath . 'category/' . $catId);
            }
        }

        echo $this->render('category.twig', array('category' => $category, 'module' => $module, 'movie' => $searchResult["movie"]));
    }


}









