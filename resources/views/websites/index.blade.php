@extends('adminlte::page')

@section('title', 'Danh sách Website')

@section('content_header')
    <h1>Danh sách Website</h1>
@stop

@section('content')
    <div class="card">
        {{-- <div class="card-header">
            <!-- Nút mở modal -->
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addWebsiteModal">
                Thêm Website
            </button>
        </div> --}}
        <div class="card-body">
            <table class="table table-success table-striped table-bordered table-hover" id="websites-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Website</th>
                        @if(Auth::user()->hasRole('admin'))
                            <th>Người Dùng</th>
                        @endif
                        <th class="text-center">Hành Động</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal Thêm Website -->
    <div class="modal fade" id="addWebsiteModal" tabindex="-1" role="dialog" aria-labelledby="addWebsiteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWebsiteModalLabel">Thêm Website</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addWebsiteForm" action="{{ route('websites.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="name">Tên Website</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        $(document).ready(function() {
            $('#websites-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,
                ajax: '{{ route("websites.index") }}',
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json"
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    @if(Auth::user()->hasRole('admin'))
                        { data: 'users', name: 'users', orderable: false, searchable: false },
                    @endif
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@stop
