@props(['cancel', 'submit' => 'Simpan', 'disabled' => false])
<div class="form-actions">
    <x-ui.button :href="$cancel" variant="secondary">Batal</x-ui.button>
    <x-ui.button type="submit" :disabled="$disabled">{{ $submit }}</x-ui.button>
</div>
<p class="sr-only" role="status" data-submit-status></p>
