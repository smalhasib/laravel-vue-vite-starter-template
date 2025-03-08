<?php

namespace App\Providers;

use App\Models\Bot;
use App\Models\Source;
use App\Models\Document;
use App\Policies\BotPolicy;
use App\Policies\SourcePolicy;
use App\Policies\DocumentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Bot::class => BotPolicy::class,
        Source::class => SourcePolicy::class,
        Document::class => DocumentPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
