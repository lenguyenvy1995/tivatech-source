@extends('adminlte::page')

@section('title', 'Dashboard Admin')

@section('content_header')
    <h1>Tổng quan</h1>
@stop

@section('content')

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- tổng Doanh sách dự kiến-->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($getMonthlySalesTypeCamp2 + $monthlySales) }}</h3>
                            <p>Doanh Số Dự Kiến</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            {{-- <h3>{{$monthlySales}}</h3> --}}
                            <h3>{{ number_format($monthlySales) }}</h3>

                            <p>Doanh số Trọn Gói</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="#" class="small-box-footer">Chi tiết <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($getMonthlySalesTypeCamp2) }}</h3>
                            <p>Doanh số ngân sách</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $campUnpaid }}</h3>

                            <p>Chưa thanh toán</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <!-- /.row -->
            <!-- Main row -->
            <div class="row">
                <!-- Left col -->
                <section class="col-lg-6 connectedSortable">
                    <!-- Custom tabs (Charts with tabs)-->
                    <canvas id="monthlySalesChart" width="400" height="200"></canvas>
                </section>
                <!-- /.right col -->
                <section class="col-lg-6 connectedSortable">
                    <!-- Custom tabs (Charts with tabs)-->
                    <canvas id="weeklyBudgetComparisonChart" width="400" height="200"></canvas>
                </section>
            </div>
            <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
@stop
@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById("monthlySalesChart").getContext("2d");

            // Gọi API để lấy dữ liệu doanh số
            fetch("{{ route('api.monthly-sales') }}")
                .then(response => response.json())
                .then(data => {
                    // Xử lý dữ liệu từ API
                    const labels = data.map(item => item.month);
                    const salesData = data.map(item => item.sales);

                    // Cấu hình dữ liệu cho biểu đồ
                    const monthlySalesData = {
                        labels: labels,
                        datasets: [{
                            label: "Doanh số (VND)",
                            backgroundColor: "rgba(75, 192, 192, 0.2)",
                            borderColor: "rgba(75, 192, 192, 1)",
                            borderWidth: 1,
                            data: salesData
                        }]
                    };

                    // Vẽ biểu đồ
                    new Chart(ctx, {
                        type: "bar",
                        data: monthlySalesData,
                        options: {
                            responsive: true,
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            return value.toLocaleString("vi-VN") + " VND";
                                        }
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        // Định dạng số cho tooltip khi di chuột qua các cột
                                        return Math.floor(tooltipItem.yLabel).toLocaleString(
                                            "vi-VN") + " VND";
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error("Lỗi khi lấy dữ liệu doanh số:", error));
        });
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById("weeklyBudgetComparisonChart").getContext("2d");

            // Gọi API để lấy dữ liệu 7 ngày qua
            fetch("{{ route('api.weekly-budget-comparison') }}")
                .then(response => response.json())
                .then(data => {
                    // Xử lý dữ liệu từ API
                    const labels = data.map(item => item.date); // Ngày
                    const totalBudgetsData = data.map(item => item.totalBudgets); // Tổng ngân sách
                    const averageDailyBudgetData = data.map(item => item
                    .averageDailyBudget); // Trung bình ngân sách

                    // Cấu hình dữ liệu cho biểu đồ
                    const chartData = {
                        labels: labels,
                        datasets: [{
                                label: "Thực chạy",
                                backgroundColor: "rgba(75, 192, 192, 0.2)",
                                borderColor: "rgba(75, 192, 192, 1)",
                                borderWidth: 1,
                                data: totalBudgetsData
                            },
                            {
                                label: "Ngân sách",
                                backgroundColor: "rgba(255, 99, 132, 0.2)",
                                borderColor: "rgba(255, 99, 132, 1)",
                                borderWidth: 1,
                                data: averageDailyBudgetData
                            }
                        ]
                    };

                    // Vẽ biểu đồ
                    new Chart(ctx, {
                        type: "bar",
                        data: chartData,
                        options: {
                            responsive: true,
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            return value.toLocaleString("vi-VN") + " VND";
                                        }
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        return Math.floor(tooltipItem.yLabel).toLocaleString(
                                            "vi-VN") + " VND";
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error("Lỗi khi lấy dữ liệu biểu đồ:", error));
        });
    </script>
@stop
