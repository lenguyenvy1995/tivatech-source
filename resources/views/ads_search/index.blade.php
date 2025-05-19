@extends('adminlte::page')

@section('title', 'Tra cứu Quảng cáo')

@section('content_header')
    <h1>Tra cứu Quảng cáo Google Search</h1>
@stop

@section('content')
<form id="search-form">
    @csrf
    <div class="row">
        <div class="col-md-4">
            <x-adminlte-input name="keyword" label="Từ khóa" placeholder="Nhập từ khóa..." required />
        </div>

        <div class="col-md-3">
            <x-adminlte-select name="location" label="Khu vực">
                <option value="Hà Nội">Hà Nội</option>
                <option value="TPHCM">TPHCM</option>
                <option value="Đà Nẵng">Đà Nẵng</option>
                <option value="Bình Dương">Bình Dương</option>
                <option value="Đồng Nai">Đồng Nai</option>
            </x-adminlte-select>
        </div>

        <div class="col-md-3">
            <x-adminlte-select name="device" label="Thiết bị">
                <option value="mobile">Điện thoại</option>

                <option value="desktop">Máy tính</option>
            </x-adminlte-select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <x-adminlte-button label="Tìm kiếm" theme="primary" type="submit" class="btn-block"/>
        </div>
    </div>
</form>

<hr>

<div id="ads-result"></div>
<div id="loading-spinner" style="display: none;" class="text-center mt-4">
    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
    <div>Đang tìm quảng cáo...</div>
</div>
@stop

@push('js')
    <script>
        document.getElementById('search-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const container = document.getElementById('ads-result');
    const spinner = document.getElementById('loading-spinner');

    container.innerHTML = '';           // clear kết quả cũ
    spinner.style.display = 'block';   // bật spinner

    fetch("{{ route('ads.ajax') }}", {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': formData.get('_token')
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        spinner.style.display = 'none'; // tắt spinner
        if (data.ads.length === 0) {
            container.innerHTML = `<x-adminlte-callout theme='warning' title='Không tìm thấy quảng cáo nào'></x-adminlte-callout>`;
            return;
        }

        let html = `<table class='table table-bordered'><thead><tr>
            <th>Tiêu đề</th><th>Link</th><th>Mô tả</th></tr></thead><tbody>`;

        data.ads.forEach(ad => {
            html += `<tr>
                <td>${ad.title ?? '-'}</td>
                <td><a href='${ad.link}' target='_blank'>${ad.displayed_link ?? ad.link}</a></td>
                <td>${ad.snippet ?? '-'}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    })
    .catch(err => {
        spinner.style.display = 'none';
        alert('Đã xảy ra lỗi!');
        console.error(err);
    });
});
    </script>
@endpush