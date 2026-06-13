@props(['for' => 'form'])

@error($for)
    <div {{ $attributes->merge(['class' => 'form-error-banner']) }} role="alert">{{ $message }}</div>
@enderror
