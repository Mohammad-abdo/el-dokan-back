<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'username',
        'phone',
        'email',
        'password',
        'avatar_url',
        'wallet_balance',
        'language_preference',
        'status',
        'role',
        'provider',
        'provider_id',
        'magic_link_token',
        'magic_link_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'magic_link_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
        'magic_link_expires_at' => 'datetime',
    ];

    // Relationships
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(UserWalletTransaction::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function medicationReminders()
    {
        return $this->hasMany(MedicationReminder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function representative()
    {
        return $this->hasOne(Representative::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    /**
     * User type for Figma/API: "user" (customer), "service_provider", or "admin"
     */
    public function getUserTypeAttribute(): string
    {
        $role = $this->role ?? $this->roles->first()?->name ?? null;
        if ($role === 'admin') {
            return 'admin';
        }
        $providerRoles = ['doctor', 'shop', 'driver', 'representative'];
        return $role && in_array($role, $providerRoles) ? 'service_provider' : 'user';
    }

    /**
     * Service provider type: "doctor" | "shop" | "driver" | "representative" | null
     * (null when user_type is "user" or admin)
     */
    public function getServiceProviderTypeAttribute(): ?string
    {
        $role = $this->role ?? $this->roles->first()?->name ?? null;
        $providerTypes = ['doctor', 'shop', 'driver', 'representative'];
        return $role && in_array($role, $providerTypes) ? $role : null;
    }
}