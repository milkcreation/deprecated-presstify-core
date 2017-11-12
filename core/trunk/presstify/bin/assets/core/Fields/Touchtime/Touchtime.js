jQuery(document).ready(function ($) {
    console.log('tutu');
    $(document).on('change.tify.fields.touchtime', '.tiFyCoreFields-touchtimeField', function (e) {
        e.preventDefault();

        var $closest = $(this).closest('.tiFyCoreFields-touchtime');
        var value = "", dateFormat = "";
        if ($('.tiFyCoreFields-touchtimeField--year', $closest).length) {
            value += $('.tiFyCoreFields-touchtimeField--year', $closest).val();
            dateFormat += "YYYY";
        }
        if ($('.tiFyCoreFields-touchtimeField--month', $closest).length) {
            value += "-" + ("0" + parseInt($('.tiFyCoreFields-touchtimeField--month', $closest).val(), 10) ).slice(-2);
            if (dateFormat)
                dateFormat += "-";
            dateFormat += "MM";
        }
        if ($('.tiFyCoreFields-touchtimeField--day', $closest).length) {
            value += "-" + ("0" + parseInt($('.tiFyCoreFields-touchtimeField--day', $closest).val(), 10) ).slice(-2);
            if (dateFormat)
                dateFormat += "-";
            dateFormat += "DD";
        }
        if ($('.tiFyCoreFields-touchtimeField--hour', $closest).length) {
            value += " " + ("0" + parseInt($('.tiFyCoreFields-touchtimeField--hour', $closest).val(), 10) ).slice(-2);
            if (dateFormat)
                dateFormat += " ";
            dateFormat += "HH";
        }
        if ($('.tiFyCoreFields-touchtimeField--minute', $closest).length) {
            value += ":" + ("0" + parseInt($('.tiFyCoreFields-touchtimeField--minute', $closest).val(), 10) ).slice(-2);

            if (dateFormat)
                dateFormat += ":";
            dateFormat += "mm";
        }
        if ($('.tiFyCoreFields-touchtimeField--second', $closest).length) {
            value += ":" + ("0" + parseInt($('.tiFyCoreFields-touchtimeField--second', $closest).val(), 10) ).slice(-2);
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

        $('.tiFyCoreFields-touchtimeField--value', $closest).val(value);

        $closest.trigger('tify_fields_touchtime_change');
    });
});