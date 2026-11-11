<label class="block min-w-0 {{ $wide ?? false ? 'sm:col-span-2' : '' }}">
    <span class="block text-sm font-semibold">
        <span data-field-label @isset($companyLabel) data-company-label="{{ $companyLabel }}" @endisset>{{ $label }}</span>
        @if($required ?? false)
            <span aria-hidden="true" class="text-[#C81424] dark:text-red-300">*</span>
        @endif
    </span>
    @php($isDate = ($type ?? null) === 'date')
    <input type="{{ $isDate ? 'text' : ($type ?? 'text') }}"
           data-contract-field="{{ $field }}"
           @if($isDate) data-contract-date inputmode="numeric" placeholder="ZZ.LL.AAAA" maxlength="10" @endif
           @if(isset($amount)) data-contract-amount inputmode="decimal" @endif
           @if($required ?? false) required @endif
           @isset($hint) aria-describedby="contract-hint-{{ $field }}" @endisset
           class="mt-1 block min-h-11 w-full min-w-0 rounded-xl border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm focus:border-[#C81424] focus:ring-[#C81424] dark:border-gray-700 dark:bg-[#202024] dark:text-gray-100">
    @isset($hint)
        <span id="contract-hint-{{ $field }}" class="mt-1 block text-sm text-gray-600 dark:text-gray-400">{{ $hint }}</span>
    @endisset
</label>
