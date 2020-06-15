<?php


namespace App\Controller;



use App\Model\Manager\McuManager;

class AdminController extends AbstractController
{

    public function __construct() {
        parent::__construct();

        if($this->isAdmin === false){
            $this->redirectIfNotAdmin();
        }
    }

    protected function redirectIfNotAdmin()
    {
        $this->addFlash("Ces pages sont réservées aux administrateurs, veuillez vous connecter.", "danger", true);
        $this->redirect('home');
        die;
    }


    public function adminActionComment(){
        // Gestion des actions sur les commentaires en Ajax
        $mcuManager = new McuManager();
        if($this->isPost()){
            if(isset($_POST['commentAction']) && isset($_POST['id'])){
                if($_POST['commentAction'] == 'delete'){
                    $response = new \stdClass();
                    $response->result = $mcuManager->deleteComment($_POST['id']);
                    echo json_encode($response);
                }
            }
        }
    }
}