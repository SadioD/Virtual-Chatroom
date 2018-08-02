
<div class = "row">
    <div class = "offset-1 col-11 offset-sm-1 col-sm-9 text-center">
        <p id = "errorMessage"></p>

        <!-- Alerte Désactivation Javascript -->
        <noscript>
            <span style = "color:red;">
                Oups... Il semblerait que Javascript ne soit pas activé sur votre navigateur.
                Certaines fonnalités de ce site ne seront pas accessibles.
            </span>
        </noscript>
    </div>
</div>

<!-- Formulaire de Connexion/Inscription -->
<div id = "myBlock">
    <div class = "row formRow">
        <div class="form col-lg-8 col-md-10 mx-auto connectForm">
            <div class="tab-group row" tabindex = -1 id = "shiftLinks">
                <div class = "offset-1 col-5 connectLink activated">
                    <span class="tab active">
                        <a href="#signup">Sign Up</a>
                    </span>
                </div>
                <div class = "col-5 connectLink">
                    <span class="tab">
                        <a href="#login">Log In</a>
                    </span>
                </div>
            </div>
            <div class="tab-content">
                <!-- The SignUp Form -->
                <div id="signup">
                    <h1>Sign Up for Free</h1>
                    <form action="" method="post" id = "signUpForm">
                        <div class="top-row">
                            <div class="field-wrap">
                                <label class = "label">
                                    Your Pseudo<span class="req">*</span>
                                </label>
                                <input type="text" name = "prenom" id = "prenomRegister" class = "form-control prenom"  required /><br/><br/>
                                <span id = "prenomError" class = "formError"><?php echo form_error('pseudo'); ?></span>
                            </div>
                            <div class="field-wrap" style = "margin-bottom: 15%;">
                                <label>
                                    You are <span class="req">*</span>
                                </label>
                                <span id = "sex">
                                    <input type="radio" name = "sex" id = "male" value="M" class = "sex" /> <label for = "male">M</label><br/>
                                    <input type="radio" name = "sex" id = "female" value="F" class = "sex" checked /> <label for = "female">F</label>
                                </span>
                            </div>
                            <div class="field-wrap">
                                <label>
                                    Your Avatar
                                </label>
                                <input type="file" name = "photo" class = "photo" /><br/>
                                <span id = "fileError" class = "formError"><?php echo form_error('nom'); ?></span>
                            </div>
                        </div>
                        <button type="submit" class="button button-block" style = "border-radius: 4px;">Get Started</button>
                    </form>
                </div>
                <!-- The logIn Form -->
                <div id="login">
                    <h1>Welcome Back!</h1>
                    <form action="" method="post" id = "logInForm">
                        <div class="top-row">
                            <div class="field-wrap">
                                <label class = "label">
                                    Your Pseudo<span class="req">*</span>
                                </label>
                                <input type="text" name = "prenom" id = "prenomAuth" class = "form-control prenom"  required /><br/><br/>
                                <span id = "connexionError" class = "formError"><?php echo form_error('pseudo'); ?></span>
                            </div>
                        </div>
                        <button class="button button-block" style = "border-radius: 4px;"/>Log In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
