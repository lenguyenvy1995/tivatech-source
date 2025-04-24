<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrtherCost;
use Yajra\DataTables\DataTables;

class OtherCostController extends Controller
{
    public function index()
    {
        $otherCosts = OrtherCost::with('user')->get(); // Lấy toàn bộ chi phí khác
        return view('salaries.other-costs.index', compact('otherCosts'));
    }
    //lấy chi phí khác
    public function getOtherCosts(Request $request)
    {
        // Lấy tham số user_id và tháng (date)
        $userId = $request->user_id;
        $month = $request->month; // Dạng Y-m (2023-12)

        // Lọc dữ liệu theo user_id và tháng
        $otherCosts = OrtherCost::where('user_id', $userId)
            ->where('date', $month)
            ->get();
        // Trả về JSON
        return DataTables::of($otherCosts)
        ->make(true);
        if ($request->ajax()) {
            return DataTables::of($otherCosts)
            ->make(true);
        }
        return response()->json(['other_costs' => $otherCosts]);

    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'khach_hang' => 'nullable|string|max:255',
            'dich_vu' => 'nullable|string|max:255',
            'doanh_thu' => 'nullable|numeric',
            'hoa_hong' => 'nullable|numeric',
            'date' => 'required|date',
        ]);

        try {
            OrtherCost::create($validated);
    
            return response()->json(['message' => 'Thêm mới thành công!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi thêm mới!'], 500);
        }
    }

    public function edit($id)
    {
        $otherCost = OrtherCost::findOrFail($id);
        return response()->json($otherCost);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'khach_hang' => 'nullable|string|max:255',
            'dich_vu' => 'nullable|string|max:255',
            'doanh_thu' => 'nullable|numeric',
            'hoa_hong' => 'nullable|numeric',
        ]);

        $otherCost = OrtherCost::findOrFail($id);
        $otherCost->update($validated);
        return response()->json(['message' => 'cập nhật thành công!']);

    }

    public function destroy($id)
    {
        $otherCost = OrtherCost::findOrFail($id);
        $otherCost->delete();
        return response()->json(['message' => 'Đã xoá thành công!']);

        // return redirect()->route('salaries.other-costs.index')->with('success', 'Xóa chi phí khác thành công!');
    }
}
