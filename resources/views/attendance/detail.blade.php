@extends('adminlte::page')

@section('content')
    <h1>Chi tiết chấm công của nhân viên</h1>

    <div id="attendance-calendar"></div>

@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@5.10.1/main.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap@5.10.1/main.min.css" rel="stylesheet">
@stop
@section('js')
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script>
        var calendarEl = document.getElementById('attendance-calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                firstDay: 1, // Bắt đầu tuần từ Thứ Hai (1)
                locale: 'vi', // Ngôn ngữ tiếng Việt
                right: 'dayGridMonth,timeGridWeek'
            },
              themeSystem: 'bootstrap',
            events: @json($events), // Truyền dữ liệu sự kiện từ controller
            textColor:'#fff',

        });

        calendar.render();

    </script>
@endsection
