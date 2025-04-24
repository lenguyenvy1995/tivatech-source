<div class="modal fade" id="kpiModal" tabindex="-1" role="dialog" aria-labelledby="kpiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="kpiForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="kpiModalLabel">Thêm/Sửa KPI</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                  <!-- Input ẩn để lưu ID KPI -->
                  <input type="hidden" id="kpiRowId" name="kpiRowId">
                <div class="modal-body">
                    <input type="hidden" id="kpiRowId">
                    <div class="form-group">
                        <label for="kpiDoanhThu">Doanh thu:</label>
                        <input type="number" id="kpiDoanhThu" name="doanh_thu" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="kpiHoaHong">Hoa hồng:</label>
                        <input type="number" id="kpiHoaHong" name="hoa_hong" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit"  class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>