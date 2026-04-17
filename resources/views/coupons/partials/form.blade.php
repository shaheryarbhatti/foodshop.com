<div class="row g-4">
    <div class="col-lg-4">
        <label class="col-form-label" for="code">{{ __('coupon_code') }} <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" readonly required>
            <button type="button" class="btn btn-outline-primary" id="generateCouponCode">{{ __('generate_coupon_code') }}</button>
        </div>
        <div class="form-text">{{ __('coupon_code_auto_help') }}</div>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-lg-8">
        <label class="col-form-label" for="title">{{ __('coupon_title') }} <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $coupon->title) }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="col-form-label" for="description">{{ __('description') }}</label>
        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $coupon->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-3">
        <label class="col-form-label" for="discount_type">{{ __('discount_type') }} <span class="text-danger">*</span></label>
        <select name="discount_type" id="discount_type" class="form-select @error('discount_type') is-invalid @enderror" required>
            <option value="fixed" {{ old('discount_type', $coupon->discount_type) === 'fixed' ? 'selected' : '' }}>{{ __('fixed_discount') }}</option>
            <option value="percentage" {{ old('discount_type', $coupon->discount_type) === 'percentage' ? 'selected' : '' }}>{{ __('percentage_discount') }}</option>
        </select>
        @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-lg-3">
        <label class="col-form-label" for="discount_value">{{ __('discount_value') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="discount_value" id="discount_value" class="form-control @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', $coupon->discount_value) }}" required>
        @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-lg-3">
        <label class="col-form-label" for="minimum_order_amount">{{ __('minimum_order_amount') }}</label>
        <input type="number" step="0.01" min="0" name="minimum_order_amount" id="minimum_order_amount" class="form-control @error('minimum_order_amount') is-invalid @enderror" value="{{ old('minimum_order_amount', $coupon->minimum_order_amount) }}">
        @error('minimum_order_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-lg-3">
        <label class="col-form-label" for="maximum_discount_amount">{{ __('maximum_discount_amount') }}</label>
        <input type="number" step="0.01" min="0" name="maximum_discount_amount" id="maximum_discount_amount" class="form-control @error('maximum_discount_amount') is-invalid @enderror" value="{{ old('maximum_discount_amount', $coupon->maximum_discount_amount) }}">
        @error('maximum_discount_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4">
        <label class="col-form-label" for="usage_limit">{{ __('usage_limit') }}</label>
        <input type="number" min="1" name="usage_limit" id="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit', $coupon->usage_limit) }}">
        <div class="form-text">{{ __('usage_limit_help') }}</div>
        @error('usage_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-lg-4">
        <label class="col-form-label" for="starts_at">{{ __('starts_at') }}</label>
        <input type="datetime-local" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\\TH:i')) }}">
        @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-lg-4">
        <label class="col-form-label" for="expires_at">{{ __('expires_at') }}</label>
        <input type="datetime-local" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d\\TH:i')) }}">
        @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4">
        <label class="col-form-label" for="status">{{ __('status') }} <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="1" {{ (string) old('status', (int) $coupon->status) === '1' ? 'selected' : '' }}>{{ __('active') }}</option>
            <option value="0" {{ (string) old('status', (int) $coupon->status) === '0' ? 'selected' : '' }}>{{ __('inactive') }}</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const codeField = document.getElementById('code');
    const generateButton = document.getElementById('generateCouponCode');

    if (!codeField || !generateButton) {
        return;
    }

    const generateCouponCode = function () {
        const now = new Date();
        const year = String(now.getFullYear()).slice(-2);
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let suffix = '';

        for (let index = 0; index < 6; index += 1) {
            suffix += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
        }

        codeField.value = `SAVE-${year}${month}-${suffix}`;
    };

    generateButton.addEventListener('click', generateCouponCode);
});
</script>
@endpush
