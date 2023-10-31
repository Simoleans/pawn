<x-filament-panels::page>
    {{-- {{dd($this)}} --}}
    @if (count($relationManagers = $this->getRelationManagers()))
        <x-filament-panels::resources.relation-managers
            :active-manager="$activeRelationManager"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        />

    @endif

    <x-filament::tabs />

    {{ $this->getTabs }}
</x-filament-panels::page>
