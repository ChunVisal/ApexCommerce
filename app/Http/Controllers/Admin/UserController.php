<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashierStock;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\StockActivity;
use App\Models\User;
use App\Services\Admin\ActivityService;
use App\Services\Admin\UserService;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use HandlesImageUploads;
    /* =========================================================================
     | 1. PAGE VIEWS
     | ========================================================================= */

    public function index(Request $request)
    {
        $summaryCards = UserService::getSummaryCards();
        $users = UserService::getUsers();

        if ($request->ajax()) {
            return response()->json(['users' => $users]);
        }

        return view('admin.users.index', compact('users', 'summaryCards'));
    }

    /* =========================================================================
     | 2. CRUD OPERATIONS
     | ========================================================================= */

    public function store(Request $request)
    {
        $request->validate([
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

        $employeeId = $request->role === 'cashier' ? $this->generateEmployeeId() : null;
        $imageUrl = $this->handleAvatarImage($request);

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

        ActivityService::log(
            'user_created',
            'Created user: ' . $request->name . ' (' . $request->role . ')',
            'Users',
            'success'
        );

        return response()->json(['success' => true, 'message' => 'User created']);
    }

    public function update(Request $request, int $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                // add email, id to verify if user edit themeself keep same eamil skip, if change emial must different from others 
                'email' => 'required|email|unique:users,email,' . $id,
                'role' => 'required|in:admin,cashier',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'shift' => 'nullable|string',
                'pin' => 'nullable|digits:4',
                'hire_date' => 'nullable|date',
                'salary' => 'nullable|numeric',
            ]);

            $imageUrl = $this->handleAvatarImage($request, $user->avatar, 'avatar_file', 'avatar_url');

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'phone' => $request->phone,
                'address' => $request->address,
                'shift' => $request->shift,
                'pin' => $request->pin,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'avatar' => $imageUrl,
                // if ? change pass make new hash else : current pass
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            ActivityService::log(
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
        // if current ? active set to inactive if already inactive : active
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        ActivityService::log(
            'user_status_changed',
            "Toggled user {$user->name} status to {$user->status}",
            'Users',
            'info'
        );

        return response()->json(['status' => $user->status]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->name;

        // Relation dependency guards
        if (Order::where('cashier_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has sales history'], 422);
        }

        if (CashierStock::where('cashier_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has stock allocations'], 422);
        }

        if (StockActivity::where('cashier_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has stock requests'], 422);
        }

        if (StockMovement::where('user_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has stock movement history'], 422);
        }

        if (ActivityLog::where('user_id', $id)->exists()) {
            return response()->json(['error' => 'Cannot delete: user has activity history'], 422);
        }

        $user->delete();

        ActivityService::log('user_deleted', 'Deleted user: ' . $userName, 'Users', 'warning');

        return response()->json(['message' => 'User deleted']);
    }

    /* =========================================================================
     | 3. BULK ACTIONS
     | ========================================================================= */

    public function bulkDeactivate(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        User::whereIn('id', $request->ids)->update(['status' => 'inactive']);

        ActivityService::log('users_bulk_deactivated', 'Bulk deactivated selected users', 'Users', 'warning');

        return response()->json(['message' => 'Users deactivated']);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        User::whereIn('id', $request->ids)->delete();

        ActivityService::log('users_bulk_deleted', 'Bulk deleted selected users', 'Users', 'danger');

        return response()->json(['message' => 'Users deleted']);
    }

    private function generateEmployeeId(): string
    {
        $lastEmp = User::where('employee_id', 'like', 'EMP-%')
            ->orderBy('employee_id', 'desc')
            ->first();

        $nextNum = $lastEmp
            ? (intval(substr($lastEmp->employee_id, 4)) + 1)
            : 1;

        return 'EMP-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
}
