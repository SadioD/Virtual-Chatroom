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
    public function loadConversation($contactPseudo, $conversationType, $conversationDate = null) {
        if ($conversationType == 'showConversation') {
            // On recupère les messages de la liste
                // SELECT WHERE date = MAX(date) AND (sender = $pseudo OR session(user)) AND (receiver = $pseudo OR session(user))
            $conversation = $this->chatManager->getLatestData('*', 'datePub',
                                                              array('sender'   => $contactPseudo, 'sender' => $this->session->userdata('userName')),
                                                              array('receiver' => $contactPseudo, 'receiver' => $this->session->userdata('userName')),
                                                              'id', null, false);
            // On envoie une requete SQL dans table chatRoom pour les mettre en OLD POST +
            // On envoie une requete SQL dans table membres pour update messageStatus en OLD POST
            $this->updateMessageStatus($contactPseudo, 'oldPost', $this->session->userdata('userName'), 'requestedFromThis');

            // Ensuite on envoie la réponse
            if(empty($conversation) || is_bool($conversation) || $conversation == null) {
                $data = array('messagesList' => 'empty', 'status' => 'showConversation');
                $reponse[0] = $data;
                echo json_encode($reponse);
                return false;
            }
            $data = array('messagesList' => 'notEmpty', 'status' => 'showConversation');
            $response = [$data, $conversation, array('datePub' => $conversation[0]->datePub)];
            echo json_encode($response);
            return true;
            // END

            /* Creation d'une branche experimentale pour mise à jour =>
                when POST  => update table messageStatus => insert new entrey sender + receiver + NEW
                when read => update table messageStatus instead of membres where sender = sender and receiver = receiver
                    set status OLD.

                    this will allow to have several sentO people */

        }
        elseif($conversationType == 'previousConversation') {

        }
        elseif($conversationType == 'nextConversation') {

        }

    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX charge les nouveaux messages dans le fil - toutes les 30s ---------------------------------------------------------------------------------------------------
    // On charge les messages de tout le monde, where receiver = session[userName] AND message status = 'notRead'
    public function loadNewMessage() {
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
                douze post a grasset =>
                sender = 12 et receiver = grasset  --- status not read

                requete loadNewMessages
                    slect * where receiver = userName and status = not read
             */


    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX Ajoute un message - POST via formulaire ---------------------------------------------------------------------------------------------------
    public function postNewMessage() {
        // On insère le message POST avec status 'newPost' dans messages +
        // Mise à jour de membres : set NewPost et sentTo (POST - receiverPseudo) WHERE pseudo = sender (session[userName])
        $heurePub = date("H:i");
        $this->chatManager->addEntry(     array('sender'         => $this->session->userdata('userName'),
                                                'receiver'       => $this->input->post('receiverPseudo'),
                                                'senderMessage'  => $this->input->post('senderMessage'),
                                                'messageStatus'  => 'newPost'),
                                          array('datePub'        => date("m-d-Y"),  // Si ca marche pas avec Previous Message try SELECT DATE(NOW()) here
                                                'senderHeurePub' => $heurePub));
        $this->memberManager->updateEntry(array('pseudo'         => $this->session->userdata('userName')),
                                          array('messageStatus'  => 'newPost',
                                                'sentTo'         => $this->input->post('receiverPseudo')));

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
                    12 post a grasset =>
                        grasset a newMessage de 12 =>
                        12 status = new Post,  sentTo = grasset

                    Requete
                    select * ; dans html
                    pour chaque element
                        if(status = newPost et	sentTo = session[pseudo]) => show icone New */

    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX met à jour MessageStatus dans table messages & membres - OLD/NEW POST ---------------------------------------------------------------------------------------------------
    public function updateMessageStatus($senderPseudo, $messageStatus, $receiverPseudo = null, $requestMethod = 'Ajax') {
        if($requestMethod == 'Ajax') {
            // Exemple : Cas update to OlD POST après chargement de NewMessages dans chatRoom
                // On modifie table messages messageStatus, set oldPost Where receiver = session[userName] AND sender = $senderPseudo
                // On modifie table membres messageStatus, set oldPost Where pseudo = $senderPseudo ////////
            // Si $receiverPseudo = null => le message est destiné à session[userName]
            if($receiverPseudo == null) {
                $receveirPseudo = $this->session->userdata('userName');
            }
            $this->chatmanager->updateEntry(  array('receiver'      => $receiverPseudo,
                                                    'sender'        => $senderPseudo),
                                              array('messageStatus' => $messageStatus));
            $this->memberManager->updateEntry(array('pseudo'        => $senderPseudo,
                                                    'sentTo'        => $receiverPseudo),
                                              array('messageStatus' => $messageStatus));
        }
        else {
            // Cette partie concerne principalement la méthode loadConversation ('showConvesation')
            // SET messageStatus = 'oldPost' WHERE (sender = $pseudo OR session(user)) AND (receiver = $pseudo OR session(user))
            $this->chatManager->setEntries(  array('sender'        => $senderPseudo,
                                                   'sender'        => $receiverPseudo),
                                             array('receiver'      => $receiverPseudo,
                                                   'receiver'      => $senderPseudo),
                                             array('messageStatus' => $messageStatus));
            // SET messagestatus = oldPost WHERE (pseudo = $pseudo OR session(user)) AND (sentTo = $pseudo OR session(user))
            $this->memberManager->setEntries(array('pseudo'        => $senderPseudo,
                                                   'pseudo'        => $receiverPseudo),
                                             array('sentTo'        => $receiverPseudo,
                                                   'sentTo'        => $senderPseudo),
                                             array('messageStatus' => $messageStatus));
        }
        // END
    }//-----------------------------------------------------------------------------------------------------------------------------
    // AJAX supprime en BDD les messages dont receiverPseudo = unserialize($_POST[deleteList]) ---------------------------------------------------------------------------------------------------
    // On recupère la liste de pseudo et pour chacun d'entre eux on on send sql request
    public function deleteConversation() {
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
