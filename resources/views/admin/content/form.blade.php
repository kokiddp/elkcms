{{-- Dynamic form fields based on content model metadata --}}

@foreach($metadata['fields'] as $fieldName => $field)
    <div class="mb-3">
        <label for="{{ $fieldName }}" class="form-label">
            {{ $field['label'] }}
            @if($field['required'])
                <span class="text-danger">*</span>
            @endif
        </label>

        @if($field['type'] === 'string')
            <input type="text"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   value="{{ old($fieldName, $content->$fieldName ?? '') }}"
                   {{ $field['required'] ? 'required' : '' }}
                   @if($field['maxLength'])
                       maxlength="{{ $field['maxLength'] }}"
                   @endif>

        @elseif($field['type'] === 'text')
            <textarea name="{{ $fieldName }}"
                      id="{{ $fieldName }}"
                      class="form-control @error($fieldName) is-invalid @enderror"
                      rows="6"
                      {{ $field['required'] ? 'required' : '' }}>{{ old($fieldName, $content->$fieldName ?? '') }}</textarea>

        @elseif($field['type'] === 'integer' || $field['type'] === 'number')
            <input type="number"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   value="{{ old($fieldName, $content->$fieldName ?? '') }}"
                   {{ $field['required'] ? 'required' : '' }}>

        @elseif($field['type'] === 'boolean')
            <div class="form-check form-switch">
                <input type="checkbox"
                       name="{{ $fieldName }}"
                       id="{{ $fieldName }}"
                       class="form-check-input @error($fieldName) is-invalid @enderror"
                       value="1"
                       {{ old($fieldName, $content->$fieldName ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $fieldName }}">
                    {{ $field['label'] }}
                </label>
            </div>

        @elseif($field['type'] === 'date')
            <input type="date"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   value="{{ old($fieldName, $content->$fieldName ? $content->$fieldName->format('Y-m-d') : '') }}"
                   {{ $field['required'] ? 'required' : '' }}>

        @elseif($field['type'] === 'datetime')
            <input type="datetime-local"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   value="{{ old($fieldName, $content->$fieldName ? $content->$fieldName->format('Y-m-d\TH:i') : '') }}"
                   {{ $field['required'] ? 'required' : '' }}>

        @elseif($field['type'] === 'image')
            <input type="file"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   accept="image/*">
            @if(isset($content->$fieldName) && $content->$fieldName)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $content->$fieldName) }}"
                         alt="{{ $field['label'] }}"
                         class="img-thumbnail"
                         style="max-width: 200px;">
                </div>
            @endif

        @elseif($field['type'] === 'email')
            <input type="email"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   value="{{ old($fieldName, $content->$fieldName ?? '') }}"
                   {{ $field['required'] ? 'required' : '' }}>

        @elseif($field['type'] === 'url')
            <input type="url"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   value="{{ old($fieldName, $content->$fieldName ?? '') }}"
                   {{ $field['required'] ? 'required' : '' }}>

        @else
            {{-- Default to text input for unknown types --}}
            <input type="text"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="form-control @error($fieldName) is-invalid @enderror"
                   value="{{ old($fieldName, $content->$fieldName ?? '') }}"
                   {{ $field['required'] ? 'required' : '' }}>
        @endif

        @error($fieldName)
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        @if($field['helpText'])
            <small class="form-text text-muted">{{ $field['helpText'] }}</small>
        @endif
    </div>
@endforeach
