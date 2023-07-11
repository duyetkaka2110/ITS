$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    beforeSend: function () {
        $('.loading').removeClass('d-none');
    },
    complete: function () {
        $('.loading').addClass('d-none');
    },
    error: function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.status);
        console.log(textStatus);
        console.info(jqXHR["responseText"]["message"]);
        if (jqXHR.status == 419) {
            location.reload();
        }
    }
});
// メッセージポップアップ表示
function dispMessageModal(content) {
    $("#MessageModal .modal-body").html(content);
    $("#MessageModal").modal();
}
// 確認ポップアップ表示
function dispConfirmModal(content, action) {
    $("#ConfirmModal .modal-body").html(content);
    $("#ConfirmModal .btnPopupOk").attr("data-action", action);
    $("#ConfirmModal").modal();
}
// 見積ポップアップ表示
function dispInvoiceModal(content) {
    $("#InvoiceModal .modal-body").html(content);
    $("#InvoiceModal").modal();
}
function getQueryStringValue(uri, key) {     
    if(!uri) return false;   
    var regEx = new RegExp("[\\?&]" + key + "=([^&#]*)");        
    var matches = uri.match(regEx);
    return matches == null ? null : matches[1];
}