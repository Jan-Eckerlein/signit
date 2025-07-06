<?php

namespace App\Models;

use App\Contracts\Ownable;
use App\Enums\DocumentFieldType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class TemplateField extends Model implements Ownable
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'template_signer_id',
        'page',
        'x',
        'y',
        'width',
        'height',
        'type',
        'label',
        'description',
        'required',
    ];

    protected $casts = [
        'type' => DocumentFieldType::class,
        'required' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function templateSigner(): BelongsTo
    {
        return $this->belongsTo(TemplateSigner::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->template->isOwnedBy($user ?? Auth::user());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->template->isViewableBy($user ?? Auth::user());
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
        // Users can only create template fields for templates they own
        $template = Template::find($attributes['template_id'] ?? null);
        return $template && $template->isOwnedBy($user);
    }
} 