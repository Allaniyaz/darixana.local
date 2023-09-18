<x-filament::page>
@foreach($this->records() as $record)

<div>
    <ul>
        <li>{{$record['id']}} - {{ $record['title'] }}</li>
    </ul>
</div>

@endforeach

</x-filament::page>
