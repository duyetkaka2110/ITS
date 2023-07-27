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
        console.info(jqXHR["responseText"]["message"]);
        if (jqXHR.status == 419) {
            location.reload();
        } else {
            dispMessageModal("予期せぬエラーが発生しました。システム管理者に連絡して下さい。");
        }
    }
});
// ２ポップアップ以上がある場合
$(document).on('show.bs.modal', '.modal', function () {
    const zIndex = 1040 + 10 * $('.modal:visible').length;
    $(this).css('z-index', zIndex);
    setTimeout(() => $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack'));
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

// 完了メッセージ表示
function dispSuccessMsg(msg) {
    $(".alert-success").html(msg);
    $(".alert-success").fadeIn(100).delay(1500).fadeOut(100);
}
function getQueryStringValue(uri, key) {
    if (!uri) return false;
    var regEx = new RegExp("[\\?&]" + key + "=([^&#]*)");
    var matches = uri.match(regEx);
    return matches == null ? null : matches[1];
}