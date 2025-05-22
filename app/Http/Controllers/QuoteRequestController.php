<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuoteRequest;
use App\Models\QuoteDomain;
use App\Models\Quote;
use App\Models\User;
use App\Notifications\QuoteCreated;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables; // Import DataTables

class QuoteRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        // Áp dụng middleware để bảo vệ các phương thức Controller
        // $this->middleware(['auth']);
    }
    public function index(Request $request)
    {

        $query = QuoteRequest::with(['user', 'quoteDomain']);


        if ($request->ajax()) {
            // Lọc theo trạng thái nếu có
            if ($request->status) {
                $query->where('status', $request->status);
            }
            // Lọc theo Quote Domain nếu có
            if ($request->filled('quoteDomain')) {
                $query->whereHas('quoteDomain', function ($q) use ($request) {
                    $q->where('id', $request->quoteDomain);
                });
            }
            // Kiểm tra quyền người dùng, nếu không phải admin hoặc quote manager, chỉ lấy báo giá của họ
            if (!auth()->user()->hasRole(['admin', 'quote manager'])) {
                $query->where('user_id', auth()->id());
            }
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at');
            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    if ($row->status == 'pending') {
                        $btn = '<a href="' . route('quote-requests.edit', $row->id) . '" class="btn btn-sm btn-primary mr-1"><i class="fas fa-pencil-alt"></i></a>';

                        $btn .= '<form action="' . route('quote-requests.destroy', $row->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa yêu cầu này?\');">';
                        $btn .= csrf_field() . method_field("DELETE") . '<button type="submit" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button></form>';
                        return $btn;
                    }
                })
                ->editColumn('status', function ($row) {
                    return ucfirst($row->status);
                })
                ->addColumn('quote_domain', function ($row) {
                    // Kiểm tra xem `quoteDomain` có tồn tại không để tránh lỗi khi không có dữ liệu
                    if ($row->quoteDomain) {
                        return '<a target="_blank"  href="' . route('quote-requests.show', $row->id) . '" title="Từ khóa">'
                            . e($row->quoteDomain->name) . '</a>';
                    }
                    return 'N/A';
                })
                ->rawColumns(['action', 'quote_domain'])
                ->make(true);
        }
        $user = auth()->user();

        // Khởi tạo truy vấn
        $query = QuoteRequest::with(['user', 'quoteDomain']);

        // Nếu người dùng không phải là admin hoặc quote manager, chỉ hiển thị báo giá của họ
        if (!$user->hasRole(['admin', 'role-quote-manager'])) {
            $query->where('user_id', $user->id);
        }

        // Lọc theo Domain nếu có
        if ($request->filled('domain')) {
            $query->whereHas('quoteDomain', function ($q) use ($request) {
                $q->where('id', $request->domain);
            });
        }

        // Lọc theo Trạng thái nếu có
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo từ khóa nếu có
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('quoteDomain', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%$search%");
                    });
            });
        }

        // Sắp xếp theo cột và hướng sắp xếp nếu có
        $sort = $request->get('sort', 'id'); // Cột mặc định
        $direction = $request->get('direction', 'asc'); // Hướng mặc định

        $quoteRequests = $query->orderBy($sort, $direction)->paginate(30)->withQueryString();

        // Lấy danh sách các Domain để sử dụng trong dropdown lọc
        $quoteDomains = QuoteDomain::all();

        // Lấy danh sách các Trạng thái để sử dụng trong dropdown lọc
        $statuses = ['pending' => 'Chờ xử lý', 'quoted' => 'Đã báo giá', 'rejected' => 'Đã từ chối'];

        return view('quote_requests.index', compact('quoteRequests', 'quoteDomains', 'statuses'));
    }

    public function reject($id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);
        $quoteRequest->status = 'rejected';
        $quoteRequest->save();

        return redirect()->route('quote-requests.index')->with('success', 'Báo giá đã được từ chối.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Nếu có yêu cầu báo giá cũ để lấy dữ liệu, bạn có thể truyền dữ liệu đó vào view
        $quoteDomains = QuoteDomain::all();
        return view('quote_requests.create', compact('quoteDomains'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|string',
            'top_position' => 'required|array',
            'quote_domain_id' => 'required',
            'region' => 'required|string',
            'keyword_type' => 'required|array',
            'campaign_type' => 'required|array',
        ]);

        $quoteRequest = new QuoteRequest($validated);
        $quoteRequest->user_id = auth()->id();
        $quoteRequest->status = 'pending';
        $quoteRequest->save();

        return redirect()->route('quote-requests.index')->with('success', 'Yêu cầu báo giá đã được gửi.');
    }




    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Eager load các quan hệ cần thiết
        $quoteRequest = QuoteRequest::with(['quoteDomain', 'quotes.user'])->findOrFail($id);

        // Kiểm tra quyền xem yêu cầu báo giá
        $this->authorize('manage own quote requests', $quoteRequest);

        // Lấy domain ID của yêu cầu
        $domainId = $quoteRequest->quote_domain_id;
        // Nếu là saler, chỉ lấy các báo giá mà saler này đã tạo cho domain này
        $quotes = Quote::whereHas('quoteRequest', function ($query) use ($domainId) {
            $query->where('quote_domain_id', $domainId);
        })->where('user_id', Auth::id())->with('user')->orderBy('updated_at',   'asc')->get();
        // Retrieve the quote domain with related quote requests and quotes
        $quoteDomain = QuoteDomain::with(['quoteRequests' => function ($query) {
            // If the user is a "saler", filter quote requests by their user_id
            if (auth()->user()->hasRole('saler') && !auth()->user()->hasRole('quote manager')) {
                $query->where('user_id', auth()->id());
            }
        }, 'quoteRequests.quotes'])->findOrFail($domainId);

        return view('quote_requests.show', compact('quoteRequest', 'quoteDomain', 'quotes'));
    }


    public function edit($id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);

        // Kiểm tra quyền truy cập
        if (!auth()->user()->hasRole(['admin', 'role-quote-manager'])) {
            // Nếu người dùng không phải admin hoặc quote manager, kiểm tra xem họ có phải là người tạo yêu cầu này không
            if ($quoteRequest->user_id !== auth()->id()) {
                abort(403, 'Không có quyền truy cập.');
            }
        }

        // Lấy danh sách các Domains để chọn trong form
        $quoteDomains = QuoteDomain::all();

        // Định nghĩa các Trạng thái
        $statuses = [
            'pending' => 'Chờ xử lý',
            'quoted' => 'Đã báo giá',
            'rejected' => 'Đã từ chối',
        ];

        return view('quote_requests.edit', compact('quoteRequest', 'quoteDomains', 'statuses'));
    }

    /**
     * Cập nhật yêu cầu báo giá trong cơ sở dữ liệu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);

        // Kiểm tra quyền truy cập
        if (!auth()->user()->hasRole(['admin', 'role-quote-manager'])) {
            // Nếu người dùng không phải admin hoặc quote manager, kiểm tra xem họ có phải là người tạo yêu cầu này không
            if ($quoteRequest->user_id !== auth()->id()) {
                abort(403, 'Không có quyền truy cập.');
            }
        }

        // Xác thực dữ liệu nhập vào
        $validated = $request->validate([
            'quote_domain_id' => 'required|exists:quote_domains,id',
            'keywords' => 'required|string',
            'top_position' => 'required|array',
            'region' => 'required|string|max:255',
            'keyword_type' => 'required|array',
            'campaign_type' => 'required|array',
        ]);

        // Cập nhật dữ liệu
        $quoteRequest->update($validated);

        return redirect()->route('quote-requests.index')->with('success', 'Yêu cầu báo giá đã được cập nhật thành công.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);

        $quoteRequest->delete();

        return redirect()->route('quote-requests.index')->with('success', 'Yêu cầu báo giá đã được xóa thành công.');
    }
    public function allRequests(Request $request)
    {

        if ($request->ajax()) {
            $query = QuoteRequest::with(['quoteDomain', 'user']);

            // Lọc theo trạng thái nếu có
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Lọc theo User nếu có
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            // Lọc theo Quote Domain nếu có
            if ($request->filled('quoteDomain')) {
                $query->whereHas('quoteDomain', function ($q) use ($request) {
                    $q->where('id', $request->quoteDomain);
                });
            }
            // Lọc theo keyword nếu có
            if ($request->filled('keywords')) {
                $query->where('keywords', 'like','%'.$request->keywords.'%');
          
            }
            
            // Sắp xếp theo ưu tiên trạng thái `pending`, sau đó updated_at mới nhất và created_at mới nhất
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at');
            return DataTables::of($query)
                ->addColumn('quote_domain', function ($row) {
                    // Kiểm tra xem `quoteDomain` có tồn tại không để tránh lỗi khi không có dữ liệu
                    if ($row->quoteDomain) {
                        return '<a target="_blank" href="' . route('quote-requests.show', $row->id) . '" title="Từ khóa">'
                            . e($row->quoteDomain->name) . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('action', function ($row) {
                    if ($row->status == 'pending') {
                        $btn = '<a href="' . route('quotes.create', $row->id) . '" class="edit btn btn-primary btn-sm mr-1"> <i class="fa fa-pencil aria-hidden="true"></i>Báo giá</a>';
                        $btn .= '<a href="#" class="edit btn btn-info btn-sm"> <i class="fa fa-pencil aria-hidden="true"></i>Báo Gấp<a>';
                    } else {
                        $btn = '<a href="' . route('quotes.edit', $row->id) . '" class="edit btn btn-danger btn-sm mr-1">Báo lại</a>';
                    }

                    return $btn;
                })
                ->orderColumn('created_at', '-name $1')
                ->rawColumns(['quote_domain', 'action'])
                ->make(true);
        }

        // Lấy danh sách các Domains để sử dụng trong dropdown lọc (nếu có)
        $quoteDomains = QuoteDomain::all();
        // Lấy danh sách người dùng
        $users = $users = User::role('saler')->get();
        return view('quote_requests.all', compact('quoteDomains', 'users'));
    }
}
