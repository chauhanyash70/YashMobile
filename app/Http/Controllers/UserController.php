<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Traits\Traits;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\ChangePasswordRequest;
use Exception;

class UserController extends Controller
{
    /**
	 * Show the User profile.
	 *
	 * @return \Illuminate\View\View
	 */
	public function profile()
	{
		return view('profile.show', [
			'title' => "Profile",
			'breadcrumb' => array()
		]);
	}

    /**
     * Update the User profile.
     */
    public function updateProfile(ProfileUpdateRequest $request)
    {
        try {
            $requestArray = $request->safe()->all();
            if($request->hasFile('profile_image')) {
                $profile = $request->file('profile_image');
                $profileUrl = Traits::uploadFile($profile, 'profile');
                $requestArray['profile_image'] = $profileUrl;
            }
            $user = User::find(Auth::user()->id);
            $user->update($requestArray);
    
            return redirect()->back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Change the User password.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $requestArray = $request->safe()->all();
            $user = User::find(Auth::user()->id);
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->with('error', 'Current password is incorrect.');
            }

            $user->update([
                'password' => Hash::make($requestArray['new_password']),
            ]);
            auth()->guard('user')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect(route('login'))->with('success', 'Password changed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
	 * Check current password is valid or not.
	 */
	public function checkCurrentPassword(Request $request)
	{
		try {
			$user = User::find(Auth::user()->id);
			if (!Hash::check($request->current_password, $user->password)) {
				return response()->json("Current password is incorrect.");
			} else {
				return response()->json(true);
			}
		} catch (\Exception $e) {
			return response()->json($e->getMessage());
		}
	}
}
