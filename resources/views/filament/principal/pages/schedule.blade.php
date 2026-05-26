<x-filament-panels::page>
    @php
        $tabs = [
            'classes'  => ['label' => 'Classes',  'icon' => 'heroicon-o-rectangle-stack'],
            'teachers' => ['label' => 'Teachers', 'icon' => 'heroicon-o-user-group'],
            'rooms'    => ['label' => 'Rooms',    'icon' => 'heroicon-o-building-office-2'],
        ];
    @endphp

    {{-- View toggle --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        @foreach ($tabs as $key => $tab)
            <button
                type="button"
                wire:click="setView('{{ $key }}')"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition
                       {{ $view === $key
                           ? 'bg-primary-600 text-white shadow'
                           : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' }}"
            >
                <x-dynamic-component :component="$tab['icon']" class="h-4 w-4" />
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Classes view --}}
    @if ($view === 'classes')
        @if ($classes->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                No timetable lessons yet. Publish a timetable from the Builder to populate this view.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($classes as $class)
                    @php $isActive = $selectedClass === $class->code; @endphp
                    <button
                        type="button"
                        wire:click="selectClass('{{ $class->code }}')"
                        class="text-left rounded-xl border bg-white p-4 transition hover:shadow-md
                               {{ $isActive ? 'ring-2 ring-primary-500 border-primary-300' : 'border-gray-200' }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="text-lg font-bold text-gray-900">{{ $class->code }}</div>
                            <div class="text-[10px] font-semibold uppercase text-gray-400">
                                {{ $class->weekly_hours }} jam
                            </div>
                        </div>

                        @if ($class->unit_head)
                            <div class="mt-2 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                Unit Head — {{ $class->unit_head->subject ?? '—' }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $class->unit_head->name }}</div>
                        @endif

                        <div class="mt-3 flex items-center -space-x-2">
                            @foreach ($class->avatars as $a)
                                @if (! empty($a['avatar']))
                                    <img
                                        src="{{ $a['avatar'] }}"
                                        alt="{{ $a['name'] }}"
                                        title="{{ $a['name'] }}"
                                        class="h-7 w-7 rounded-full border-2 border-white object-cover"
                                    >
                                @else
                                    <div
                                        title="{{ $a['name'] }}"
                                        class="h-7 w-7 rounded-full border-2 border-white bg-gray-200 text-[10px] font-semibold text-gray-600 flex items-center justify-center"
                                    >
                                        {{ $a['initials'] }}
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-3 flex items-center justify-between text-[11px] text-gray-500">
                            <span>{{ count($class->subjects) }} subjects</span>
                            <span>{{ $class->student_count }} students</span>
                        </div>
                    </button>
                @endforeach
            </div>

            @if ($selectedClass)
                <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-base font-semibold text-gray-900">
                            Timetable — {{ $selectedClass }}
                        </div>
                        <button
                            type="button"
                            wire:click="selectClass(null)"
                            class="text-xs text-gray-500 hover:text-gray-700"
                        >
                            Close
                        </button>
                    </div>
                    <x-timetable-grid
                        :lessons="$lessons"
                        :days="$days"
                        :periods="$periods"
                        :compact="true"
                    />
                </div>
            @endif
        @endif
    @endif

    {{-- Teachers view --}}
    @if ($view === 'teachers')
        @if ($teachers->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                No teacher assignments found in the active timetable.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($teachers as $t)
                    @php $isActive = $selectedTeacherCode === $t->code; @endphp
                    <button
                        type="button"
                        wire:click="selectTeacher('{{ $t->code }}')"
                        class="text-left rounded-xl border bg-white p-4 transition hover:shadow-md
                               {{ $isActive ? 'ring-2 ring-primary-500 border-primary-300' : 'border-gray-200' }}"
                    >
                        <div class="flex items-start gap-3">
                            @if (! empty($t->avatar))
                                <img src="{{ $t->avatar }}" alt="{{ $t->name }}"
                                     class="h-11 w-11 rounded-full object-cover ring-2 ring-white shadow-sm">
                            @else
                                <div class="h-11 w-11 rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold flex items-center justify-center">
                                    {{ $t->initials }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-gray-900 truncate">{{ $t->name }}</div>
                                <div class="text-[11px] text-gray-500 truncate">
                                    {{ $t->subject ?? '—' }}
                                </div>
                                @if ($t->is_unit_head)
                                    <div class="mt-1 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                        Unit Head{{ $t->unit_head_subject ? ' — '.$t->unit_head_subject : '' }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-md bg-gray-50 px-2 py-1.5">
                                <div class="text-gray-400 uppercase text-[10px]">Weekly</div>
                                <div class="font-semibold text-gray-900">{{ $t->lesson_count }} jam</div>
                            </div>
                            <div class="rounded-md bg-gray-50 px-2 py-1.5">
                                <div class="text-gray-400 uppercase text-[10px]">Classes</div>
                                <div class="font-semibold text-gray-900">{{ $t->class_count }}</div>
                            </div>
                        </div>

                        <div class="mt-3 text-right text-[11px] font-medium text-primary-600">
                            {{ $isActive ? 'Hide timetable ↑' : 'View timetable →' }}
                        </div>
                    </button>
                @endforeach
            </div>

            @if ($selectedTeacherCode && $selectedTeacher)
                <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white px-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if (! empty($selectedTeacher->avatar))
                                <img src="{{ $selectedTeacher->avatar }}" alt=""
                                     class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow-sm">
                            @else
                                <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold flex items-center justify-center">
                                    {{ $selectedTeacher->initials }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="text-base font-semibold text-gray-900 truncate">
                                    {{ $selectedTeacher->name }}
                                </div>
                                <div class="text-[11px] text-gray-500">
                                    {{ $selectedTeacher->subject ?? '—' }} ·
                                    {{ $selectedTeacher->lesson_count }} jam/week ·
                                    {{ $selectedTeacher->class_count }} classes
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="selectTeacher(null)"
                            class="text-xs text-gray-500 hover:text-gray-700 shrink-0"
                        >
                            Close
                        </button>
                    </div>
                    <div class="p-4">
                        <x-timetable-grid
                            :lessons="$selectedTeacherLessons"
                            :days="$days"
                            :periods="$periods"
                            :compact="true"
                        />
                    </div>
                </div>
            @endif
        @endif
    @endif

    {{-- Rooms view --}}
    @if ($view === 'rooms')
        @if ($rooms->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                No rooms are assigned to lessons yet.
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach ($rooms as $r)
                    @php $isActive = $selectedRoom === $r->room; @endphp
                    <button
                        type="button"
                        wire:click="selectRoom('{{ $r->room }}')"
                        class="rounded-xl border bg-white p-4 text-left transition hover:shadow-md
                               {{ $isActive ? 'ring-2 ring-primary-500 border-primary-300' : 'border-gray-200' }}"
                    >
                        <div class="text-base font-semibold text-gray-900">Room {{ $r->room }}</div>
                        <div class="mt-1 text-[11px] text-gray-500">
                            {{ $r->lesson_count }} lessons · {{ $r->class_count }} classes
                        </div>
                    </button>
                @endforeach
            </div>

            @if ($selectedRoom)
                <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-base font-semibold text-gray-900">
                            Room {{ $selectedRoom }} — schedule
                        </div>
                        <button
                            type="button"
                            wire:click="selectRoom(null)"
                            class="text-xs text-gray-500 hover:text-gray-700"
                        >
                            Close
                        </button>
                    </div>
                    <x-timetable-grid
                        :lessons="$selectedRoomLessons"
                        :days="$days"
                        :periods="$periods"
                        :compact="true"
                    />
                </div>
            @endif
        @endif
    @endif
</x-filament-panels::page>
