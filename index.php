<?php require_once('templates/header.php'); ?>

        <div class="row">
            <div class="col-lg-12 text-center">
                <h1>Coursera Parser</h1>
                <p class="lead">Análisis del registro de clics de un curso de Coursera</p>
                <ul class="list-unstyled">
                    <li>v 1.0</li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h1>Buscar rutas usuario</h1>

                <form action="searchuser.php" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="searchuser" class="control-label col-sm-2">Session user ID:</label>
                        <div class="col-sm-10">
                            <input id="searchuser" class="form-control" type="text" name="userid" placeholder="Indica el identificador anonimizado del usuario"><br>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button class="btn btn-default" type="submit">Buscar</button>
                        </div>
                    </div>
                </form>

                <h1>Calcular estadísticas de usuarios</h1>

                <form action="statistics.php" method="post" role="form">
                    <div class="form-group">
                        <label for="userslist">Listado de session user IDs:</label>
                        <textarea id="userslist" class="form-control" name="userids" rows="10" placeholder="Escribe cada session user ID en una línea."></textarea>
                    </div>
                    <button class="btn btn-default" type="submit">Calcular</button>
                </form>

                <h1>Calcular <i>learning paths</i></h1>

                <form action="learningpaths.php" method="post" role="form">
                    <div class="form-group">
                        <label for="userspaths">Listado de session user IDs:</label>
                        <textarea id="userspaths" class="form-control" name="userids" rows="10" placeholder="Escribe cada session user ID en una línea."></textarea>
                    </div>
                    <button class="btn btn-default" type="submit">Calcular</button>
                </form>
            </div>
        </div>
        <!-- /.row -->

<?php require_once('templates/footer.php'); ?>
