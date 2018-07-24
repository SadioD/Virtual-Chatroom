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
        $this->session->set_userdata('userName', 'Ahmed');
        $this->session->set_userdata('photo', 'avatar_homme.png');
        $this->memberManager->updateEntry(['pseudo' => $this->session->userdata('userName')], ['connexionStatus' => 'online']);

        echo 'Page de connexion , if online access true, green boutton else gray boutton';

        // if sexe = home => photo = https://bootdey.com/img/Content/avatar/avatar3.png (cherchez photo homme et femme)
    }//-------------------------------------------------------------------------------------------------------------------------------
    // La page de Chat  ---------------------------------------------------------------------------------------------------------------------
    public function home() {
        if($this->session->isAuthentificated()) {
            $contactList = $this->memberManager->getAllDataBut('*', ['pseudo' => $this->session->userdata('userName')], null, null, 'pseudo');
            $this->layout->showView('chat/home', array('contactList' => $contactList));
            // $contactList = $this->memberManager->getData();
            //var_dump($contactList->result());
            //echo $this->session->userdata('userName');
        }
        else {
            redirect(site_url('user/connexion'));
        }
    }//-------------------------------------------------------------------------------------------------------------------------------
    // AJAX Charge la conversation au click sur Contact, bouton Précédent, bouton Suivant ---------------------------------------------------------------------------------------------------
    public function loadConversation($contactPseudo, $conversationType, $conversationDate = null) {
        if($this->session->isAuthentificated()) {
            $colsOne = ['sender', 'sender'];
            $colsTwo = ['receiver', 'receiver'];
            $values  = [$contactPseudo, $this->session->userdata('userName')];

            if ($conversationType == 'showConversation') {
                // On recupère les messages de la liste where la date est la plus récente
                    // SELECT WHERE date = [SELECT date WHERE (sender = pseudo OR session(User)) AND (receiver = pseudo OR session(User))
                    // ORDER BY date DESC limit 1] AND (sender = $pseudo OR session(user)) AND (receiver = $pseudo OR session(user))
                // Enfin on envoie la réponse
                $conversation = $this->chatManager->getLatestData('*', 'datePub', $colsOne, $values, $colsTwo, $values, 'id', null, false);
                $this->sendResponse($conversation->result(), 'showConversation', $contactPseudo);
            }
            elseif($conversationType == 'previousMessages') {
                // On recupère les messages de la liste where date = previous
                // SELECT WHERE date = [SELECT date WHERE date < conversationDate AND (sender = pseudo OR session(User)) AND (receiver = pseudo OR session(User))
                // ORDER BY date DESC limit 1] AND (sender = $pseudo OR session(user)) AND (receiver = $pseudo OR session(user))
                // Enfin on envoie la réponse
                $conversation = $this->chatManager->getPreviousData('*', 'datePub', $conversationDate, $colsOne, $values, $colsTwo, $values, 'id', null, false);
                $this->sendResponse($conversation->result(), 'previousMessages');
            }
            elseif($conversationType == 'nextMessages') {
                // On recupère les messages de la liste where date = next
                // SELECT WHERE date = [SELECT date WHERE date > conversationDate AND (sender = pseudo OR session(User)) AND (receiver = pseudo OR session(User))
                // ORDER BY date DESC limit 1] AND (sender = $pseudo OR session(user)) AND (receiver = $pseudo OR session(user))
                // Enfin on envoie la réponse
                $conversation = $this->chatManager->getNextData('*', 'datePub', $conversationDate, $colsOne, $values, $colsTwo, $values, 'id', null, false);
                $this->sendResponse($conversation->result(), 'nextMessages');
            }
        }
        return false;
    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX Ajoute un message - POST via formulaire ---------------------------------------------------------------------------------------------------
    public function postNewMessage() {
        if($this->session->isAuthentificated()) {
            // On insère le message POST avec status 'newPost' dans messages
            $heurePub = date("H:i");
            $this->chatManager->addEntry(     array('sender'         => $this->session->userdata('userName'),
                                                    'receiver'       => $this->input->post('receiverPseudo'),
                                                    'senderMessage'  => $this->input->post('senderMessage'),
                                                    'messageStatus'  => 'newPost'),
                                              array('datePub'        => date("m-d-Y"),  // Si ca marche pas avec Previous Message try SELECT DATE(NOW()) here
                                                    'senderHeurePub' => $heurePub));
            // Ensuite on envoie la réponse AJAX
            $response  = [array('status'         => 'postMessage'),
                          array('senderMessage'  => htmlspecialchars($this->input->post('senderMessage')),
                                'senderHeurePub' => $heurePub)];
            echo json_encode($response);
            return true;
            // END
        }
        return false;


        // recuperer en POST $receiverPseudo, $senderMessage
        // PS insert with senderPseudo => session['userName'], receiverPseudo => $receiverPseudo
        // Aussi on met status du message Not read dans chatroom table
        // et on upate la table membres en mettant les colonnes
            // messageStatus = newPost, sentTo = destinataire du Message envoyé WHERE pseudo = expéditeur (session[userName])


            /* Exemple :
                    12 post a grasset =>
                        grasset a newMessage de 12 =>
                        12 status = new Post,  sentTo = grasset

                    Requete
                    select * ; dans html
                    pour chaque element
                        if(status = newPost et	sentTo = session[pseudo]) => show icone New */

    }//-----------------------------------------------------------------------------------------------------------------------------
    //Gère les requetes automatic recues par Ajax (toutes les 30s) ---------------------------------------------------------------------------------------------------
    public function ajaxAutomaticRequests($requestStatus) {
        if($this->session->isAuthentificated()) {
            if(    $requestStatus == 'loadNewMessages')      $this->loadNewMessage();
            elseif($requestStatus == 'checkOnlineStatus')    $this->checkOnlineStatus();
        }
        return false;
    }//---------------------------------------------------------------------------------------------------------------------------------------------------------
    // AJAX met à jour MessageStatus dans table messages & membres - OLD/NEW POST ---------------------------------------------------------------------------------------------------
    public function updateMessageStatus($senderPseudo, $messageStatus) {
        if($this->session->isAuthentificated()) {
            // Exemple : Cas update to OlD POST après chargement de NewMessages dans chatRoom
            // On modifie table messages messageStatus, set oldPost Where receiver = session[userName] AND sender = $senderPseudo
            $this->chatManager->updateEntry(  array('receiver'      => $this->session->userdata('userName'),
                                                    'sender'        => $senderPseudo),
                                              array('messageStatus' => $messageStatus));
        }
        return false;
    }//---------------------------------------------------------------------------------------------------------------------------------------------------------
    // AJAX supprime en BDD les messages dont receiverPseudo = unserialize($_POST[deleteList]) ---------------------------------------------------------------------------------------------------
    // On recupère la liste de pseudo et pour chacun d'entre eux on on send sql request
    public function deleteConversation() {
        if($this->session->isAuthentificated()) {
            $deleteList = unserialize($this->input->post('deleteList'));
            for($i = 0; $i < count($deleteList); $i++)
            {
                if(!empty($deleteList[$i])) {
                    $this->chatManager->deleteEntries('sender', $deleteList[$i], array('receiver' => $deleteList[$i]));
                }
            }
            // Ensuite on envoie la reponse AJAX
            $response = [array('status' => 'suppression')];
            echo json_encode($response);
            // END
        }
        return false;
    }//-----------------------------------------------------------------------------------------------------------------------------
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonctions Internes -----------------------------------------------------------------------------------------------------------------------------------------------
    // AJAX met à jour MessageStatus dans table messages & membres - OLD/NEW POST
    protected function sendResponse($conversation, $conversationType, $contactPseudo = null) {
        // Si la conversation est vide on envoie une message EMPTY
        if(empty($conversation) || is_bool($conversation) || $conversation == null) {
            $data = array('messagesList' => 'empty', 'status' => $conversationType);
            $reponse[0] = $data;
            echo json_encode($reponse);
            return false;
        }
        // CAS showConversation
        // Si la conversation n'est pas vide, on envoie une requete SQL dans table chatRoom pour mettre les messages en OLD POST
        // WHERE receiver = session(userName) AND sender = $contactPseudo (seulement ceux que nous avons recus)
        if($conversationType == 'showConversation') { $this->updateMessageStatus($contactPseudo, 'oldPost'); }

        // Enfin on envoie la réponse (conversation)
        $data = array('messagesList' => 'notEmpty', 'status' => $conversationType);
        if(  $conversationType == 'previousMessages' || $conversationType == 'showConversation' || $conversationType == 'nextMessages') {
            $response = [$data, $conversation, array('datePub' => $conversation[0]->datePub)];
        }
        elseif($conversationType == 'loadNewMessages' || $conversationType == 'checkOnlineStatus') {
            $response = [$data, $conversation];
        }
        echo json_encode($response);
        return true;
    }
    public function test() {
        /*$this->layout->includeJS('test');
        $this->layout->showView('test.php');*/
        echo date('d-m-Y à H:i');
    }
    public function ajaxtest() {
        $myVar = [['pere' => 'douze', 'mere' => 'chou'], ['pere' => 'ayden', 'mere' => 'hope']];
        echo json_encode($myVar);
    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX charge les nouveaux messages dans le fil - toutes les 30s ---------------------------------------------------------------------------------------------------
    // On charge les messages de tout le monde, where receiver = session[userName] AND message status = 'newPost'
    // Ensuite on envoie la réponse
    protected function loadNewMessage() {
        $newMessages = $this->chatManager->getData('*', array('receiver'      => $this->session->userdata('userName'),
                                                              'messageStatus' => 'newPost'), null, null, 'senderHeurePub');
        $this->sendResponse($newMessages, 'loadNewMessages');
    }//-----------------------------------------------------------------------------------------------------------------------------
    // Permet de vérifier si les contacts sont connectés ---------------------------------------------------------------------------------------------------
    protected function checkOnlineStatus() {
        // On vérifie si session(userName) est connecté
        if(!$this->session->isAuthentificated()) {
            $this->memberManager->updateEntry(['pseudo' => $this->session->userdata('userName')], ['connexionStatus' => 'offline']);
        }
        // Ensuite on recupère la liste des contacts pour revérifier le statut (online/offline) et actualié le cercle vert/gris
        $contactList = $this->memberManager->getData();
        $this->sendResponse($contactList, 'checkOnlineStatus');
    }//---------------------------------------------------------------------------------------------------------------------------------------------------------


}
