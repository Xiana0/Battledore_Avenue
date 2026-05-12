<?php

namespace PHPMaker2026\Project1;
?>
<?= $Page->getPageHeader() ?>
<?= $Page->getHtmlMessage() ?>
<main class="edit">
<?php $formAction = UrlFor("edit.accessories", $Page->getUrlKey(true)) ?>
<form name="faccessoriesedit" id="faccessoriesedit" class="<?= $Page->FormClassName ?>" action="<?= $formAction ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->getClientVars()) ?>;
ew.deepAssign(ew.vars, { tables: { accessories: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var faccessoriesedit;
ew.on("wrapper", function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("faccessoriesedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["id", [fields.id.visible && fields.id.required ? ew.Validators.required(fields.id.caption) : null], fields.id.isInvalid],
            ["accessory_name", [fields.accessory_name.visible && fields.accessory_name.required ? ew.Validators.required(fields.accessory_name.caption) : null], fields.accessory_name.isInvalid],
            ["price", [fields.price.visible && fields.price.required ? ew.Validators.required(fields.price.caption) : null, ew.Validators.float], fields.price.isInvalid],
            ["image", [fields.image.visible && fields.image.required ? ew.Validators.required(fields.image.caption) : null], fields.image.isInvalid],
            ["stock", [fields.stock.visible && fields.stock.required ? ew.Validators.required(fields.stock.caption) : null, ew.Validators.integer], fields.stock.isInvalid]
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
<input type="hidden" name="t" value="accessories">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->getOldKeyAsString() ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->id->Visible) { // id ?>
    <div id="r_id"<?= $Page->id->rowAttributes() ?>>
        <label id="elh_accessories_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->id->caption() ?><?= $Page->id->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->id->cellAttributes() ?>>
<span id="el_accessories_id">
<span<?= $Page->id->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= $Page->id->getDisplayValue($Page->id->getEditValue()) ?>"></span>
<input type="hidden" data-table="accessories" data-field="x_id" data-hidden="1" name="x_id" id="x_id" value="<?= HtmlEncode(ConvertToString($Page->id->CurrentValue)) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->accessory_name->Visible) { // accessory_name ?>
    <div id="r_accessory_name"<?= $Page->accessory_name->rowAttributes() ?>>
        <label id="elh_accessories_accessory_name" for="x_accessory_name" class="<?= $Page->LeftColumnClass ?>"><?= $Page->accessory_name->caption() ?><?= $Page->accessory_name->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->accessory_name->cellAttributes() ?>>
<span id="el_accessories_accessory_name">
<input type="<?= $Page->accessory_name->getInputTextType() ?>" name="x_accessory_name" id="x_accessory_name" data-table="accessories" data-field="x_accessory_name" value="<?= $Page->accessory_name->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->accessory_name->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->accessory_name->formatPattern()) ?>"<?= $Page->accessory_name->editAttributes() ?> aria-describedby="x_accessory_name_help">
<?= $Page->accessory_name->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->accessory_name->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->price->Visible) { // price ?>
    <div id="r_price"<?= $Page->price->rowAttributes() ?>>
        <label id="elh_accessories_price" for="x_price" class="<?= $Page->LeftColumnClass ?>"><?= $Page->price->caption() ?><?= $Page->price->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->price->cellAttributes() ?>>
<span id="el_accessories_price">
<input type="<?= $Page->price->getInputTextType() ?>" name="x_price" id="x_price" data-table="accessories" data-field="x_price" value="<?= $Page->price->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->price->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->price->formatPattern()) ?>"<?= $Page->price->editAttributes() ?> aria-describedby="x_price_help">
<?= $Page->price->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->price->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->image->Visible) { // image ?>
    <div id="r_image"<?= $Page->image->rowAttributes() ?>>
        <label id="elh_accessories_image" for="x_image" class="<?= $Page->LeftColumnClass ?>"><?= $Page->image->caption() ?><?= $Page->image->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->image->cellAttributes() ?>>
<span id="el_accessories_image">
<input type="<?= $Page->image->getInputTextType() ?>" name="x_image" id="x_image" data-table="accessories" data-field="x_image" value="<?= $Page->image->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->image->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->image->formatPattern()) ?>"<?= $Page->image->editAttributes() ?> aria-describedby="x_image_help">
<?= $Page->image->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->image->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->stock->Visible) { // stock ?>
    <div id="r_stock"<?= $Page->stock->rowAttributes() ?>>
        <label id="elh_accessories_stock" for="x_stock" class="<?= $Page->LeftColumnClass ?>"><?= $Page->stock->caption() ?><?= $Page->stock->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->stock->cellAttributes() ?>>
<span id="el_accessories_stock">
<input type="<?= $Page->stock->getInputTextType() ?>" name="x_stock" id="x_stock" data-table="accessories" data-field="x_stock" value="<?= $Page->stock->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->stock->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->stock->formatPattern()) ?>"<?= $Page->stock->editAttributes() ?> aria-describedby="x_stock_help">
<?= $Page->stock->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->stock->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn ew-submit" name="btn-action" id="btn-action" type="submit" form="faccessoriesedit" formaction="<?= $formAction ?>"><?= Language()->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= Language()->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="faccessoriesedit" formaction="<?= $formAction ?>" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= Language()->phrase("CancelBtn") ?></button>
<?php } ?>
    </div><!-- /buttons offset -->
<?= $Page->IsModal ? "</template>" : "</div>" ?><!-- /buttons .row -->
</form>
</main>
<?= $Page->getPageFooter() ?>
<script<?= Nonce() ?>>
// Field event handlers
ew.on("head", function() {
    ew.addEventHandlers("accessories");
});
</script>
<script<?= Nonce() ?>>
ew.on("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
