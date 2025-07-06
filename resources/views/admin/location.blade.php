@extends('layouts.app')

@section('title', 'Manajemen Lokasi GPS')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Lokasi GPS</h1>
                <p class="text-gray-600 mt-1">Kelola lokasi kantor dan pengaturan GPS untuk absensi Flutter</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <button onclick="openAddLocationModal()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Lokasi
                </button>
                <button onclick="openSettingsModal()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Pengaturan GPS
                </button>
            </div>
        </div>
    </div>

    <!-- Location List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Lokasi Kantor</h2>
                <div class="flex space-x-2">
                    <form action="{{ route('admin.location.export') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md text-sm">
                            Export CSV
                        </button>
                    </form>
                    <button onclick="openBulkActionModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-md text-sm">
                        Aksi Massal
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll()" class="rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama & Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Radius</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($locations as $location)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="selected_locations[]" value="{{ $location->id }}" class="location-checkbox rounded">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($location->type === 'main')
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $location->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($location->address, 50) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>{{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $location->radius_meters }}m
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($location->type === 'main')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Kantor Pusat
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Cabang
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($location->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button onclick="editLocation({{ $location->id }})" 
                                        class="text-blue-600 hover:text-blue-900 font-medium">
                                    Edit
                                </button>
                                <form action="{{ route('admin.location.destroy', $location) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Yakin ingin menghapus lokasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="text-lg font-medium">Belum ada lokasi kantor</p>
                                <p class="text-sm">Tambahkan lokasi kantor pertama untuk memulai</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Location -->
<div id="locationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form id="locationForm" method="POST">
                @csrf
                <div id="methodField"></div>
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Lokasi</h3>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                        <input type="text" name="name" id="location_name" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="address" id="location_address" rows="3" required 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                            <input type="number" name="latitude" id="location_latitude" step="any" required 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                            <input type="number" name="longitude" id="location_longitude" step="any" required 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Radius (meter)</label>
                            <input type="number" name="radius_meters" id="location_radius" min="1" max="5000" required 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <select name="type" id="location_type" required 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="branch">Cabang</option>
                                <option value="main">Kantor Pusat</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" id="location_description" rows="2" 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="location_active" value="1" checked 
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="location_active" class="ml-2 text-sm text-gray-700">Aktif</label>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                    <button type="button" onclick="closeLocationModal()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Settings -->
<div id="settingsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <form action="{{ route('admin.location.settings.update') }}" method="POST">
                @csrf
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Pengaturan GPS & Lokasi</h3>
                </div>
                
                <div class="px-6 py-4 space-y-6">
                    @foreach($settings as $setting)
                    <div class="flex items-center justify-between py-2">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">{{ $setting->label }}</label>
                            @if($setting->description)
                                <p class="text-xs text-gray-500 mt-1">{{ $setting->description }}</p>
                            @endif
                        </div>
                        <div class="ml-4">
                            @if($setting->type === 'boolean')
                                <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" 
                                       {{ $setting->value ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            @elseif($setting->type === 'integer')
                                <input type="number" name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value }}" min="1" max="9999"
                                       class="w-20 border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" 
                                       value="{{ $setting->value }}"
                                       class="w-32 border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                    <button type="button" onclick="closeSettingsModal()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bulk Action -->
<div id="bulkActionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form action="{{ route('admin.location.bulk-action') }}" method="POST">
                @csrf
                <input type="hidden" name="selected_ids" id="bulkSelectedIds">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Aksi Massal</h3>
                </div>
                
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Aksi</label>
                    <select name="action" required class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">-- Pilih Aksi --</option>
                        <option value="activate">Aktifkan</option>
                        <option value="deactivate">Nonaktifkan</option>
                        <option value="delete">Hapus</option>
                    </select>
                    <p id="selectedCount" class="text-sm text-gray-500 mt-2"></p>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                    <button type="button" onclick="closeBulkActionModal()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md font-medium">
                        Jalankan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Location data from PHP
const locations = @json($locations);

// Modal functions
function openAddLocationModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Lokasi';
    document.getElementById('locationForm').action = '{{ route("admin.location.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('locationForm').reset();
    document.getElementById('location_active').checked = true;
    document.getElementById('locationModal').classList.remove('hidden');
}

function editLocation(locationId) {
    const location = locations.find(l => l.id === locationId);
    if (!location) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Lokasi';
    document.getElementById('locationForm').action = `/admin/location/${locationId}`;
    document.getElementById('methodField').innerHTML = '@method("PUT")';
    
    document.getElementById('location_name').value = location.name;
    document.getElementById('location_address').value = location.address;
    document.getElementById('location_latitude').value = location.latitude;
    document.getElementById('location_longitude').value = location.longitude;
    document.getElementById('location_radius').value = location.radius_meters;
    document.getElementById('location_type').value = location.type;
    document.getElementById('location_description').value = location.description || '';
    document.getElementById('location_active').checked = location.is_active;
    
    document.getElementById('locationModal').classList.remove('hidden');
}

function closeLocationModal() {
    document.getElementById('locationModal').classList.add('hidden');
}

function openSettingsModal() {
    document.getElementById('settingsModal').classList.remove('hidden');
}

function closeSettingsModal() {
    document.getElementById('settingsModal').classList.add('hidden');
}

// Bulk action functions
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.location-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function openBulkActionModal() {
    const checkedBoxes = document.querySelectorAll('.location-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu lokasi terlebih dahulu');
        return;
    }
    
    const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
    document.getElementById('bulkSelectedIds').value = JSON.stringify(selectedIds);
    document.getElementById('selectedCount').textContent = `${selectedIds.length} lokasi dipilih`;
    document.getElementById('bulkActionModal').classList.remove('hidden');
}

function closeBulkActionModal() {
    document.getElementById('bulkActionModal').classList.add('hidden');
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    const modals = ['locationModal', 'settingsModal', 'bulkActionModal'];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});

// Close modals with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLocationModal();
        closeSettingsModal();
        closeBulkActionModal();
    }
});
</script>
@endpush