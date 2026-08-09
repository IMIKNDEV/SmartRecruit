{{-- Form field wrapper. Usage:
    <x-field label="Email" hint="We will never share it." error="{{ $errors->first('email') }}" required>
        <input class="input" ...>
    </x-field>
--}}
@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'class' => '',
])

<div {{ $attributes->merge(['class' => trim('space-y-2 ' . $class)]) }}>
    @if ($label)
        <label class="block text-sm font-semibold text-dark">
            {{ $label }}
            @if ($required) <span class="text-secondary">*</span> @endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint && !$error)
        <p class="text-xs text-body">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="text-xs font-medium text-danger">{{ $error }}</p>
    @endif
</div>