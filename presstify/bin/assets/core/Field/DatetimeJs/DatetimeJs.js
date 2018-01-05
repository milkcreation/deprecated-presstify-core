jQuery(document).ready(function ($) {
    $(document)
        .on('change.tify.fields.ajax_date', '.tiFyCoreField-datetimeJsField', function (e) {
            e.preventDefault();

            var $closest = $(this).closest('.tiFyCoreField-datetimeJs');
            var value = "", dateFormat = "";
            if ($('.tiFyCoreField-datetimeJsField--year', $closest).length) {
                value += $('.tiFyCoreField-datetimeJsField--year', $closest).val();
                dateFormat += "YYYY";
            }
            if ($('.tiFyCoreField-datetimeJsField--month', $closest).length) {
                value += "-" + ("0" + parseInt($('.tiFyCoreField-datetimeJsField--month', $closest).val(), 10)).slice(-2);
                if (dateFormat)
                    dateFormat += "-";
                dateFormat += "MM";
            }
            if ($('.tiFyCoreField-datetimeJsField--day', $closest).length) {
                value += "-" + ("0" + parseInt($('.tiFyCoreField-datetimeJsField--day', $closest).val(), 10)).slice(-2);
                if (dateFormat)
                    dateFormat += "-";
                dateFormat += "DD";
            }
            if ($('.tiFyCoreField-datetimeJsField--hour', $closest).length) {
                value += " " + ("0" + parseInt($('.tiFyCoreField-datetimeJsField--hour', $closest).val(), 10)).slice(-2);
                if (dateFormat)
                    dateFormat += " ";
                dateFormat += "HH";
            }
            if ($('.tiFyCoreField-datetimeJsField--minute', $closest).length) {
                value += ":" + ("0" + parseInt($('.tiFyCoreField-datetimeJsField--minute', $closest).val(), 10)).slice(-2);

                if (dateFormat)
                    dateFormat += ":";
                dateFormat += "mm";
            }
            if ($('.tiFyCoreField-datetimeJsField--second', $closest).length) {
                value += ":" + ("0" + parseInt($('.tiFyCoreField-datetimeJsField--second', $closest).val(), 10)).slice(-2);
                if (dateFormat)
                    dateFormat += ":";
                dateFormat += "ss";
            }

            // Test d'intégrité
            if (moment(value, dateFormat, true).isValid()) {
                $closest.removeClass('invalid');
            } else {
                $closest.addClass('invalid');
            }

            $('.tiFyCoreField-datetimeJsField--value', $closest).val(value);

            $closest.trigger('tify_fields_ajax_date_change');
        });
});