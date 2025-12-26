<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Agregar Incapacidad </b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="incapacidad_add.php">
                    <div class="form-group">
                        <label for="employee" class="col-sm-3 control-label">ID Empleado</label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="employee" name="employee" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="desde" class="col-sm-3 control-label">desde</label>

                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="desde" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="hasta" class="col-sm-3 control-label">hasta</label>

                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="hasta" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dias" class="col-sm-3 control-label">dias</label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="dias" required>
                        </div>
                    </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit -->
<div class="modal fade " id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b><span class="date"></span> Editar <span class="employee_name"></span></b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="incapacidad_edit.php">
                    <input type="hidden" class="caid" name="id">
                    <div class="form-group">
                        <label for="desde" class="col-sm-3 control-label">desde</label>

                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="desde" name="desde" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="hasta" class="col-sm-3 control-label">hasta</label>

                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="hasta" name="hasta" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dias" class="col-sm-3 control-label">dias</label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="dias" name="dias" required>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i> Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b><span class="date"></span></b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="incapacidad_delete.php">
                    <input type="hidden" class="caid" name="id">
                    <div class="text-center">
                        <p>Eliminar Incapacidad?</p>
                        <h2 class="employee_name bold"></h2>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
                <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>