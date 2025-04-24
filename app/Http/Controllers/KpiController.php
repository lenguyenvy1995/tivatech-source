<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KpiSalary;
use Yajra\DataTables\DataTables;

class KpiController extends Controller
{
    public function index(Request $request)
    {
        $kpis = KpiSalary::where('user_id', $request->user_id)->get();
        return response()->json(['data' => $kpis]);
    }
    public function getData(Request $request)
    {
        $query = KpiSalary::query();
    
        // Lọc theo `user_id` nếu có
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
    
        return DataTables::of($query)
            ->addColumn('actions', function ($kpi) {
                return '
                    <button class="btn btn-sm btn-primary edit-kpi-btn" data-id="' . $kpi->id . '" 
                        data-doanh-thu="' . $kpi->doanh_thu . '" 
                        data-hoa-hong="' . $kpi->hoa_hong . '" 
                            data-action="edit"
                        type="button">Sửa</button>
                    <button class="btn btn-sm btn-danger delete-kpi-btn" data-id="' . $kpi->id . '" type="button">Xóa</button>
                ';
            })
            ->editColumn('doanh_thu', function ($kpi) {
                return number_format($kpi->doanh_thu, 0, ',', '.'); // Hiển thị định dạng tiền tệ
            })
            ->editColumn('hoa_hong', function ($kpi) {
                return number_format($kpi->hoa_hong, 0, ',', '.'); // Hiển thị định dạng tiền tệ
            })
            ->rawColumns(['actions']) // Giữ thẻ HTML trong cột actions
            ->make(true);
    }
    public function store(Request $request)
    {
        $kpi = KpiSalary::create($request->all());
        return response()->json(['message' => 'KPI đã được thêm!', 'data' => $kpi]);
    }
    public function show($id)
    {
        $kpi = KpiSalary::find($id);

        if (!$kpi) {
            return response()->json(['message' => 'KPI không tồn tại!'], 404);
        }

        return response()->json($kpi);
    }
    public function update(Request $request, $id)
    {
        $kpi = KpiSalary::find($id);

        if (!$kpi) {
            return response()->json(['message' => 'KPI không tồn tại!'], 404);
        }

        $request->validate([
            'doanh_thu' => 'required|numeric',
            'hoa_hong' => 'required|numeric',
        ]);

        $kpi->update([
            'doanh_thu' => $request->doanh_thu,
            'hoa_hong' => $request->hoa_hong,
        ]);

        return response()->json(['message' => 'Cập nhật KPI thành công!']);
    }


    public function destroy($id)
    {
        $kpi = KpiSalary::findOrFail($id);
        $kpi->delete();
        return response()->json(['message' => 'KPI đã được xóa!']);
    }
}
