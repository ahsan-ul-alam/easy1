<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Support\ImageOptimizer;
use App\Support\SiteSettings;
use App\Support\WhatsAppOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings');
    }

    public function update(UpdateSettingsRequest $request, SiteSettings $settings, ImageOptimizer $optimizer): RedirectResponse
    {
        $values = $request->safe()->except(['logo', 'remove_logo']);
        $values['whatsapp_number'] = WhatsAppOrder::normalizeNumber($values['whatsapp_number'] ?? null);

        $currentLogo = $settings->get('logo_path');

        if ($request->hasFile('logo')) {
            $values['logo_path'] = $optimizer->storeLogo($request->file('logo'));
        } elseif ($request->boolean('remove_logo')) {
            $values['logo_path'] = null;
        }

        $settings->update($values);

        if ($currentLogo && array_key_exists('logo_path', $values)) {
            Storage::disk('public')->delete($currentLogo);
        }

        return redirect()->route('admin.settings.edit')->with('success', __('admin.flash.saved'));
    }
}
