<!DOCTYPE html>
<html lang="fr">
    <head>
        <title><?php echo $title; ?></title>
		<meta charset = "<?php echo $charset; ?>"/>
        <?php foreach($css as $url): ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $url; ?>" />
        <?php endforeach; ?>
    </head>
    <body>
        <div id="contenu">

            <?php echo $output; ?>

        </div>
        <?php foreach($js as $url): ?>
            <script type="text/javascript" src="<?php echo $url; ?>"></script>
        <?php endforeach; ?>
    </body>
</html>
