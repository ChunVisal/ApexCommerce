<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\CashierStock;
use App\Models\StockRequest;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ActivityHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::latest()->get();

        $summaryCards = [
            ['title' => 'Total Users', 'value' => User::count(), 'icon' => 'fa-solid fa-users', 'iconBg' => '#0F6E8C', 'iconColor' => '#0F6E8C', 'trend' => 'up', 'percentage' => '', 'period' => ''],
            ['title' => 'Admins', 'value' => User::where('role', 'admin')->count(), 'icon' => 'fa-solid fa-user-shield', 'iconBg' => '#8B5CF6', 'iconColor' => '#8B5CF6', 'trend' => 'up', 'percentage' => '', 'period' => ''],
            ['title' => 'Cashiers', 'value' => User::where('role', 'cashier')->count(), 'icon' => 'fa-solid fa-cash-register', 'iconBg' => '#10B981', 'iconColor' => '#10B981', 'trend' => 'up', 'percentage' => '', 'period' => ''],
            ['title' => 'Active', 'value' => User::where('status', 'active')->count(), 'icon' => 'fa-solid fa-circle-check', 'iconBg' => '#F59E0B', 'iconColor' => '#D97706', 'trend' => 'up', 'percentage' => '', 'period' => ''],
        ];

        $users = User::query()
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('role', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("FIELD(role, 'admin', 'cashier')")
            ->get();

        if ($request->ajax) {
            return response()->json(['users' => $users]);
        }

        return view('admin.users', compact('users', 'summaryCards'));
    }


    private function uploadToCloudinary($file): string
    {
        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');
        $timestamp = time();
        $signature = sha1("folder=pos/products&timestamp={$timestamp}{$apiSecret}");

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false,   // ← bypass SSL for Laragon
            CURLOPT_SSL_VERIFYHOST => false,   // ← bypass SSL for Laragon
            CURLOPT_POSTFIELDS => [
                'file' => new \CURLFile($file->getRealPath(), $file->getMimeType(), $file->getClientOriginalName()),
                'api_key' => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'folder' => 'pos/products',
            ],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Cloudinary upload failed: {$error}");
        }

        $data = json_decode($response, true);

        if (! isset($data['secure_url'])) {
            throw new \Exception('Cloudinary error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        return $data['secure_url'];
    }

    public function store(Request $request)
    {

        Log::info('Store called', $request->all());
        Log::info('Has avatar_file: ' . ($request->hasFile('avatar_file') ? 'YES' : 'NO'));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,cashier',
            'status' => 'required|in:active,inactive',
            'employee_id' => 'nullable|unique:users',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'shift' => 'nullable|string',
            'pin' => 'nullable|digits:4',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
        ]);

        $employeeId = null;
        if ($request->role === 'cashier') {
            $lastEmp = User::where('employee_id', 'like', 'EMP-%')
                ->orderBy('employee_id', 'desc')
                ->first();

            $nextNum = $lastEmp
                ? (intval(substr($lastEmp->employee_id, 4)) + 1)
                : 1;

            $employeeId = 'EMP-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }


        $imageUrl = null;

        if ($request->hasFile('avatar_file')) {
            $imageUrl = $this->uploadToCloudinary($request->file('avatar_file'), 'pos/avatars');
        } elseif ($request->avatar_url) {
            $imageUrl = $request->avatar_url;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status ?? 'active',
            'avatar' => $imageUrl,
            'employee_id' => $request->role === 'cashier' ? $employeeId : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'shift' => $request->role === 'cashier' ? $request->shift : null,
            'pin' => $request->role === 'cashier' ? $request->pin : null,
            'hire_date' => $request->role === 'cashier' ? $request->hire_date : null,
            'salary' => $request->role === 'cashier' ? $request->salary : null,
        ]);

        ActivityHelper::log(
            'user_created',
            'Created user: ' . $request->name . ' (' . $request->role . ')',
            'Users',
            'success'
        );

        return response()->json(['success' => true, 'message' => 'User created']);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $imageUrl = $user->avatar;

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'role' => 'required|in:admin,cashier',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'shift' => 'nullable|string',
                'pin' => 'nullable|digits:4',
                'hire_date' => 'nullable|date',
                'salary' => 'nullable|numeric',
            ]);

            // update()
            if ($request->hasFile('avatar_file')) {
                $imageUrl = $this->uploadToCloudinary($request->file('avatar_file'), 'pos/avatars');
            } elseif ($request->avatar_url) {
                $imageUrl = $request->avatar_url;
            }

            $data = $request->only([
                'name',
                'email',
                'role',
                'status',
                'phone',
                'address',
                'shift',
                'pin',
                'hire_date',
                'salary'
            ]);
            $data['avatar'] = $imageUrl;

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            ActivityHelper::log(
                'user_updated',
                'Updated user: ' . $request->name,
                'Users',
                'info'
            );

            return response()->json(['success' => true, 'message' => 'User updated']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return response()->json(['status' => $user->status]); // ← JSON not redirect
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->name;

        // Check if user has orders
        if (Order::where('cashier_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has sales history'], 422);
        }

        // Check if user has stock allocations
        if (CashierStock::where('cashier_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has stock allocations'], 422);
        }

        // Check if user has stock requests
        if (StockRequest::where('cashier_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has stock requests'], 422);
        }

        // Check if user has stock movements
        if (StockMovement::where('user_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has stock movement history'], 422);
        }

        // Check if user has activity logs
        if (ActivityLog::where('user_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has activity history'], 422);
        }

        $user->delete();

        ActivityHelper::log('user_deleted', 'Deleted user: ' . $userName, 'Users', 'warning');

        return response()->json(['message' => 'User deleted']);
    }

    // UserController.php

    public function bulkDeactivate(Request $request)
    {
        User::whereIn('id', $request->ids)->update(['status' => 'inactive']);

        return response()->json(['message' => 'Users deactivated']);
    }

    public function bulkDestroy(Request $request)
    {
        User::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Users deleted']);
    }
}
