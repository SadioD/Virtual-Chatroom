<div class="container app">
  <div class="row app-one">
    <div class="col-sm-4 side" id = "leftSide">
      <div class="side-one">
        <div class="row heading">
          <div class="col-sm-6 col-xs-6 heading-avatar">
            <div class="heading-avatar-icon">
              <img src="<?php echo base_url() . 'assets/images/' . $this->session->userdata('photo'); ?>">
              <span id = "sideBarUserName"><?php echo $this->session->userdata('userName'); ?> </span>
            </div>
          </div>
          <div class="col-sm-2 col-xs-2 heading-compose  pull-right">
            <i class="fa fa-trash fa-2x  pull-right" aria-hidden="true"></i>
          </div>
        </div>

        <div class="row searchBox">
          <div class="col-sm-12 searchBox-inner">
            <div class="form-group has-feedback">
              <input id="searchText" type="text" class="form-control" name="searchText" placeholder="Search people">
            </div>
          </div>
        </div>

        <!-- Liste Contact -->
        <div class="row sideBar">
            <form id = "deleteForm">
            <?php foreach($contactList as $contact) { ?>
                <div class="row sideBar-body">
                    <div class="col-sm-3 col-xs-3 sideBar-avatar">
                        <div class="avatar-icon">
                            <img src="<?php echo base_url() . 'assets/images/' . $contact->photo; ?>" />
                        </div>
                    </div>
                    <div class="col-sm-9 col-xs-9 sideBar-main">
                        <div class="row">
                            <div class="col-xs-8  sideBar-name">
                                <span class="name-meta"><?php $contact->pseudo ?></span><br/>
                                <?php if($this->session->isAuthentificated()) { ?>
                                    <span class="time-meta"><i class="fa fa-circle" aria-hidden="true"></i> online</span>
                                <?php } else { ?>
                                    <span class="time-meta"><i class="fa fa-circle-o" aria-hidden="true"></i> offline</span>
                                <?php } ?>
                                <?php if($contact->messageStatus == 'newPost' && $contact->sentTo == $this->session->userdata('userName')) { ?>
                                    <span class="time-meta messageIcone"><i class="fa fa-commenting" aria-hidden="true"></i> new</span>
                                <?php } ?>
                            </div>
                            <div class="col-xs-4  pull-right sideBar-time">
                                <span class = "openButton"><i class="fa fa-comments" aria-hidden="true"></i>|</span>
                                <input type="checkbox" name="" value="<?php $contact->pseudo ?>" class = "myCheckBox"/>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </form>
        </div>
      </div>
    </div>

    <!-- Right Side -->
    <div class="col-sm-8 col-xs-12 conversation" id = "rightSide">
      <div class="row heading">
        <!-- avatar du destinataire -->
        <div class="col-sm-1 col-xs-2 heading-avatar text-center">
          <div class="heading-avatar-icon">
            <img src="https://bootdey.com/img/Content/avatar/avatar6.png" id = "chatHeadingAvatar" />
          </div>
        </div>
        <div class="col-sm-11 col-xs-10 heading-name">
          <a class="heading-name-meta" id = "receiverHeading">John Doe</a>
          <i class="fa fa-arrow-left pull-right" aria-hidden="true" id = "backButton"></i>
        </div>
      </div>
      <!-- Zone de conversation -->
      <div class="row message" id ="conversation">
        <div class="row message-previous" id = "messagePrevious">
          <div class="col-sm-12 previous">
            <a class ="message-date">
                Posté le 12-02-2018
            </a>
          </div>
        </div>
        <!-- Chargement messages Précédents -->
        <div class="row bouttonPrecedent">
          <div class="col-sm-12 text-center">
            <a href = "" title = "Show Previous Messages" id = "bouttonPrecedent">
                <i class="fa fa-chevron-circle-up fa-2x" aria-hidden="true"></i>
            </a>
          </div>
        </div>
        <!-- Messages List -->
        <div class="row message-body">
          <div class="col-sm-12 message-main-receiver">
            <div class="receiver">
              <div class="message-text">
                  Hi, what are you doing?hihuihihhihihuihuftyftyddrtdrutfyfytftfyvy
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-sender">
            <div class="sender">
              <div class="message-text">
                I am doing nothing man!gvygvyuvytytvtyvtyvtyvtvtvtyvtyftyfyftyftftyftfu
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>







        <div class="row message-body">
          <div class="col-sm-12 message-main-receiver">
            <div class="receiver">
              <div class="message-text">
                  Hi, what are you doing?
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-sender">
            <div class="sender">
              <div class="message-text">
                I am doing nothing man!
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-receiver">
            <div class="receiver">
              <div class="message-text">
                  Hi, what are you doing?
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-sender">
            <div class="sender">
              <div class="message-text">
                I am doing nothing man!
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-receiver">
            <div class="receiver">
              <div class="message-text">
                  Hi, what are you doing?
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-sender">
            <div class="sender">
              <div class="message-text">
                I am doing nothing man!
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-receiver">
            <div class="receiver">
              <div class="message-text">
                  Hi, what are you doing?
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <div class="row message-body">
          <div class="col-sm-12 message-main-sender">
            <div class="sender">
              <div class="message-text">
                I am doing nothing man!
              </div>
              <span class="message-time pull-right">
                Sun
              </span>
            </div>
          </div>
        </div>
        <!-- Chargement messages Suivants -->
        <div class="row bouttonSuivant">
          <div class="col-sm-12 text-center">
            <a href = "" title = "Show Next Messages" id = "bouttonSuivant">
                <i class="fa fa-chevron-circle-down fa-2x" aria-hidden="true"></i>
            </a>
          </div>
        </div>

        <span id = "myAnchor" tabindex = -1></span>

























      </div>
      <!-- Formulaire envoi messaie -->
      <div class="row reply">
          <form id = "senderForm">
              <div class="col-sm-11 col-xs-11 reply-main">
                  <textarea class="form-control" rows="1" id = "senderMessage" disabled></textarea>
              </div>
              <div class="col-sm-1 col-xs-1 reply-send">
                  <i class="fa fa-send fa-2x" aria-hidden="true"></i>
              </div>
          </form>
      </div>
    </div>
  </div>
</div>

<!-- overlay -->
<div id = "myGifLoad">
    <h3>WAIT WHILE LOADING...</h3>
    <img src = "<?php echo base_url() . 'assets/images/loading.gif' ;?> " />
</div>
<div id = "myOverlay"></div>

<!-- surcouche destinée à masquer la zone de conversation lors du chargement de la page -->
<div id = "surcouche"></div>
