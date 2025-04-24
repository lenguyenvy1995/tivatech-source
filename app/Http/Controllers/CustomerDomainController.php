<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerDomain;
use App\Models\Domain;
use App\Models\Hosting;
class CustomerDomainController extends Controller
{
 
    
    public function index()
    {
        $customers = CustomerDomain::with(['domain', 'hosting'])->get();
        return view('customer_domains.index', compact('customers'));
    }
    
    public function create()
    {
        $domains = Domain::all();
        $hostings = Hosting::all();
        return view('customer_domains.create', compact('domains', 'hostings'));
    }
    
    public function store(Request $request)
    {
        CustomerDomain::create($request->all());
        return redirect()->route('customer-domains.index');
    }
    
    public function edit($id)
    {
        $customer = CustomerDomain::findOrFail($id);
        $domains = Domain::all();
        $hostings = Hosting::all();
        return view('customer_domains.edit', compact('customer', 'domains', 'hostings'));
    }
    
    public function update(Request $request, $id)
    {
        $customer = CustomerDomain::findOrFail($id);
        $customer->update($request->all());
        return redirect()->route('customer-domains.index');
    }
    
    public function destroy($id)
    {
        CustomerDomain::destroy($id);
        return redirect()->route('customer-domains.index');
    }
}
