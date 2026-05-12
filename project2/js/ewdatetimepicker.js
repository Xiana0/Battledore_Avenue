/**
 * Create Date/Time Picker (for PHPMaker 2026)
 * @license Copyright (c) e.World Technology Limited. All rights reserved.
 */

tempusDominus.Namespace.css.toggleMeridiem = "toggleMeridiem,btn,btn-primary";

// Global options
ew.dateTimePickerOptions = {
    keepInvalid: true,
    localization: {
        dayViewHeaderFormat: { month: "long", year: "numeric" }
    }
};

/**
 * Create date/time picker
 *
 * @param {string} formid - Form ID
 * @param {string} id - Field variable name
 * @param {object} pickerOptions - DateTime picker options
 * @param {object} options - Options
 * @returns
 */
ew.createDateTimePicker = function(formid, id, pickerOptions, options) {
    if (id.includes("$rowindex$"))
        return;
    let $ = jQuery,
        el = ew.getElement(id, formid),
        $el = $(el),
        sv = ew.getElement("sv_" + id, formid), // AutoSuggest
        $input = $(sv || el),
        dataKey = tempusDominus.Namespace.dataKey; // "td"
    if (!el || $input.data(dataKey) || $input.parent().data(dataKey))
        return;
    let args = {
        "id": id,
        "form": formid,
        "enabled": true,
        "options": ew.deepAssign({}, ew.dateTimePickerOptions, pickerOptions)
    };
    $(document).trigger("datetimepicker", [args]);
    if (!args.enabled)
        return;
    if (options.inputGroup) {
        // <div class="input-group date" id="{id}" data-td-target-input="nearest" data-td-target-toggle="nearest">
        //     <input type="text" class="form-control td-input" data-td-target="#{id}"/>
        //     <button class="btn btn-default" type="button" data-td-target="#{id}" data-td-toggle="datetimepicker"><i class="fa-regular fa-calendar"></i></button>
        // </div>
        let $textbox = $input,
            isInvalid = $input.hasClass("is-invalid"),
            id = "datetimepicker_" + formid + "_" + $input.attr("id");
            $btn = $('<button class="btn btn-default" type="button"><i class="fa-regular fa-calendar"></i></button>')
                .on(`click.${dataKey}`, () => $textbox.removeClass("is-invalid"));
        $input.addClass(`${dataKey}-input`).attr("data-td-target", "#" + id)
            .wrap(`<div class="input-group${isInvalid ? " is-invalid" : ""}" id="${id}" data-td-target-input="nearest" data-td-target-toggle="nearest"></div>`)
            .after($btn.attr("data-td-target", "#" + id).attr("data-td-toggle", "datetimepicker"))
            .on(`focus.${dataKey}`, () => {
                let tooltip = bootstrap.Tooltip.getInstance($textbox);
                tooltip?.hide();
                tooltip?.disable();
            }).on(`blur.${dataKey}`, () => bootstrap.Tooltip.getInstance($textbox)?.enable())
            .on(`change.${dataKey}`, () => $input.trigger("change"));
        $input = $input.parent();
    } else {
        // <input type="text" class="form-control td-input" id="{id}"/>
        $input.addClass(`${dataKey}-input`)
            .on(`focus.${dataKey}`, () => {
                let tooltip = bootstrap.Tooltip.getInstance($input);
                tooltip?.hide();
                tooltip?.disable();
            }).on(`blur.${dataKey}`, () => bootstrap.Tooltip.getInstance($input).enable())
            .on(`change.${dataKey}`, () => $input.trigger("change"));
    }
    $input.tempusDominus(args.options);
    const td = $input.data(dataKey);
    document.addEventListener("changetheme", e => td.updateOptions({ display: { theme: e.detail } }));
    if (options.minDateField) {
        $el.fields(options.minDateField)?.[0]?.addEventListener("change", (e) => td.updateOptions({
            restrictions: {
                minDate: e.detail?.date,
            },
        }));
    }
    if (options.maxDateField) {
        $el.fields(options.maxDateField)?.[0]?.addEventListener("change", (e) => td.updateOptions({
            restrictions: {
                maxDate: e.detail?.date,
            },
        }));
    }
    return td;
}
