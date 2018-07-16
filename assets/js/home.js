// Avant que le DOM ne soit chargé on caché le back button
$('#backButton').hide();

// ENsuite on exécute le reste du code quand le DOM sera chargé
$(function(){
    // CLIENT - Design : Afficher/ Faire disparaitre la liste de contacts et la zone de conversation ----------------------------------------------------------
    $('#backButton').on('click', showLeftSide);
    $('.fa-comments').on('click', showRightSide);

    function showRightSide() {
        $('#leftSide').fadeOut();
        $('#backButton').show();
    }
    function showLeftSide() {
        $('#backButton').hide();
        $('#leftSide').fadeIn();
    }//------------------------------------------------------------------------------------------------------------------------------------------------------------
    // SERVEUR - Gestion des requetes : Envoyer des requetes et afficher les réponses (AJAX) -------------------------------------------------------------------
    var manager;
    manager =
    {
        // Définit les fonctionnalités internes
        settings: {
            // Agit sur l'ajax loader (image gif - loading in progress...)
            turnOverlay: function(status) {
                if     (status == 'ON')  $('#myOverlay, #myGifLoad').show();
                else if(status == 'OFF') $('#myOverlay, #myGifLoad').hide();
            },
            // Change le background du contact quand il est cliqué
            setElementActive: function(element) {
                if(element != null) {
                    if(typeof $('.active').get()[0] != 'undefined') {
                        $('.active').removeClass('active').css('background', 'white');
                    }
                    $(element).addClass('active').css('background', 'rgba(93, 93, 93, 0.1)');
                    return true;
                }
                return false;
            },
            // Vide la zone de conversation (en cas de suppression de données)
            emptyChatRoom: function() {
                $('.message-body, .bouttonPrecedent, .bouttonSuivant').remove();
                $('.message-date').text('Say Hello!');
            },
            // Active le chatRoom lors du click sur un contact
            setChatRoomActive: function(calledEvent, datePub = null) {
                if(calledEvent == 'click') {
                    // On Scroll la conversation en bas de page (dans zone de conversation)
                    // Et on Retire la surcouche grise du ChatRoom et active le champ de Texte
                    $('#myAnchor').focus();
                    $('#surcouche').hide();
                    $('#senderMessage').attr('disabled', false);
                }
                // Si la date est fournie, on actualise la date de la zone de texte
                if(datePub != null) {
                    $('.message-date').text(datePub);
                }
                // On affiche et fait disparaitre la date (Zone de conversation - Click et scroll)
                $('#messagePrevious').fadeIn('slow');
                setTimeout('$("#messagePrevious").hide()', 2000);
            },
            // Vérifie le formulaire avant l'envoi
            checkForm: function(message) {
                var myRegex  = /[a-z0-9]+/i;
                if(myRegex.test(message.value)) {
                    return true;
                }
                alert('You cannot post an empty message!');
                return false;
            },
            // Crée les élements HTML qui contiendront la conversation (zone de chat)
            createHTMLElements: function(response, responseStatus) {
                if(responseStatus == 'current||previous||next-Timeline')
                {
                    // Le bouton precedent
                    var conversation = '<div class="row bouttonPrecedent"><div class="col-sm-12 text-center">';
                    conversation +='<a href="" title="Show Previous Messages" id="previousMessage" class="' + response[1].datePub + '">';
                    conversation +='<i class="fa fa-chevron-circle-up fa-2x" aria-hidden="true"></i></a></div></div>';

                    // Les messages
                    for(var i = 1, c = response.length; i < c; i++) {
                        if(response[i].receiverMessage != null) {
                            conversation +='<div class="row message-body"><div class="col-sm-12 message-main-receiver">';
                            conversation +='<div class="receiver"><div class="message-text">' + response[i].receiverMessage + '</div>';
                            conversation +='<span class="message-time pull-right">' + response[i].receiverHeurePub + '</span>';
                            conversation +='</div></div></div>';
                        }
                        if(response[i].senderMessage != null) {
                            conversation +='<div class="row message-body"><div class="col-sm-12 message-main-sender">';
                            conversation +='<div class="sender"><div class="message-text">' + response[i].senderMessage + '</div>';
                            conversation +='<span class="message-time pull-right">' + response[i].senderHeurePub + '</span>';
                            conversation +='</div></div></div>';
                        }
                    }
                    // Le bouton suivant
                    conversation +='<div class="row bouttonSuivant"><div class="col-sm-12 text-center">';
                    conversation +='<a href = "" title = "Show Next Messages" id="nextMessage" class="' + response[1].datePub + '">';
                    conversation +='<i class="fa fa-chevron-circle-down fa-2x" aria-hidden="true"></i></a></div></div>';
                }
                else if(responseStatus == 'postMessage')
                {
                    // Le nouveau message posté
                    var conversation = '<div class="row message-body"><div class="col-sm-12 message-main-sender">';
                    conversation += '<div class="sender"><div class="message-text">' + response[1].senderMessage + '</div>';
                    conversation += '<span class="message-time pull-right">' + response[1].senderHeurePub + '</span>';
                    conversation += '</div></div></div>';
                }
                else if(responseStatus == 'loadNewMessages')
                {
                    // On parcours la liste des messages reçus, si elle n'est pas vide
                    // Et si le pseudoReceveur de la liste == pseudo figurant sur l'element actif = on crée élement HTML
                    var conversation = null;
                    for(var i = 0, c = response[1].length; i < c; i++) {
                        if(response[1][i].receiverMessage != null && response[1][i].receiverPseudo == $('#receiverHeading').text()) {
                            conversation +='<div class="row message-body"><div class="col-sm-12 message-main-receiver">';
                            conversation +='<div class="receiver"><div class="message-text">' + response[1][i].receiverMessage + '</div>';
                            conversation +='<span class="message-time pull-right">' + response[1][i].receiverHeurePub + '</span>';
                            conversation +='</div></div></div>';
                        }
                    }
                }
                return conversation;
            },
            // SHOW or HIDE l'icone de nouveaux messages
            setNewMessageIcone: function(status, element, response = null) {
                if(status == 'ON') {
                    for(var i = 0, c = response.length; i < c; i++) {
                        if(response[1][i].receiverMessage != null && response[1][i].receiverPseudo == $(element).find('.name-meta').text()) {
                            $(element).find('.messageIcone').show();
                        }
                    }
                }
                else { $(element).find('.messageIcone').hide(); }
                return true;
            }
        },
        // Active les évènements
        setEvents: {
            contactSide: function() {
                // Au CLICK sur Contact - Envoie une requete pour récupérer la conversation à afficher dans chatRoom
                // Paramètres envoyés en requete : pseudo du contact + type de conversation (showConversation)
                $('.sideBar-body').click(function() {
                    manager.sendAjaxRequest('chatRoomSide',
                                            'GET',
                                            'chat/loadConversation/' + $(this).find('.name-meta').text() + '/showConversation',
                                             this);
                });
                // Au CLICK sur DELETE - Vide le ChatRoom si element actif (via class active) + supprime liste de BDD (via Ajax)
                // Paramètres envoys en GET : ARRAY liste des pseudos à supprimer where receiverPseudo = each pseudo
                $(".heading-compose").click(function() {
                    var deleteList = [];
                    $('input:checked').each(function() {
                        if($(this).hasClass('active')) { manager.settings.emptyChatRoom(); }
                        deleteList = $(this).val();
                    });
                    var dataToSend = {deleteList: $.serialize(deleteList)};
                    manager.sendAjaxRequest('contactSide', 'POST', 'chat/deleteConversation', null, dataToSend);
                });
                // Toutes les 30s on envoie une requete pour charger les nouveaux messages de tous les membres
                // Paramètres envoyés : Aucun
                setInterval("manager.sendAjaxRequest('chatRoomSide', 'GET', 'chat/loadNewMessages/', $('.sideBar-body').get());", 30000);
            },
            chatRoomSide: function() {
                // Au SCROLL - Affiche la date quand on scroll la zone de conversation
                $('#conversation').scroll(function() {
                    manager.settings.setChatRoomActive('scroll');
                });
                // Au CLICK sur Précédent - Envoie une requete pour récupérer la conversation à afficher dans chatRoom
                // Paramètres envoyés en requete : pseudo du contact + type de conversation (previousMessages) + currentDate
                $('#bouttonPrecedent').click(function() {
                    manager.sendAjaxRequest('chatRoomSide',
                                            'GET',
                                            'chat/loadConversation/' + $('#receiverHeading').text() + '/previousMessages/' + $('.message-date').text());
                });
                // Au CLICK sur Suivant - Envoie une requete pour récupérer la conversation à afficher dans chatRoom
                // Paramètres envoyés en requete : pseudo du contact + type de conversation (nextMessages) + currentDate
                $('#bouttonSuivant').click(function() {
                    manager.sendAjaxRequest('chatRoomSide',
                                            'GET',
                                            'chat/loadConversation/' + $('#receiverHeading').text() + '/nextMessages/' + $('.message-date').text());
                });
                // Au POST - Vérifie le formulaire et envoie une requete AJAX si OK
                // Paramètres envoyés : le message envoyé + le pseudo du receveur
                $('.reply-send').click(function() {
                    if(manager.settings.checkForm($('#senderMessage').get()[0])) {
                        var dataToSend = {senderMessage: $('#senderMessage').val(), receiverPseudo: $('#receiverHeading').text()};
                        manager.sendAjaxRequest('chatRoomSide', 'POST', 'chat/postNewMessage', null, dataToSend);
                    }
                });
            }
        },
        // Envoye des requetes AJAX GET/POST
        sendAjaxRequest: function(side, methodType, url, element = null, param = null) {
            $.ajax({
                method: methodType,
                url: 'http://homework:800/Projects/Chat/CodeIgniter/' + url,
                data: param,
                dataType: 'json',
                error: function(xhr) {
                    manager.settings.turnOverlay('OFF');
                    manager.displayResponse.error(xhr);
                },
                success: function(response) {
                    manager.settings.turnOverlay('OFF');
                    if     (side == 'contactSide')  manager.displayResponse.contactSide(response);
                    else if(side == 'chatRoomSide') manager.displayResponse.chatRoomSide(response, element);
                }
            });
        },
        // Affiche les réponses AJAX
        displayResponse: {
            error: function(xhr) {
                alert('Oups.. Une erreur est survenue - ' + xhr.statusText);
            },
            contactSide: function(response) {
                // Cas - Suppression de contact (conversation) - On affiche la confirmation de suppression
                if(response[0].status == 'suppression') {
                    alert('The selected messages have been deleted!');
                    return true;
                }
                return false;
            },
            chatRoomSide: function(response, element = null) {
                // Cas - Affichage de conversation (quand on click sur un contact)
                // On met à jour la photo et le pseudo du heading dans le chatRoom
                // S'il n'y a pas de messages envoyés et reçus on affiche la zone vide avec le message say hello!
                if(response[0].status == 'showConversation') {
                    $('#chatHeadingAvatar').attr('src', $(element).find('img').attr('src')); // mise à jour photo Chatromm
                    $('#receiverHeading').text($(element).find('.name-meta').text());        // mise à jour pseudo Chatromm
                    manager.settings.emptyChatRoom();

                    if(response[0].messagesList == 'empty') {
                        $('.message-date').text('Say Hello!');
                        return true;
                    }
                }
                // Cas - Affichage des messages précédents (quand on click sur Boutton précédent)
                // S'il n'y a pas de messages à afficher on supprime le boutton précédent
                else if(response[0].status == 'previousMessages') {
                    if(response[0].messagesList == 'empty') {
                        $('.bouttonPrecedent').remove();
                        alert('There are no more messages to display!');
                        return true;
                    }
                    // On vide le chatRoom et on met à jour la date
                    manager.settings.emptyChatRoom();
                    $('.message-date').text(response[1].datePub);
                }
                // Cas - Affichage des messages suivants (quand on click sur Boutton suivant)
                // S'il n'y a pas de messages à afficher on supprime le boutton suivant
                else if(response[0].status == 'nextMessages') {
                    if(response[0].messageList == 'empty') {
                        $('.bouttonSuivant').remove();
                        alert('There are no more messages to display!');
                        return true;
                    }
                    // On vide le chatRoom et on met à jour la date
                    manager.settings.emptyChatRoom();
                    $('.message-date').text(response[1].datePub);
                }
                // Cas - Ajout message conversation - POST via Formulaire
                else if(response[0].status == 'postMessage') {
                    var conversation = manager.settings.createHTMLElements(response, 'postMessage');
                    $(conversation).insertBefore($('#myAnchor'));
                    return true;
                }
                // Cas - Affichage des New Messages (Toutes les 20s)
                // S'il n'y a pas de nouveaux messages, on ne fait rien! - S'il y a des messages :
                    //=> Soit l'element est actif       => on affiche les messages dans chatRoom,
                    //=> Soit l'element n'est pas actif => on affiche l'icone de nouveaux messages
                else if(response[0].status == 'loadNewMessages') {
                    if(response[0].messagesList == 'empty') { return false; }
                    $(element).each(function()
                    {
                        if($(this).hasClass('active')) {
                            var conversation = manager.settings.createHTMLElements(response, 'loadNewMessages');
                            if(conversation != null)
                            {
                                // On affiche les new Messages et on send Ajax pour update messageStatus 'Read' (table messages)
                                // Paramètres envoyés : pseudo du contact qui a send le message (depuis left side) + messageStatus (oldPost)
                                $(conversation).insertBefore($('#myAnchor'));
                                manager.sendAjaxRequest('none', 'GET', 'chat/updateMessageStatus/' . $(this).find('.name-meta').text() . '/oldPost');
                            }
                        }
                        else { manager.settings.setNewMessageIcone('ON', this, response); }
                    });
                    return true;
                }
                // On recupère les elements HTML de la conversation et On insère la conversation avant l'ancre' FOCUS
                var conversation = manager.settings.createHTMLElements(response, 'current||previous||next-Timeline');
                $(conversation).insertBefore($('#myAnchor'));

                // Enfin Si le status = showConversation, on active l'element et le chatRoom +
                // Paramètres envoyés : $receiverPseudo, $conversationType, $conversationDate, $conversationTime
                if(response[0].status == 'showConversation') {
                    manager.settings.setElementActive(element);
                    manager.settings.setNewMessageIcone('OFF', element);
                    manager.settings.setChatRoomActive('click', response[1].datePub, element);
                }
                return true;
            }
        },
        // Initialise la fonction
        loadProcess: function() {
            manager.setEvents.contactSide();
            manager.setEvents.chatRoomSide();
        }
    }
    manager.loadProcess();




    // Test OVERLAY ---
    /*$(".heading-compose").click(function() {
        $('#myOverlay, #myGifLoad').show();
    });*/



/*
    $(".heading-compose").click(function() {
      // Code to suppress contact
  });*/

});


/*
if(response[0].status == 'loadNewMessages') {
var conversation = manager.settings.createHTMLElements(response, 'loadNewMessages', element);
if(conversation != null)
{
    if($(element).hasClass('active')) { $(conversation).insertBefore($('#myAnchor')); }
    else { manager.settings.setNewMessageIcone('ON', element); }
}
return true;
}


if(response[0].status == 'showConversation') {
    manager.settings.setElementActive(element);
    manager.settings.setChatRoomActive('click', response[1].datePub, element);
    clearID = setInterval("manager.sendAjaxRequest('chatRoomSide', 'GET', 'chat/loadNewMessages/' + $('#receiverHeading').text() + '/loadNewMessages/' + $('.message-date').text() + '/' + $('.receiver:last .message-time').text(), element);", 30000);
}
return true;



Quand User send message --->
        On affiche messageage ds Conversation
        On save message dans BDD - messages


        REQUESTS - CONVERSATION
            On charge d'abord les messages de la derniere date
                SELECT * FROM messages
                WHERE ladate = (SELECT MAX(ladate) FROM test) AND sender = pseudo AND receiver = pseudo
                ORDER BY time

            Ensuite au click du boutton precedent/suivant on charge les messages de date -1 ou date + 1
                SELECT * FROM test
                WHERE ladate = CAST(:ladate AS DATE) + 1 AND sender = pseudo AND receiver = pseudo
                ORDER BY time

        REQUESTS - CONTACT
        SELECT * FROM membres ORDER BY id
            (if userStatus == online => l'user est connecté    => on affiche le Membre + boutton vert
             if userStatus != online => l'user est déconnecté  => on affiche le Membre + boutton gris)

        NEW FONCTION .. ON New MESSAGE => icone nouveau Message
            - when message is sent => update membre set Status = "new"
            - on contact, if member->messageStatus = new => echo icone New Message
            - on contact, on click => open conversation and remove Icone New Message

            setInterval on charge toutes les 30s les messages de tout le monde,
                if element is active => displayResponse
                    => for each line in Response Array => getElementHTML
                else show icone new Message

                On click (show Conversation => remove icone message and send Ajax status read)
                On Post new Message => insert into database and set message notRead








        OBJET JSON - RESPONSE
            response.status
                - ajoutConversation
                - showConversation
                - previousMessages
                - nextMessages
                - suppression

            response.deleteList     | Suppression
                - #ahmed
                -#chou
                -#hope
            // Ensuite on ajoute les colonnes de la table
            response[i].sender    | Afficher message
            response[i].photoSender    | Afficher message
            response[i].senderMessage    | Afficher message
            etc.

            Exemple
            $var =  array result of select from data base

            ensuite on rajoute au début (array_unshift) la ligne suivante
                array_unshift($var, "{status: showMessage, messageList: 'empty'}");
                resultat: $var[0] = '{status: showMessage, messageList: 'empty'}'




        */
