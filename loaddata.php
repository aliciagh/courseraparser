<?php require_once('templates/header.php'); ?>

<?php

/** Define ABSPATH as this file's directory */
define( 'ABSPATH', dirname(__FILE__) . '/' );

/** Uncomment the following lines to start loading data */
/*require_once(ABSPATH.'config.php');
require(ABSPATH.'includes/ProcessJson.inc.php');

$process = new ProcessJson (ABSPATH.DATA_DIR.DATA_FILE);

echo 'START...<br /><br />';

$process->loadData(true);

echo 'DONE!!!!';
*/

/** Comment the following HTML to start loading data */
?>

<div class="row">
    <div class="col-lg-12 text-center">
        <h1>Ya se cargaron los datos</h1>
    </div>
</div>
<!-- /.row -->

<?php require_once('templates/footer.php'); ?>


