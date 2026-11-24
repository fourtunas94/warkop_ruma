<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PengaturanApiController extends Controller
{
    protected array $defaultSettings = [
        'store_name'        => 'Warkop Ruma',
        'store_logo'        => '',
        'store_favicon'     => '',
        'store_description' => 'Sistem pemesanan QR Code untuk warkop Ruma.',
        'store_whatsapp'    => '',
        'store_address'     => '',
        'footer_text'       => 'Powered By Four',
    ];

    public function index()
    {
        $this->ensureDefaultSettings();
        $settings = Setting::getAllSettings();
        return response()->json(['success' => true, 'settings' => $settings]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name'        => 'required|max:100',
            'store_description' => 'nullable|max:255',
            'store_whatsapp'    => 'nullable|max:30',
            'store_address'     => 'nullable|max:500',
            'footer_text'       => 'nullable|max:255',
            'store_logo'        => 'nullable|image|max:2048',
            'store_favicon'     => 'nullable|image|max:1024',
        ]);

        Setting::setValue('store_name',        trim($request->input('store_name', '')));
        Setting::setValue('store_description', trim($request->input('store_description', '')));
        Setting::setValue('store_whatsapp',    trim($request->input('store_whatsapp', '')));
        Setting::setValue('store_address',     trim($request->input('store_address', '')));
        Setting::setValue('footer_text',       trim($request->input('footer_text', '')));

        if ($request->hasFile('store_logo')) {
            $path = $this->uploadImage($request, 'store_logo', 'store_logo');
            if ($path) {
                Setting::setValue('store_logo', $path);
            }
        }

        if ($request->hasFile('store_favicon')) {
            $path = $this->uploadImage($request, 'store_favicon', 'store_favicon');
            if ($path) {
                Setting::setValue('store_favicon', $path);
            }
        }

        return response()->json(['success' => true, 'message' => 'Pengaturan berhasil diperbarui.']);
    }

    private function ensureDefaultSettings(): void
    {
        foreach ($this->defaultSettings as $key => $value) {
            if (!Setting::where('setting_key', $key)->exists()) {
                Setting::create(['setting_key' => $key, 'setting_value' => $value]);
            }
        }
    }

    private function uploadImage(Request $request, string $inputName, string $settingKey): ?string
    {
        if (!$request->hasFile($inputName)) {
            return null;
        }
        $file       = $request->file($inputName);
        $uploadPath = public_path('uploads/settings');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }
        $newName = $inputName . '_' . time() . '_' . $file->getClientOriginalName();
        $file->move($uploadPath, $newName);

        $oldPath = Setting::getValue($settingKey);
        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        return 'uploads/settings/' . $newName;
    }
}
