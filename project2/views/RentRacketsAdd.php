<?php

namespace PHPMaker2026\Project1;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->getClientVars()) ?>;
ew.deepAssign(ew.vars, { tables: { rent_rackets: currentTable } });
var currentPageID = ew.PAGE_ID = "add";
var currentForm;
var frent_racketsadd;
ew.on("wrapper", function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("frent_racketsadd")
        .setPageId("add")

        // Add fields
        .setFields([
            ["racket_name", [fields.racket_name.visible && fields.racket_name.required ? ew.Validators.required(fields.racket_name.caption) : null], fields.racket_name.isInvalid],
            ["brand", [fields.brand.visible && fields.brand.required ? ew.Validators.required(fields.brand.caption) : null], fields.brand.isInvalid],
            ["price_per_day", [fields.price_per_day.visible && fields.price_per_day.required ? ew.Validators.required(fields.price_per_day.caption) : null, ew.Validators.float], fields.price_per_day.isInvalid],
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
<?= $Page->getPageHeader() ?>
<?= $Page->getHtmlMessage() ?>
<form name="frent_racketsadd" id="frent_racketsadd" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION")) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token ID -->
<input type="hidden" name="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="rent_rackets">
<input type="hidden" name="action" id="action" value="insert">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->getOldKeyAsString() ?>">
<div class="ew-add-div"><!-- page* -->
<?php if ($Page->racket_name->Visible) { // racket_name ?>
    <div id="r_racket_name"<?= $Page->racket_name->rowAttributes() ?>>
        <label id="elh_rent_rackets_racket_name" for="x_racket_name" class="<?= $Page->LeftColumnClass ?>"><?= $Page->racket_name->caption() ?><?= $Page->racket_name->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->racket_name->cellAttributes() ?>>
<span id="el_rent_rackets_racket_name">
<input type="<?= $Page->racket_name->getInputTextType() ?>" name="x_racket_name" id="x_racket_name" data-table="rent_rackets" data-field="x_racket_name" value="<?= $Page->racket_name->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->racket_name->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->racket_name->formatPattern()) ?>"<?= $Page->racket_name->editAttributes() ?> aria-describedby="x_racket_name_help">
<?= $Page->racket_name->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->racket_name->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->brand->Visible) { // brand ?>
    <div id="r_brand"<?= $Page->brand->rowAttributes() ?>>
        <label id="elh_rent_rackets_brand" for="x_brand" class="<?= $Page->LeftColumnClass ?>"><?= $Page->brand->caption() ?><?= $Page->brand->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->brand->cellAttributes() ?>>
<span id="el_rent_rackets_brand">
<input type="<?= $Page->brand->getInputTextType() ?>" name="x_brand" id="x_brand" data-table="rent_rackets" data-field="x_brand" value="<?= $Page->brand->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->brand->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->brand->formatPattern()) ?>"<?= $Page->brand->editAttributes() ?> aria-describedby="x_brand_help">
<?= $Page->brand->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->brand->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->price_per_day->Visible) { // price_per_day ?>
    <div id="r_price_per_day"<?= $Page->price_per_day->rowAttributes() ?>>
        <label id="elh_rent_rackets_price_per_day" for="x_price_per_day" class="<?= $Page->LeftColumnClass ?>"><?= $Page->price_per_day->caption() ?><?= $Page->price_per_day->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->price_per_day->cellAttributes() ?>>
<span id="el_rent_rackets_price_per_day">
<input type="<?= $Page->price_per_day->getInputTextType() ?>" name="x_price_per_day" id="x_price_per_day" data-table="rent_rackets" data-field="x_price_per_day" value="<?= $Page->price_per_day->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->price_per_day->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->price_per_day->formatPattern()) ?>"<?= $Page->price_per_day->editAttributes() ?> aria-describedby="x_price_per_day_help">
<?= $Page->price_per_day->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->price_per_day->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->image->Visible) { // image ?>
    <div id="r_image"<?= $Page->image->rowAttributes() ?>>
        <label id="elh_rent_rackets_image" for="x_image" class="<?= $Page->LeftColumnClass ?>"><?= $Page->image->caption() ?><?= $Page->image->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->image->cellAttributes() ?>>
<span id="el_rent_rackets_image">
<input type="<?= $Page->image->getInputTextType() ?>" name="x_image" id="x_image" data-table="rent_rackets" data-field="x_image" value="<?= $Page->image->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->image->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->image->formatPattern()) ?>"<?= $Page->image->editAttributes() ?> aria-describedby="x_image_help">
<?= $Page->image->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->image->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->stock->Visible) { // stock ?>
    <div id="r_stock"<?= $Page->stock->rowAttributes() ?>>
        <label id="elh_rent_rackets_stock" for="x_stock" class="<?= $Page->LeftColumnClass ?>"><?= $Page->stock->caption() ?><?= $Page->stock->Required ? Language()->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->stock->cellAttributes() ?>>
<span id="el_rent_rackets_stock">
<input type="<?= $Page->stock->getInputTextType() ?>" name="x_stock" id="x_stock" data-table="rent_rackets" data-field="x_stock" value="<?= $Page->stock->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->stock->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->stock->formatPattern()) ?>"<?= $Page->stock->editAttributes() ?> aria-describedby="x_stock_help">
<?= $Page->stock->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->stock->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn ew-submit" name="btn-action" id="btn-action" type="submit" form="frent_racketsadd"><?= Language()->phrase("AddBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= Language()->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="frent_racketsadd" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= Language()->phrase("CancelBtn") ?></button>
<?php } ?>
    </div><!-- /buttons offset -->
<?= $Page->IsModal ? "</template>" : "</div>" ?><!-- /buttons .row -->
</form>
<?= $Page->getPageFooter() ?>
<script<?= Nonce() ?>>
// Field event handlers
ew.on("head", function() {
    ew.addEventHandlers("rent_rackets");
});
</script>
<script<?= Nonce() ?>>
ew.on("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
