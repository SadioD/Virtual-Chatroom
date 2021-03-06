// Avant que le DOM ne soit chargé on cache le back button + l'icone des nouveaux messages (contact list) + la photo Avatar zone de chat
$('#backButton, #chatHeadingAvatar, .messageIcone').hide();

// Ensuite on exécute le reste du code quand le DOM sera chargé
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
                    $('#myAnchor').focus();
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
                var myRegex  = /[a-z0-9?/\\.]+/i;
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
                    conversation +='<a href="" title="Show Previous Messages" id="previousMessage">';
                    conversation +='<i class="fa fa-chevron-circle-up fa-2x" aria-hidden="true"></i></a></div></div>';

                    // Les messages
                    for(var i = 0, c = response[1].length; i < c; i++)
                    {
                        // Cas des messages où sender = session(userName) => received by $contact
                        if(response[1][i].senderMessage != null && response[1][i].sender != $('#receiverHeading').text()) {
                            conversation +='<div class="row message-body"><div class="col-sm-12 message-main-sender">';
                            conversation +='<div class="sender"><div class="message-text">' + response[1][i].senderMessage + '</div>';
                            conversation +='<span class="message-time pull-right">' + response[1][i].senderHeurePub.slice(0, -3) + '</span>';
                            conversation +='</div></div></div>';
                        }
                        // Cas des messages où sender = $contact => received by session(userName)
                        if(response[1][i].senderMessage != null && response[1][i].sender == $('#receiverHeading').text()) {
                            conversation +='<div class="row message-body"><div class="col-sm-12 message-main-receiver">';
                            conversation +='<div class="receiver"><div class="message-text">' + response[1][i].senderMessage + '</div>';
                            conversation +='<span class="message-time pull-right">' + response[1][i].senderHeurePub.slice(0, -3) + '</span>';
                            conversation +='</div></div></div>';
                        }
                    }
                    // Le bouton suivant
                    conversation +='<div class="row bouttonSuivant"><div class="col-sm-12 text-center">';
                    conversation +='<a href = "" title = "Show Next Messages" id="nextMessage" class="' + response[2].datePub + '">';
                    conversation +='<i class="fa fa-chevron-circle-down fa-2x" aria-hidden="true"></i></a></div></div>';
                }
                else if(responseStatus == 'postMessage')
                {
                    // Le nouveau message posté
                    var conversation = '<div class="row message-body"><div class="col-sm-12 message-main-sender">';
                    conversation += '<div class="sender"><div class="message-text">' + response[1].senderMessage + '</div>';
                    conversation += '<span class="message-time pull-right">' + response[1].senderHeurePub.slice(0, -3) + '</span>';
                    conversation += '</div></div></div>';
                }
                else if(responseStatus == 'loadNewMessages')
                {
                    // On parcours la liste des messages reçus, si elle n'est pas vide
                    // Et si le pseudoReceveur de la liste == pseudo figurant sur l'element actif = on crée élement HTML
                    var conversation = '';
                    for(var i = 0, c = response[1].length; i < c; i++) {
                        if(response[1][i].senderMessage != null && response[1][i].sender == $('#receiverHeading').text()) {
                            conversation +='<div class="row message-body"><div class="col-sm-12 message-main-receiver">';
                            conversation +='<div class="receiver"><div class="message-text">' + response[1][i].senderMessage + '</div>';
                            conversation +='<span class="message-time pull-right">' + response[1][i].senderHeurePub.slice(0, -3) + '</span>';
                            conversation +='</div></div></div>';
                        }
                    }
                }
                return conversation;
            },
            // SHOW or HIDE l'icone de nouveaux messages
            setNewMessageIcone: function(status, element, response = null) {
                if(status == 'ON') {
                    for(var i = 0, c = response[1].length; i < c; i++) {
                        if(response[1][i].senderMessage != null && response[1][i].sender == $(element).find('.name-meta').text()) {
                            $(element).find('.messageIcone').show();
                        }
                    }
                }
                else { $(element).find('.messageIcone').hide(); }
                return true;
            },
            // Affiche ou HIDE l'icone de connexion en fonction du statut de connexion
            // Si element = photoAvater session(userName) & connexionStatus == 'online' => one ne fait rien
            setConnexionIcone: function(contact, element) {
                if(contact.connexionStatus == 'online') {
                    if($(element).attr('id') == 'connexionStatus') { return true; }
                    $(element).attr('class', 'fa fa-circle');
                    $(element).parent().html($(element)[0]).append(' online');
                }
                else {
                    $(element).attr('class', 'fa fa-circle-o');
                    $(element).parent().html($(element)[0]).append(' offline');
                }
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
                // Paramètres envoyés en GET : ARRAY liste des pseudos à supprimer where receiverPseudo = each pseudo
                $(".heading-compose").click(function() {
                    var deleteList = [];
                    $('input:checked').each(function(i) {
                        if($(this).hasClass('active')) { manager.settings.emptyChatRoom(); }
                        deleteList[i] = $(this).val();
                    });
                    var dataToSend = { contactList: deleteList };
                    manager.sendAjaxRequest('contactSide', 'POST', 'chat/deleteConversation', null, dataToSend);
                });
                // AutoCompletion - On KeyUp, on send une requete Ajax pour recupérer la liste des membres
                // Paramètres envoyés en GET : chacune des lettres entrées
                $('#searchText').on('keyup', function() {
                    manager.sendAjaxRequest('contactSide', 'GET', 'chat/contactResearch');
                });
                // Toutes les 30s on envoie une requete pour charger les nouveaux messages de tous les membres
                // Paramètres envoyés : requestStatus(loadNewMessage)
                setInterval(function() {
                    manager.sendAjaxRequest('chatRoomSide', 'GET', 'chat/ajaxAutomaticRequests/loadNewMessages', $('.sideBar-body').get());
                }, 30000);
                // Toutes les 30s on envoie une requete pour vérifier si l'état de connexion des membres
                // Paramètres envoyés : requestStatus(checkOnlineStatus)
                setInterval(function() {
                    manager.sendAjaxRequest('contactSide', 'GET', 'chat/ajaxAutomaticRequests/checkOnlineStatus');
                }, 30000);
            },
            chatRoomSide: function(param = null) {
                // Au SCROLL - Affiche la date quand on scroll la zone de conversation
                $('#conversation').scroll(function() {
                    manager.settings.setChatRoomActive('scroll');
                });
                // Au CLICK sur Précédent - Désactivation form + send ajax pour récupérer la conversation à afficher dans chatRoom
                // Paramètres envoyés en requete : pseudo du contact + type de conversation (previousMessages) + currentDate
                $('.bouttonPrecedent').on('click', function(e) {
                    e.preventDefault();
                    if(param != null) {
                        alert('Your are now reviewing previous messages. To post a new message, you need first to load the latest messages (by clicking either "next Messages" button or "contact list" button)');
                    }
                    $('#senderMessage').attr('disabled', true);
                    manager.sendAjaxRequest('chatRoomSide',
                                            'GET',
                                            'chat/loadConversation/' + $('#receiverHeading').text() + '/previousMessages/' + $('.message-date').text());
                });
                // Au CLICK sur Suivant - Envoie une requete pour récupérer la conversation à afficher dans chatRoom
                // Paramètres envoyés en requete : pseudo du contact + type de conversation (nextMessages) + currentDate
                $('.bouttonSuivant').on('click', function(e) {
                    e.preventDefault();
                    manager.sendAjaxRequest('chatRoomSide',
                                            'GET',
                                            'chat/loadConversation/' + $('#receiverHeading').text() + '/nextMessages/' + $('.message-date').text());
                });
                // Au POST - Vérifie le formulaire et envoie une requete AJAX si OK
                // Paramètres envoyés : le message envoyé + le pseudo du receveur
                $('.reply-send').on('click', function() {
                    if(manager.settings.checkForm($('#senderMessage').get()[0])) {
                        var dataToSend = { senderMessage: $('#senderMessage').val(), receiverPseudo: $('#receiverHeading').text() };
                        manager.sendAjaxRequest('chatRoomSide', 'POST', 'chat/postNewMessage', null, dataToSend);
                    }
                });
            }
        },
        // Envoye des requetes AJAX GET/POST
        sendAjaxRequest: function(side, methodType, url, element = null, param = null) {
            $.ajax({
                method: methodType,
                url: 'http://homework:800/Projects/Chatroom/CodeIgniter/' + url,
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
                // Cas - Suppression de contact (conversation) - On affiche la confirmation de suppression +
                // on discheck les cases cochées et on vide la zone de chat.
                if(response[0].status == 'suppression') {
                    $('input:checkbox').each(function() {
                        $(this).prop('checked', false);     // equivalent de $(this).attr('checked', false);
                    });
                    manager.settings.emptyChatRoom();
                    alert('The selected messages have been deleted!');
                    return true;
                }
                // Cas - Vérification status de connexion des membres
                else if(response[0].status == 'checkOnlineStatus') {
                    // On met à jour le statut de connexion des Contacts - LeftSide
                    // On parcourt la liste des contacts (leftside) pour le faire
                    $('.name-meta').each(function() {
                        var contact = this;
                        for(var i = 0, c = response[1].length; i < c; i++) {
                            if($(contact).text() == response[1][i].pseudo) {
                                manager.settings.setConnexionIcone(response[1][i], $(contact).next().next().children('i').get()[0]);
                            }
                        }
                    });
                    // On met à jour le statut de connexion de session(userName) - Zone ChatRoom
                    // On parcourt la liste des contacts (reponse) pour le faire + Si session --> offline on désactive le form POST
                    for(var i = 0, c = response[1].length; i < c; i++) {
                        if($('#sideBarUserName').text().slice(1) == response[1][i].pseudo) {
                            manager.settings.setConnexionIcone(response[1][i], $('#connexionStatus').get()[0]);
                            if(response[1][i].connexionStatus == 'offline') $('#senderMessage').attr('disabled', true);
                        }
                    }
                    return true;
                }
                // Cas - Autocompletion, affichage des membres (zone de recherche)
                // On affiche l'autocompletion + send Ajax pour (activer le membre choisi et afficher la conversation)
                else if(response[0].status == 'autoCompletion') {
                    $('#searchText').autocomplete({
                        source : response[0].contactList
                    });
                    $('.sideBar-body').each(function() {
                        if($(this).find('.name-meta').text() == $('#searchText').val()) {
                            manager.sendAjaxRequest('chatRoomSide',
                                                    'GET',
                                                    'chat/loadConversation/' + $(this).find('.name-meta').text() + '/showConversation',
                                                     this);
                        }
                    });
                }
                return false;
            },
            chatRoomSide: function(response, element = null) {
                // Cas - Affichage de conversation (quand on click sur un contact)
                // On met à jour la photo et le pseudo du heading dans le chatRoom
                // S'il n'y a pas de messages envoyés et reçus on affiche la zone vide avec le message say hello!
                if(response[0].status == 'showConversation') {
                    $('#chatHeadingAvatar').attr('src', $(element).find('img').attr('src')).show(); // mise à jour photo Chatromm
                    $('#receiverHeading').text($(element).find('.name-meta').text());        // mise à jour pseudo Chatromm
                    manager.settings.setElementActive(element);
                    manager.settings.emptyChatRoom();

                    if(response[0].messagesList == 'empty') {
                        $('.message-date').text('Say Hello!');
                        manager.settings.setChatRoomActive('click');
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
                    $('.message-date').text(response[2].datePub);
                }
                // Cas - Affichage des messages suivants (quand on click sur Boutton suivant)
                // S'il n'y a pas de messages à afficher on supprime le boutton suivant
                else if(response[0].status == 'nextMessages') {
                    if(response[0].messagesList == 'empty') {
                        $('.bouttonSuivant').remove();
                        alert('There are no more messages to display!');
                        manager.settings.setChatRoomActive('click');
                        return true;
                    }
                    // On vide le chatRoom et on met à jour la date
                    manager.settings.emptyChatRoom();
                    $('.message-date').text(response[2].datePub);
                }
                // Cas - Ajout message conversation - POST via Formulaire
                // On vide la zone de texte du formulaire et on affiche le message envoyé
                else if(response[0].status == 'postMessage') {
                    var conversation = manager.settings.createHTMLElements(response, 'postMessage');
                    $('#senderMessage').val('');
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
                            if(conversation != '')
                            {
                                // On affiche les new Messages et on send Ajax pour update messageStatus 'oldPost' (table messages)
                                // Paramètres envoyés : pseudo du contact qui a send le message (depuis left side) + messageStatus (oldPost)
                                $(conversation).insertBefore($('#myAnchor'));
                                manager.sendAjaxRequest('none', 'GET', 'chat/updateMessageStatus/' + $(this).find('.name-meta').text() + '/oldPost/loadNewmessage');
                            }
                        }
                        else { manager.settings.setNewMessageIcone('ON', this, response); }
                    });
                    return true;
                }
                // On recupère les elements HTML de la conversation et On insère la conversation avant l'ancre' FOCUS
                // Ensuite on désactive les anciens évènement clicks  et on active les nouveaux bouttons precédent et suivant + show la date
                var conversation = manager.settings.createHTMLElements(response, 'current||previous||next-Timeline');
                $(conversation).insertBefore($('#myAnchor'));
                $('.reply-send, .bouttonSuivant, .bouttonPrecedent').off('click');
                response[0].status == 'showConversation' ? manager.setEvents.chatRoomSide('first') : manager.setEvents.chatRoomSide();
                manager.settings.setChatRoomActive('displayDate');

                // Enfin Si le status = showConversation, on active l'element et le chatRoom +
                // Paramètres envoyés : $receiverPseudo, $conversationType, $conversationDate, $conversationTime
                if(response[0].status == 'showConversation') {
                    manager.settings.setNewMessageIcone('OFF', element);
                    manager.settings.setChatRoomActive('click', response[2].datePub);
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
});
