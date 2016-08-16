<?php require_once('templates/header.php'); ?>

    <div class="row">
        <div class="col-lg-12 text-center">
            <h1>Calcular <i>learning paths</i></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?php
            if(!empty($_POST['userids'])) {
                ini_set('memory_limit', '1024M');

                require_once(dirname(__FILE__) . '/config.php');
                require(dirname(__FILE__) . '/includes/ProcessData.inc.php');

                $users = trim($_POST['userids']);
                $ids = explode("\n", $users);
                $ids = array_map('trim', $ids);

                $data = new ProcessData();
                $graph = $data->buildLearningPaths($ids, false);
                $file = $data->buildCSV($graph);

                echo '<p class="lead">DONE!!!</p>';
                echo '<p class="lead">Download CSV <a href="http://'.$_SERVER['SERVER_NAME'].'/'.DATA_CSV.$file.'" >here</a></p>';
            } else {
                echo '<p class="lead">Debes indicar al menos un identificador de usuario que exista en la base de datos</p>';
            }
            ?>
        </div>
    </div>

<?php require_once('templates/footer.php'); ?>