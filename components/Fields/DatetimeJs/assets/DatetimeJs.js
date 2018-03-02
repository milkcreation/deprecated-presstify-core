jQuery(document).ready(function ($) {
    $(document)
        .on('change.tify.fields.ajax_date', '.tiFyField-datetimeJsField', function (e) {
            e.preventDefault();

            var $closest = $(this).closest('.tiFyField-datetimeJs');
            var value = "", dateFormat = "";
            if ($('.tiFyField-datetimeJsField--year', $closest).length) {
                value += $('.tiFyField-datetimeJsField--year', $closest).val();
                dateFormat += "YYYY";
            }
            if ($('.tiFyField-datetimeJsField--month', $closest).length) {
                value += "-" + ("0" + parseInt($('.tiFyField-datetimeJsField--month', $closest).val(), 10)).slice(-2);
                if (dateFormat)
                    dateFormat += "-";
                dateFormat += "MM";
            }
            if ($('.tiFyField-datetimeJsField--day', $closest).length) {
                value += "-" + ("0" + parseInt($('.tiFyField-datetimeJsField--day', $closest).val(), 10)).slice(-2);
                if (dateFormat)
                    dateFormat += "-";
                dateFormat += "DD";
            }
            if ($('.tiFyField-datetimeJsField--hour', $closest).length) {
                value += " " + ("0" + parseInt($('.tiFyField-datetimeJsField--hour', $closest).val(), 10)).slice(-2);
                if (dateFormat)
                    dateFormat += " ";
                dateFormat += "HH";
            }
            if ($('.tiFyField-datetimeJsField--minute', $closest).length) {
                value += ":" + ("0" + parseInt($('.tiFyField-datetimeJsField--minute', $closest).val(), 10)).slice(-2);

                if (dateFormat)
                    dateFormat += ":";
                dateFormat += "mm";
            }
            if ($('.tiFyField-datetimeJsField--second', $closest).length) {
                value += ":" + ("0" + parseInt($('.tiFyField-datetimeJsField--second', $closest).val(), 10)).slice(-2);
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

            $('.tiFyField-datetimeJsField--value', $closest).val(value);

            $closest.trigger('tify_fields_ajax_date_change');
        });
});