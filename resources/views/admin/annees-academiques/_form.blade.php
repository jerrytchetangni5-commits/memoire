<form class="admin-form" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-form-field">
        <label class="admin-form-label" for="libelle">Année académique</label>
        <input
            class="form-control admin-form-control @error('libelle') is-invalid @enderror"
            id="libelle"
            name="libelle"
            type="text"
            maxlength="20"
            placeholder="2025-2026"
            value="{{ old('libelle', $anneeAcademique->libelle ?? '') }}"
            aria-describedby="libelle-help"
            required
        >
        <div class="admin-form-help" id="libelle-help">Format attendu : AAAA-AAAA (ex. 2025-2026).</div>
        @error('libelle')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="admin-form-actions">
        <button class="admin-primary-link" type="submit">{{ $submitLabel }} <i class="bi bi-arrow-right"></i></button>
        <a class="admin-secondary-link" href="{{ route('admin.annees-academiques.index') }}">Annuler</a>
    </div>
</form>
