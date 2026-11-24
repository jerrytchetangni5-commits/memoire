<form class="admin-form" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-form-field">
        <label class="admin-form-label" for="code">Code de la filière</label>
        <input
            class="form-control admin-form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            type="text"
            maxlength="20"
            value="{{ old('code', $filiere->code ?? '') }}"
            required
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="admin-form-field">
        <label class="admin-form-label" for="nom">Nom de la filière</label>
        <input
            class="form-control admin-form-control @error('nom') is-invalid @enderror"
            id="nom"
            name="nom"
            type="text"
            maxlength="150"
            value="{{ old('nom', $filiere->nom ?? '') }}"
            required
        >
        @error('nom')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="admin-form-actions">
        <button class="admin-primary-link" type="submit">{{ $submitLabel }} <i class="bi bi-arrow-right"></i></button>
        <a class="admin-secondary-link" href="{{ route('admin.filieres.index') }}">Annuler</a>
    </div>
</form>
