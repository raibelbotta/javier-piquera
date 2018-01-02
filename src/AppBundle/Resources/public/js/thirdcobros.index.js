App = typeof App !== 'undefined' ? App : {};
App.ThirdCobros = typeof App.ThirdCobros !== 'undefined' ? App.ThirdCobros : {};

+(App.ThirdCobros.Index = function($) {
    var $table = $('table#services');

    var initTable = function() {
        $table.on('preDraw.dt', function() {
            $(this).block({
                message: 'Cargando datos...'
            });
        }).on('draw.dt', function() {
            $(this).find('input:checkbox').iCheck({
                checkboxClass: 'icheckbox_flat-blue'
            });
            $(this).unblock();
        });

        $table.dataTable({
            aaSorting: [[2, 'asc']],
            ajax: {
                data: function(baseData) {
                    var fields = [];
                    $.each($('form#formFilter').serializeArray(), function(i, e) {
                        fields[e['name']] = e['value'];
                    });

                    return $.extend(true, baseData, fields);
                },
                error: function(xhr) {
                    alert('Error cargando datos' + "\n\n" + xhr.statusText);
                },
                method: 'GET',
                url : Routing.generate('app_thirdcobros_getdata')
            },
            columns: [
                {
                    searchable: false,
                    sortable: false,
                    width: '22px'
                },
                {
                    name: 'service',
                    title: 'Servicio'
                },
                {
                    name: 'startat',
                    title: 'Fecha'
                },
                {
                    name: 'provider',
                    title: 'Proveedor'
                },
                {
                    name: 'providerreference',
                    title: 'Referencia del proveedor',
                    sortable: false
                },
                {
                    name: 'customer',
                    title: 'Agencia'
                },
                {
                    name: 'customerreference',
                    title: 'Referencia de la agencia'
                },
                {
                    name: 'state',
                    title: 'Estado',
                    searchable: false,
                    sortable: false,
                    width: '40px'
                },
                {
                    name: 'charge',
                    title: 'Importe',
                    searchable: false,
                    sortable: false
                }
            ],
            processing: false,
            serverSide: true
        });
    }

    var initTools = function() {
        $('a#link-select-all').on('click', function(event) {
            event.preventDefault();

            $table.find('input:checkbox').iCheck('check');
        });
        $('a#link-select-none').on('click', function(event) {
            event.preventDefault();

            $table.find('input:checkbox').iCheck('uncheck');
        });

        $('#btnPrepareCobro').on('click', function() {
            var ids = $.map($table.find('input:checkbox:checked'), function(element) {
                return $(element).val();
            });

            if (ids.length === 0) {
                toastr.error('Seleccione servicios', 'Error', {
                    timeout: 5000
                });

                return true;
            }

            $.ajax({
                error: function(xhr) {
                    toastr.error('Error cargando datos', 'Error', {
                        timeout: 5000
                    });
                },
                method: 'GET',
                success: function(response) {
                    $.unblockUI();
                    $('div.modal#_dV').remove();

                    var modal = $(response).appendTo($('body')).attr('id', '_dV');
                    modal.on('hide.bs.modal', function() {
                        modal.find('form').remove();
                    });

                    App.Main.validate(modal.find('form'), {
                        submitHandler: function() {
                            modal.find('form').ajaxSubmit({
                                beforeSubmit: function() {
                                    modal.find('button[type=submit]').attr('disabled', 'disabled').text('Creando pago...');
                                },
                                target: modal.find('.modal-content')
                            });
                        }
                    });
                    modal.modal();
                },
                url: Routing.generate('app_thirdcobros_cobrar', {id: ids})
            })
        });
    }

    var initFilter = function() {
        $('form#formFilter').find('input:text[name$="[startAt][left_date]"], input:text[name$="[startAt][right_date]"]').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            clearBtn: true,
            language: 'es'
        });

        $('#collapseFilter').find('input, select').on('change', function() {
            $table.DataTable().draw();
        });
    }

    return {
        init: function() {
            initTable();
            initTools();
            initFilter();
        }
    }
}(jQuery));
