@extends('adminlte::page')

@section('title', 'Keyword Planner')

@section('content_header')
    <h1>Keyword Planner</h1>
@stop

@push('css')
    <style>
        .tagify {
            border: none;
            box-shadow: none;
            padding: 0;
        }

        .tagify__input {
            padding: 0.375rem 0.5rem !important;
        }

        .tagify__tag {
            margin: 2px 4px;
        }

        @media (max-width: 768px) {
            form.d-flex {
                overflow-x: auto;
            }

            form.d-flex .form-control,
            form.d-flex .btn {
                flex-shrink: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <form id="keyword-form" class="mb-4 d-flex align-items-center gap-2">

                <textarea name="keywords" class="form-control" placeholder="Nhập từ khóa và nhấn Enter" style="flex:1; min-width:200px;"></textarea>
                <select class="form-control" name="location" style="min-width: 220px;">
                    @foreach ($locations as $loc)
                        <option value="{{ $loc['id'] }}" {{ $loc['name'] === 'Hồ Chí Minh' ? 'selected' : '' }}>
                            {{ $loc['name'] }} ({{ $loc['target_type'] }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn"
                    style="background-color: #6A1B9A; color: #fff; border-radius: 30px; padding: 0.5rem 1.25rem; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                    <i class="fas fa-search mr-1"></i>
                </button>
            </form>

            <div id="keyword-result">
                <table id="keywordTable" class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Từ khóa</th>
                            <th>Lượt tìm kiếm TB hàng tháng</th>
                            <th>Mức độ cạnh tranh</th>
                            <th>Giá thầu thấp (VNĐ)</th>
                            <th>Giá thầu cao (VNĐ)</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let input = document.querySelector('textarea[name=keywords]');
            let tagify = new Tagify(input, {
                enforceWhitelist: false,
                delimiters: "\n", // 👈 xử lý Enter hoặc xuống dòng
                dropdown: {
                    enabled: 0
                }
            });

            // xử lý khi dán nhiều dòng
            input.addEventListener('paste', function(e) {
                let pastedText = e.clipboardData.getData('text/plain');
                let lines = pastedText.split(/\r?\n/).filter(line => line.trim() !== '');
                tagify.addTags(lines);
                e.preventDefault();
            });
            const table = $('#keywordTable').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: false,
                deferLoading: 0,
                ajax: {
                    url: "{{ route('keyword-planner.search') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(d) {
                        const form = document.getElementById('keyword-form');
                        const formData = new FormData(form);
                        const keywords = formData.get('keywords');
                        if (!keywords || keywords.trim() === '[]') {
                            return false; // cancel the request
                        }
                        d.keywords = keywords;
                        d.location = formData.get('location');
                    }
                },
                columns: [{
                        data: 'keyword',
                        name: 'keyword'
                    },
                    {
                        data: 'avg_monthly_searches',
                        name: 'avg_monthly_searches',
                        className: 'text-right'
                    },
                    {
                        data: 'competition',
                        name: 'competition'
                    },
                    {
                        data: 'low_bid',
                        name: 'low_bid',
                        className: 'text-right'
                    },
                    {
                        data: 'high_bid',
                        name: 'high_bid',
                        className: 'text-right'
                    },
                ]
            });

            document.getElementById('keyword-form').addEventListener('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });
    </script>
@endpush
