<!DOCTYPE html>
<html lang="fr">
    <head>
        <title><?php echo $title; ?></title>
	 <meta charset = "utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
	  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <?php foreach($css as $url): ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $url; ?>" />
        <?php endforeach; ?>
    </head>
    <body>
        <div id="contenu">

            <?php echo $output; ?>

        </div>
	 <hr>
        <!-- Footer -->
        <footer>
          <div class="container">
            <div class="row">
              <div class="col-lg-8 col-md-10 mx-auto">

			<!-- contenu du footer dans footer.js -->
                <p class="copyright text-muted"></p>
              </div>
            </div>
          </div>
        </footer>


	<!-- JS For Jquery -->
	 <script src="<?php echo base_url() . 'assets/'; ?>vendor/jquery/jquery.min.js"></script>
        <?php foreach($js as $url): ?>
            <script type="text/javascript" src="<?php echo $url; ?>"></script>
        <?php endforeach; ?>
    </body>
</html>
