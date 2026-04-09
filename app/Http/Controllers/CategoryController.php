<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'categories.manage', ['categories.view', 'categories.add', 'categories.edit', 'categories.delete', 'categories.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Category::query()->select('categories.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($category) {
                    return $category->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($category) use ($user) {
                    $canEdit = $this->userCanAny($user, ['categories.edit']);
                    $canDelete = $this->userCanAny($user, ['categories.delete']);

                    $edit = $canEdit
                        ? '<a href="' . route('categories.edit', $category->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>'
                        : '';
                    $delete = $canDelete
                        ? '<form action="' . route('categories.destroy', $category->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">'
                            . csrf_field() . method_field('DELETE') .
                            '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>'
                        : '';

                    if (! $edit && ! $delete) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="action d-flex gap-2">' . $edit . $delete . '</div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('categories.manage');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['categories.add'])) {
            abort(403);
        }

        return view('categories.add');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['categories.add'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:categories,title',
            'permalink' => 'required|string|max:255|alpha_dash|unique:categories,permalink',
            'status' => 'required|boolean',
        ]);

        Category::create($validated);

        return redirect()->route('categories.manage')
            ->with('success', __('category_created_successfully'));
    }

    public function edit(Category $category)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['categories.edit'])) {
            abort(403);
        }

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['categories.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:categories,title,' . $category->id,
            'permalink' => 'required|string|max:255|alpha_dash|unique:categories,permalink,' . $category->id,
            'status' => 'required|boolean',
        ]);

        $category->update($validated);

        return redirect()->route('categories.manage')
            ->with('success', __('category_updated_successfully'));
    }

    public function destroy(Category $category)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['categories.delete'])) {
            abort(403);
        }

        $category->delete();

        return redirect()->route('categories.manage')
            ->with('success', __('category_deleted_successfully'));
    }
}
