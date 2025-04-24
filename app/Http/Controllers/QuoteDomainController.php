<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Models\QuoteDomain;
use App\Models\QuoteRequest;

class QuoteDomainController extends Controller
{
    public function index()
    {
        $quoteDomains = QuoteDomain::withCount('quotes')->get();

        return view('quote_domains.index', compact('quoteDomains'));
    }
    public function create()
    {
        return view('quote_domains.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:quote_domains,name',
        ]);
    
        $quoteDomain = QuoteDomain::create($validated);
    
        // Kiểm tra nếu có yêu cầu redirect back
        if ($request->has('redirect_back')) {
            return redirect()->back()->with('success', 'Quote Domain đã được thêm.');
        }
    
        return redirect()->route('quote-domains.index')->with('success', 'Quote Domain đã được thêm.');
   
    }
    
}
