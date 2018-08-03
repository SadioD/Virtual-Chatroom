<?php
class User extends CI_Controller
{
    // ATTRIBUT + CONST --------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();

        $this->load->model('memberManager');
        $this->load->library('form_validation');
        $this->layout->setLayout('bootstrap');
        $this->layout->includeJS('footer');
        $this->layout->includeCSS('footer');

    //    $this->layout->includeJS('footer');

    }//-------------------------------------------------------------------------------------------------------------------------------
    // Redirection Controleur  ---------------------------------------------------------------------------------------------------------------------
    public function index() {
        $this->connexion();
    }//------------------------------------------------------------------------------------------------------------------------------
    // Page de connexion - Accueil ---------------------------------------------------------------------------------------------------------------------
    public function connexion()
    {
        if($this->input->server('REQUEST_METHOD') == 'POST') {

            // On vérifie si sex = H ou F => et si FILES = empty => on attribue une photo avatar par défaut
            /* If isset sex => registration, sinon => authentification */
            
            /*$this->session->setAuthentificated(true);
            $this->session->set_userdata('userName', 'Ahmed');
            $this->session->set_userdata('photo', 'avatar_homme.png');
            if sexe = home => photo = https://bootdey.com/img/Content/avatar/avatar3.png (cherchez photo homme et femme)
            $this->memberManager->updateEntry(['pseudo' => $this->session->userdata('userName')], ['connexionStatus' => 'online']);*/
        }
        // controler user



        $this->layout->includeJS('connexion');
        $this->layout->includeCSS('connexion');
        $this->layout->showView('user/connexion');



    }//-------------------------------------------------------------------------------------------------------------------------------
    // Permet de traiter requetes AJAX de registration/connexion ------------------------------------------------------------------------------------
    public function dataProcess($reqType, $pseudo) {
        // code pour vérifier si pseudo est unique en BDD
        if($reqType == 'pseudoRegistration') {
            if($this->memberManager->isUnique(['pseudo' => $pseudo])) {
                return $this->sendResponse(true, $pseudo);
            }
            return $this->sendResponse(false);
        }
        // code pour vérifier si pseudo existe en BDD
        elseif($reqType == 'authentification') {
            if($this->memberManager->isUnique(['pseudo' => $pseudo])) {
                return $this->sendResponse(false);
            }
            return $this->sendResponse(true);
        }

    }//-------------------------------------------------------------------------------------------------------------------------------
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Permet d'envoyer les réponses AJAX' ------------------------------------------------------------------------------------
    public function sendResponse($status) {
        echo json_encode(['status' => $status]);
        return $status;
    }//-------------------------------------------------------------------------------------------------------------------------------

}
