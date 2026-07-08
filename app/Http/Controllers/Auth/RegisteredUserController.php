<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function checkCompanyName(Request $request)
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
        ], [
            'company_name.required' => 'Completează numele parcului auto.',
            'company_name.max' => 'Numele parcului auto poate avea maximum 255 de caractere.',
        ]);

        $companyName = trim((string) $request->input('company_name'));
        $exists = User::query()
            ->where('company_name', $companyName)
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'suggestions' => $exists ? $this->companyNameSuggestions($companyName) : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_type' => ['required', 'in:individual,dealer'],

            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // pentru parc auto
            'company_name' => ['nullable', 'string', 'max:255', 'required_if:user_type,dealer', Rule::unique('users', 'company_name')],
            'cui' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:32', 'required_if:user_type,dealer'],
            'county' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'company_name.required_if' => 'Completează numele parcului auto.',
            'company_name.unique' => 'Numele parcului auto este deja folosit.',
            'company_name.max' => 'Numele parcului auto poate avea maximum 255 de caractere.',
            'phone.required_if' => 'Completează numărul de telefon al parcului auto.',
        ]);

        $user = User::create([
            'user_type' => $request->user_type,

            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

            // dealer fields (vor fi null la persoane fizice)
            'company_name' => $request->user_type === 'dealer' ? $request->company_name : null,
            'cui' => $request->user_type === 'dealer' ? $request->cui : null,
            'phone' => $request->user_type === 'dealer' ? $request->phone : null,
            'county' => $request->user_type === 'dealer' ? $request->county : null,
            'city' => $request->user_type === 'dealer' ? $request->city : null,
            'address' => $request->user_type === 'dealer' ? $request->address : null,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('services.index');
    }

    private function companyNameSuggestions(string $companyName): array
    {
        $base = preg_replace('/\s+/', ' ', trim($companyName));

        return [
            $base . ' Auto',
            $base . ' Group',
            $base . ' SRL',
            $base . ' Premium',
            $base . ' ' . rand(1, 99),
        ];
    }
}
