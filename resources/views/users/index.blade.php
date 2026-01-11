<x-layouts.app :title="__('Users')">
    <div class="p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">{{ __('Team Members') }}</flux:heading>

            <flux:button variant="primary" :href="route('users.create')" icon="user-plus">
                {{ __('Invite User') }}
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div>
            <flux:table class="min-w-full">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>                    
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($users as $user)
                        <flux:table.row>                     
                            <flux:table.cell>
                                {{ $user->name }}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $user->email }}
                            </flux:table.cell>
                            <flux:table.cell class="text-end">
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button
                                        type="submit"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        onclick="return confirm('Are you sure you want to remove this user?')"
                                    >
                                        {{ __('Remove') }}
                                    </flux:button>
                                </form>
                            </flux:table.cell>
                        </flux:table.row>                   
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center">
                                <flux:text>
                                    {{ __('No users yet. Invite your first teammate to get started.') }}
                                </flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($invitations->isNotEmpty())
            <div class="space-y-4">
                <flux:heading size="lg">{{ __('Pending Invitations') }}</flux:heading>

                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-start text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Name') }}
                                </th>
                                <th class="px-6 py-3 text-start text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Email') }}
                                </th>
                                <th class="px-6 py-3 text-start text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Sent') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            @foreach ($invitations as $invitation)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $invitation->name ?? __('Pending invite') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $invitation->email }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $invitation->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
