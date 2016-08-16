<?php require_once('templates/header.php'); ?>

    <div class="row">
        <div class="col-lg-12 text-center">
            <h1>Calcular estadísticas de usuarios</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?php
            require_once(dirname(__FILE__) . '/config.php');
            require(dirname(__FILE__) . '/includes/ProcessData.inc.php');

            $data = new ProcessData();

            $ok = $data->getUserStatistics($_POST['userids']);

            if($ok) {
                $data->getGlobalStatistics();
            }
            ?>
        </div>
    </div>

<?php require_once('templates/footer.php'); ?>