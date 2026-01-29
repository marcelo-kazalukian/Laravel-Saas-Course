<x-layouts.app :title="__('Tasks')">
    <div class="p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">{{ __('Tasks') }}</flux:heading>

            @can('create', App\Models\Task::class)
                <flux:button variant="primary" :href="route('tasks.create')" icon="plus">
                    {{ __('New Task') }}
                </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Image') }}</flux:table.column>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Assigned To') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    <flux:table.row>
                        @forelse ($tasks as $task)
                            <flux:table.cell>
                                @if($task->getFirstMedia('images'))
                                    <img
                                        src="{{ $task->getFirstMediaUrl('images', 'thumb') }}"
                                        alt="{{ $task->name }}"
                                        class="h-10 w-10 rounded-lg object-cover"
                                    />
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="photo" class="h-5 w-5 text-zinc-400" />
                                    </div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $task->name }}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $task->assignedToUser?->name ?? __('Unassigned') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center justify-end gap-2">
                                    @can('view', $task)
                                        <flux:button
                                            :href="route('tasks.show', $task)"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                        >
                                            {{ __('View') }}
                                        </flux:button>
                                    @endcan
                                    @can('update', $task)
                                        <flux:button
                                            :href="route('tasks.edit', $task)"
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil"
                                        >
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan
                                    @can('delete', $task)
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button
                                                type="submit"
                                                variant="danger"
                                                size="sm"
                                                icon="trash"
                                                onclick="return confirm('Are you sure you want to delete this task?')"
                                            >
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </form>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <flux:text>
                                        {{ __('No tasks yet. Create your first task to get started.') }}
                                    </flux:text>
                                </td>
                            </tr>
                        @endforelse                       
                    </flux:table.row>                
                </flux:table.rows>
            </flux:table>           
        </div>
    </div>
</x-layouts.app>
