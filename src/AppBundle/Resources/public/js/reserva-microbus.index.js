App = typeof App !== 'undefined' ? App : {};
App.ReservasMicrobus = typeof App.ReservasMicrobus !== 'undefined' ? App.ReservasMicrobus : {};

+(App.ReservasMicrobus.Index = function() {
    var $table = $('#dataTables-reservas');

    var initTable = function() {
        $table.on('preDraw.dt', function() {
            $(this).block({
                message: 'Cargando datos...'
            });
        }).on('draw.dt', function() {
            $(this).unblock();
            $(this).find('input:checkbox').iCheck({
                checkboxClass: 'icheckbox_flat-blue'
            });
            $(this).find('tbody [title]').tooltip({'trigger': 'hover'});
        });

        $table.dataTable({
            ajax: {
                data: function(baseData) {
                    var filter = [];
                    $.each($('form#formFilter').serializeArray(), function(i, e) {
                        filter[e['name']] = e['value'];
                    });

                    return $.extend(true, baseData, filter);
                },
                error: function() {
                    alert('Error obteniendo datos de listado')
                },
                method: 'GET',
                url: Routing.generate('app_reservasmicrobus_getdata')
            },
            columns: [
                {
                    sortable: false,
                    searchable: false,
                    width: '24px'
                },
                {
                    name: 'state',
                    title: 'Estado',
                    searchable: false,
                    sortable: false
                },
                {
                    name: 'startat',
                    title: 'Fecha'
                },
                {
                    name: 'service',
                    title: 'Servicio'
                },
                {
                    name: 'client',
                    title: 'Cliente'
                },
                {
                    name: 'clientSerial',
                    title: 'Referencia del cliente'
                },
                {
                    name: 'provider',
                    title: 'Proveedor'
                },
                {
                    name: 'providerSerial',
                    title: 'Referencia del proveedor'
                },
                {
                    name: 'clientNames',
                    title: 'Nombres'
                },
                {
                    name: 'pax',
                    title: 'Pax'
                },
                {
                    searchable: false,
                    sortable: false,
                    width: '130px'
                }
            ],
            searching: false,
            serverSide: true,
            sorting: [[2, 'asc']]
        });
    }

    var initActions = function() {
        $table.on('click', '.btn-delete', function(event) {
            event.preventDefault();

            if (confirm('¿Seguro desea borrar esta reserva?')) {
                var url = $(this).attr('href');
                $(this).closest('td').text('Eliminando...')
                $.ajax({
                    method: 'POST',
                    success: function(json) {
                        $table.DataTable().draw(true);
                    },
                    dataType: 'json',
                    url: url
                });
            }
        });

        $table.on('click', '.btn-execute', function(event) {
            event.preventDefault();

            $('#_m').remove();

            var modal = $('<div id="_m" class="modal fade" data-backdrop="static"><div class="modal-dialog"><div class="modal-content"/></div></div>');
            modal.modal();
            modal.find('.modal-content').load($(this).attr('href'), function() {
                modal.find('form').ajaxForm({
                    beforePost: function() {
                        modal.find('button:submit').attr('disabled', 'disabled');
                    },
                    dataType: 'json',
                    success: function(json) {
                        modal.modal('hide');
                        $table.DataTable().draw(true);
                    }
                });
            });
        });

        $table.on('click', '.btn-change-state', function(event) {
            event.preventDefault();

            $('#_m').remove();

            var modal = $('<div id="_m" class="modal fade" data-backdrop="static"><div class="modal-dialog"><div class="modal-content"/></div></div>');
            modal.modal();
            modal.find('.modal-content').load($(this).attr('href'), function() {
                modal.find('form').ajaxForm({
                    beforePost: function() {
                        modal.find('button:submit').attr('disabled', 'disabled');
                    },
                    dataType: 'json',
                    success: function(json) {
                        modal.modal('hide');
                        $table.DataTable().draw(true);
                    }
                });
            });
        });
    }

    var initTools = function() {
        $('#link-select-all').on('click', function(event) {
            event.preventDefault();

            $table.find('input:checkbox').iCheck('check');
        });

        $('#link-select-none').on('click', function(event) {
            event.preventDefault();

            $table.find('input:checkbox').iCheck('uncheck');
        });
    }

    var initFilter = function() {
        $('#formFilter input:text.datepicker').on('change', function() {
            $table.DataTable().draw();
        }).datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            closeBtn: true,
            language: 'es'
        });

        $('#formFilter input:text:not(.datepicker)').on('keyup', function() {
            $table.DataTable().draw();
        });
    }

    var showNotices = function(notices) {
        if (notices.length > 0) {
            swal({
                'title': 'Notificaciones',
                'text': notices[0].replace(/\\n/g, "\n")
            });
        }
    }

    return {
        init: function(notices) {
            initTable();
            initActions();
            initTools();
            initFilter();
            showNotices(notices);
        }
    }
}(jQuery));
