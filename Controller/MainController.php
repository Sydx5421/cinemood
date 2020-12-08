<?php

namespace App\Controller;

use App\Library\API\TmdbApi;
use App\Model\Entity\User;
use App\Model\Manager\CategoryManager;
use App\Model\Manager\McuManager;
use App\Model\Manager\UserManager;

class MainController extends AbstractController
{
    use TmdbRequestsTrait;

    public function randomMovies(){
        $TmdbApi = new TmdbApi("01caf40148572dc465c9503e59ded4bf");
        $randMovies =  $TmdbApi->getRandomMovies();
        echo $randMovies ;
    }

    public function home(){
        // Récupération des 3 dernières catégories créées
        $CategoryManager = new CategoryManager();
        $lastCategories = $CategoryManager->getRandomCategories();

        // Récupération des 3 dernières connexions :
        $McuManager = new McuManager();
        $lastMcuConnection = $McuManager->getRandomMcu();

        echo $this->render('home.twig', array('lastCategories' => $lastCategories, 'lastMcuConnection' => $lastMcuConnection));

    }

    public function register(){
        $UserManager = new UserManager();

        if($this->isPost()){
            $pseudo = trim(htmlspecialchars($_POST['pseudo']));
            $password = sha1($_POST['register_password']);
            $confirm_password = sha1($_POST['confirm_register_password']);
            $email = trim(htmlspecialchars($_POST['email']));

            $newUserData = [
                'pseudo' => $pseudo,
                'password' => $password,
                'confirmPassword' => $confirm_password,
                'email' => $email
            ];

            $newUser = new User($newUserData);

            if($newUser->isValid() === true){
                $userRegistration = $UserManager->register($newUser);
                if($userRegistration === true){
                    $this->addFlash('Votre compte a bien été créé ! Bienvenue sur Cinemood ' . $newUser->getPseudo()
                        . '. Vous pouvez dès à présent vous connecter.', 'success');
                    $this->redirect('home');
                }else{
                    // Erreur au niveau de l'enregistrement dans la base (mail ou pseudo déjà utilisés)
                    $this->addFlash('Erreur : ' . $userRegistration, 'danger');
                }
            }else{
                // Erreurs au niveau de la validation du formulaire d'inscription (mal rempli)
                $this->addFlash('Erreur : ' . $newUser->isValid(), 'danger');
            }
        }
            echo $this->render('register.twig');
    }


    public function login(){
        $UserManager = new UserManager();

        if($this->isPost()){

            if(isset($_POST['email_or_pseudo']) && isset($_POST['password'])){
                if(!empty($_POST['email_or_pseudo']) && !empty($_POST['password'])){
                    $password = sha1($_POST['password']);
                    $login = htmlspecialchars($_POST['email_or_pseudo']);

                    $userLogin = $UserManager->login($login, $password);

                    if(is_object($userLogin)){
                        $userLogged = new User($userLogin);
                        if($userLogged->getIsAdmin() == 1){
                            $_SESSION['admin'] = true;
                            $this->isAdmin = true;
                        }
                        $_SESSION['user'] = $userLogin;
                        $this->userConnected = true;

                        $this->addFlash('Bienvenue ' . $userLogged->getPseudo(), "success");
                        $this->redirect('dashboard/' . $userLogged->getId());
                        exit();
                    }else{
                        $this->addFlash($userLogin, "danger");
                        $this->redirect('home' );
                    }
                }else{
                    $this->addFlash('Veuillez remplir tous les champs afin de pouvoir vous connecter.', "danger");
                    $this->redirect('home');
                }
            }
        }
        $this->redirect('home');
    }

    public function simpleMovieSearch($searchQueryGet = null, $pageQueryGet = null){
        $McuManager = new McuManager();

        $searchResult = $this->searchMovies($searchQueryGet, $pageQueryGet);

        $results =$searchResult["moviesSearchResults"]->results;

        $resultsIds = array();
        foreach ($results as $result){
            $resultsIds[] =  $result->id;
        }

        $cinemoodMovies = $McuManager->MLinkCheck($resultsIds);
        $cinemoodMoviesIds = array();
        foreach ($cinemoodMovies as $movie){
            $cinemoodMoviesIds[] = $movie->getMovieId();
        }

        foreach ($results as $result) {
            if (in_array($result->id, $cinemoodMoviesIds)) {
                $result->cinemood_movie = 'true';
            }
        }
//        vd($cinemoodMovies, $cinemoodMoviesIds, $results);

        echo $this->render('searchResults.twig', array('moviesSearchResults' => $searchResult["moviesSearchResults"], 'searchQuery' => $searchResult["searchQuery"], 'previousPage' => $searchResult["previousPage"], 'nextPage' => $searchResult["nextPage"]));

    }


    public function categories(){
        $CategoryManager = new CategoryManager();
        $categories =  $CategoryManager->getCategories();

        echo $this->render('categories.twig', array('categories' => $categories));
    }

    public function category($categoryId, $search=null){
        $CategoryManager = new CategoryManager();
        $category =  $CategoryManager->getCategory($categoryId);

        $movieList = $CategoryManager->getCategoryMovieList($categoryId);

        $module = "categoryFilms";

        if($search != null){
            $module = "categorySearch";
        }

        echo $this->render('category.twig', array('category' => $category, 'module'
        => $module, 'movieList' => $movieList));
    }

    public function movie($movieId, $firstParam = null){
        $MovieAPI = new TmdbApi("01caf40148572dc465c9503e59ded4bf");
        $infosMovie = $MovieAPI->getMoviesById($movieId);

        $CategoryManager = new CategoryManager();
        $McuManager = new McuManager();

        $allCategories = $CategoryManager->getCategories();

//        vd($movieId, $firstParam, is_numeric($firstParam));

        if($_POST){
            vd($_POST);
        }

        if($firstParam === null ){
//            Afficher toutes les catégories liées à ce film

            $categories = $CategoryManager->getCategories($movieId);

            if($categories == null ){
                $module = "addCategory";
                echo $this->render('movie.twig', array("movie" => $infosMovie, "associatedCategories" => $categories, "allCategories" => $allCategories, 'module'=> $module));
                die;
            }else{
                $module = "categoryList";
                echo $this->render('movie.twig', array("movie" => $infosMovie, "associatedCategories" => $categories, "allCategories" => $allCategories, 'module'=> $module));
                die;
            }
            die;
        }elseif(is_numeric($firstParam)){// $firtParam designate the id of a category
            //TODO
            // traiter le cas ou l'id ne correspond à rien !
            // Afficher un NOT FOund pour la catégorie ou qqch comme ça, qui traite cette éventualité.
            $module = "categorySelected";
            $category =  $CategoryManager->getCategory($firstParam);
            // récupérer les comentaires pour cette catégory et ce film
            $mcuList = $McuManager->getAllCommentsForMC($movieId, $firstParam);

            echo $this->render('movie.twig', array("movie" => $infosMovie, "category" =>
                $category, "mcuList" => $mcuList, 'module'=> $module));
        }else{ //$firtParam designate a module for the page
            if($firstParam == "addCategories"){
                $module = "addCategory";
                $allCategories = $CategoryManager->getCategories();
//                vd($allCategories);
                echo $this->render('movie.twig', array("movie" => $infosMovie, "allCategories" => $allCategories, 'module'=> $module));
                die;
            }
        }
    }

    public function categoryFilms(){
        $this->render('categoryFilms');
    }

}
