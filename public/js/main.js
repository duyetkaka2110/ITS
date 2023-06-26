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