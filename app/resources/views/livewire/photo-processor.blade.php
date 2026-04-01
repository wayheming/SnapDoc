<div>
@if($step === 1)
    @foreach($formats as $country => $countryFormats)
        @foreach($countryFormats as $format)
            {{ $format->name }}
        @endforeach
    @endforeach
@endif
@if($step === 2)Processing...@endif
</div>
