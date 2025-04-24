@extends('adminlte::page')

@section('title', 'Danh sách Campaigns')
@section('css')
    @routes()
@stop
@section('content')
<div class="container-fluid mt-4">
    <h1>Danh sách Ghi Chú</h1>

    <!-- Thêm Ghi Chú -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Thêm Ghi Chú Mới</h3>
        </div>
        <div class="card-body">
            <form id="addNoteForm">
                @csrf
                <input type="hidden" id="campaign_id" value="{{ $campaignId }}">
                <div class="form-group">
                    <label for="newNote">Nội dung Ghi Chú</label>
                    <textarea id="newNote" class="form-control" rows="3" placeholder="Nhập nội dung ghi chú..."></textarea>
                </div>
                <button type="button" id="addNoteButton" class="btn btn-success">Thêm Ghi Chú</button>
            </form>
        </div>
    </div>

    <!-- Danh sách Ghi Chú -->
    <div class="card">
        <div class="card-header">
            <h3>Ghi Chú Hiện Tại</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="notesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nội Dung</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($note as $item)
                        <tr id="noteRow{{ $item->id }}">
                            <td>{{ $item->id }}</td>
                            <td>
                                <textarea class="form-control editNote" data-id="{{ $item->id }}">{{ $item->note }}</textarea>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm deleteNote" data-id="{{ $item->id }}">Xóa</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Thêm ghi chú mới
        $('#addNoteButton').click(function() {
            const campaignId = $('#campaign_id').val();
            const newNote = $('#newNote').val();

            if (!newNote.trim()) {
                alert('Vui lòng nhập nội dung ghi chú.');
                return;
            }

            $.ajax({
                url: route('notes.store'),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    campaign_id: campaignId,
                    note: newNote
                },
                success: function(response) {
                    // Thêm dòng mới vào bảng
                    $('#notesTable tbody').append(`
                        <tr id="noteRow${response.id}">
                            <td>${response.id}</td>
                            <td>
                                <textarea class="form-control editNote" data-id="${response.id}">${response.note}</textarea>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm deleteNote" data-id="${response.id}">Xóa</button>
                            </td>
                        </tr>
                    `);

                    $('#newNote').val(''); // Reset nội dung ghi chú
                    toastr.success('Ghi chú đã được thêm!');
                },
                error: function() {
                    toastr.error('Không thể thêm ghi chú.');
                }
            });
        });

        // Chỉnh sửa ghi chú
        $(document).on('change', '.editNote', function() {
            const noteId = $(this).data('id');
            const updatedNote = $(this).val();

            $.ajax({
                url: route('notes.update',noteId),
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    note: updatedNote
                },
                success: function() {
                    toastr.success('Ghi chú đã được cập nhật!');
                },
                error: function() {
                    toastr.error('Không thể cập nhật ghi chú.');
                }
            });
        });

        // Xóa ghi chú
        $(document).on('click', '.deleteNote', function() {
            const noteId = $(this).data('id');

            if (confirm('Bạn có chắc chắn muốn xóa ghi chú này?')) {
                $.ajax({
                    url: route('notes.destroy',noteId),
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        $(`#noteRow${noteId}`).remove(); // Xóa dòng khỏi bảng
                        toastr.success('Ghi chú đã được xóa!');
                    },
                    error: function() {
                        toastr.error('Không thể xóa ghi chú.');
                    }
                });
            }
        });
    });
</script>
@endsection
