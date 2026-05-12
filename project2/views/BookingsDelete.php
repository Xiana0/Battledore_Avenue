<?php

namespace PHPMaker2026\Project1;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->getClientVars()) ?>;
ew.deepAssign(ew.vars, { tables: { bookings: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var fbookingsdelete;
ew.on("wrapper", function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fbookingsdelete")
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
<form name="fbookingsdelete" id="fbookingsdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION")) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token ID -->
<input type="hidden" name="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="bookings">
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
        <th class="<?= $Page->id->headerCellClass() ?>"><span id="elh_bookings_id" class="bookings_id"><?= $Page->id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->user_id->Visible) { // user_id ?>
        <th class="<?= $Page->user_id->headerCellClass() ?>"><span id="elh_bookings_user_id" class="bookings_user_id"><?= $Page->user_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->court_name->Visible) { // court_name ?>
        <th class="<?= $Page->court_name->headerCellClass() ?>"><span id="elh_bookings_court_name" class="bookings_court_name"><?= $Page->court_name->caption() ?></span></th>
<?php } ?>
<?php if ($Page->booking_date->Visible) { // booking_date ?>
        <th class="<?= $Page->booking_date->headerCellClass() ?>"><span id="elh_bookings_booking_date" class="bookings_booking_date"><?= $Page->booking_date->caption() ?></span></th>
<?php } ?>
<?php if ($Page->booking_time->Visible) { // booking_time ?>
        <th class="<?= $Page->booking_time->headerCellClass() ?>"><span id="elh_bookings_booking_time" class="bookings_booking_time"><?= $Page->booking_time->caption() ?></span></th>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
        <th class="<?= $Page->status->headerCellClass() ?>"><span id="elh_bookings_status" class="bookings_status"><?= $Page->status->caption() ?></span></th>
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
<?php if ($Page->user_id->Visible) { // user_id ?>
        <td<?= $Page->user_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->user_id->viewAttributes() ?>>
<?= $Page->user_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->court_name->Visible) { // court_name ?>
        <td<?= $Page->court_name->cellAttributes() ?>>
<span id="">
<span<?= $Page->court_name->viewAttributes() ?>>
<?= $Page->court_name->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->booking_date->Visible) { // booking_date ?>
        <td<?= $Page->booking_date->cellAttributes() ?>>
<span id="">
<span<?= $Page->booking_date->viewAttributes() ?>>
<?= $Page->booking_date->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->booking_time->Visible) { // booking_time ?>
        <td<?= $Page->booking_time->cellAttributes() ?>>
<span id="">
<span<?= $Page->booking_time->viewAttributes() ?>>
<?= $Page->booking_time->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
        <td<?= $Page->status->cellAttributes() ?>>
<span id="">
<span<?= $Page->status->viewAttributes() ?>>
<?= $Page->status->getViewValue() ?></span>
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
