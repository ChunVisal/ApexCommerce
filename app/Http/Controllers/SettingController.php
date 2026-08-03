<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings');
    }

    public function save(Request $request)
    {
        Setting::set('shop_name', $request->shop_name);
        Setting::set('shop_address', $request->shop_address);
        Setting::set('shop_phone', $request->shop_phone);
        Setting::set('tax_rate', $request->tax_rate);

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

        return back()->with('success', 'Settings saved');
    }
}
