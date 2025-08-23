<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SystemSettingController extends Controller
{
    /** List + filters */
    // app/Http/Controllers/SystemSettingController.php

    public function index(Request $request)
    {
        // If the front-end asks for JSON (our AJAX table), return a flat array.

        // First render: load the Blade shell; JS will fetch data via AJAX.
        return view('system_settings.index', [
            'types' => SystemSetting::TYPES,
        ]);
    }

    // Show the form for creating a new setting.
    public function data(Request $request)
    {
        // If the front-end asks for JSON (our AJAX table), return a flat array.

            $q        = $request->input('q');
            $type     = $request->input('data_type');
            $isPublic = $request->input('is_public'); // 0/1

            $settings = SystemSetting::query()
                ->when($q, fn($qb) => $qb->where(function ($qq) use ($q) {
                    $qq->where('key', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
                }))
                ->when($type, fn($qb) => $qb->where('data_type', $type))
                ->when($request->filled('is_public'), fn($qb) => $qb->where('is_public', $request->boolean('is_public')))
                ->orderBy('key')
                ->get(['id','key','data_type','is_public','description','value','created_at','updated_at']);

            $payload = $settings->map(function (SystemSetting $s) {
                $val = $s->value;
                if (is_array($val)) {
                    $valueText = implode(', ', array_map(
                        fn($x) => is_scalar($x) ? (string) $x : json_encode($x, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                        $val
                    ));
                    $valueJson = json_encode($val, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                } elseif (is_bool($val)) {
                    $valueText = $val ? 'true' : 'false';
                    $valueJson = $valueText;
                } else {
                    $valueText = (string) $val;
                    $valueJson = $valueText;
                }

                return [
                    'id'          => $s->id,
                    'key'         => $s->key,
                    'data_type'   => $s->data_type,
                    'is_public'   => (bool) $s->is_public,
                    'description' => $s->description,
                    'value_text'  => $valueText,
                    'value_json'  => $valueJson,
                    'created_at'  => optional($s->created_at)->toDateTimeString(),
                    'updated_at'  => optional($s->updated_at)->toDateTimeString(),
                ];
            });

            return response()->json($payload);
       
    }
    public function create()
    {
        return view('system_settings.create', [
            'types' => SystemSetting::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // Build the setting and coerce the value to the right PHP type
        $setting = new SystemSetting([
            'key'         => $data['key'],
            'data_type'   => $data['data_type'],
            'description' => $data['description'] ?? null,
            'is_public'   => $request->boolean('is_public'),
        ]);

        $setting->value = $this->coerceValue($request, $setting->data_type);
        $setting->save();

        return redirect()->route('system-settings.index',)
            ->with('success', 'Setting created successfully.');
    }

    public function show(SystemSetting $system_setting)
    {
        return view('system_settings.show', ['setting' => $system_setting]);
    }

    public function edit(SystemSetting $system_setting)
    {
        return view('system_settings.edit', [
            'setting' => $system_setting,
            'types'   => SystemSetting::TYPES,
        ]);
    }

    public function update(Request $request, SystemSetting $system_setting)
    {
        $data = $this->validated($request, updating: true, currentId: $system_setting->id);

        $system_setting->key         = $data['key'];
        $system_setting->data_type   = $data['data_type'];            // set type first
        $system_setting->description = $data['description'] ?? null;
        $system_setting->is_public   = $request->boolean('is_public');

        $system_setting->value = $this->coerceValue($request, $system_setting->data_type);
        $system_setting->save();

        return redirect()->route('system-settings.index')
            ->with('success', 'Setting updated.');
    }

    public function destroy(SystemSetting $system_setting)
    {
        $system_setting->delete();
        return redirect()->route('system-settings.index')
            ->with('success', 'Setting deleted.');
    }

    /** Quick toggle public/private */
    public function togglePublic(SystemSetting $system_setting)
    {
        $system_setting->update(['is_public' => ! (bool) $system_setting->is_public]);
        return back()->with('success', 'Visibility updated.');
    }

    /* ================= helpers ================= */

    private function validated(Request $request, bool $updating = false, ?int $currentId = null): array
    {
        return $request->validate([
            'key'       => ['required','string','max:100', Rule::unique('system_settings', 'key')->ignore($currentId)],
            'data_type' => ['required', Rule::in(SystemSetting::TYPES)],
            // generic “value” input is required; the controller coerces it per data_type
            'value'     => ['required'],
            'description' => ['nullable','string'],
            'is_public' => ['sometimes','boolean'],
        ]);
    }

    /**
     * Coerce request input to the right PHP type before saving.
     * Supports:
     *  - string: value
     *  - integer: value or value_int
     *  - boolean: checkbox value_bool or value ("true"/"1")
     *  - json: value_json or value (JSON string or comma/newline list -> array)
     */
    private function coerceValue(Request $request, string $type)
    {
        $raw = $request->input('value');

        return match ($type) {
            SystemSetting::TYPE_INTEGER => (int) ($request->input('value_int', $raw)),
            SystemSetting::TYPE_BOOLEAN => $request->boolean('value_bool', $this->stringToBool($raw)),
            SystemSetting::TYPE_JSON    => $this->normalizeJson($request->input('value_json', $raw)),
            default                     => (string) $raw,
        };
    }

    private function stringToBool($v): bool
    {
        if (is_bool($v)) return $v;
        return in_array(strtolower((string) $v), ['1','true','on','yes'], true);
    }

    /** JSON string | array | "a,b,c" | multi-line -> array (never null; returns [] if empty/invalid) */
    private function normalizeJson($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn($x) => $x !== '' && $x !== null));
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // fallback: comma/newline separated list to array of strings
            $parts = preg_split('/[\r\n,]+/', trim($value));
            $parts = array_values(array_filter(array_map('trim', (array) $parts), fn($x) => $x !== ''));
            return $parts;
        }

        return [];
    }
}
