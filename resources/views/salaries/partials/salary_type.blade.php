<div class="row">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">HÌNH THỨC LƯƠNG</h3>
            </div>
            <div class="card-body">
                <div class="form-group form-inline">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="fixed_salary" name="salary_type" value="0" checked>
                        <label for="fixed_salary" class="custom-control-label">Cố định</label>
                    </div>
                    <div class="custom-control custom-radio ml-2">
                        <input class="custom-control-input" type="radio" id="kpi_salary" name="salary_type" value="1">
                        <label for="kpi_salary" class="custom-control-label">KPI</label>
                    </div>
                </div>
                <div id="type_sa" style="display: none;">
                    <div class="form-group">
                        <label for="doanhSo">Doanh số:</label>
                        <input type="number" id="doanhSo" name="doanhSo" class="form-control" placeholder="Nhập Doanh số">
                    </div>
                    <div class="form-group">
                        <label for="kpi">KPI:</label>
                        <input type="number" id="kpi" name="kpi" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Bảng KPI -->
        <div id="kpi_table" class="card" style="display: none;">
            <div class="card-header">
                <h3 class="card-title">Thông tin KPI</h3>
                <button type="button" class="btn btn-primary  float-right" id="addNewKpi" data-action="add">
                    Thêm KPI
                </button>

            </div>
            <div class="card-body">
                <table id="tbl_kpi_table" class="display table table-border"
                    style="width: 100%">
                    <thead>
                        <tr>
                            <th>Doanh Thu</th>
                            <th>Hoa Hồng</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu sẽ được thêm tự động từ DataTables -->
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>



