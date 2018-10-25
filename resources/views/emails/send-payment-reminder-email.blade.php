@component('mail::message')
    {{'Payment Reminder'}}

            @component('mail::table')
                {{'The Total amounts For Payment is '}}{{$body}}
            @endcomponent


            {{__('Thanks')}},
    {{ config('app.name') }} {{__('Team')}}
@endcomponent
