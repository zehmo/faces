{{-- Shared student form partial --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Registration Number <span class="text-danger">*</span></label>
        <input type="text" name="reg_number" class="form-control @error('reg_number') is-invalid @enderror" value="{{ old('reg_number', $student->reg_number ?? '') }}" required>
        @error('reg_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">JAMB Reg Number</label>
        <input type="text" name="jamb_reg_number" class="form-control" value="{{ old('jamb_reg_number', $student->jamb_reg_number ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $student->full_name ?? '') }}" required>
        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', isset($student) && $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Sex <span class="text-danger">*</span></label>
        <select name="sex" class="form-select" required>
            @foreach(['Male','Female'] as $s)
                <option value="{{ $s }}" {{ old('sex', $student->sex ?? '') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Marital Status</label>
        <select name="marital_status" class="form-select">
            @foreach(['Single','Married'] as $m)
                <option value="{{ $m }}" {{ old('marital_status', $student->marital_status ?? 'Single') == $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $student->phone_number ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}">
    </div>

    {{-- Cascading dropdowns --}}
    <div class="col-md-4">
        <label class="form-label">State of Origin</label>
        <select name="state_id" id="stateSelect" class="form-select">
            <option value="">— Select State —</option>
            @foreach($states as $state)
                <option value="{{ $state->id }}" {{ old('state_id', $student->state_id ?? '') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">L.G.A.</label>
        <select name="lga_id" id="lgaSelect" class="form-select">
            <option value="">— Select LGA —</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Town</label>
        <select name="town_id" id="townSelect" class="form-select">
            <option value="">— Select Town —</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select">
            <option value="">— Select —</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ old('department_id', $student->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Level <span class="text-danger">*</span></label>
        <select name="level" class="form-select" required>
            @foreach(['100','200','300','400'] as $lvl)
                <option value="{{ $lvl }}" {{ old('level', $student->level ?? '100') == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Passport Photo (JPEG/PNG, max 10MB)</label>
        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/jpeg,image/png">
        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if(isset($student) && $student->photo_filename)
            <div class="mt-2"><img src="{{ asset('storage/photos/thumbs/' . $student->photo_filename) }}" class="photo-thumb" alt="Current photo"> <small class="text-muted">Current photo</small></div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('stateSelect');
    const lgaSelect = document.getElementById('lgaSelect');
    const townSelect = document.getElementById('townSelect');
    const oldLga = '{{ old("lga_id", $student->lga_id ?? "") }}';
    const oldTown = '{{ old("town_id", $student->town_id ?? "") }}';

    function loadLgas(stateId, selectedLga) {
        lgaSelect.innerHTML = '<option value="">— Select LGA —</option>';
        townSelect.innerHTML = '<option value="">— Select Town —</option>';
        if (!stateId) return;
        fetch('{{ url("admin/api/lgas") }}/' + stateId)
            .then(r => r.json())
            .then(data => {
                data.forEach(lga => {
                    const opt = new Option(lga.name, lga.id, false, lga.id == selectedLga);
                    lgaSelect.add(opt);
                });
                if (selectedLga) loadTowns(selectedLga, oldTown);
            });
    }

    function loadTowns(lgaId, selectedTown) {
        townSelect.innerHTML = '<option value="">— Select Town —</option>';
        if (!lgaId) return;
        fetch('{{ url("admin/api/towns") }}/' + lgaId)
            .then(r => r.json())
            .then(data => {
                data.forEach(town => {
                    const opt = new Option(town.name, town.id, false, town.id == selectedTown);
                    townSelect.add(opt);
                });
            });
    }

    stateSelect.addEventListener('change', () => loadLgas(stateSelect.value, ''));
    lgaSelect.addEventListener('change', () => loadTowns(lgaSelect.value, ''));

    // Pre-load on edit
    if (stateSelect.value) loadLgas(stateSelect.value, oldLga);
});
</script>
@endpush
