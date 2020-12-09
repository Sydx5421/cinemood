<?php


namespace App\Controller;

use App\Library\API\TmdbApi;
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
        $McuManager = new McuManager();
        $category =  $CategoryManager->getCategory($catId);
        $module = "categorySearch";
        // API call
        $searchResult = $this->searchMovies($searchQueryGet, $pageQueryGet);
        $results =$searchResult["moviesSearchResults"]->results;

        $resultsIds = array();
        foreach ($results as $result){
            $resultsIds[] =  $result->id;
        }
        //Checking for movies already classed in this category and retrieving their infos stored on my db
        $classifiedMovies = $McuManager->MCLinkCheck($resultsIds, $catId);
        // Same for the movies classed by the user connected
        $userId = $this->session["user"]->id;
        $userClassifiedMovies = $McuManager->MCULinkCheck($resultsIds, $catId, $userId);

        // Making tables of movies's ids with preexisting MCLink and MCULink respectively
        $classifiedMoviesIds = array();
        foreach ($classifiedMovies as $movie){
            $classifiedMoviesIds[] = $movie->getMovieId();
        }
        $userClassifiedMoviesIds = array();
        foreach ($userClassifiedMovies as $movie){
            $userClassifiedMoviesIds[] = $movie->getMovieId();
        }
        // Making key/value table associating each MClinked movie's id with the number of comments associated for this category
        $moviesNbComments = array();
        foreach($classifiedMovies as $movie){
            $comment = trim($movie->getJustificationComment());
            if(isset($comment) === true && $comment !== ''){
                $key = $movie->getMovieId();
                if(array_key_exists($key, $moviesNbComments) ){
                    $moviesNbComments[$key]++;
                }else{
                    $moviesNbComments[$key] = 1;
                }
            }
        }
        //counting the number of MCLink for each movie's id (comments or no comments)
        $mcLinkOccurencies = array_count_values ($classifiedMoviesIds);

        //Setting twig parameters in the result to exploit the infos recolted
        foreach ($results as $result){
            if(in_array($result->id, $classifiedMoviesIds) ){
                $result->mclink = 'true';
            }
            if(array_key_exists($result->id, $moviesNbComments)){
                $result->nbcomments = $moviesNbComments[$result->id];
            }
            if(in_array($result->id, $userClassifiedMoviesIds) ){
                $result->mculink = 'true';
            }
            if(array_key_exists($result->id, $mcLinkOccurencies)){
                $result->mc_link_occurence = $mcLinkOccurencies[$result->id];
            }
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
        $category =  $CategoryManager->getCategory($catId);

        $module = "addMovie";
        $searchResult = $this->movie($movieId);

        if($this->isPost()){

            // proceed with mcuCreation() method
            $twigVue ='category.twig';
            $arrayTwigVue = array('category' => $category, 'module' => $module);
            $redirectPath = 'category/' . $catId;

            $this->mcuCreation($twigVue, $arrayTwigVue, $redirectPath, $category);

        }

        echo $this->render('category.twig', array('category' => $category, 'module' => $module, 'movie' => $searchResult["movie"]));
    }

    /**
     * @param $movieId
     * @param null $catId
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function addCategoryToMovie($movieId, $catId = null){
        $MovieAPI = new TmdbApi("01caf40148572dc465c9503e59ded4bf");
        $infosMovie = $MovieAPI->getMoviesById($movieId);

        $CategoryManager = new CategoryManager();
        $allCategories = $CategoryManager->getCategories();

        $module = "addCategory";

        if($this->isPost()){

            $category =  $CategoryManager->getCategory(trim(htmlspecialchars($_POST['categoryId'])));
            // proceed with mcuCreation() method
            $twigVue ='movie.twig';
            $arrayTwigVue = array("movie" => $infosMovie, "allCategories" => $allCategories, 'module'=> $module);
            $redirectPath = 'movie/' . $movieId;

            $this->mcuCreation($twigVue, $arrayTwigVue, $redirectPath, $category);

        }

        echo $this->render('movie.twig', array("movie" => $infosMovie, "allCategories" => $allCategories, 'module'=> $module));
        die;

    }

    /**
     * @param $twigVue
     * @param $arrayTwigVue
     * @param $redirectPath
     * @param $category
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function mcuCreation($twigVue, $arrayTwigVue, $redirectPath, $category){
    // factorisation of the MCU creation process, to be used weather we come from a movie or a category.

        $MovieManager = new MovieManager();
        $McuManager = new McuManager();

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
                // new movie registration
                $movieCreation = $MovieManager->createMovie($newMovie);
                if($movieCreation !== true){
                    $this->addFlash('Erreur 1: le classement n\'a pas pu être enregistré car  ' .                         $movieCreation,'danger');
                    // vu à afficher si la création du film echoue :
                    echo $this->render($twigVue, $arrayTwigVue);
                }

            }else{
                // error management
                $this->addFlash('Erreur 1: le classement n\'a pas pu être enregistré car  ' .                         $MovieManager->doesMovieExist($newMovie),'danger');
                echo $this->render($twigVue, $arrayTwigVue);
//                echo $this->render('movie.twig', array("movie" => $infosMovie, "allCategories" => $allCategories, 'module'=> $module));
            }
        }
        // Now that we made sure the film exist in our DB, we can create the MCU connection :
        // first we check that the MCU does not allready exist
        if($McuManager->doesMcuExistsAlready($newMCUConnection) > 0 ){
            if($twigVue ==='movie.twig'){
                $this->addFlash('Vous avez déjà associé cette catégorie à ce film. Vous pouvez modifier ou supprimer cette connexion depuis votre  dashboard.', 'info');
            }else{
                $this->addFlash('Vous avez déjà classé ce film dans cette catégorie. Vous pouvez modifier ou supprimer cette connexion depuis votre  dashboard.', 'info');
            }
            $this->redirect($this->basePath . $redirectPath);
            die;
        }

        $MCUConnectionCreation = $McuManager->createMovieConnection($newMCUConnection);

        if($MCUConnectionCreation === true){
            if($twigVue ==='movie.twig'){
                $this->addFlash('Votre ajout de la catégorie "'  . $category->getNom() .  '" au film ' .
                    $newMovie->getTitle() . ' a bien été enregistré ! Merci pour votre contribution !', 'success');
            }else{
                $this->addFlash('Votre classement du film "'  . $newMovie->getTitle() .  '" dans la catégorie ' .
                    $category->getNom() . ' a bien été enregistré ! Merci pour votre contribution !', 'success');
            }
            $this->redirect($this->basePath . $redirectPath);

        }else{
            // Erreur au niveau de l'enregistrement dans la base (mail ou pseudo déjà utilisés)
            $this->addFlash('Erreur 2: le classement n\'a pas pu être enregistré car ' . $MCUConnectionCreation, 'danger');
            $this->redirect($this->basePath . $redirectPath);;
        }
    }

}









