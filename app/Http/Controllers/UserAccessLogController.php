<?php

namespace App\Http\Controllers;

use App\Models\UserAccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserAccessLogController extends Controller
{
    /** List + filters */
    public function index(Request $request)
    {
        // If the front-end asks for JSON (our AJAX table), return a flat array
        if ($request->ajax() || $request->wantsJson()) {
            $userId = $request->input('user_id');
            $from   = $request->input('from'); // yyyy-mm-dd
            $to     = $request->input('to');   // yyyy-mm-dd
            $q      = $request->input('q');    // search ip/agent/email/name

            $logs = \App\Models\UserAccessLog::query()
                ->with(['user:id,first_name,last_name,email'])
                ->when($userId, fn($qb) => $qb->where('user_id', $userId))
                ->when($from,   fn($qb) => $qb->where('access_time', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
                ->when($to,     fn($qb) => $qb->where('access_time', '<=', \Carbon\Carbon::parse($to)->endOfDay()))
                ->when($q, function ($qb) use ($q) {
                    $qb->where('ip_address', 'like', "%{$q}%")
                    ->orWhere('user_agent', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('email', 'like', "%{$q}%")
                            ->orWhere('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('access_time')
                ->get([
                    'id','user_id','access_time','ip_address','user_agent',
                    'session_duration','pages_visited','actions_performed','created_at','updated_at'
                ]);

            $payload = $logs->map(function (\App\Models\UserAccessLog $log) {
                $name  = trim(($log->user->first_name ?? '').' '.($log->user->last_name ?? ''));
                $email = $log->user->email ?? null;

                // human session duration (minutes/seconds heuristic)
                $dur = (int) ($log->session_duration ?? 0);
                $hours = intdiv($dur, 3600);
                $mins  = intdiv($dur % 3600, 60);
                $secs  = $dur % 60;
                $human = $dur >= 3600 ? sprintf('%dh %dm', $hours, $mins)
                        : ($dur >= 60 ? sprintf('%dm %ds', $mins, $secs)
                                    : sprintf('%ds', $secs));

                // stringify arrays safely
                $pages   = is_array($log->pages_visited) ? implode(', ', array_slice($log->pages_visited, 0, 8)) : (string)($log->pages_visited ?? '');
                $actions = is_array($log->actions_performed) ? implode(', ', array_slice($log->actions_performed, 0, 8)) : (string)($log->actions_performed ?? '');

                return [
                    'id'              => $log->id,
                    'user_name'       => $name !== '' ? $name : '—',
                    'user_email'      => $email,
                    'ip_address'      => $log->ip_address,
                    'user_agent'      => Str::limit((string) $log->user_agent, 120),
                    'session_duration'=> $dur,
                    'session_human'   => $human,
                    'pages_str'       => Str::limit($pages, 120),
                    'actions_str'     => Str::limit($actions, 120),
                    'access_time'     => optional($log->access_time)->format('Y-m-d H:i'),
                    'created_at'      => optional($log->created_at)->toDateTimeString(),
                    'updated_at'      => optional($log->updated_at)->toDateTimeString(),
                ];
            });

            return response()->json($payload);
        }

        // First render: load the Blade shell; JS will fetch data via AJAX.
        $users = \App\Models\User::orderBy('first_name')->get(['id','first_name','last_name','email']);

        return view('user_access_logs.index', compact('users'));
    }


    public function create()
    {
        $users = User::orderBy('first_name')->get(['id','first_name','last_name','email']);
        return view('user_access_logs.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // Defaults from request if omitted
        $data['ip_address']  = $data['ip_address']  ?? $request->ip();
        $data['user_agent']  = $data['user_agent']  ?? $request->userAgent();
        $data['access_time'] = $data['access_time'] ?? now();

        // JSON normalization
        $data['pages_visited']     = $this->normalizeJson($request->input('pages_visited'));
        $data['actions_performed'] = $this->normalizeJson($request->input('actions_performed'));

        $log = UserAccessLog::create($data);

        return redirect()->route('user-access-logs.index')
            ->with('success', 'Access log created.');
    }

    public function show(UserAccessLog $user_access_log)
    {
        $user_access_log->load('user');
        return view('user_access_logs.show', ['log' => $user_access_log]);
    }

    public function edit(UserAccessLog $user_access_log)
    {
        $users = User::orderBy('first_name')->get(['id','first_name','last_name','email']);
        return view('user_access_logs.edit', [
            'log'   => $user_access_log,
            'users' => $users,
        ]);
    }

    public function update(Request $request, UserAccessLog $user_access_log)
    {
        $data = $this->validated($request, updating: true);

        if (!isset($data['ip_address'])) $data['ip_address'] = $user_access_log->ip_address ?? $request->ip();
        if (!isset($data['user_agent'])) $data['user_agent'] = $user_access_log->user_agent ?? $request->userAgent();

        $data['pages_visited']     = $this->normalizeJson($request->input('pages_visited', $user_access_log->pages_visited));
        $data['actions_performed'] = $this->normalizeJson($request->input('actions_performed', $user_access_log->actions_performed));

        $user_access_log->update($data);

        return redirect()->route('user-access-logs.index')
            ->with('success', 'Access log updated.');
    }

    public function destroy(UserAccessLog $user_access_log)
    {
        $user_access_log->delete();
        return redirect()->route('user-access-logs.index')
            ->with('success', 'Access log deleted.');
    }

    /* ---------------- helpers ---------------- */

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'user_id'         => ['required','exists:users,id'],
            'access_time'     => [$updating ? 'nullable' : 'required','date'],
            'ip_address'      => ['nullable','string','max:45'],
            'user_agent'      => ['nullable','string'],
            'session_duration'=> ['nullable','integer','min:0'],
            'pages_visited'   => ['nullable'], // array or JSON string
            'actions_performed'=>['nullable'], // array or JSON string
        ]);
    }

    private function normalizeJson($value)
    {
        if ($value === null || $value === '') return null;
        if (is_array($value)) return $value;

        // Try JSON string
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) return $decoded;

        // Fallback: comma/newline separated list -> array of strings
        $parts = preg_split('/[\r\n,]+/', (string) $value);
        $parts = array_values(array_filter(array_map('trim', $parts)));
        return $parts ?: null;
    }
}
