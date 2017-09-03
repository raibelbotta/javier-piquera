App = typeof App !== 'undefined' ? App : {};
App.Invoices = typeof App.Invoices !== 'undefined' ? App.Invoices : {};

+(App.Invoices.Form = function($) {
    var initValidator = function() {
        $.validator.addMethod('norepeatedservices', function(value, element, param) {
            var ids = [], error = false;
            $('#invoice_form_lines select[name$="[service]"]').each(function() {
                var id = $(this).val();
                if (id) {
                    if (ids.indexOf(id) !== -1) {
                        error = true;
                        return false;
                    }
                    ids.push(id);
                }
            });
            return !error;
        }, 'Hay servicios repetidos en la factura');

        App.Main.validate($('form#invoice'), {
            ignore: ':hidden:not([name="services"])',
            rules: {
                'services': {
                    norepeatedservices: true,
                    min: {
                        depends: function() {
                            return $('#invoice_form_modelName').val() === 'GENERAL';
                        },
                        param: 1
                    }
                }
            },
            messages: {
                services: {
                    min: 'Agregue servicios a la factura'
                }
            }
        });
    }

    var serviceOnChange = function() {
        var data = $(this).select2('data');

        if ($('#invoice_form_modelName').val() === 'ATRIO') {
            $('input:hidden[name$="[1][service]"]').val($(this).val());
            $('input:hidden[name$="[1][serviceSerialNumber]"]').val(data[0].serialNumber);
        }


        $(this).closest('.item').find('input[name$="[serviceName]"]').val(data[0].serviceName);
        $(this).closest('.item').find('input[name$="[clientsName]"]').val(data[0].clientNames);
        $(this).closest('.item').find('input[name$="[clientReference]"]').val(data[0].reference);
        $(this).closest('.item').find('input[name$="[serviceSerialNumber]"]').val(data[0].serialNumber);
        $(this).closest('.item').find('input[name$="[totalPrice]"]').val(data[0].price).trigger('change');
    }

    var serviceSelect2Options = {
        ajax: {
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page
                }
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        templateResult: function(repo) {
            if (repo.loading) return repo.text;

            var markup = '<div class="select2-result-repository clearfix">' +
                '<div class="select2-result-repository__title">' + repo.text + '</div>';

            markup += '<div class="select2-result-repository__description">';
            var elements = [];
            if (repo.clientNames) {
                elements.push("<strong>Clientes:</strong> " + repo.clientNames);
            }
            if (repo.reference) {
                elements.push("<strong>Referencia:</strong> " + repo.reference);
            }
            if (repo.price) {
                elements.push('<strong>Importe:</strong> ' + repo.price);
            }

            markup += elements.join(' | ', elements) + '</div></div>';

            return markup;
        },
        language: 'es',
        minimunInputLength: 1,
        width: '100%'
    }

    var initControls = function() {
        $('#invoice_form_provider').on('change', function() {
            $('#invoice_form_lines').find('.item').remove();
        }).select2({width: '100%'});
        $('#invoice_form_driver').select2({width: '100%'});
        $('#invoice_form_modelName').on('change', function() {
            if ($(this).val() === 'ATRIO') {
                $('#fakedServices').removeClass('hidden').find('.panel-body').append($('<div class="row item"><div class="col-sm-4 form-group"><label for="invoice_form_lines_0_service" class="required">Servicio</label><select id="invoice_form_lines_0_service" name="invoice_form[lines][0][service]" required="required"></select></div><div class="form-group col-sm-2"><label for="invoice_form_lines_0_meassurementUnit">Unidad</label><input type="text" id="invoice_form_lines_0_meassurementUnit" name="invoice_form[lines][0][meassurementUnit]" maxlength="49" value="Km" class="form-control"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_0_quantity">Cantidad</label><input type="number" id="invoice_form_lines_0_quantity" name="invoice_form[lines][0][quantity]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_0_unitPrice">Precio</label><input type="text" id="invoice_form_lines_0_unitPrice" name="invoice_form[lines][0][unitPrice]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_0_totalPrice" class="required">Importe</label><input type="text" id="invoice_form_lines_0_totalPrice" name="invoice_form[lines][0][totalPrice]" required="required" class="form-control text-right"></div><input type="hidden" id="invoice_form_lines_0_serviceName" name="invoice_form[lines][0][serviceName]"><input type="hidden" id="invoice_form_lines_0_clientsName" name="invoice_form[lines][0][clientsName]"><input type="hidden" id="invoice_form_lines_0_clientReference" name="invoice_form[lines][0][clientReference]"><input type="hidden" id="invoice_form_lines_0_serviceSerialNumber" name="invoice_form[lines][0][serviceSerialNumber]"><input type="hidden" id="invoice_form_lines_0_serviceSerialNumber" name="invoice_form[lines][0][notes]"></div>' + '<div class="row item"><div class="col-sm-4 form-group"><label for="invoice_form_lines_1_serviceName" class="required">Servicio</label><input id="invoice_form_lines_1_serviceName" name="invoice_form[lines][1][serviceName]" required="required" value="Horas de espera" class="form-control" readonly="readonly"></div><div class="form-group col-sm-2"><label for="invoice_form_lines_1_meassurementUnit">Unidad</label><input type="text" id="invoice_form_lines_1_meassurementUnit" name="invoice_form[lines][1][meassurementUnit]" maxlength="49" value="hora" class="form-control"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_1_quantity">Cantidad</label><input type="number" id="invoice_form_lines_1_quantity" name="invoice_form[lines][1][quantity]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_1_unitPrice">Precio</label><input type="text" id="invoice_form_lines_1_unitPrice" name="invoice_form[lines][1][unitPrice]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_1_totalPrice" class="required">Importe</label><input type="text" id="invoice_form_lines_1_totalPrice" name="invoice_form[lines][1][totalPrice]" required="required" class="form-control text-right"></div><input type="hidden" id="invoice_form_lines_1_service" name="invoice_form[lines][1][service]"><input type="hidden" id="invoice_form_lines_1_clientsName" name="invoice_form[lines][1][clientsName]"><input type="hidden" id="invoice_form_lines_1_clientReference" name="invoice_form[lines][1][clientReference]"><input type="hidden" id="invoice_form_lines_1_serviceSerialNumber" name="invoice_form[lines][1][serviceSerialNumber]"><input type="hidden" id="invoice_form_lines_1_serviceSerialNumber" name="invoice_form[lines][1][notes]"></div>'));
                $('#fakedServices').find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
                    ajax: {
                        url: Routing.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
                    }
                }));

                $('#invoice_form_lines').closest('.panel').addClass('hidden').find('.item').remove();
            } else {
                $('#fakedServices').addClass('hidden').find('.item').remove();
                $('#invoice_form_lines').closest('.panel').removeClass('hidden');
            }
        }).trigger('change');
    }

    var initCollection = function() {
        var $container = $('#invoice_form_lines');
        $container.data('index', $container.find('.item').length);

        $container.on('click', '.btn-delete-item', function() {
            $(this).closest('.item').fadeOut(function() {
                $(this).remove();
                $('input:hidden[name="services"]').val($container.find('.item').length);
            });
        });

        $('button.btn-add-item').on('click', function() {
            var index = $container.data('index'),
                prototype = $container.data('prototype').replace(/__name__/g, index),
                $item = $(prototype);

            $container.data('index', index + 1);
            $item.appendTo($container);
            $('input:hidden[name="services"]').val($container.find('.item').length);

            $item.find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
                ajax: {
                    url: Routing.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
                }
            }));
        });

        $container.find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
            ajax: {
                url: Routing.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
            }
        }));

        var updateTotalCharge = function() {
            var value = 0;
            $.map($('input[name$="[totalPrice]"]'), function(element) {
                value += $(element).val() ? $(element).val() * 1 : 0;
            });

            $('input[name="invoice_form[totalCharge]"]').val(value.toFixed(2));
        }

        $('body').on('change', 'input[name$="[totalPrice]"]', function() {
            updateTotalCharge();
        });

        $('body').on('change', 'input[name$="[quantity]"], input[name$="[unitPrice]"]', function() {
            var $item = $(this).closest('.item'),
                $up = $item.find('input[name$="[unitPrice]"]'),
                $q = $item.find('input[name$="[quantity]"]');

            if ($up.val() === '' || $q.val() === '' || isNaN($q.val() * $up.val())) {
                if ($('#invoice_form_modelName').val() === 'GENERAL') {
                    var data = $item.find('select[name$="[service]"]').select2('data');
                    $item.find('input[name$="[totalPrice]"]').val(data.length > 0 ? data[0].price.toFixed(2) : "0.00").trigger('change');
                } else {
                    $item.find('input[name$="[totalPrice]"]').val("0.00").trigger('change');
                }
            } else {
                var t = ($up.val() * $q.val()).toFixed(2);
                $item.find('input[name$="[totalPrice]"]').val(t).trigger('change');
            }
        });
    }

    return {
        init: function() {
            initValidator();
            initControls();
            initCollection();
        }
    }
}(jQuery));