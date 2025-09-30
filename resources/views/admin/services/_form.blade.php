<div class="vstack gap-3">
    <div>
        <label class="form-label" for="name">Nome</label>
        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $service->name) }}" required>
    </div>
    <div>
        <label class="form-label" for="description">Descricao</label>
        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea>
    </div>
    <div class="row g-3">
        <div class="col-sm-4">
            <label class="form-label" for="duration_minutes">Duracao (min)</label>
            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" min="15" max="480" step="15" value="{{ old('duration_minutes', $service->duration_minutes) }}" required>
        </div>
        <div class="col-sm-4">
            <label class="form-label" for="price">Valor (R$)</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="{{ old('price', $service->price) }}" required>
        </div>
        <div class="col-sm-4">
            <label class="form-label">Status</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                <span class="form-check-label">Disponivel</span>
            </div>
        </div>
    </div>
</div>
