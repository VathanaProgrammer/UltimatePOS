@extends('layouts.app')
@section('title', __('Telegram Link'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">
        {{ __('Telegram Link') }}
    </h1>
</section>

<section class="content">
    {!! Form::open(['route' => 'telegramLinkUpdate', 'method' => 'post']) !!}
    <div class="shadow-md bg-white p-6 mt-6">
    <div class="form-group">
        {!! Form::label('telegram_link', __('Telegram Link')) !!}
        {!! Form::text('telegram_link', $telegramLink ?? null, [
            'class' => 'form-control',
            'placeholder' => 'https://t.me/your_channel'
        ]) !!}
    </div>

    <button type="submit" class="tw-dw-btn tw-dw-btn-error tw-text-white">
        {{ __('Save Changes') }}
    </button>
    </div>
    {!! Form::close() !!}
</section>
@endsection