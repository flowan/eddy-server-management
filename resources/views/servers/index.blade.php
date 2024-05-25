@seoTitle(__('Servers'))

<x-app-layout>
    <x-slot:header>
        {{ __('Servers') }}
    </x-slot>

    <x-slot:description>
        {{ __('Manage your servers.') }}
    </x-slot>

    @if ($servers->isNotEmpty())
        <x-slot:actions>
            <x-splade-button type="link" modal href="{{ route('servers.create') }}">
                {{ __('New Server') }}
            </x-splade-button>
        </x-slot>
    @endif

    <x-splade-table :for="$servers">
        <x-splade-cell type>
            @svg($item->type->getIcon(), 'mr-3 w-6 h-6 text-gray-700') {{ $item->type->getDisplayName() }}
        </x-splade-cell>
        <x-splade-cell status>
            <p class="space-x-2">
                <span>{{ $item->status_name }}</span>
            </p>
        </x-splade-cell>

        <x-slot:empty-state>
            <x-empty-state modal :href="route('servers.create')">
                {{ __('New Server') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-app-layout>
