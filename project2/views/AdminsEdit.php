<?php

namespace PHPMaker2026\Project1;
?>
<?= $Page->getPageHeader() ?>
<?= $Page->getHtmlMessage() ?>
<main class="edit">
<?php $formAction = UrlFor("edit.admins", $Page->getUrlKey(true)) ?>
<form name="fadminsedit" id="fadminsedit" class="<?= $Page->FormClassName ?>" action="<?= $formAction ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->getClientVars()) ?>;
ew.deepAssign(ew.vars, { tables: { admins: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var fadminsedit;
ew.on("wrapper", function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fadminsedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["id", [fields.id.visible && fields.id.required ? ew.Validators.required(fields.id.caption) : null], fields.id.isInvalid],
            ["admin_id", [fields.admin_id.visible && fields.admin_id.required ? ew.Validators.required(fields.admin_id.caption) : null], fields.admin_id.isInvalid],
            ["password", [fields.password.visible && fields.password.required ? ew.Validators.required(fields.password.caption) : null], fields.password.isInvalid]
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
<input type="hidden" name="t" value="admins">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->getOldKeyAsString() ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->id->Visible) { // id ?>
    <div id="r_id"<?= $Page->id->rowAttributes() ?>>
        <label id="elh_admins_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->id->caption() ?><?= $Page->id->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->id->cellAttributes() ?>>
<span id="el_admins_id">
<span<?= $Page->id->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= $Page->id->getDisplayValue($Page->id->getEditValue()) ?>"></span>
<input type="hidden" data-table="admins" data-field="x_id" data-hidden="1" name="x_id" id="x_id" value="<?= HtmlEncode(ConvertToString($Page->id->CurrentValue)) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->admin_id->Visible) { // admin_id ?>
    <div id="r_admin_id"<?= $Page->admin_id->rowAttributes() ?>>
        <label id="elh_admins_admin_id" for="x_admin_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->admin_id->caption() ?><?= $Page->admin_id->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->admin_id->cellAttributes() ?>>
<span id="el_admins_admin_id">
<input type="<?= $Page->admin_id->getInputTextType() ?>" name="x_admin_id" id="x_admin_id" data-table="admins" data-field="x_admin_id" value="<?= $Page->admin_id->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->admin_id->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->admin_id->formatPattern()) ?>"<?= $Page->admin_id->editAttributes() ?> aria-describedby="x_admin_id_help">
<?= $Page->admin_id->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->admin_id->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->password->Visible) { // password ?>
    <div id="r_password"<?= $Page->password->rowAttributes() ?>>
        <label id="elh_admins_password" for="x_password" class="<?= $Page->LeftColumnClass ?>"><?= $Page->password->caption() ?><?= $Page->password->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->password->cellAttributes() ?>>
<span id="el_admins_password">
<input type="<?= $Page->password->getInputTextType() ?>" name="x_password" id="x_password" data-table="admins" data-field="x_password" value="<?= $Page->password->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->password->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->password->formatPattern()) ?>"<?= $Page->password->editAttributes() ?> aria-describedby="x_password_help">
<?= $Page->password->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->password->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn ew-submit" name="btn-action" id="btn-action" type="submit" form="fadminsedit" formaction="<?= $formAction ?>"><?= Language()->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= Language()->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fadminsedit" formaction="<?= $formAction ?>" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= Language()->phrase("CancelBtn") ?></button>
<?php } ?>
    </div><!-- /buttons offset -->
<?= $Page->IsModal ? "</template>" : "</div>" ?><!-- /buttons .row -->
</form>
</main>
<?= $Page->getPageFooter() ?>
<script<?= Nonce() ?>>
// Field event handlers
ew.on("head", function() {
    ew.addEventHandlers("admins");
});
</script>
<script<?= Nonce() ?>>
ew.on("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
