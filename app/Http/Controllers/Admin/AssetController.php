<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager() && !auth()->user()->isAccountant()) {
                abort(403, 'You do not have permission to access company assets.');
            }
            return $next($request);
        });
    }
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $assets = $query->latest()->paginate(15);
        $categories = Asset::select('category')->distinct()->pluck('category');

        return view('admin.assets.index', compact('assets', 'categories'));
    }

    public function create()
    {
        return view('admin.assets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'serial_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,disposed,repair',
        ]);

        Asset::create($validated);

        return redirect()->route('admin.assets.index')->with('success', 'Asset recorded successfully.');
    }

    public function show(Asset $asset)
    {
        return view('admin.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        return view('admin.assets.edit', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'serial_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,disposed,repair',
        ]);

        $asset->update($validated);

        return redirect()->route('admin.assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('admin.assets.index')->with('success', 'Asset deleted successfully.');
    }
}
