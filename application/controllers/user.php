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
        // controler user

        /*$this->session->setAuthentificated(true);
        $this->session->set_userdata('userName', 'Ahmed');
        $this->session->set_userdata('photo', 'avatar_homme.png');
        if sexe = home => photo = https://bootdey.com/img/Content/avatar/avatar3.png (cherchez photo homme et femme)
        $this->memberManager->updateEntry(['pseudo' => $this->session->userdata('userName')], ['connexionStatus' => 'online']);*/

        $this->layout->includeCSS('cmxform');               // Pluggin Validation Form (Jquery) - Le CSS du message d'erreur
        $this->layout->includeCSS('connexion');
        $this->layout->includeJS('jquery.validate.min');    // Pluggin Validation Form (Jquery) - Valide le Form
        $this->layout->includeJS('additional-methods.min'); // Pluggin Validation Form (Jquery) - Valide les fichier à upload
        $this->layout->includeJS('connexion');      




        $this->layout->showView('user/connexion');



    }//-------------------------------------------------------------------------------------------------------------------------------
    // Permet de traiter requetes AJAX de registration/connexion ------------------------------------------------------------------------------------
    public function dataProcess($reqType = null) {
        if($reqType == 'pseudoRegistration') {
            // code pour vérifier si pseudo est unique en BDD
        }
        elseif($reqType == 'authentification') {
            // code pour vérifier si pseudo existe en BDD
        }
        elseif($reqType == null) {
            // Cas upload photo
        }
    }//-------------------------------------------------------------------------------------------------------------------------------

}
