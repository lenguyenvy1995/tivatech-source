<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserKeyword;
use App\Models\Keyword;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Lấy ngẫu nhiên 5 từ khoá chưa từng hiện cho user
    public function getRandomKeywords()
    {
        $userId = Auth::id();
        $usedKeywordIds = UserKeyword::where('user_id', $userId)->pluck('keyword_id')->toArray();

        // Lấy từ khoá chưa từng hiện
        $keywords = Keyword::whereNotIn('id', $usedKeywordIds)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        if ($keywords->isEmpty()) {
            return response()->json(['message' => 'Đã hết từ khoá mới cho bạn!'], 404);
        }

        // Chỉ lưu những từ khoá vừa lấy
        foreach ($keywords as $keyword) {
            UserKeyword::create([
                'user_id' => $userId,
                'keyword_id' => $keyword->id,
            ]);
        }

        return response()->json($keywords);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $keywords = Keyword::query();
            return DataTables::of($keywords)
                ->addColumn('action', function ($row) {
                    return '
                        <a href="' . route('keywords.edit', $row) . '" class="btn btn-warning btn-sm mr-2">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form action="' . route('keywords.destroy', $row) . '" method="POST" style="display:inline-block;" onsubmit="return confirm(\'Bạn có chắc chắn muốn xoá từ khoá này?\');">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Xoá
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('keywords.index');
    }

    public function create()
    {
        return view('keywords.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'keywords' => 'required|string',
        ]);

        $keywords = explode("\n", $request->keywords);

        foreach ($keywords as $name) {
            $name = trim($name);
            if (!empty($name)) {
                // Chỉ tạo mới nếu chưa có trong database
                if (!Keyword::where('name', $name)->exists()) {
                    Keyword::create(['name' => $name]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function edit(Keyword $keyword)
    {
        return view('keywords.edit', compact('keyword'));
    }

    public function update(Request $request, Keyword $keyword)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $keyword->update($request->only('name'));

        return redirect()->route('keywords.index')->with('success', 'Cập nhật từ khoá thành công!');
    }

    public function destroy(Keyword $keyword)
    {
        $keyword->delete();

        return redirect()->route('keywords.index')->with('success', 'Xoá từ khoá thành công!');
    }
}
