@extends('layouts.app')
@section('title', __('Telegram Link'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">
        {{ __('Telegram Link') }}
    </h1>
</section>

<section class="content">
    {!! Form::open(['route' => 'telegram_template.telegramLinkUpdate', 'method' => 'post']) !!}

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

    {!! Form::close() !!}
</section>
@endsection