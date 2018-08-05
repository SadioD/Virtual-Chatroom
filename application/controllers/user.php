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
    }//-------------------------------------------------------------------------------------------------------------------------------
    // Redirection Controleur  ---------------------------------------------------------------------------------------------------------------------
    public function index() {
        $this->connexion();
    }//------------------------------------------------------------------------------------------------------------------------------
    // Page de connexion - Accueil ---------------------------------------------------------------------------------------------------------------------
    public function connexion() {
        $errorAuth = '';
        $this->layout->includeJS('connexion');
        $this->layout->includeCSS('connexion');

        if($this->input->server('REQUEST_METHOD') == 'POST') {

            // Registration Case
            // Si le formulaire est OK, on vérifie s'il existe une PJ (Si Oui, on la traite)
            if($this->input->post('sex')) {
                $this->form_validation->set_rules('prenom', 'Pseudo', 'required|is_unique[membres.pseudo]');

                if($this->form_validation->run()) {
                    if(isset($_FILES['photo']) AND $_FILES['photo']['error'] == 0 AND $_FILES['photo']['size'] > 0) {
                        $config['max_size']      = 250;
                        $config['upload_path']   = './assets/images/';
                        $config['allowed_types'] = 'png|jpg';
                        $this->load->library('upload', $config);

                        if(!$this->upload->do_upload('photo'))
                        {
                            $error = $this->upload->display_errors();
                            $this->layout->showView('user/connexion', array('errorUpload' => $error));
                            return false;
                        }
                        $file     = $this->upload->data();
                        $fileName = $file['file_name'];
                    }
                    else {
                        $fileName = ($this->input->post('sex') == 'M' ? 'avatar_homme.png' : 'avatar_femme.png');
                    }
                    // Ensuite on Save le nouveau membre
                    $this->memberManager->addEntry(['pseudo'          => $this->input->post('prenom'),
                                                    'photo'           => $fileName,
                                                    'connexionStatus' => 'online']);
                    // On le connecte et on le redirige
                    $this->session->setAuthentificated(true);
                    $this->session->set_userdata('userName', $this->input->post('prenom'));
                    $this->session->set_userdata('photo', $fileName);
                    redirect(site_url('chat/home'));
                    exit;
                }
            }
            else {
                // Authentification Case
                $this->form_validation->set_rules('prenom', 'Pseudo', 'is_unique[membres.pseudo]');

                if(!$this->form_validation->run()) {
                    $this->memberManager->updateEntry(           ['pseudo'          => $this->input->post('prenom')],
                                                                 ['connexionStatus' => 'online']);
                    $member = $this->memberManager->getData('*', ['pseudo'          => $this->input->post('prenom')]);

                    // On le connecte et on le redirige
                    $this->session->setAuthentificated(true);
                    $this->session->set_userdata('userName', $member[0]->pseudo);
                    $this->session->set_userdata('photo',    $member[0]->photo);
                    redirect(site_url('chat/home'));
                    exit;
                }
                $errorAuth = 'The entered Pseudo does not exist!';
            }
        }
        $this->layout->showView('user/connexion', ['errorPrenomAuth' => $errorAuth]);
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
