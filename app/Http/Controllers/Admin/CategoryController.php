<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Only admins and storekeepers can manage categories
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $route = $request->route()->getName();
            
            // Allow index (view) for all authenticated users
            if ($route === 'admin.categories.index') {
                return $next($request);
            }
            
            // Restrict create, edit, update, destroy to admins and storekeepers only
            if (!in_array($route, ['admin.categories.index']) && !$user->is_admin && !$user->isStorekeeper()) {
                abort(403, 'You do not have permission to manage categories.');
            }
            
            return $next($request);
        });
    }

    public function index()
    {
        $parentCategories = Category::parentCategories()
            ->with(['children' => function($q) {
                $q->withCount('products');
            }])
            ->withCount('products')
            ->orderBy('name')
            ->get();
        
        $allCategories = Category::with('parent')->withCount('products')->latest()->paginate(15);
        
        return view('admin.categories.index', compact('parentCategories', 'allCategories'));
    }

    public function create(Request $request)
    {
        $parentCategories = Category::parentCategories()->active()->orderBy('name')->get();
        $parentId = $request->filled('parent_id') ? $request->parent_id : null;
        
        return view('admin.categories.create', compact('parentCategories', 'parentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'department' => 'required|in:bar_kitchen,hotel',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check for unique name within the same parent
        $existingCategory = Category::where('name', $validated['name'])
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->first();

        if ($existingCategory) {
            return back()->withErrors(['name' => 'A category with this name already exists in this level.'])->withInput();
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = Category::create($validated);

        $categoryType = $category->isSubcategory() ? 'subcategory' : 'category';
        AuditLog::log('category_created', "Created {$categoryType}: {$category->full_name}", $category);

        return redirect()->route('admin.categories.index')
            ->with('success', ucfirst($categoryType) . ' created successfully.');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::parentCategories()
            ->where('id', '!=', $category->id)
            ->active()
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|different:' . $category->id,
            'department' => 'required|in:bar_kitchen,hotel',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Prevent setting a subcategory as parent
        if ($validated['parent_id']) {
            $parentCategory = Category::find($validated['parent_id']);
            if ($parentCategory && $parentCategory->isSubcategory()) {
                return back()->withErrors(['parent_id' => 'Cannot set a subcategory as parent.'])->withInput();
            }
        }

        // Check for unique name within the same parent (excluding current category)
        $existingCategory = Category::where('name', $validated['name'])
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->where('id', '!=', $category->id)
            ->first();

        if ($existingCategory) {
            return back()->withErrors(['name' => 'A category with this name already exists in this level.'])->withInput();
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $category->toArray();
        $category->update($validated);

        $categoryType = $category->isSubcategory() ? 'subcategory' : 'category';
        AuditLog::log('category_updated', "Updated {$categoryType}: {$category->full_name}", $category, $oldValues, $category->toArray());

        return redirect()->route('admin.categories.index')
            ->with('success', ucfirst($categoryType) . ' updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with products.');
        }

        if ($category->children()->count() > 0) {
            return back()->with('error', 'Cannot delete category with subcategories. Please delete or move subcategories first.');
        }

        $categoryType = $category->isSubcategory() ? 'subcategory' : 'category';
        AuditLog::log('category_deleted', "Deleted {$categoryType}: {$category->full_name}", $category);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', ucfirst($categoryType) . ' deleted successfully.');
    }
}


