@extends('layouts.app')
@section('title', __('Telegram Link'))

@section('content')
<section class="content-header tw-py-4">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        {{ __('Telegram Link') }}
    </h1>
</section>

<section class="content">
    {!! Form::open(['route' => 'telegramLinkUpdate', 'method' => 'post']) !!}

    <div class="tw-max-w-lg tw-mx-auto tw-mt-6">
        <div class="tw-bg-white tw-shadow-md tw-p-6 tw-rounded">
            <div class="form-group">
                {!! Form::label('telegram_link', __('Telegram Link'), ['class' => 'tw-font-medium']) !!}
                {!! Form::text('telegram_link', $telegram_link ?? null, [
                    'class' => 'form-control tw-mt-2',
                    'placeholder' => 'https://t.me/your_channel'
                ]) !!}
            </div>

            <button type="submit"
                class="tw-mt-4 tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded hover:tw-bg-red-700">
                {{ __('Save Changes') }}
            </button>
        </div>
    </div>

    {!! Form::close() !!}
</section>
@endsection