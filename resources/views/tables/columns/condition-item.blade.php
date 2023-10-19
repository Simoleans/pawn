<div>
    @php
        $state = match ($getState()) {
             'NEW'=> 'Nuevo',
             'USED'=> 'Usado',
             'DAMAGE'=> 'Dañado',
        };
    @endphp

    {{ $state }}
</div>
