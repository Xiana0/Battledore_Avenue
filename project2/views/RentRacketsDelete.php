<?php

namespace PHPMaker2026\Project1;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->getClientVars()) ?>;
ew.deepAssign(ew.vars, { tables: { rent_rackets: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var frent_racketsdelete;
ew.on("wrapper", function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("frent_racketsdelete")
        .setPageId("delete")
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
<form name="frent_racketsdelete" id="frent_racketsdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION")) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token ID -->
<input type="hidden" name="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="rent_rackets">
<input type="hidden" name="action" id="action" value="delete">
<?php foreach ($Page->Records as $record) { ?>
<input type="hidden" name="key_m[]" value="<?= HtmlEncode($record->identifierValuesAsString()) ?>">
<?php } ?>
<div class="card ew-card ew-grid <?= $Page->TableGridClass ?>">
<div class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>">
<table class="<?= $Page->TableClass ?>">
    <thead>
    <tr class="ew-table-header">
<?php if ($Page->id->Visible) { // id ?>
        <th class="<?= $Page->id->headerCellClass() ?>"><span id="elh_rent_rackets_id" class="rent_rackets_id"><?= $Page->id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->racket_name->Visible) { // racket_name ?>
        <th class="<?= $Page->racket_name->headerCellClass() ?>"><span id="elh_rent_rackets_racket_name" class="rent_rackets_racket_name"><?= $Page->racket_name->caption() ?></span></th>
<?php } ?>
<?php if ($Page->brand->Visible) { // brand ?>
        <th class="<?= $Page->brand->headerCellClass() ?>"><span id="elh_rent_rackets_brand" class="rent_rackets_brand"><?= $Page->brand->caption() ?></span></th>
<?php } ?>
<?php if ($Page->price_per_day->Visible) { // price_per_day ?>
        <th class="<?= $Page->price_per_day->headerCellClass() ?>"><span id="elh_rent_rackets_price_per_day" class="rent_rackets_price_per_day"><?= $Page->price_per_day->caption() ?></span></th>
<?php } ?>
<?php if ($Page->image->Visible) { // image ?>
        <th class="<?= $Page->image->headerCellClass() ?>"><span id="elh_rent_rackets_image" class="rent_rackets_image"><?= $Page->image->caption() ?></span></th>
<?php } ?>
<?php if ($Page->stock->Visible) { // stock ?>
        <th class="<?= $Page->stock->headerCellClass() ?>"><span id="elh_rent_rackets_stock" class="rent_rackets_stock"><?= $Page->stock->caption() ?></span></th>
<?php } ?>
    </tr>
    </thead>
    <tbody>
<?php
while ($Page->getRowData()) {
?>
    <tr <?= $Page->rowAttributes() ?>>
<?php if ($Page->id->Visible) { // id ?>
        <td<?= $Page->id->cellAttributes() ?>>
<span id="">
<span<?= $Page->id->viewAttributes() ?>>
<?= $Page->id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->racket_name->Visible) { // racket_name ?>
        <td<?= $Page->racket_name->cellAttributes() ?>>
<span id="">
<span<?= $Page->racket_name->viewAttributes() ?>>
<?= $Page->racket_name->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->brand->Visible) { // brand ?>
        <td<?= $Page->brand->cellAttributes() ?>>
<span id="">
<span<?= $Page->brand->viewAttributes() ?>>
<?= $Page->brand->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->price_per_day->Visible) { // price_per_day ?>
        <td<?= $Page->price_per_day->cellAttributes() ?>>
<span id="">
<span<?= $Page->price_per_day->viewAttributes() ?>>
<?= $Page->price_per_day->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->image->Visible) { // image ?>
        <td<?= $Page->image->cellAttributes() ?>>
<span id="">
<span<?= $Page->image->viewAttributes() ?>>
<?= $Page->image->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->stock->Visible) { // stock ?>
        <td<?= $Page->stock->cellAttributes() ?>>
<span id="">
<span<?= $Page->stock->viewAttributes() ?>>
<?= $Page->stock->getViewValue() ?></span>
</span>
</td>
<?php } ?>
    </tr>
<?php
}
?>
</tbody>
</table>
</div>
</div>
<div class="ew-buttons ew-desktop-buttons">
<button class="btn btn-primary ew-btn ew-submit" name="btn-action" id="btn-action" type="submit"><?= Language()->phrase("DeleteBtn") ?></button>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= Language()->phrase("CancelBtn") ?></button>
</div>
</form>
<?= $Page->getPageFooter() ?>
<script<?= Nonce() ?>>
ew.on("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
