<div class="modal fade" id="kpiModal" tabindex="-1" role="dialog" aria-labelledby="kpiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="kpiForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="kpiModalLabel">Quản lý KPI</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="kpiRowId">
                    <div class="form-group">
                        <label for="kpiDoanhThu">Doanh thu:</label>
                        <input type="number" class="form-control" id="kpiDoanhThu" placeholder="Nhập doanh thu" required>
                    </div>
                    <div class="form-group">
                        <label for="kpiHoaHong">Hoa hồng:</label>
                        <input type="number" class="form-control" id="kpiHoaHong" placeholder="Nhập hoa hồng" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
