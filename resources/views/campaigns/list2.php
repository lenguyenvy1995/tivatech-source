@extends('adminlte::page')

@section('title', 'Danh sách Campaigns')
@section('css')
    @routes()
    <style>
        td {
            position: relative;
        }

        .status-dots {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .status-dot {
            width: 15px;
            height: 15px;
            display: inline-block;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s ease;
            border: 2px solid transparent;
        }

        .status-dot.selected {
            transform: scale(1.2);
            border: 2px solid #000;
            /* Viền đen để chỉ trạng thái được chọn */
        }
        #campaigns-table th, #campaigns-table td {
            white-space: normal !important; /* Cho phép xuống dòng */
            word-wrap: break-word !important; /* Ngắt dòng khi cần */
            overflow-wrap: break-word !important;
            min-width: 150px; /* Đặt kích thước tối thiểu cho cột */
        }
    </style>
@stop
@section('content_header')
    <h1 id='total'>Danh sách Campaigns</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div id="filterContainer">
                <div class="form-group d-inline-block ml-2">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="filterPaid">
                        <label class="custom-control-label" for="filterPaid">Chưa Thanh toán</label>
                    </div>
                </div>
                <div class="form-group d-inline-block ml-2">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="filterVat">
                        <label class="custom-control-label" for="filterVat">Thuế GTGT</label>
                    </div>
                </div>
                <div class="checkbox icheck-turquoise d-inline-block ml-2">
                    <input type="checkbox" id="filterExpired">
                    <label for="filterExpired">Sắp hết hạn</label>
                </div>
                <div class="checkbox icheck-peterriver d-inline-block ml-2">
                    <input type="checkbox" id="filter_typecamp_tg">
                    <label for="filter_typecamp_tg">Trọn gói</label>
                </div>
                <div class="checkbox icheck-amethyst d-inline-block ml-2">
                    <input type="checkbox" id="filter_typecamp_ns">
                    <label for="filter_typecamp_ns">Ngân sách</label>
                </div>
                <form class="form-inline d-inline-block">
                    <select class="custom-select my-1 ml-2" id="filterStatus">
                        <option value=''>Chọn trạng thái</option>
                        @foreach (App\Models\Status::all() as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </form>
                @if (Auth::user()->hasRole('admin|manager|techads'))
                    <form class="form-inline d-inline-block ">
                        <select class="custom-select my-1 ml-2" id="filterUser">
                            <option value=''>Chọn trạng thái</option>
                            @foreach (App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
            <div class="table-responsive">
                <table id="campaigns-table"
                    class="table table-bordered table-success table-stripped table-hover text-center" style="
    table-layout: auto !important;
    width: 100% !important;">
                <thead>
                    <tr>
                        <th>Trạng thái</th>
                        <th>Website</th>
                        <th>Thời gian</th>
                        <th>Thông tin</th>
                        <th>Gia hạn</th>
                        <th>Ghi Chú</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteModalLabel">Ghi chú chiến dịch</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="noteCampaignId" name="campaign_id">
                    <div class="form-group">
                        <label for="noteContent">Nội dung ghi chú</label>
                        <textarea class="form-control" id="noteContent" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="saveNote()">Lưu ghi chú</button>
                </div>
            </div>
        </div>
    </div>

@<i class="fa fa-stop" aria-hidden="true"></i>