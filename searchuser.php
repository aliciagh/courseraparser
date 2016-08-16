<?php require_once('templates/header.php'); ?>

<div class="row">
    <div class="col-lg-12 text-center">
        <h1>Buscar rutas usuario: <?php echo $_POST['userid']; ?></h1>
    </div>
</div>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">

        <!-- use the filter_reset : '.reset' option or include data-filter="" using the filter button demo code to reset the filters -->
        <div class="bootstrap_buttons">
            Reset filter : <button type="button" class="reset btn btn-primary" data-column="0" data-filter=""><i class="icon-white icon-refresh glyphicon glyphicon-refresh"></i> Reset filters</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <table id="paths" class="tablesorter">
            <thead>
            <tr>
                <th>Fecha desde</th>
                <th>Desde</th>
                <th>Fecha actual</th>
                <th>Actual</th>
                <th>Tipo</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>Fecha desde</th>
                <th>Desde</th>
                <th>Fecha actual</th>
                <th>Actual</th>
                <th>Tipo</th>
            </tr>
            <tr>
                <th colspan="5" class="ts-pager form-horizontal">
                    <button type="button" class="btn first"><i class="icon-step-backward glyphicon glyphicon-step-backward"></i></button>
                    <button type="button" class="btn prev"><i class="icon-arrow-left glyphicon glyphicon-backward"></i></button>
                    <span class="pagedisplay"></span> <!-- this can be any element, including an input -->
                    <button type="button" class="btn next"><i class="icon-arrow-right glyphicon glyphicon-forward"></i></button>
                    <button type="button" class="btn last"><i class="icon-step-forward glyphicon glyphicon-step-forward"></i></button>
                    <select class="pagesize input-mini" title="Select page size">
                        <option selected="selected" value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="40">40</option>
                    </select>
                    <select class="pagenum input-mini" title="Select page number"></select>
                </th>
            </tr>
            </tfoot>
            <tbody>
            <?php
            require_once(dirname(__FILE__) . '/config.php');
            require(dirname(__FILE__) . '/includes/ProcessData.inc.php');

            $data = new ProcessData();

            $data->searchData(array('preline' => '<tr>', 'postline' => '</tr>', 'pre' => '<td>', 'post' => '</td>'), $_POST['userid']);

            ?>
            </tbody>
        </table>

        <!-- JQuery tablesorter -->
        <script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
        <script src="js/jquery.tablesorter.widgets.js"></script>

        <!-- pager plugin -->
        <script src="js/jquery.tablesorter.pager.min.js"></script>

        <script src="js/usertable.js"></script>
    </div>
</div>

<?php require_once('templates/footer.php'); ?>
