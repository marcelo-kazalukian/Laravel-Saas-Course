<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string|null $stripe_id
 * @property-read string|null $pm_type
 * @property-read string|null $pm_last_four
 * @property-read Carbon|null $trial_ends_at
 */
class Organization extends Model
{
    use Billable;

    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    public function getCurrentPlan(): string
    {
        if ($this->subscribed('default')) {
            $subscription = $this->subscription('default');
            $priceId = $subscription->stripe_price;

            return match ($priceId) {
                config('subscriptions.plans.pro.prices.monthly'),
                config('subscriptions.plans.pro.prices.yearly') => 'pro',
                config('subscriptions.plans.ultimate.prices.monthly'),
                config('subscriptions.plans.ultimate.prices.yearly') => 'ultimate',
                default => 'free',
            };
        }

        return 'free';
    }

    public function tasksCount(): int
    {
        return $this->tasks()->count();
    }

    public function projectsCount(): int
    {
        return $this->projects()->count();
    }

    public function getTaskLimit(): ?int
    {
        $plan = $this->getCurrentPlan();

        return config("subscriptions.plans.{$plan}.task_limit");
    }

    public function canCreateTask(): bool
    {
        $limit = $this->getTaskLimit();

        if ($limit === null) {
            return true;
        }

        return $this->tasksCount() < $limit;
    }

    /**
     * Pending: See canCreateTask method for reference
     */
    public function canCreateLocation(): bool
    {
        return true;
    }

    public function canAccessProjects(): bool
    {
        $plan = $this->getCurrentPlan();

        return config("subscriptions.plans.{$plan}.projects_enabled", false);
    }
}
