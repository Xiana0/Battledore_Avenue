<?php

namespace PHPMaker2026\Project1;
?>
<?= $Page->getPageHeader() ?>
<?= $Page->getHtmlMessage() ?>
<main class="edit">
<?php $formAction = UrlFor("edit.bookings", $Page->getUrlKey(true)) ?>
<form name="fbookingsedit" id="fbookingsedit" class="<?= $Page->FormClassName ?>" action="<?= $formAction ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->getClientVars()) ?>;
ew.deepAssign(ew.vars, { tables: { bookings: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var fbookingsedit;
ew.on("wrapper", function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fbookingsedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["id", [fields.id.visible && fields.id.required ? ew.Validators.required(fields.id.caption) : null], fields.id.isInvalid],
            ["user_id", [fields.user_id.visible && fields.user_id.required ? ew.Validators.required(fields.user_id.caption) : null, ew.Validators.integer], fields.user_id.isInvalid],
            ["court_name", [fields.court_name.visible && fields.court_name.required ? ew.Validators.required(fields.court_name.caption) : null], fields.court_name.isInvalid],
            ["booking_date", [fields.booking_date.visible && fields.booking_date.required ? ew.Validators.required(fields.booking_date.caption) : null, ew.Validators.datetime(fields.booking_date.clientFormatPattern)], fields.booking_date.isInvalid],
            ["booking_time", [fields.booking_time.visible && fields.booking_time.required ? ew.Validators.required(fields.booking_time.caption) : null], fields.booking_time.isInvalid],
            ["status", [fields.status.visible && fields.status.required ? ew.Validators.required(fields.status.caption) : null], fields.status.isInvalid]
        ])

        // Use JavaScript validation or not
        .setValidateRequired(ew.CLIENT_VALIDATE)

        // Dynamic selection lists
        .setLists({
        })
        .build();
    window[form.id] = form;
    currentForm = form;
    ew.emit(form.id);
});
</script>
<script<?= Nonce() ?>>
ew.on("head", function () {
    // Write your table-specific client script here, no need to add script tags.
});
</script>
<?php if (Config("CSRF_PROTECTION")) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token ID -->
<input type="hidden" name="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="bookings">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->getOldKeyAsString() ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->id->Visible) { // id ?>
    <div id="r_id"<?= $Page->id->rowAttributes() ?>>
        <label id="elh_bookings_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->id->caption() ?><?= $Page->id->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->id->cellAttributes() ?>>
<span id="el_bookings_id">
<span<?= $Page->id->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= $Page->id->getDisplayValue($Page->id->getEditValue()) ?>"></span>
<input type="hidden" data-table="bookings" data-field="x_id" data-hidden="1" name="x_id" id="x_id" value="<?= HtmlEncode(ConvertToString($Page->id->CurrentValue)) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->user_id->Visible) { // user_id ?>
    <div id="r_user_id"<?= $Page->user_id->rowAttributes() ?>>
        <label id="elh_bookings_user_id" for="x_user_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->user_id->caption() ?><?= $Page->user_id->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->user_id->cellAttributes() ?>>
<span id="el_bookings_user_id">
<input type="<?= $Page->user_id->getInputTextType() ?>" name="x_user_id" id="x_user_id" data-table="bookings" data-field="x_user_id" value="<?= $Page->user_id->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->user_id->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->user_id->formatPattern()) ?>"<?= $Page->user_id->editAttributes() ?> aria-describedby="x_user_id_help">
<?= $Page->user_id->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->user_id->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->court_name->Visible) { // court_name ?>
    <div id="r_court_name"<?= $Page->court_name->rowAttributes() ?>>
        <label id="elh_bookings_court_name" for="x_court_name" class="<?= $Page->LeftColumnClass ?>"><?= $Page->court_name->caption() ?><?= $Page->court_name->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->court_name->cellAttributes() ?>>
<span id="el_bookings_court_name">
<input type="<?= $Page->court_name->getInputTextType() ?>" name="x_court_name" id="x_court_name" data-table="bookings" data-field="x_court_name" value="<?= $Page->court_name->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->court_name->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->court_name->formatPattern()) ?>"<?= $Page->court_name->editAttributes() ?> aria-describedby="x_court_name_help">
<?= $Page->court_name->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->court_name->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->booking_date->Visible) { // booking_date ?>
    <div id="r_booking_date"<?= $Page->booking_date->rowAttributes() ?>>
        <label id="elh_bookings_booking_date" for="x_booking_date" class="<?= $Page->LeftColumnClass ?>"><?= $Page->booking_date->caption() ?><?= $Page->booking_date->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->booking_date->cellAttributes() ?>>
<span id="el_bookings_booking_date">
<input type="<?= $Page->booking_date->getInputTextType() ?>" name="x_booking_date" id="x_booking_date" data-table="bookings" data-field="x_booking_date" value="<?= $Page->booking_date->getEditValue() ?>" placeholder="<?= HtmlEncode($Page->booking_date->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->booking_date->formatPattern()) ?>"<?= $Page->booking_date->editAttributes() ?> aria-describedby="x_booking_date_help">
<?= $Page->booking_date->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->booking_date->getErrorMessage() ?></div>
<?php if (!$Page->booking_date->ReadOnly && !$Page->booking_date->Disabled && !isset($Page->booking_date->EditAttrs["readonly"]) && !isset($Page->booking_date->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
(function () {
    let format = "<?= DateFormat(0) ?>",
        options = {
            localization: {
                locale: ew.LANGUAGE_ID + "-u-nu-" + ew.getNumberingSystem(),
                hourCycle: format.match(/H/) ? "h24" : "h12",
                format,
                ...ew.language.phrase("datetimepicker")
            },
            display: {
                icons: {
                    previous: ew.IS_RTL ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left",
                    next: ew.IS_RTL ? "fa-solid fa-chevron-left" : "fa-solid fa-chevron-right"
                },
                components: {
                    clock: !!format.match(/h/i) || !!format.match(/m/) || !!format.match(/s/i),
                    hours: !!format.match(/h/i),
                    minutes: !!format.match(/m/),
                    seconds: !!format.match(/s/i)
                },
                theme: ew.getPreferredTheme()
            }
        };
    ew.createDateTimePicker(
        "fbookingsedit",
        "x_booking_date",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true,"minDateField":null,"maxDateField":null}
    );
})();
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->booking_time->Visible) { // booking_time ?>
    <div id="r_booking_time"<?= $Page->booking_time->rowAttributes() ?>>
        <label id="elh_bookings_booking_time" for="x_booking_time" class="<?= $Page->LeftColumnClass ?>"><?= $Page->booking_time->caption() ?><?= $Page->booking_time->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->booking_time->cellAttributes() ?>>
<span id="el_bookings_booking_time">
<input type="<?= $Page->booking_time->getInputTextType() ?>" name="x_booking_time" id="x_booking_time" data-table="bookings" data-field="x_booking_time" value="<?= $Page->booking_time->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->booking_time->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->booking_time->formatPattern()) ?>"<?= $Page->booking_time->editAttributes() ?> aria-describedby="x_booking_time_help">
<?= $Page->booking_time->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->booking_time->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
    <div id="r_status"<?= $Page->status->rowAttributes() ?>>
        <label id="elh_bookings_status" for="x_status" class="<?= $Page->LeftColumnClass ?>"><?= $Page->status->caption() ?><?= $Page->status->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->status->cellAttributes() ?>>
<span id="el_bookings_status">
<input type="<?= $Page->status->getInputTextType() ?>" name="x_status" id="x_status" data-table="bookings" data-field="x_status" value="<?= $Page->status->getEditValue() ?>" size="30" maxlength="20" placeholder="<?= HtmlEncode($Page->status->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->status->formatPattern()) ?>"<?= $Page->status->editAttributes() ?> aria-describedby="x_status_help">
<?= $Page->status->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->status->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn ew-submit" name="btn-action" id="btn-action" type="submit" form="fbookingsedit" formaction="<?= $formAction ?>"><?= Language()->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= Language()->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fbookingsedit" formaction="<?= $formAction ?>" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= Language()->phrase("CancelBtn") ?></button>
<?php } ?>
    </div><!-- /buttons offset -->
<?= $Page->IsModal ? "</template>" : "</div>" ?><!-- /buttons .row -->
</form>
</main>
<?= $Page->getPageFooter() ?>
<script<?= Nonce() ?>>
// Field event handlers
ew.on("head", function() {
    ew.addEventHandlers("bookings");
});
</script>
<script<?= Nonce() ?>>
ew.on("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
