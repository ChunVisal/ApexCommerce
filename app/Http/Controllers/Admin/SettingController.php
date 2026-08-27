<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Admin\ActivityService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function save(Request $request)
    {
        Setting::set('shop_name', $request->shop_name);
        Setting::set('shop_address', $request->shop_address);
        Setting::set('shop_phone', $request->shop_phone);
        Setting::set('tax_rate', $request->tax_rate);
        Setting::set('vip_discount', $request->vip_discount);

        if ($request->hasFile('logo')) {
            // Delete old logo
            $oldLogo = Setting::get('logo');
            if ($oldLogo) {
                $oldPath = str_replace(asset(''), '', $oldLogo);
                if (file_exists(public_path($oldPath))) {
                    unlink(public_path($oldPath));
                }
            }

            // Save new logo
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            Setting::set('logo', asset('images/' . $filename));
        }

        ActivityService::log(
            'settings_updated',
            'Updated shop settings',
            'Settings',
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting saved successfully',
        ]);
    }
}
