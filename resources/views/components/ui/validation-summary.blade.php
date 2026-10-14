@if($errors->any())
    <x-ui.alert type="error" title="Periksa Kembali Data Anda" class="mb-6">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif
