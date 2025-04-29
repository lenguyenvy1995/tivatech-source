

@extends('adminlte::page')

@section('title', 'Danh sách Campaigns')

@section('content_header')
<h1>Danh sách Campaigns</h1>
<!-- Modal Ghi chú -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="noteModalLabel">Ghi chú chiến dịch</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <textarea id="noteContent" class="form-control" rows="6"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-primary" id="saveNoteBtn">Lưu ghi chú</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filterStatus" class="form-control select2" multiple="multiple" data-placeholder="Chọn trạng thái" style="width: 100%;">
                    <option value="1" selected>Hoạt động</option>
                    <option value="2" selected>Tạm dừng</option>
                    <option value="3">Hoàn thành</option>
                    <option value="4">Hết chạy</option>
                    <option value="5">Setup</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterPaid" class="form-control">
                    <option value="">-- Lọc thanh toán --</option>
                    <option value="1">Đã thanh toán</option>
                    <option value="0">Chưa thanh toán</option>
                </select>
            </div>
            <!-- Removed filterName input field -->
            <div class="col-md-3 d-flex align-items-center">
                <div class="form-check mr-3">
                    <input class="form-check-input" type="checkbox" value="1" id="filterExpired">
                    <label class="form-check-label font-weight-bold" for="filterExpired">Sắp hết hạn</label>
                </div>
                <div class="form-check mr-3">
                    <input class="form-check-input" type="checkbox" id="filterTypecampTg" checked>
                    <label class="form-check-label font-weight-bold" for="filterTypecampTg">Trọn gói</label>
                </div>
                <div class="form-check mr-3">
                    <input class="form-check-input" type="checkbox" id="filterTypecampNs" checked>
                    <label class="form-check-label font-weight-bold" for="filterTypecampNs">Ngân sách</label>
                </div>
            </div>
        </div>
        <table id="campaigns-table" class="table table-bordered table-striped table-hover" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th>Trạng thái</th>
                    <th>Website</th>
                    <th>Thời gian</th>
                    <th>Ngân sách / Thanh toán</th>
                    <th>Gia hạn</th>
                    <th>Ghi chú</th>
                    <th>Hành động</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@stop

@section('css')
<style>
  .status-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 5px;
      flex-shrink: 0;
      display: inline-block;
      vertical-align: middle;
      background-color: #6c757d;
  }
  .bg-success { background-color: #28a745 !important; }
  .bg-danger { background-color: #dc3545 !important; }
  .bg-warning { background-color: #ffc107 !important; }
  .bg-info { background-color: #17a2b8 !important; }
  /* Có thể thêm các màu khác nếu cần */
</style>
<style>
.badge {
    font-size: 0.9rem;
    padding: 5px 10px;
    border-radius: 12px;
    text-transform: capitalize;
}
.badge-success { background-color: #28a745; color: white; }
.badge-danger { background-color: #dc3545; color: white; }
.badge-warning { background-color: #ffc107; color: black; }
.badge-info { background-color: #17a2b8; color: white; }
.badge-secondary { background-color: #6c757d; color: white; }
</style>
@stop

@section('js')
<script>
$(document).ready(function(){
    $('.select2').select2({
        placeholder: "Chọn trạng thái",
        allowClear: true,
        width: '100%'
    });
    $('#campaigns-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"row mb-3"<"col-md-6"l><"col-md-6 text-right"f>>tip',
        language: {
            searchPlaceholder: "Tìm kiếm website...",
        },
        ajax: {
            url: '{{ route("campaigns") }}',
            data: function (d) {
                d.filter_status = $('#filterStatus').val() || [];
                d.filter_paid = $('#filterPaid').val();
                d.filter_expired = $('#filterExpired').is(':checked') ? '1' : '';
                d.filter_typecamp_tg = $('#filterTypecampTg').is(':checked') ? '1' : '';
                d.filter_typecamp_ns = $('#filterTypecampNs').is(':checked') ? '2' : '';
                // Removed: d.search = $('#filterName').val();
            }
        },
        lengthMenu: [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
        pageLength: -1,
        order: [[0, 'asc'], [2, 'asc']], // Sắp xếp theo status trước, rồi đến end
        columns: [
            { data: 'status', name: 'status', orderable: false, searchable: false, width: '80px' },
            { data: 'website_name', name: 'website_name', orderable: false, searchable: false },
            { data: 'duration', name: 'duration', orderable: false, searchable: false },
            { data: 'budget_payment', name: 'budget_payment', orderable: false, searchable: false },
            { data: 'renew', name: 'renew', orderable: false, searchable: false },
            { data: 'note', name: 'note', orderable: false, searchable: false },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false, 
                className: 'text-center', 
                width: '100px' // 🛠️ thêm width cứng cho vừa 3 nút
                },        
            ]
    });

    $('#filterStatus, #filterPaid, #filterExpired, #filterTypecampTg, #filterTypecampNs').on('change', function() {
        $('#campaigns-table').DataTable().ajax.reload();
    });
});

var currentCampaignId = null;

function openNoteModal(campaignId) {
    currentCampaignId = campaignId;
    $('#noteContent').val('');
    $('#noteModal').modal('show');
    // Gọi API để lấy ghi chú hiện tại nếu cần
    $.get('/campaigns/' + campaignId + '/get-note', function(response) {
        $('#noteContent').val(response.note || '');
    });
}

$('#saveNoteBtn').on('click', function() {
    var note = $('#noteContent').val();
    $.post('{{ route("notes.store") }}', {
        _token: '{{ csrf_token() }}',
        campaign_id: currentCampaignId,
        note: note
    }, function(response) {
        $('#noteModal').modal('hide');
        $('#campaigns-table').DataTable().ajax.reload(null, false);
    });
});

// Xử lý toggle thanh toán
$(document).on('change', '.toggle-paid', function() {
    var campaignId = $(this).data('id');
    var paid = $(this).is(':checked') ? 1 : 0;

    $.ajax({
        url: '/campaigns/update-paid',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            campaign_id: campaignId,
            paid: paid
        },
        success: function(response) {
            console.log('Cập nhật trạng thái thanh toán thành công');
            $('#campaigns-table').DataTable().ajax.reload(null, false);
        },
        error: function(xhr) {
            console.error('Lỗi cập nhật trạng thái thanh toán');
        }
    });
});
// Xử lý đổi trạng thái chiến dịch
$(document).on('click', '.change-status', function(e) {
    e.preventDefault();
    var campaignId = $(this).data('campaign-id');
    var statusId = $(this).data('status-id');

    $.ajax({
        url: '/campaigns/update-status',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            campaign_id: campaignId,
            status_id: statusId
        },
        success: function(response) {
            console.log('Đổi trạng thái thành công');
            $('#campaigns-table').DataTable().ajax.reload(null, false);
        },
        error: function(xhr) {
            console.error('Lỗi đổi trạng thái');
        }
    });
});
// Toggle hiển thị ghi chú đầy đủ
function toggleNotes(campaignId) {
    var fullNotes = document.getElementById('fullNotes' + campaignId);
    if (fullNotes) {
        if (fullNotes.style.display === 'none') {
            fullNotes.style.display = 'block';
        } else {
            fullNotes.style.display = 'none';
        }
    }
}
</script>
@stop