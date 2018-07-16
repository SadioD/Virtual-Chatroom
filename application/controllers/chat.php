<?php
class Chat extends CI_Controller
{
    // ATTRIBUT + CONST --------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        //$this->output->cache(1);
        $this->load->model('chatManager');
        $this->load->model('memberManager');
        $this->layout->setLayout('bootstrap');

        // On inclue le CSS et JS propre aux pages pas de Bootstrap
        $this->layout->includeCSS('home');
        $this->layout->includeJS('home');
    //    $this->layout->includeJS('footer');

    }//-------------------------------------------------------------------------------------------------------------------------------
    // Redirection Controleur  ---------------------------------------------------------------------------------------------------------------------
    public function index() {
        $this->home();
    }//------------------------------------------------------------------------------------------------------------------------------
    // Page de connexion - Accueil ---------------------------------------------------------------------------------------------------------------------
    public function connexion()
    {
        // controler user
        $this->session->setAuthentificated(true);
        echo 'Page de connexion , if online access true, green boutton else gray boutton';

        // if sexe = home => photo = https://bootdey.com/img/Content/avatar/avatar3.png (cherchez photo homme et femme)
    }//-------------------------------------------------------------------------------------------------------------------------------
    // La page de Chat  ---------------------------------------------------------------------------------------------------------------------
    public function home() {
        if($this->session->isAuthentificated()) {
            $contactList = $this->membersManager->getData('*', array(), null, null, 'pseudo');
            $this->layout->showView('chat/room', array('contactList' => $contactList));
        }
        else {
            redirect(site_url('user/connexion'));
        }
    }//-------------------------------------------------------------------------------------------------------------------------------
    // AJAX Charge la conversation au click sur Contact, bouton Précédent, bouton Suivant ---------------------------------------------------------------------------------------------------
    public function loadConversation($receiverPseudo, $conversationType, $conversationDate = null) {
        if ($conversationType == 'showConversation') {
            // on recupère les messages de la liste
                // on envoie une requete SQL dans table chatRoom pour les mettre en READ
                // on envoie une requete SQL dans table membres pour update le membre =>  set status = none AND sentTo = none


                // response[0].status , response[0].photo  et response[0].du receiver (il faut rajouter response.photo à
                    // à l'index [0] pour afficher la photo  )
        }
        elseif($conversationType == 'previousConversation') {

        }
        elseif($conversationType == 'nextConversation') {

        }

    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX charge les nouveaux messages dans le fil - toutes les 30s ---------------------------------------------------------------------------------------------------
    public function loadNewMessage($receiverPseudo, $conversationType, $conversationDate, $receiverLastMessageTime) {
        $newMessages = $this->chatManager->getData('*', array('receiver' => $thi->session->userdata('userName'), 'messageStatus' => 'newPost'), null, null, 'senderHeurePub');

        // S'il n'y a pas de nouveaux messages on crée la variable $messagesList = 'empty'
        if(empty($newMessages) || is_bool($newMessages) || $newMessages == null) {
            $data = array('messagesList' => 'empty', 'status' => 'loadNewMessages');
            $reponse[0] = $data;
            echo json_encode($reponse);
            return false;
        }
        // if isset $newMessage => on les envoie en reponse AJAX
        $data = array('messagesList' => 'notEmpty', 'status' => 'loadNewMessages');
        $reponse = [$data, $newMessages];
        echo json_encode($response);
        return true;
        // END

        /* On charge le messae de tout le monde, where receiver = session[userName] AND message status = 'notRead'
            => on n'a plus besoin des paramètres d'en haut
            Exemple :
                1er cas :
                    douze post a grasset =>
                    sender = 12 et receiver = grasset  --- status not read

                2 cas :
                    grasset post à douze =>
                    serder = grasset et receiver = 12  --- status not read

                requete loadNewMessages
                    slect * where receiver = userName and status = not read
             */


    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX Ajoute un message - POST via formulaire ---------------------------------------------------------------------------------------------------
    public function postNewMessage() {
        // On insère le message POST avec status 'newPost' dans messages +
        // Mise à jour de membres : set NewPost et sentTo (POST - receiver) WHERE pseudo = sender (session[userName])
        $heurePub = date("H:i");
        $this->chatManager->addEntry(     array('sender'         => $this->session->userdata('userName'),
                                                'receiver'       => $this->input->post('receiverPseudo'),
                                                'senderMessage'  => $this->input->post('senderMessage'),
                                                'messageStatus'  => 'newPost'),
                                          array('datePub'        => date("m-d-Y"),  // Si ca marche pas avec Previous Message try SELECT DATE(NOW()) here
                                                'senderHeurePub' => $heurePub));
        $this->memberManager->updateEntry(array('pseudo'         => $this->session->userdata('userName')),
                                                'messageStatus'  => 'newPost',
                                                'sentTo'         => $this->input->post('receiverPseudo'));

        // Ensuite on envoie la réponse AJAX
        $response  = [array('status'         => 'postMessage'),
                      array('senderMessage'  => htmlspecialchars($this->input->post('senderMessage')),
                            'senderHeurePub' => $heurePub)];
        echo json_encode($response);
        return true;
        // END 


        // recuperer en POST $receiverPseudo, $senderMessage
        // PS insert with senderPseudo => session['userName'], receiverPseudo => $receiverPseudo
        // Aussi on met status du message Not read dans chatroom table
        // et on upate la table membres en mettant les colonnes
            // messageStatus = newPost, sentTo = destinataire du Message envoyé WHERE pseudo = expéditeur (session[userName])


            /* Exemple :
                    1er cas :
                        12 post a grasset =>
                        grasset a newMessage de 12 =>
                        12 status = new Post,  sentTo = grasset

                    Requete
                    select * ; dans html
                    pour chaque element
                        if(status = newPost et	sentTo = session[pseudo]) => show icone New */

    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX met à jour MessageStatus dans table messages & membres - OLD/NEW POST ---------------------------------------------------------------------------------------------------
    public function updateMessageStatus($senderPseudo, $messageStatus, $receiverPseudo = null, $sentTo = 'none') {
        // Exemple : Cas update OlD POST après chargement de NewMessages dans chatRoom
            // On modifie table messages messageStatus (oldPost) Where receiver = session[userName] AND sender = $senderPseudo
            // On modifie table membres messageStatus (oldPost) Where pseudo = $senderPseudo ////////

        // Si $receiverPseudo = null => le message est destiné à session[userName]
        if($receiverPseudo == null) {
            $receveirPseudo = $this->session->userdata('userName');
        }
        $this->chatmanager->updateEntry(  array('receiver'      => $receiverPseudo,
                                                'sender'        => $senderPseudo),
                                          array('messageStatus' => $messageStatus));
        $this->memberManager->updateEntry(array('pseudo'        => $senderPseudo),
                                          array('messageStatus' => $messageStatus,
                                                'sentTo'        => $sentTo));
    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX supprime en BDD les messages dont receiverPseudo = $_POST[deleteList] (array) ---------------------------------------------------------------------------------------------------
    public function deleteConversation() {
        $deleteList = unserialize($_POST['deleteList']);
        //$deleteList est un array

    }//-----------------------------------------------------------------------------------------------------------------------------
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonctions Internes -----------------------------------------------------------------------------------------------------------------------------------------------
    protected function anonyme() {

    }
    public function test() {
        /*$this->layout->includeJS('test');
        $this->layout->showView('test.php');*/
        echo date('d-m-Y à H:i');
    }
    public function ajaxtest() {
        $myVar = [['pere' => 'douze', 'mere' => 'chou'], ['pere' => 'ayden', 'mere' => 'hope']];
        echo json_encode($myVar);
    }


}
