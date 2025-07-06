<?php

namespace App\Models;

use App\Contracts\Ownable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class TemplateSigner extends Model implements Ownable
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'name',
        'description',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function templateFields(): HasMany
    {
        return $this->hasMany(TemplateField::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->template->isOwnedBy($user);
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->template->isViewableBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('template', function (Builder $query) use ($user) {
            $query->ownedBy($user);
        });
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('template', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create template signers for templates they own
        $template = Template::find($attributes['template_id']);
        return $template && $template->isOwnedBy($user);
    }
} 