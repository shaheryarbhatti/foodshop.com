<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'coupons.manage', ['coupons.manage', 'coupons.view', 'coupons.add', 'coupons.edit', 'coupons.delete'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Coupon::query()->orderByDesc('id');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('type_label', function (Coupon $coupon) {
                    if ($coupon->discount_type === 'percentage') {
                        return e(number_format((float) $coupon->discount_value, 2)) . '%';
                    }

                    return e(number_format((float) $coupon->discount_value, 2));
                })
                ->addColumn('usage_label', function (Coupon $coupon) {
                    $limit = $coupon->usage_limit ? (string) $coupon->usage_limit : __('unlimited');

                    return '<div class="fw-semibold">' . e((string) $coupon->used_count) . '</div>'
                        . '<div class="small text-muted">' . e(__('limit')) . ': ' . e($limit) . '</div>';
                })
                ->addColumn('date_window', function (Coupon $coupon) {
                    $starts = $coupon->starts_at?->format('d M Y h:i A') ?: __('not_available');
                    $expires = $coupon->expires_at?->format('d M Y h:i A') ?: __('not_available');

                    return '<div class="small text-dark">' . e(__('starts_at')) . ': ' . e($starts) . '</div>'
                        . '<div class="small text-muted">' . e(__('expires_at')) . ': ' . e($expires) . '</div>';
                })
                ->addColumn('status_badge', function (Coupon $coupon) {
                    return $coupon->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function (Coupon $coupon) use ($user) {
                    $canEdit = $this->userCanAny($user, ['coupons.edit']);
                    $canDelete = $this->userCanAny($user, ['coupons.delete']);

                    $edit = $canEdit ? '<a href="' . route('coupons.edit', $coupon) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete
                        ? '<form action="' . route('coupons.destroy', $coupon) . '" method="POST" style="display:inline;" class="js-confirm-delete">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>'
                        : '';

                    return $edit || $delete ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['usage_label', 'date_window', 'status_badge', 'action'])
                ->make(true);
        }

        return view('coupons.manage');
    }

    public function create()
    {
        $this->authorizeCouponAction('coupons.add');

        return view('coupons.add', [
            'coupon' => new Coupon([
                'code' => $this->generateUniqueCouponCode(),
                'discount_type' => 'fixed',
                'status' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeCouponAction('coupons.add');

        Coupon::create($this->validateCoupon($request));

        return redirect()->route('coupons.manage')->with('success', __('coupon_created_successfully'));
    }

    public function edit(Coupon $coupon)
    {
        $this->authorizeCouponAction('coupons.edit');

        return view('coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->authorizeCouponAction('coupons.edit');

        $coupon->update($this->validateCoupon($request, $coupon));

        return redirect()->route('coupons.manage')->with('success', __('coupon_updated_successfully'));
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorizeCouponAction('coupons.delete');

        $coupon->delete();

        return redirect()->route('coupons.manage')->with('success', __('coupon_deleted_successfully'));
    }

    private function authorizeCouponAction(string $permission): void
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, [$permission])) {
            abort(403);
        }
    }

    private function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        $couponId = $coupon?->id;
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:80', Rule::unique('coupons', 'code')->ignore($couponId)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'boolean'],
        ]);

        $validated['code'] = $this->resolveCouponCode($validated['code'] ?? null, $couponId);
        $validated['minimum_order_amount'] = $validated['minimum_order_amount'] ?? 0;

        if ($validated['discount_type'] === 'percentage' && (float) $validated['discount_value'] > 100) {
            $validated['discount_value'] = 100;
        }

        return $validated;
    }

    private function resolveCouponCode(?string $requestedCode = null, ?int $ignoreCouponId = null): string
    {
        $normalized = strtoupper(trim((string) $requestedCode));
        if ($normalized !== '' && $this->couponCodeAvailable($normalized, $ignoreCouponId)) {
            return $normalized;
        }

        return $this->generateUniqueCouponCode($ignoreCouponId);
    }

    private function generateUniqueCouponCode(?int $ignoreCouponId = null): string
    {
        do {
            $code = 'SAVE-' . now()->format('ym') . '-' . strtoupper(Str::random(6));
        } while (! $this->couponCodeAvailable($code, $ignoreCouponId));

        return $code;
    }

    private function couponCodeAvailable(string $code, ?int $ignoreCouponId = null): bool
    {
        return ! Coupon::query()
            ->when($ignoreCouponId, fn ($query) => $query->where('id', '!=', $ignoreCouponId))
            ->whereRaw('UPPER(code) = ?', [strtoupper($code)])
            ->exists();
    }
}
