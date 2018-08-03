$(function() {
    // Ce code JS est venu avec le Formulaire --------------------------------------------------------------------------------------------
    $('.form').find('input, textarea').on('keyup blur focus', function (e) {

      var $this = $(this),
          label = $this.prev('label');

    	  if (e.type === 'keyup') {
    			if ($this.val() === '') {
              label.removeClass('active highlight');
            } else {
              label.addClass('active highlight');
            }
        } else if (e.type === 'blur') {
        	if( $this.val() === '' ) {
        		label.removeClass('active highlight');
    			} else {
    		    label.removeClass('highlight');
    			}
        } else if (e.type === 'focus') {

          if( $this.val() === '' ) {
        		label.removeClass('highlight');
    			}
          else if( $this.val() !== '' ) {
    		    label.addClass('highlight');
    			}
        }

    });

    $('.tab a').on('click', function (e) {

      e.preventDefault();

      $(this).parent().addClass('active');
      //$(this).parent().siblings().removeClass('active');
      $(this).parent().parent().css('background', 'rgb(0, 133, 161)');
      $(this).css('color', 'white');
      $(this).parent().parent().siblings().css('background', 'rgba(255, 255, 255, 0.6)');
      $(this).parent().parent().siblings().children().children().css('color', 'black');

      target = $(this).attr('href');

      $('.tab-content > div').not(target).hide();

      $(target).fadeIn(600);

    }); //----------------------------------------------------------------------------------------------------------------------------------

    //La partie ci-dessous gère les champs --------------------------------------------------------------------------------------------------
    (function() {
        var myRegex = /[a-z0-9]+/i,
            manager = {};

        manager = {
            settings: {
                // Valide les champs
                setValid: function(element) {
                    element.attr('class', 'form-control prenom is-valid');
                    element.next().next().next().html('');
                    return true;
                },
                // Invalide les champs
                setInvalid: function(element, message) {
                    element.attr({class: 'form-control prenom is-invalid'});
                    element.next().next().next().html(message);
                    return false;
                },
                // Vérifie que le champ prenom ets valide (type de caractère et existence du pseudo)
                checkString: function(reqType, methodType, url, element = null) {
                    if(myRegex.test(element.val())) {
                        return manager.request(reqType, methodType, url, element);
                    }
                    return manager.settings.setInvalid(element, 'The "Pseudo" field is not valid!');
                },
                // Vérifie que le fichier joint respecte la norme (taille/format)
                checkFile: function(photo) {
                    allowedTypes = ['png', 'jpg'],
                    uploadedFile = photo.files[0].name.split('.');
                    uploadedFile = uploadedFile[uploadedFile.length - 1].toLowerCase();

                    if(photo.files[0].size >= 250000) {
                        $('#fileError').attr('class', 'formError inValid').text('Failed to upload - The file is too large!');
                        return false
                    }
                    else if(allowedTypes.indexOf(uploadedFile) == -1) {
                        $('#fileError').attr('class', 'formError inValid').text('Failed to upload - not a valid image!');
                        return false
                    }
                    $('#fileError').attr('class', 'formError valid').text('');
                    return true;
                }
            },
            setEvents: {
                beforeSubmit: function() {
                    // Vérification Registration
                    $('#prenomRegister').on('blur', function() {
                        manager.settings.checkString('pseudoRegistration', 'GET', 'user/dataProcess/pseudoRegistration/' + $(this).val(), $(this));
                    });
                    // Vérification Connexion
                    $('#prenomAuth').on('blur', function() {
                        manager.settings.checkString('authentification', 'GET', 'user/dataProcess/authentification/' + $(this).val(), $(this));
                    });
                    // Vérification du fichier joint
                    $('.photo').on('change', function() {
                        manager.settings.checkFile(this);
                    });
                },
                onSubmit: function() {
                    // Vérification à l'envoi du formulaire, si le pseudo et la photo sont valides
                    $('#signUpForm').submit(function(e) {
                        if($('#prenomRegister').hasClass('is-invalid') || $('#fileError').hasClass('inValid')) {
                            e.preventDefault();
                            return false;
                        }
                    });
                    // Vérification à l'envoi du formulaire, si le pseudo existe en BDD
                    $('#logInForm').submit(function(e) {
                        if($('#prenomAuth').hasClass('is-invalid')) {
                            e.preventDefault();
                            return false;
                        }
                    });
                }
            },
            request: function(reqType, methodType, url, element = null, param = null) {
                $.ajax({
                    method: methodType,
                    url: 'http://homework:800/Projects/Chatroom/CodeIgniter/' + url,
                    data: param,
                    dataType: 'json',
                    error: function(xhr) {
                        alert('Oups... une erreur s\'est produite - ' + xhr.statusText);
                    },
                    success: function(response) {
                        if(    reqType == 'pseudoRegistration') {
                            if(response.status == true) { return manager.settings.setValid(element); }
                            else                          { return manager.settings.setInvalid(element, 'The entered pseudo already exists, please choose another one!'); }
                        }
                        else if(reqType == 'authentification')  {
                            if(response.status == true) { return manager.settings.setValid(element); }
                            else                          { return manager.settings.setInvalid(element, 'The entered pseudo does not exist!'); }
                        }
                    }
                });
            },
            displayResponse: function(response, element) {
                // Cas de l'upload de la photo avatar
                // Si le fichier est upload on met class valid et on renvoie true
                // Si le fichier !(taille/format) on met la class invalid et on affiche le message d'erreur
                if($(element).hasClass('photo')) {
                    if(response.status == 'success') {
                        $(element).attr('class', 'photo valid');
                        return true;
                    }
                    $(element).attr('class', 'photo invalid');
                    $('#fileError').text(response.value);
                }
            },
            loadProcess: function() {
                manager.setEvents.beforeSubmit();
                manager.setEvents.onSubmit();
            }
        }
        manager.loadProcess();
    })();//---------------------------------------------------------------------------------------------------------------------------------------
});
