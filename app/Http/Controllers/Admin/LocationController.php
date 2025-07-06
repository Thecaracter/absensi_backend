<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use App\Models\LocationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    // Main index page - tampilkan semua lokasi + settings
    public function index()
    {
        $locations = OfficeLocation::orderBy('sort_order')->get();
        $settings = LocationSetting::orderBy('sort_order')->get();

        return view('admin.location', compact('locations', 'settings'));
    }

    // Store new office location
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:1|max:5000',
            'type' => 'required|in:main,branch',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Data tidak valid. Silakan periksa kembali.');
        }

        try {
            // Auto set sort_order jika tidak ada
            if (!$request->sort_order) {
                $request->merge(['sort_order' => OfficeLocation::count() + 1]);
            }

            OfficeLocation::create($request->all());

            return redirect()->back()->with('success', 'Lokasi kantor berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan lokasi: ' . $e->getMessage());
        }
    }

    // Update office location
    public function update(Request $request, OfficeLocation $location)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:1|max:5000',
            'type' => 'required|in:main,branch',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Data tidak valid. Silakan periksa kembali.');
        }

        try {
            $location->update($request->all());

            return redirect()->back()->with('success', 'Lokasi kantor berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui lokasi: ' . $e->getMessage());
        }
    }

    // Delete office location
    public function destroy(OfficeLocation $location)
    {
        try {
            $location->delete();

            return redirect()->back()->with('success', 'Lokasi kantor berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus lokasi: ' . $e->getMessage());
        }
    }

    // Bulk action untuk locations
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $selectedIds = $request->input('selected_ids', []);

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'Tidak ada lokasi yang dipilih.');
        }

        try {
            switch ($action) {
                case 'activate':
                    OfficeLocation::whereIn('id', $selectedIds)->update(['is_active' => true]);
                    $message = count($selectedIds) . ' lokasi berhasil diaktifkan.';
                    break;

                case 'deactivate':
                    OfficeLocation::whereIn('id', $selectedIds)->update(['is_active' => false]);
                    $message = count($selectedIds) . ' lokasi berhasil dinonaktifkan.';
                    break;

                case 'delete':
                    OfficeLocation::whereIn('id', $selectedIds)->delete();
                    $message = count($selectedIds) . ' lokasi berhasil dihapus.';
                    break;

                default:
                    return redirect()->back()->with('error', 'Aksi tidak valid.');
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal melakukan aksi: ' . $e->getMessage());
        }
    }

    // Update location settings
    public function updateSettings(Request $request)
    {
        $settings = $request->input('settings', []);

        if (empty($settings)) {
            return redirect()->back()->with('error', 'Tidak ada pengaturan yang diubah.');
        }

        try {
            foreach ($settings as $key => $value) {
                $setting = LocationSetting::where('key', $key)->first();
                if ($setting) {
                    // Cast value berdasarkan type
                    if ($setting->type === 'boolean') {
                        $value = $request->has("settings.{$key}") ? '1' : '0';
                    }

                    $setting->update(['value' => $value]);
                }
            }

            return redirect()->back()->with('success', 'Pengaturan lokasi berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    // Get location data for JSON response (untuk modal edit)
    public function getLocationJson(OfficeLocation $location)
    {
        return response()->json([
            'success' => true,
            'data' => $location
        ]);
    }

    // Test location configuration - untuk debugging
    public function testConfiguration()
    {
        try {
            $locations = OfficeLocation::active()->get();
            $settings = LocationSetting::all()->pluck('value', 'key');

            $response = [
                'office_location' => OfficeLocation::active()->main()->first(),
                'branch_locations' => OfficeLocation::active()->branch()->get(),
                'settings' => [
                    'enable_location_validation' => (bool) ($settings['enable_location_validation'] ?? true),
                    'default_radius_meters' => (int) ($settings['default_radius_meters'] ?? 800),
                    'allow_manual_location' => (bool) ($settings['allow_manual_location'] ?? false),
                    'strict_mode' => (bool) ($settings['strict_mode'] ?? true),
                    'auto_tracking_enabled' => (bool) ($settings['auto_tracking_enabled'] ?? true),
                    'location_update_interval_seconds' => (int) ($settings['location_update_interval_seconds'] ?? 15),
                    'accuracy_threshold_meters' => (int) ($settings['accuracy_threshold_meters'] ?? 50),
                ],
                'total_locations' => $locations->count(),
                'active_locations' => $locations->where('is_active', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi lokasi berhasil dimuat',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat konfigurasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Import locations dari CSV (bonus feature)
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            $header = array_shift($csvData);

            $imported = 0;
            foreach ($csvData as $row) {
                $data = array_combine($header, $row);

                // Validate required fields
                if (empty($data['name']) || empty($data['latitude']) || empty($data['longitude'])) {
                    continue;
                }

                OfficeLocation::create([
                    'name' => $data['name'],
                    'address' => $data['address'] ?? '',
                    'latitude' => (float) $data['latitude'],
                    'longitude' => (float) $data['longitude'],
                    'radius_meters' => (int) ($data['radius_meters'] ?? 800),
                    'type' => $data['type'] ?? 'branch',
                    'description' => $data['description'] ?? '',
                    'is_active' => ($data['is_active'] ?? 'true') === 'true',
                    'sort_order' => OfficeLocation::count() + 1,
                ]);

                $imported++;
            }

            return redirect()->back()->with('success', "{$imported} lokasi berhasil diimpor.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    // Export locations ke CSV
    public function export(Request $request)
    {
        try {
            $locations = OfficeLocation::orderBy('sort_order')->get();

            $filename = 'office_locations_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($locations) {
                $file = fopen('php://output', 'w');

                // Header CSV
                fputcsv($file, [
                    'ID',
                    'Name',
                    'Address',
                    'Latitude',
                    'Longitude',
                    'Radius (m)',
                    'Type',
                    'Description',
                    'Active',
                    'Sort Order'
                ]);

                // Data
                foreach ($locations as $location) {
                    fputcsv($file, [
                        $location->id,
                        $location->name,
                        $location->address,
                        $location->latitude,
                        $location->longitude,
                        $location->radius_meters,
                        $location->type,
                        $location->description,
                        $location->is_active ? 'true' : 'false',
                        $location->sort_order,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }
}