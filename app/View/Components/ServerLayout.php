<?php

namespace App\View\Components;

use App\Models\Server;
use App\Server\Software;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class ServerLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Server $server, public string $title = '')
    {
    }

    public function href(NavigationItem $item): string
    {
        return $item->href($this->server);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $navigationItems = collect([
            new NavigationItem(__('Overview'), route('servers.show', $this->server), 'heroicon-o-server'),
            $this->server->softwareIsInstalled(Software::Caddy2)
                ? new NavigationItem(__('Sites'), 'servers.sites', 'heroicon-o-list-bullet')
                : null,
            new NavigationItem(__('Databases'), 'servers.databases', 'heroicon-o-circle-stack'),
            $this->server->softwareIsInstalled(Software::Caddy2)
                ? new NavigationItem(__('Cronjobs'), 'servers.crons', 'heroicon-o-clock')
                : null,
            $this->server->softwareIsInstalled(Software::Caddy2)
                ? new NavigationItem(__('Daemons'), 'servers.daemons', 'heroicon-o-arrow-path')
                : null,
            new NavigationItem(__('Firewall Rules'), 'servers.firewall-rules', 'heroicon-o-shield-check'),
            new NavigationItem(__('Backups'), 'servers.backups', 'heroicon-o-rectangle-stack'),
            new NavigationItem(__('Software'), 'servers.software', 'heroicon-o-code-bracket'),
            new NavigationItem(__('Files'), 'servers.files', 'heroicon-o-document-text'),
            new NavigationItem(__('Logs'), 'servers.logs', 'heroicon-o-book-open'),
        ])->filter()->toArray();

        return view('components.server-layout', [
            'server' => $this->server,
            'navigationItems' => $navigationItems,
        ]);
    }
}
